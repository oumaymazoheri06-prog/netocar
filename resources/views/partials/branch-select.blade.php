@php
    $branchSelectId = $id ?? 'branch_id';
    $branchSelected = old('branch_id', $selected ?? null);
    $branchLabel = $label ?? 'Branche';
    $branchEmptyLabel = $emptyLabel ?? 'Toute l’agence';
@endphp

<div>
    <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="{{ $branchSelectId }}">{{ $branchLabel }}</label>
    <select name="branch_id" id="{{ $branchSelectId }}"
            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
        <option value="">{{ $branchEmptyLabel }}</option>
        @foreach ($branches as $branch)
            <option value="{{ $branch->id }}" {{ (string) $branchSelected === (string) $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}{{ $branch->is_active ? '' : ' (inactive)' }}
            </option>
        @endforeach
    </select>
    @error('branch_id') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
</div>
