<x-layouts::app :title="__('Edit Reservation')">
    <div class="p-6 max-w-3xl mx-auto space-y-6">
        <div class="rounded-3xl bg-gradient-to-r from-indigo-600 via-slate-700 to-zinc-800 p-8 text-white shadow-xl shadow-slate-200/60 dark:shadow-black/20">
            <p class="text-sm uppercase tracking-[0.3em] text-white/60">Réservations</p>
            <h1 class="mt-3 text-3xl font-black">Modifier la réservation</h1>
            <p class="mt-3 max-w-2xl text-white/75">
                Modifiez les détails de la réservation.
            </p>
        </div>

        <form action="{{ route('reservations.update', $reservation) }}" method="POST" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/50 dark:border-zinc-700 dark:bg-zinc-800 space-y-6">
            @csrf
            @method('PUT')

            @include('partials.branch-select', ['branches' => $branches, 'selected' => $reservation->branch_id])

            @include('reservations.partials.client-fields', ['selectedClient' => $reservation->client])

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="employee_id">Employé</label>
                <select name="employee_id" id="employee_id"
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Non assigné</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('employee_id', $reservation->employee_id) == $employee->id ? 'selected' : '' }}>
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
                    <option value="car" {{ old('vehicle_type', $reservation->vehicle_type) === 'car' ? 'selected' : '' }}>Voiture</option>
                    <option value="motorcycle" {{ old('vehicle_type', $reservation->vehicle_type) === 'motorcycle' ? 'selected' : '' }}>Moto</option>
                    <option value="van" {{ old('vehicle_type', $reservation->vehicle_type) === 'van' ? 'selected' : '' }}>Utilitaire</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="plate_number">Immatriculation</label>
                <input type="text" name="plate_number" id="plate_number"
                       value="{{ old('plate_number', $reservation->plate_number) }}"
                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="service_id">Service</label>
                <select name="service_id" id="service_id" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="">Sélectionner un service</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ old('service_id', $reservation->service_id) == $service->id ? 'selected' : '' }}>
                            {{ $service->name }}{{ $service->branch ? ' - '.$service->branch->name : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="reservation_date">Date de réservation</label>
                <input type="datetime-local" name="reservation_date" id="reservation_date"
                       value="{{ old('reservation_date', optional($reservation->reservation_date)->format('Y-m-d\TH:i')) }}" required
                       class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="status">Statut</label>
                <select name="status" id="status" required
                        class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                    <option value="pending" {{ old('status', $reservation->status) == 'pending' ? 'selected' : '' }}>En attente</option>
                    <option value="confirmed" {{ old('status', $reservation->status) == 'confirmed' ? 'selected' : '' }}>Confirmée</option>
                    <option value="cancelled" {{ old('status', $reservation->status) == 'cancelled' ? 'selected' : '' }}>Annulée</option>
                </select>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('reservations.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-slate-300 dark:hover:bg-zinc-700">
                    Annuler
                </a>
                <button type="submit" class="rounded-lg bg-amber-500 px-4 py-2.5 font-semibold text-white hover:bg-amber-600">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</x-layouts::app>
