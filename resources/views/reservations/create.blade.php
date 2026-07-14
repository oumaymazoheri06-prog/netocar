<x-layouts::app :title="__('Create Reservation')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Réservations</p>
            <h1 class="mt-3 text-3xl font-black">Créer une réservation</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Créez une réservation pour un client, un service et un employé.
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

        <form action="{{ route('reservations.store') }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
            @csrf

            @include('partials.branch-select', ['branches' => $branches])

            @include('reservations.partials.client-fields')

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="employee_id">Employé</label>
                <select name="employee_id" id="employee_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Non assigné</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->email }}){{ $employee->branch ? ' - '.$employee->branch->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="vehicle_type">Type de véhicule</label>
                <select name="vehicle_type" id="vehicle_type" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Sélectionner un type</option>
                    <option value="car" {{ old('vehicle_type') === 'car' ? 'selected' : '' }}>Voiture</option>
                    <option value="motorcycle" {{ old('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Moto</option>
                    <option value="van" {{ old('vehicle_type') === 'van' ? 'selected' : '' }}>Utilitaire</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="plate_number">Immatriculation</label>
                <input type="text" name="plate_number" id="plate_number" value="{{ old('plate_number') }}"
                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="service_id">Service</label>
                <select name="service_id" id="service_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Sélectionner un service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}{{ $service->branch ? ' - '.$service->branch->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="reservation_date">Date de réservation</label>
                <input type="datetime-local" name="reservation_date" id="reservation_date" value="{{ old('reservation_date') }}" required
                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('reservations.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Annuler
                </a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-500">
                    Créer la réservation
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
