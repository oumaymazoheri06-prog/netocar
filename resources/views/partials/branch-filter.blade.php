@if ($branches->isNotEmpty())
    <form method="GET" action="{{ $action }}" class="surface-card flex flex-col gap-3 p-4 lg:flex-row lg:items-end">
        @foreach (($preserve ?? []) as $name => $value)
            @if ($value !== null && $value !== '')
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endif
        @endforeach

        <div class="flex-1">
            <label for="branch_filter" class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300">
                Branche
            </label>
            <select name="branch_id" id="branch_filter" class="input-modern">
                <option value="">Toutes les branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" {{ (string) ($selectedBranchId ?? '') === (string) $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}{{ $branch->is_active ? '' : ' (inactive)' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="btn-primary">Appliquer</button>
            <a href="{{ $action }}" class="btn-secondary">Réinitialiser</a>
        </div>
    </form>
@endif
