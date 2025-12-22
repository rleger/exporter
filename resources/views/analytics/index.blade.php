<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Tableau de bord') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">

            <!-- Row 1: Key Metrics -->
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Patients</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $summaryStats->total_unique }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Partagés</p>
                    <p class="mt-1 text-2xl font-bold text-indigo-600">{{ $summaryStats->shared }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Exclusifs</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $summaryStats->exclusive }}</p>
                </div>
                <div class="rounded-lg bg-white p-4 shadow">
                    <p class="text-xs font-medium uppercase tracking-wide text-orange-500">Transferts</p>
                    <p class="mt-1 text-2xl font-bold text-orange-600">{{ $summaryStats->shifting }}</p>
                </div>
            </div>

            <!-- Row 2: Calendars & User Flows side by side -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Calendars Card -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Calendriers</h3>
                    </div>
                    <div class="p-4">
                        @if($calendars->count())
                            <div class="space-y-2">
                                @foreach($calendars as $calendar)
                                    <div class="flex items-center justify-between rounded bg-gray-50 px-3 py-2">
                                        <div>
                                            <span class="text-sm font-medium text-gray-900">{{ $calendar->name }}</span>
                                            <span class="ml-2 text-xs text-gray-500">{{ $calendar->user->name ?? '?' }}</span>
                                        </div>
                                        <span class="inline-flex items-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                            {{ $calendar->entries_count }} entrées
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Aucun calendrier.</p>
                        @endif
                    </div>
                </div>

                <!-- User Flows Card -->
                <div class="rounded-lg bg-white shadow">
                    <div class="border-b border-gray-100 px-4 py-3">
                        <h3 class="text-sm font-semibold text-gray-900">Flux entre utilisateurs</h3>
                    </div>
                    <div class="p-4">
                        @if($userComparison->isEmpty())
                            <p class="text-sm text-gray-500">Aucun patient partagé.</p>
                        @else
                            <div class="space-y-3">
                                @foreach($userComparison as $pair)
                                    <div class="rounded bg-gray-50 p-3" x-data="{ open: false }">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 text-sm">
                                                <span class="font-medium">{{ $pair->user_a->name }}</span>
                                                <div class="flex flex-col items-center text-xs">
                                                    @if($pair->flow_a_to_b > 0)
                                                        <span class="text-orange-600">{{ $pair->flow_a_to_b }} →</span>
                                                    @endif
                                                    @if($pair->flow_b_to_a > 0)
                                                        <span class="text-orange-600">← {{ $pair->flow_b_to_a }}</span>
                                                    @endif
                                                    @if($pair->flow_a_to_b == 0 && $pair->flow_b_to_a == 0)
                                                        <span class="text-gray-400">↔</span>
                                                    @endif
                                                </div>
                                                <span class="font-medium">{{ $pair->user_b->name }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500">{{ $pair->shared_count }} partagés</span>
                                                @if($pair->flow_a_to_b > 0 || $pair->flow_b_to_a > 0)
                                                    <button @click="open = !open" class="text-xs text-indigo-600 hover:underline">
                                                        <span x-text="open ? '−' : '+'"></span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                        <!-- Transfer details -->
                                        <div x-show="open" x-collapse class="mt-2 space-y-1 border-t border-gray-200 pt-2">
                                            @foreach($pair->transfers_a_to_b as $p)
                                                <div class="flex items-center justify-between text-xs">
                                                    <a href="{{ route('appointments.show', $p->entry_id) }}" class="text-gray-700 hover:text-indigo-600">{{ $p->lastname }} {{ $p->name }}</a>
                                                    <span class="text-orange-500">→ {{ $pair->user_b->name }}</span>
                                                </div>
                                            @endforeach
                                            @foreach($pair->transfers_b_to_a as $p)
                                                <div class="flex items-center justify-between text-xs">
                                                    <a href="{{ route('appointments.show', $p->entry_id) }}" class="text-gray-700 hover:text-indigo-600">{{ $p->lastname }} {{ $p->name }}</a>
                                                    <span class="text-orange-500">→ {{ $pair->user_a->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Row 3: Patient List -->
            <div class="rounded-lg bg-white shadow" x-data="{
                search: '',
                filter: 'all',
                allPatients: {{ Js::from($allPatients) }},
                get filteredPatients() {
                    let patients = this.allPatients;
                    if (this.filter === 'shared') patients = patients.filter(p => p.users.length > 1);
                    else if (this.filter === 'exclusive') patients = patients.filter(p => p.users.length === 1);
                    else if (this.filter === 'transfers') patients = patients.filter(p => p.shift_pattern.type === 'shifting');
                    if (this.search.trim()) {
                        const s = this.search.toLowerCase();
                        patients = patients.filter(p =>
                            (p.name && p.name.toLowerCase().includes(s)) ||
                            (p.lastname && p.lastname.toLowerCase().includes(s)) ||
                            (p.email && p.email.toLowerCase().includes(s))
                        );
                    }
                    return patients;
                }
            }">
                <!-- Header with search & filters -->
                <div class="flex flex-col gap-3 border-b border-gray-100 p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <h3 class="text-sm font-semibold text-gray-900">Patients</h3>
                        <div class="flex gap-1">
                            <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="rounded px-2 py-1 text-xs font-medium">Tous</button>
                            <button @click="filter = 'shared'" :class="filter === 'shared' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="rounded px-2 py-1 text-xs font-medium">Partagés</button>
                            <button @click="filter = 'exclusive'" :class="filter === 'exclusive' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'" class="rounded px-2 py-1 text-xs font-medium">Exclusifs</button>
                            <button @click="filter = 'transfers'" :class="filter === 'transfers' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-600 hover:bg-orange-200'" class="rounded px-2 py-1 text-xs font-medium">Transferts</button>
                        </div>
                    </div>
                    <div class="relative">
                        <input type="text" x-model="search" placeholder="Rechercher..." class="w-full rounded border-gray-300 py-1.5 pl-8 pr-3 text-sm sm:w-64">
                        <svg class="absolute left-2.5 top-2 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                <!-- Results count -->
                <div class="border-b border-gray-100 bg-gray-50 px-4 py-2 text-xs text-gray-500">
                    <span x-text="filteredPatients.length"></span> patient(s)
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-4 py-2 text-left font-medium">Patient</th>
                                <th class="px-4 py-2 text-left font-medium">Utilisateurs</th>
                                <th class="px-4 py-2 text-left font-medium">Distribution</th>
                                <th class="px-4 py-2 text-left font-medium">Statut</th>
                                <th class="px-4 py-2 text-left font-medium">Dernier RDV</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="patient in filteredPatients" :key="patient.entry_id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a :href="'/entries/' + patient.entry_id + '/appointments'" class="font-medium text-gray-900 hover:text-indigo-600" x-text="patient.lastname + ' ' + patient.name"></a>
                                        <div class="text-xs text-gray-400" x-text="patient.email" x-show="patient.email"></div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            <template x-for="user in patient.users" :key="user.id">
                                                <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-700" x-text="user.name"></span>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="w-32" x-show="patient.distribution.length > 1">
                                            <div class="flex h-2 overflow-hidden rounded-full bg-gray-200">
                                                <template x-for="(dist, i) in patient.distribution" :key="dist.user_id">
                                                    <div :class="['bg-blue-500','bg-green-500','bg-purple-500','bg-yellow-500'][i%4]" :style="'width:'+dist.percentage+'%'" :title="dist.user_name+': '+dist.percentage+'%'"></div>
                                                </template>
                                            </div>
                                        </div>
                                        <span x-show="patient.distribution.length <= 1" class="text-xs text-gray-400">—</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span :class="{
                                            'bg-orange-100 text-orange-700': patient.shift_pattern.type === 'shifting',
                                            'bg-green-100 text-green-700': patient.shift_pattern.type === 'stable',
                                            'bg-blue-100 text-blue-700': patient.shift_pattern.type === 'new_patient',
                                            'bg-gray-100 text-gray-600': patient.shift_pattern.type === 'temporary_visit' || patient.shift_pattern.type === 'inactive' || patient.shift_pattern.type === 'no_data'
                                        }" class="rounded px-1.5 py-0.5 text-xs font-medium" x-text="patient.shift_pattern.label"></span>
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        <template x-for="last in patient.last_appointments.slice(0,2)" :key="last.user_name">
                                            <div><span class="font-medium" x-text="last.user_name + ':'"></span> <span x-text="last.date ? new Date(last.date).toLocaleDateString('fr-FR') : '—'"></span></div>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    <template x-if="filteredPatients.length === 0">
                        <div class="p-8 text-center text-sm text-gray-500">Aucun patient trouvé.</div>
                    </template>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
