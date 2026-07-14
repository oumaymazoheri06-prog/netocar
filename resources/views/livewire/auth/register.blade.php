<x-layouts::auth :title="__('Register')">
    <div class="auth-panel-stack flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="auth-form-stack flex flex-col gap-5">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Name -->
                <flux:input
                    name="name"
                    :label="__('Name')"
                    :value="old('name')"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="__('Full name')"
                />

                <!-- Email Address -->
                <flux:input
                    name="email"
                    :label="__('Email address')"
                    :value="old('email')"
                    type="email"
                    required
                    autocomplete="email"
                    placeholder="email@example.com"
                />
            </div>

            <flux:input
                name="agency_name"
                label="Nom de l'agence"
                :value="old('agency_name')"
                type="text"
                required
                autocomplete="organization"
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input
                    name="agency_phone"
                    label="Telephone de l'agence"
                    :value="old('agency_phone')"
                    type="tel"
                    required
                    autocomplete="tel"
                />

                <flux:input
                    name="agency_address"
                    label="Adresse de l'agence"
                    :value="old('agency_address')"
                    type="text"
                    required
                    autocomplete="street-address"
                />
            </div>

            <flux:select name="package" label="Plan" required>
                @foreach (config('netocar.plans') as $key => $plan)
                    <flux:select.option :value="$key" :selected="old('package', 'basic') === $key">
                        {{ $plan['label'] }} - {{ number_format($plan['price_yearly_mad']) }} MAD/an
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="grid gap-4 sm:grid-cols-2">
                <!-- Password -->
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Password')"
                    viewable
                />

                <!-- Confirm Password -->
                <flux:input
                    name="password_confirmation"
                    :label="__('Confirm password')"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="__('Confirm password')"
                    viewable
                />
            </div>

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="auth-primary-button w-full" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 text-center text-sm text-slate-600 rtl:space-x-reverse dark:text-slate-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link class="auth-link" :href="route('login')">{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
