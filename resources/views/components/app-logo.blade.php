@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="NetoCar" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <video
                src="{{ asset('images/LOGO.mp4') }}"
                class="brand-logo-video"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
                aria-hidden="true"
            ></video>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="NetoCar" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <video
                src="{{ asset('images/LOGO.mp4') }}"
                class="brand-logo-video"
                autoplay
                muted
                loop
                playsinline
                preload="metadata"
                aria-hidden="true"
            ></video>
        </x-slot>
    </flux:brand>
@endif
