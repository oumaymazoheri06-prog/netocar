<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="app-body-shell min-h-screen text-slate-900 dark:text-slate-100">
        <flux:sidebar sticky collapsible="mobile" class="app-sidebar-shell border-e border-slate-300 bg-slate-50 text-slate-900 shadow-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            @php
                $user = auth()->user();
                $agency = $user?->agency;
                $role = $user?->role;
            @endphp

            @if ($agency)
                <div class="app-sidebar-card mx-3 mb-4 rounded-xl p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700 dark:text-blue-300">Agence</p>
                    <p class="mt-1 truncate font-semibold text-slate-900 dark:text-white">{{ $agency->name }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Plan {{ $agency->plan_name }}
                    </p>
                </div>
            @endif

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid [&_[data-flux-sidebar-group-heading]]:text-slate-500 dark:[&_[data-flux-sidebar-group-heading]]:text-slate-400">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate
                    class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>

                    @if ($role === 'admin')
                        <flux:sidebar.item icon="building-office" :href="route('agencies.index')" :current="request()->routeIs('agencies.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Agencies') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="presentation-chart-bar" :href="route('analytics.index')" :current="request()->routeIs('analytics.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Analytics') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="queue-list" :href="route('activity-logs.index')" :current="request()->routeIs('activity-logs.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Activity Log') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($role === 'manager')
                        <flux:sidebar.item icon="map-pin" :href="route('branches.index')" :current="request()->routeIs('branches.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Branches') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="arrow-up-tray" :href="route('imports.index')" :current="request()->routeIs('imports.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Import Data') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('clients.index')" :current="request()->routeIs('clients.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Clients') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="users" :href="route('employees.index')" :current="request()->routeIs('employees.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Employees') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="sparkles" :href="route('services.index')" :current="request()->routeIs('services.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Services') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Reservations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="ticket" :href="route('tickets.index')" :current="request()->routeIs('tickets.*')" wire:navigate
                        class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Tickets') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="presentation-chart-bar" :href="route('analytics.index')" :current="request()->routeIs('analytics.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Analytics') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="queue-list" :href="route('activity-logs.index')" :current="request()->routeIs('activity-logs.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('Activity Log') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($role === 'staff')
                        <flux:sidebar.item icon="calendar-days" :href="route('reservations.index')" :current="request()->routeIs('reservations.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('My Reservations') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="ticket" :href="route('tickets.index')" :current="request()->routeIs('tickets.*')" wire:navigate
                            class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                            {{ __('My Tickets') }}
                        </flux:sidebar.item>
                    @endif

                    <flux:sidebar.item icon="cog-6-tooth" :href="route('profile.edit')" :current="request()->routeIs('profile.edit', 'user-password.edit', 'appearance.edit', 'two-factor.show', 'agency-billing.edit')" wire:navigate
                        class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 data-[current=true]:bg-blue-900/10 data-[current=true]:text-slate-700 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:data-[current=true]:bg-slate-800 dark:data-[current=true]:text-slate-200">
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="book-open-text" :href="route('guide')" wire:navigate
                    class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                    {{ __('Quick Guide') }}
                </flux:sidebar.item>

                @if ($role === 'manager' && $agency)
                    <flux:sidebar.item icon="banknotes" :href="route('agency-billing.edit')" wire:navigate
                        class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        {{ __('Billing Info') }}
                    </flux:sidebar.item>
                @elseif ($role === 'admin')
                    <flux:sidebar.item icon="banknotes" :href="route('agency-billing.edit')" wire:navigate
                        class="rounded-xl text-slate-700 hover:bg-blue-900/10 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        {{ __('Billing Info') }}
                    </flux:sidebar.item>
                @endif
            </flux:sidebar.nav>

            <div class="app-sidebar-card mx-3 mt-4 rounded-xl p-3">
                <button
                    type="button"
                    data-theme-toggle
                    class="app-sidebar-theme-toggle flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-semibold text-slate-900 transition hover:bg-blue-50 dark:text-slate-100 dark:hover:bg-slate-800">
                    <span class="flex items-center gap-2">
                        <svg data-theme-dark-icon class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 3a9 9 0 1 0 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <svg data-theme-light-icon class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M12 18a6 6 0 1 0 0-12 6 6 0 0 0 0 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span>Thème</span>
                    </span>
                    <span class="text-xs text-slate-500 dark:text-slate-400">Clair / sombre</span>
                </button>
            </div>

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Menu utilisateur mobile -->
        <flux:header class="app-mobile-header border-b border-slate-200 bg-white lg:hidden dark:border-slate-800 dark:bg-slate-950">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
        @stack('page-scripts')
        @fluxScripts
    </body>
</html>
