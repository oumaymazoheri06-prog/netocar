@props(['maxWidth' => 'max-w-lg', 'heading' => null, 'subheading' => null])

<div class="grid gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <flux:navlist aria-label="{{ __('Settings') }}">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('user-password.edit')" wire:navigate>{{ __('Password') }}</flux:navlist.item>
            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <flux:navlist.item :href="route('two-factor.show')" wire:navigate>{{ __('Two-Factor Auth') }}</flux:navlist.item>
            @endif
            @if (auth()->user()?->role === 'manager' && auth()->user()?->agency)
                <flux:navlist.item :href="route('agency-billing.edit')" wire:navigate>{{ __('Agency & Billing') }}</flux:navlist.item>
            @endif
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
        <div class="w-full {{ $maxWidth }}">
            @if ($heading || $subheading)
                <div class="mb-6">
                    @if ($heading)
                        <flux:heading size="xl">{{ $heading }}</flux:heading>
                    @endif

                    @if ($subheading)
                        <flux:text class="mt-2">{{ $subheading }}</flux:text>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
