<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BranchesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $status = $request->string('status')->toString();

        $query = Branch::withCount(['clients', 'employees', 'services', 'reservations', 'tickets'])
            ->where('agency_id', $agencyId);

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $branches = $query
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('branches.index', [
            'branches' => $branches,
            'activeCount' => Branch::where('agency_id', $agencyId)->where('is_active', true)->count(),
            'inactiveCount' => Branch::where('agency_id', $agencyId)->where('is_active', false)->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->requiredAgencyId();

        return view('branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $branch = Branch::create($this->validatedAttributes($request, $agencyId));

        $this->logActivity('branch.created', $branch);

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branche créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        $this->requireSameAgency($branch);

        $branch->loadCount(['clients', 'employees', 'services', 'reservations', 'tickets']);

        return view('branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        $this->requireSameAgency($branch);

        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $this->requireSameAgency($branch);

        $before = $branch->only(['name', 'code', 'address', 'phone_number', 'opening_time', 'closing_time', 'simultaneous_capacity', 'is_active']);

        $branch->update($this->validatedAttributes($request, $branch->agency_id, $branch));

        $changes = $this->activityChanges($before, $branch->only(['name', 'code', 'address', 'phone_number', 'opening_time', 'closing_time', 'simultaneous_capacity', 'is_active']));

        if ($changes) {
            $this->logActivity('branch.updated', $branch, $changes);
        }

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branche mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        $this->requireSameAgency($branch);
        $branch->loadCount(['clients', 'employees', 'services', 'reservations', 'tickets']);

        if (
            $branch->clients_count
            || $branch->employees_count
            || $branch->services_count
            || $branch->reservations_count
            || $branch->tickets_count
        ) {
            return back()->with('error', 'Cette branche contient des données liées. Désactivez-la plutôt que de la supprimer.');
        }

        $this->logActivity(
            'branch.deleted',
            $branch,
            metadata: $branch->only(['name', 'code', 'address', 'phone_number', 'is_active'])
        );

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('success', 'Branche archivée avec succès.');
    }

    private function validatedAttributes(Request $request, int $agencyId, ?Branch $branch = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('branches', 'name')
                    ->where(fn ($query) => $query->where('agency_id', $agencyId)->whereNull('deleted_at'))
                    ->ignore($branch?->id),
            ],
            'code' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('branches', 'code')
                    ->where(fn ($query) => $query->where('agency_id', $agencyId)->whereNull('deleted_at'))
                    ->ignore($branch?->id),
            ],
            'address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i', 'after:opening_time'],
            'simultaneous_capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'agency_id' => $agencyId,
            'name' => $validated['name'],
            'code' => filled($validated['code'] ?? null) ? Str::upper($validated['code']) : null,
            'address' => $validated['address'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'opening_time' => $validated['opening_time'] ?? $branch?->opening_time ?? '08:00',
            'closing_time' => $validated['closing_time'] ?? $branch?->closing_time ?? '18:00',
            'simultaneous_capacity' => $validated['simultaneous_capacity'] ?? $branch?->simultaneous_capacity ?? 1,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
