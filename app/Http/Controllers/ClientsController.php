<?php

namespace App\Http\Controllers;

use App\Models\clients;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $selectedBranchId = $this->selectedBranchFilter($request, $agencyId);

        $clients = clients::with('branch')
            ->where('agency_id', $agencyId)
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->latest()
            ->get();

        return view('clients.index', compact('clients', 'branches', 'selectedBranchId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agencyId = $this->requiredAgencyId();
        $request->merge(['normalized_phone' => clients::normalizePhone($request->phone)]);
        $branches = $this->branchOptions($agencyId);

        return view('clients.create', compact('branches'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $this->enforceAgencyPlanLimit(
            'clients',
            clients::where('agency_id', $agencyId)->count(),
            'clients'
        );

        $request->validate([

            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')->where(fn ($query) => $query->where('agency_id', $agencyId)),
            ],
            'phone' => 'required|string|max:30',
            'normalized_phone' => [
                'required',
                Rule::unique('clients', 'normalized_phone')->where(fn ($query) => $query->where('agency_id', $agencyId)),
            ],
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],

        ]);

        $client = clients::create([
            'branch_id' => $request->integer('branch_id') ?: null,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'normalized_phone' => $request->normalized_phone,
            'name' => $request->name,
            'agency_id' => $agencyId,
        ]);

        $this->logActivity('client.created', $client);

        return redirect()->route('clients.index')
            ->with('success', 'Client créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(clients $client)
    {
        $this->requireSameAgency($client);
        $client->load('branch');

        return view('clients.show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(clients $client)
    {
        $this->requireSameAgency($client);
        $branches = $this->branchOptions($this->requiredAgencyId(), false);

        return view('clients.edit', compact('client', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, clients $client)
    {
        $this->requireSameAgency($client);
        $agencyId = $this->requiredAgencyId();
        $request->merge(['normalized_phone' => clients::normalizePhone($request->phone)]);

        $request->validate([

            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('clients', 'email')
                    ->where(fn ($query) => $query->where('agency_id', $agencyId))
                    ->ignore($client->id),
            ],
            'phone' => 'required|string|max:30',
            'normalized_phone' => [
                'required',
                Rule::unique('clients', 'normalized_phone')
                    ->where(fn ($query) => $query->where('agency_id', $agencyId))
                    ->ignore($client->id),
            ],
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],

        ]);

        $before = $client->only(['name', 'email', 'phone_number', 'normalized_phone', 'branch_id']);

        $client->update([

            'branch_id' => $request->integer('branch_id') ?: null,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'normalized_phone' => $request->normalized_phone,
            'name' => $request->name,

        ]);

        $changes = $this->activityChanges($before, $client->only(['name', 'email', 'phone_number', 'normalized_phone', 'branch_id']));

        if ($changes) {
            $this->logActivity('client.updated', $client, $changes);
        }

        return redirect()->route('clients.index')
            ->with('success', 'Client mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(clients $client)
    {
        $this->requireSameAgency($client);

        $this->logActivity(
            'client.deleted',
            $client,
            metadata: $client->only(['name', 'email', 'phone_number', 'branch_id'])
        );

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client archivé avec succès.');

    }
}
