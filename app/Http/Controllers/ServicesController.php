<?php

namespace App\Http\Controllers;

use App\Models\services;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $selectedBranchId = $this->selectedBranchFilter($request, $agencyId);

        $services = services::with('branch')
            ->where('agency_id', $agencyId)
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->latest()
            ->get();

        return view('services.index', compact('services', 'branches', 'selectedBranchId'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId);

        return view('services.create', compact('branches'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $this->enforceAgencyPlanLimit(
            'services',
            services::where('agency_id', $agencyId)->count(),
            'services'
        );

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('services', 'name')->where(fn ($query) => $query
                    ->where('agency_id', $agencyId)
                    ->where('branch_id', $request->integer('branch_id') ?: null)
                    ->whereNull('deleted_at')),
            ],
            'description' => 'required|string|max:555',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('services', 'public');
        }

        $service = services::create([
            'name' => $request->name,
            'branch_id' => $request->integer('branch_id') ?: null,
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'photo' => $photoPath,
            'agency_id' => $agencyId,
        ]);

        $this->logActivity('service.created', $service);

        return redirect()->route('services.index')->with('success', 'Service créé avec succès.');

    }

    /**
     * Display the specified resource.
     */
    public function show(services $service)
    {
        $this->requireSameAgency($service);
        $service->load('branch');

        return view('services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(services $service)
    {
        $this->requireSameAgency($service);
        $branches = $this->branchOptions($this->requiredAgencyId(), false);

        return view('services.edit', compact('service', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, services $service)
    {
        $this->requireSameAgency($service);
        $agencyId = $this->requiredAgencyId();

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('services', 'name')->where(fn ($query) => $query
                    ->where('agency_id', $agencyId)
                    ->where('branch_id', $request->integer('branch_id') ?: null)
                    ->whereNull('deleted_at'))->ignore($service->id),
            ],
            'description' => 'required|string|max:555',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
        ]);

        $before = $service->only(['name', 'description', 'price', 'duration_minutes', 'photo', 'branch_id']);
        $photoPath = $service->photo;

        if ($request->hasFile('photo')) {
            if ($service->photo) {
                Storage::disk('public')->delete($service->photo);
            }

            $photoPath = $request->file('photo')->store('services', 'public');
        }

        $service->update([
            'name' => $request->name,
            'branch_id' => $request->integer('branch_id') ?: null,
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'photo' => $photoPath,
        ]);

        $changes = $this->activityChanges($before, $service->only(['name', 'description', 'price', 'duration_minutes', 'photo', 'branch_id']));

        if ($changes) {
            $this->logActivity('service.updated', $service, $changes);
        }

        return redirect()->route('services.index')->with('success', 'Service mis à jour avec succès.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(services $service)
    {
        $this->requireSameAgency($service);

        $this->logActivity(
            'service.deleted',
            $service,
            metadata: $service->only(['name', 'description', 'price', 'duration_minutes', 'photo', 'branch_id'])
        );

        $service->delete();

        return redirect()->route('services.index')
            ->with('success', 'Service archivé avec succès.');
    }
}
