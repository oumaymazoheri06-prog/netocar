<?php

namespace App\Http\Controllers;

use App\Models\agencies;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AgenciesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $agencies = agencies::all();

        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('agencies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email',
            'phone_number' => 'required|string|max:13',
            'package' => 'required|in:basic,standard,premium',
            'address' => 'required|string|max:255',
            'license_status' => 'required|in:active,suspended',
            'license_expires_at' => 'required|date',
            'manager_name' => 'required|string|max:255',
            'manager_email' => 'required|email|unique:users,email',
            'manager_password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $agency = DB::transaction(function () use ($request) {
            $agency = agencies::create([
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'name' => $request->name,
                'email' => $request->email,
                'package' => $request->package,
                'license_status' => $request->license_status,
                'license_expires_at' => $request->license_expires_at,
                'activated_at' => $request->license_status === 'active' ? now() : null,
            ]);

            User::create([
                'name' => $request->manager_name,
                'email' => $request->manager_email,
                'password' => $request->manager_password,
                'role' => 'manager',
                'agency_id' => $agency->id,
            ]);

            return $agency;
        });

        $this->logActivity('agency.created', $agency);

        return redirect()->route('agencies.index')
            ->with('success', 'Agence créée avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(agencies $agency)
    {
        return view('agencies.show', compact('agency'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(agencies $agency)
    {
        return view('agencies.edit', compact('agency'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, agencies $agency)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agencies,email,'.$agency->id,
            'phone_number' => 'required|string|max:13',
            'package' => 'required|in:basic,standard,premium',
            'address' => 'required|string|max:255',
            'license_status' => 'required|in:active,suspended',
            'license_expires_at' => 'required|date',
        ]);

        $before = $agency->only([
            'name',
            'email',
            'phone_number',
            'package',
            'address',
            'license_status',
            'license_expires_at',
            'activated_at',
        ]);

        $agency->update([
            'address' => $request->address,
            'phone_number' => $request->phone_number,
            'name' => $request->name,
            'email' => $request->email,
            'package' => $request->package,
            'license_status' => $request->license_status,
            'license_expires_at' => $request->license_expires_at,
            'activated_at' => $request->license_status === 'active' && ! $agency->activated_at
                ? now()
                : $agency->activated_at,
        ]);

        $changes = $this->activityChanges($before, $agency->only([
            'name',
            'email',
            'phone_number',
            'package',
            'address',
            'license_status',
            'license_expires_at',
            'activated_at',
        ]));

        if ($changes) {
            $this->logActivity('agency.updated', $agency, $changes);
        }

        return redirect()->route('agencies.index')
            ->with('success', 'Agence mise à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(agencies $agency)
    {
        $this->logActivity(
            'agency.deleted',
            $agency,
            metadata: $agency->only(['name', 'email', 'phone_number', 'package', 'address', 'license_status', 'license_expires_at'])
        );

        $agency->delete();

        return redirect()->route('agencies.index')
            ->with('success', 'Agence archivée avec succès.');
    }
}
