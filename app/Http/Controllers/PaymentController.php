<?php

namespace App\Http\Controllers;

use App\Models\agencies;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('agency')
            ->when(! $this->userIsAdmin(), fn ($query) => $query->where('agency_id', $this->requiredAgencyId()))
            ->latest()
            ->get();

        return view('payments.index', compact('payments'));
    }

    public function createCashpluss()
    {
        $agency = auth()->user()->agency;

        $this->requiredAgencyId();

        return view('payments.cashpluss', compact('agency'));
    }

    public function createVirement()
    {
        $agency = auth()->user()->agency;

        $this->requiredAgencyId();

        return view('payments.virement', compact('agency'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_method' => ['required', Rule::in(['cashpluss', 'virement'])],
            'receipt_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $agency = auth()->user()->agency;

        if (! $agency) {
            abort(403, 'Aucune agence n’est liée à ce compte.');
        }

        $receiptPath = $request->file('receipt_photo')->store('payments/receipts');

        try {
            $payment = DB::transaction(function () use ($agency, $validated, $receiptPath) {
                $lockedAgency = agencies::whereKey($agency->id)->lockForUpdate()->firstOrFail();

                if (Payment::where('agency_id', $lockedAgency->id)->where('status', 'pending')->exists()) {
                    throw ValidationException::withMessages([
                        'payment_method' => 'Une demande de paiement est déjà en attente de validation.',
                    ]);
                }

                return Payment::create([
                    'agency_id' => $lockedAgency->id,
                    'plan' => $lockedAgency->package,
                    'billing_period' => 'yearly',
                    'amount' => $lockedAgency->plan_amount,
                    'status' => 'pending',
                    'payment_method' => $validated['payment_method'],
                    'receipt_photo' => $receiptPath,
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($receiptPath);

            throw $exception;
        }

        $this->logActivity(
            'payment.submitted',
            $payment,
            metadata: [
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
            ],
        );

        return redirect()
            ->route('payments.index')
            ->with('success', 'Paiement envoyé avec succès.');
    }

    public function show(Payment $payment)
    {
        $this->requireVisibleAgencyRecord($payment);
        $payment->load('agency');

        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        $this->requireAdminUser();
        $this->requireVisibleAgencyRecord($payment);
        $payment->load('agency');

        return view('payments.edit', compact('payment'));
    }

    public function receipt(Payment $payment)
    {
        $this->requireVisibleAgencyRecord($payment);
        abort_unless($payment->receipt_photo, 404);

        if (Storage::disk('local')->exists($payment->receipt_photo)) {
            return Storage::disk('local')->download($payment->receipt_photo, null, $this->secureDownloadHeaders());
        }

        abort_unless(Storage::disk('public')->exists($payment->receipt_photo), 404);

        return Storage::disk('public')->download($payment->receipt_photo, null, $this->secureDownloadHeaders());
    }

    public function update(Request $request, Payment $payment)
    {
        $this->requireAdminUser();
        $this->requireVisibleAgencyRecord($payment);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'completed', 'failed'])],
        ]);

        $oldStatus = $payment->status;

        if ($oldStatus === $validated['status'] && $oldStatus !== 'pending') {
            return redirect()->route('payments.index')->with('success', 'Ce paiement est déjà traité.');
        }

        if ($oldStatus !== $validated['status'] && $oldStatus !== 'pending') {
            throw ValidationException::withMessages([
                'status' => 'Un paiement déjà traité ne peut plus changer de statut.',
            ]);
        }

        $changed = false;

        DB::transaction(function () use ($payment, $validated, &$changed) {
            $lockedPayment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($lockedPayment->status !== 'pending') {
                if ($lockedPayment->status === $validated['status']) {
                    return;
                }

                throw ValidationException::withMessages(['status' => 'Ce paiement a déjà été traité.']);
            }

            $lockedPayment->update([
                'status' => $validated['status'],
                'processed_at' => $validated['status'] === 'pending' ? null : now(),
                'reviewed_by' => $validated['status'] === 'pending' ? null : auth()->id(),
            ]);
            $changed = $validated['status'] !== 'pending';

            if ($validated['status'] === 'completed') {
                $this->renewAgencyLicense($lockedPayment);
            }
        });

        $payment->refresh();

        if ($changed && $oldStatus !== $payment->status) {
            $this->logActivity(
                'payment.status_changed',
                $payment,
                [
                    'status' => [
                        'from' => $oldStatus,
                        'to' => $payment->status,
                    ],
                ],
                subjectLabel: "Paiement n°{$payment->id}",
            );
        }

        return redirect()
            ->route('payments.index')
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function destroy(Payment $payment)
    {
        $this->requireAdminUser();
        $this->requireVisibleAgencyRecord($payment);

        if ($payment->status === 'completed') {
            return back()->with('error', 'Un paiement validé ne peut pas être supprimé.');
        }

        $this->logActivity(
            'payment.deleted',
            $payment,
            metadata: [
                'amount' => $payment->amount,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
            ],
            subjectLabel: "Paiement n°{$payment->id}",
        );

        if ($payment->receipt_photo) {
            Storage::disk('local')->delete($payment->receipt_photo);
            Storage::disk('public')->delete($payment->receipt_photo);
        }

        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }

    private function renewAgencyLicense(Payment $payment): void
    {
        $payment->loadMissing('agency');
        $agency = $payment->agency;

        if (! $agency) {
            return;
        }

        $before = $agency->only([
            'package',
            'license_status',
            'license_expires_at',
            'activated_at',
        ]);

        $startsAt = $agency->license_expires_at && $agency->license_expires_at->copy()->endOfDay()->isFuture()
            ? $agency->license_expires_at->copy()->addDay()
            : now()->startOfDay();
        $endsAt = $payment->billing_period === 'monthly'
            ? $startsAt->copy()->addMonthNoOverflow()
            : $startsAt->copy()->addYearNoOverflow();

        $agency->forceFill([
            'package' => $payment->plan ?: $agency->package,
            'license_status' => 'active',
            'license_expires_at' => $endsAt->toDateString(),
            'activated_at' => $agency->activated_at ?: now(),
        ])->save();

        $payment->forceFill([
            'period_starts_at' => $startsAt->toDateString(),
            'period_ends_at' => $endsAt->toDateString(),
        ])->save();

        $changes = $this->activityChanges($before, $agency->only([
            'package',
            'license_status',
            'license_expires_at',
            'activated_at',
        ]));

        if ($changes) {
            $this->logActivity(
                'agency.license_renewed',
                $agency,
                $changes,
                metadata: [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                ],
            );
        }
    }

    private function secureDownloadHeaders(): array
    {
        return [
            'Content-Type' => 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'",
        ];
    }
}
