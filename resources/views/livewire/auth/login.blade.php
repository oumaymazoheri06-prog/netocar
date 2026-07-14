<x-layouts::auth :title="__('Log in')">
    <div class="auth-panel-stack flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        @if (config('netocar.demo.enabled'))
            <div class="rounded-2xl border border-teal-200 bg-teal-50 p-4 text-sm text-teal-900 dark:border-teal-400/20 dark:bg-teal-400/10 dark:text-teal-100">
                <p class="font-black">Acces demo public</p>
                <div class="mt-3 grid gap-2 text-xs font-semibold sm:grid-cols-2">
                    <div class="rounded-xl bg-white/70 p-3 dark:bg-slate-950/50">
                        <span class="block text-teal-700 dark:text-teal-200">Manager</span>
                        <span class="mt-1 block break-all text-slate-700 dark:text-slate-200">{{ config('netocar.demo.manager_email') }}</span>
                    </div>
                    <div class="rounded-xl bg-white/70 p-3 dark:bg-slate-950/50">
                        <span class="block text-teal-700 dark:text-teal-200">Staff</span>
                        <span class="mt-1 block break-all text-slate-700 dark:text-slate-200">{{ config('netocar.demo.staff_email') }}</span>
                    </div>
                </div>
                <p class="mt-3 text-xs font-semibold text-teal-800 dark:text-teal-100">
                    Mot de passe : <span class="font-black">{{ config('netocar.demo.password') }}</span>
                </p>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form-stack flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="auth-link absolute top-0 text-sm end-0" :href="route('password.request')">
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="auth-primary-button w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 text-center text-sm text-slate-600 rtl:space-x-reverse dark:text-slate-400">
                <span>{{ __('Don\'t have an account?') }}</span>
                <flux:link class="auth-link" :href="route('register')">{{ __('Sign up') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
