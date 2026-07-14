<?php

namespace App\Http\Controllers;

use App\Models\employees;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmployeesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId, false);
        $selectedBranchId = $this->selectedBranchFilter($request, $agencyId);

        $employees = employees::with(['user', 'branch'])
            ->where('agency_id', $agencyId)
            ->when($selectedBranchId, fn ($query) => $query->where('branch_id', $selectedBranchId))
            ->latest()
            ->get();

        return view('employees.index', compact('employees', 'branches', 'selectedBranchId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $agencyId = $this->requiredAgencyId();
        $branches = $this->branchOptions($agencyId);

        return view('employees.create', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $agencyId = $this->requiredAgencyId();
        $createStaffAccount = $request->boolean('create_staff_account');

        $this->enforceAgencyPlanLimit(
            'employees',
            employees::where('agency_id', $agencyId)->count(),
            'employees'
        );

        $emailRules = [
            'required',
            'email',
            Rule::unique('employees', 'email')->where(fn ($query) => $query->where('agency_id', $agencyId)),
        ];

        if ($createStaffAccount) {
            $emailRules[] = Rule::unique('users', 'email');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone_number' => 'required|string|max:30',
            'job_title' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
            'create_staff_account' => 'nullable|boolean',
            'password' => [$createStaffAccount ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ]);

        $employee = null;

        DB::transaction(function () use ($request, $agencyId, $createStaffAccount, &$employee) {
            $user = null;

            if ($createStaffAccount) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'role' => 'staff',
                    'agency_id' => $agencyId,
                ]);
            }

            $employee = employees::create([
                'user_id' => $user?->id,
                'branch_id' => $request->integer('branch_id') ?: null,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'name' => $request->name,
                'job_title' => $request->job_title,
                'salary' => $request->salary,
                'agency_id' => $agencyId,
            ]);
        });

        if ($employee) {
            $this->logActivity(
                $employee->user_id ? 'employee.created_with_staff_account' : 'employee.created',
                $employee,
                metadata: ['staff_account_created' => (bool) $employee->user_id]
            );
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(employees $employee)
    {
        $this->requireSameAgency($employee);
        $employee->load(['user', 'branch']);

        return view('employees.show', ['employees' => $employee]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(employees $employee)
    {
        $this->requireSameAgency($employee);
        $employee->load(['user', 'branch']);
        $branches = $this->branchOptions($this->requiredAgencyId(), false);

        return view('employees.edit', ['employees' => $employee, 'branches' => $branches]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, employees $employee)
    {
        $this->requireSameAgency($employee);
        $employee->load('user');

        $agencyId = $this->requiredAgencyId();
        $createStaffAccount = $request->boolean('create_staff_account') && ! $employee->user;

        $emailRules = [
            'required',
            'email',
            Rule::unique('employees', 'email')
                ->where(fn ($query) => $query->where('agency_id', $agencyId))
                ->ignore($employee->id),
        ];

        if ($employee->user) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($employee->user->id);
        } elseif ($createStaffAccount) {
            $emailRules[] = Rule::unique('users', 'email');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone_number' => 'required|string|max:30',
            'job_title' => 'required|string|max:255',
            'salary' => 'required|numeric|min:0',
            'branch_id' => ['nullable', $this->activeBranchRule($agencyId)],
            'create_staff_account' => 'nullable|boolean',
            'password' => [$createStaffAccount ? 'required' : 'nullable', 'confirmed', Password::defaults()],
        ]);

        $before = $employee->only(['name', 'email', 'phone_number', 'job_title', 'salary', 'user_id', 'branch_id']);
        $staffAccountCreated = false;
        $passwordChanged = false;

        DB::transaction(function () use ($request, $employee, $agencyId, $createStaffAccount, &$staffAccountCreated, &$passwordChanged) {
            $user = $employee->user;

            if (! $user && $createStaffAccount) {
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => $request->password,
                    'role' => 'staff',
                    'agency_id' => $agencyId,
                ]);
                $staffAccountCreated = true;
            } elseif ($user) {
                $user->update([
                    'name' => $request->name,
                    'email' => $request->email,
                    'role' => 'staff',
                    'agency_id' => $agencyId,
                    ...($request->filled('password') ? ['password' => $request->password] : []),
                ]);
                $passwordChanged = $request->filled('password');
            }

            $employee->update([
                'user_id' => $user?->id,
                'branch_id' => $request->integer('branch_id') ?: null,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'name' => $request->name,
                'job_title' => $request->job_title,
                'salary' => $request->salary,
            ]);
        });

        $changes = $this->activityChanges($before, $employee->only(['name', 'email', 'phone_number', 'job_title', 'salary', 'user_id', 'branch_id']));

        if ($changes || $passwordChanged) {
            $this->logActivity(
                'employee.updated',
                $employee,
                $changes,
                metadata: [
                    'staff_account_created' => $staffAccountCreated,
                    'password_changed' => $passwordChanged,
                ]
            );
        }

        return redirect()->route('employees.index')
            ->with('success', 'Employé mis à jour avec succès.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(employees $employee)
    {
        $this->requireSameAgency($employee);
        $employee->load('user');

        $this->logActivity(
            'employee.deleted',
            $employee,
            metadata: [
                ...$employee->only(['name', 'email', 'phone_number', 'job_title', 'salary', 'branch_id']),
                'had_staff_account' => (bool) $employee->user,
            ]
        );

        DB::transaction(function () use ($employee) {
            $user = $employee->user;

            $employee->delete();

            if ($user?->role === 'staff') {
                $user->delete();
            }
        });

        return redirect()->route('employees.index')
            ->with('success', 'Employé archivé et accès staff désactivé avec succès.');
    }
}
