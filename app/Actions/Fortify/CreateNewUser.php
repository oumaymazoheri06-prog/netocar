<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\agencies;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'agency_name' => ['required', 'string', 'max:255'],
            'agency_phone' => ['required', 'string', 'max:30'],
            'agency_address' => ['required', 'string', 'max:255'],
            'package' => ['required', Rule::in(array_keys(config('netocar.plans')))],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'manager',
            ]);

            $agency = agencies::create([
                'name' => $input['agency_name'],
                'email' => $input['email'],
                'phone_number' => $input['agency_phone'],
                'address' => $input['agency_address'],
                'package' => $input['package'],
                'license_status' => 'suspended',
                'license_expires_at' => null,
                'activated_at' => null,
            ]);

            $user->update(['agency_id' => $agency->id]);

            return $user->refresh();
        });
    }
}
