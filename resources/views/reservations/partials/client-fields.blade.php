@php
    $selectedClient = $selectedClient ?? null;
    $clientOptions = $clients->map(fn ($client) => [
        'id' => $client->id,
        'name' => $client->name,
        'phone' => $client->phone_number,
        'email' => $client->email,
    ])->values();
@endphp

<div class="rounded-2xl border border-slate-200 bg-slate-50 p-5 dark:border-zinc-700 dark:bg-zinc-900/50">
    <div class="mb-4">
        <h2 class="font-bold text-slate-900 dark:text-white">Coordonnées du client</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
            Saisissez son téléphone. S'il existe déjà, ses informations seront retrouvées automatiquement.
        </p>
    </div>

    <input type="hidden" name="client_id" id="client_id" value="{{ old('client_id', $selectedClient?->id) }}">

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="client_phone">Téléphone</label>
            <input type="tel" name="client_phone" id="client_phone" list="client-phone-options" required autocomplete="tel"
                   value="{{ old('client_phone', $selectedClient?->phone_number) }}"
                   class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
            <datalist id="client-phone-options">
                @foreach ($clients as $client)
                    <option value="{{ $client->phone_number }}">{{ $client->name }}</option>
                @endforeach
            </datalist>
        </div>

        <div>
            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="client_name">Nom</label>
            <input type="text" name="client_name" id="client_name" required autocomplete="name"
                   value="{{ old('client_name', $selectedClient?->name) }}"
                   class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
        </div>

        <div class="sm:col-span-2">
            <label class="mb-1 block font-semibold text-slate-700 dark:text-slate-300" for="client_email">Email <span class="font-normal text-slate-400">(facultatif)</span></label>
            <input type="email" name="client_email" id="client_email" autocomplete="email"
                   value="{{ old('client_email', $selectedClient?->email) }}"
                   class="w-full rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
        </div>
    </div>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const clients = {{ Illuminate\Support\Js::from($clientOptions) }};
            const idInput = document.getElementById('client_id');
            const phoneInput = document.getElementById('client_phone');
            const nameInput = document.getElementById('client_name');
            const emailInput = document.getElementById('client_email');
            const normalizePhone = value => (value || '').replace(/\D/g, '');

            phoneInput?.addEventListener('input', () => {
                const client = clients.find(item => normalizePhone(item.phone) === normalizePhone(phoneInput.value));

                idInput.value = client?.id ?? '';

                if (client) {
                    nameInput.value = client.name ?? '';
                    emailInput.value = client.email ?? '';
                }
            });
        });
    </script>
@endonce
