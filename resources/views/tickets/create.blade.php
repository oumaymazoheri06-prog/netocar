<x-layouts::app :title="__('New Ticket')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-sky-600 p-8 text-white shadow-xl shadow-slate-200/60 dark:bg-sky-900 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.6em] text-white/70">File en direct</p>
            <h1 class="mt-3 text-3xl font-black">Créer un ticket</h1>
            <p class="mt-3 max-w-2xl text-white/80">
                Ajoutez rapidement un ticket de lavage.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-200">
                <p class="font-semibold">Corrigez les erreurs suivantes :</p>
                <ul class="mt-3 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-800">
            <form action="{{ route('tickets.store') }}" method="POST" class="space-y-6">
                @csrf

                @include('partials.branch-select', ['branches' => $branches])

                @include('reservations.partials.client-fields')

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="service_id">Service</label>
                    <select name="service_id" id="service_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        <option value="">Sélectionner un service</option>
                        @foreach($services as $service)
                            <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                {{ $service->name }}{{ $service->branch ? ' - '.$service->branch->name : '' }} - MAD {{ number_format($service->price, 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="vehicle_type">Type de véhicule</label>
                    <select name="vehicle_type" id="vehicle_type" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        <option value="">Sélectionner un type</option>
                        <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Voiture</option>
                        <option value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Moto</option>
                        <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Utilitaire</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="plate_number">Immatriculation</label>
                    <input type="text" name="plate_number" id="plate_number" value="{{ old('plate_number') }}"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>

                <div>
                    <label class="mb-1 block text-sm font-semibold text-slate-700 dark:text-slate-300" for="employee_id">Assigner un employé</label>
                    <select name="employee_id" id="employee_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                        <option value="">Non assigné</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                {{ $employee->name }}{{ $employee->branch ? ' - '.$employee->branch->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end gap-4">
                    <a href="{{ route('tickets.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                        Annuler
                    </a>
                    <button type="submit" class="rounded-lg bg-sky-600 px-4 py-2.5 font-semibold text-white transition hover:bg-sky-500">
                        Créer le ticket
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
