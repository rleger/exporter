<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Analyse des patients partagés') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <!-- Summary Cards -->
            <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Unique Patients -->
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Patients uniques</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $summaryStats->total_unique }}</dd>
                </div>

                <!-- Shared Patients -->
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Patients partagés</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-indigo-600">{{ $summaryStats->shared }}</dd>
                </div>

                <!-- Exclusive Patients -->
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Patients exclusifs</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $summaryStats->exclusive }}</dd>
                </div>

                <!-- Shifting Patients -->
                <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                    <dt class="truncate text-sm font-medium text-gray-500">Transferts détectés</dt>
                    <dd class="mt-1 text-3xl font-semibold tracking-tight text-orange-600">{{ $summaryStats->shifting }}</dd>
                </div>
            </div>

            <!-- Tabs -->
            <div x-data="{
                activeTab: 'patients',
                search: '',
                filter: 'all',
                allPatients: {{ Js::from($allPatients) }},
                get filteredPatients() {
                    let patients = this.allPatients;

                    // Apply filter
                    if (this.filter === 'shared') {
                        patients = patients.filter(p => p.users.length > 1);
                    } else if (this.filter === 'exclusive') {
                        patients = patients.filter(p => p.users.length === 1);
                    } else if (this.filter === 'transfers') {
                        patients = patients.filter(p => p.shift_pattern.type === 'shifting');
                    }

                    // Apply search
                    if (this.search.trim() !== '') {
                        const searchLower = this.search.toLowerCase();
                        patients = patients.filter(p =>
                            (p.name && p.name.toLowerCase().includes(searchLower)) ||
                            (p.lastname && p.lastname.toLowerCase().includes(searchLower)) ||
                            (p.email && p.email.toLowerCase().includes(searchLower))
                        );
                    }

                    return patients;
                }
            }" class="overflow-hidden bg-white shadow sm:rounded-lg">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex" aria-label="Tabs">
                        <button @click="activeTab = 'patients'"
                            :class="activeTab === 'patients' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                            class="w-1/2 border-b-2 px-1 py-4 text-center text-sm font-medium">
                            Vue par patient
                        </button>
                        <button @click="activeTab = 'users'"
                            :class="activeTab === 'users' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700'"
                            class="w-1/2 border-b-2 px-1 py-4 text-center text-sm font-medium">
                            Comparaison utilisateurs
                        </button>
                    </nav>
                </div>

                <!-- Patient View Tab -->
                <div x-show="activeTab === 'patients'" class="p-6">
                    <!-- Search and Filters -->
                    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Search -->
                        <div class="relative w-full sm:w-80">
                            <input type="text" x-model="search" placeholder="Rechercher par nom ou email..."
                                class="block w-full rounded-md border-gray-300 pl-10 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Filters -->
                        <div class="flex flex-wrap gap-2">
                            <button @click="filter = 'all'"
                                :class="filter === 'all' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                Tous ({{ $summaryStats->total_unique }})
                            </button>
                            <button @click="filter = 'shared'"
                                :class="filter === 'shared' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                Partagés ({{ $summaryStats->shared }})
                            </button>
                            <button @click="filter = 'exclusive'"
                                :class="filter === 'exclusive' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                Exclusifs ({{ $summaryStats->exclusive }})
                            </button>
                            <button @click="filter = 'transfers'"
                                :class="filter === 'transfers' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700 hover:bg-orange-200'"
                                class="rounded-full px-4 py-2 text-sm font-medium transition-colors">
                                Transferts ({{ $summaryStats->shifting }})
                            </button>
                        </div>
                    </div>

                    <!-- Results count -->
                    <div class="mb-4 text-sm text-gray-500">
                        <span x-text="filteredPatients.length"></span> patient(s) trouvé(s)
                    </div>

                    <!-- Patients Table -->
                    <template x-if="filteredPatients.length === 0">
                        <p class="text-gray-500">Aucun patient trouvé.</p>
                    </template>

                    <template x-if="filteredPatients.length > 0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-300">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">Patient</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Utilisateurs</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Distribution</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tendance</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Derniers RDV</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <template x-for="patient in filteredPatients" :key="patient.lastname + patient.name + patient.birthdate">
                                        <tr>
                                            <!-- Patient Name -->
                                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-0">
                                                <a :href="'/entries/' + patient.entry_id + '/appointments'" class="font-medium text-gray-900 hover:text-indigo-600 hover:underline" x-text="patient.lastname + ' ' + patient.name"></a>
                                                <div class="text-gray-500" x-text="patient.birthdate ? new Date(patient.birthdate).toLocaleDateString('fr-FR') : ''"></div>
                                                <div class="text-xs text-gray-400" x-text="patient.email" x-show="patient.email"></div>
                                            </td>

                                            <!-- Users -->
                                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                <div class="flex flex-wrap gap-1">
                                                    <template x-for="user in patient.users" :key="user.id">
                                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800" x-text="user.name"></span>
                                                    </template>
                                                </div>
                                            </td>

                                            <!-- Distribution Bar -->
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                <div class="w-48" x-show="patient.distribution.length > 0">
                                                    <div class="flex h-4 overflow-hidden rounded-full bg-gray-200">
                                                        <template x-for="(dist, index) in patient.distribution" :key="dist.user_id">
                                                            <div :class="['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-pink-500'][index % 5]"
                                                                 :style="'width: ' + dist.percentage + '%'"
                                                                 :title="dist.user_name + ': ' + dist.percentage + '%'"></div>
                                                        </template>
                                                    </div>
                                                    <div class="mt-1 flex flex-wrap gap-x-2 text-xs">
                                                        <template x-for="(dist, index) in patient.distribution" :key="dist.user_id">
                                                            <span class="flex items-center">
                                                                <span :class="['bg-blue-500', 'bg-green-500', 'bg-purple-500', 'bg-yellow-500', 'bg-pink-500'][index % 5]" class="mr-1 inline-block h-2 w-2 rounded-full"></span>
                                                                <span x-text="dist.user_name + ': ' + dist.percentage + '%'"></span>
                                                            </span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Shift Pattern -->
                                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                <span :class="{
                                                    'bg-orange-100 text-orange-800': patient.shift_pattern.type === 'shifting',
                                                    'bg-gray-100 text-gray-800': patient.shift_pattern.type === 'temporary_visit',
                                                    'bg-green-100 text-green-800': patient.shift_pattern.type === 'stable',
                                                    'bg-blue-100 text-blue-800': patient.shift_pattern.type === 'new_patient',
                                                    'bg-red-100 text-red-800': patient.shift_pattern.type === 'inactive'
                                                }" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium" x-text="patient.shift_pattern.label"></span>
                                                <div class="mt-1 text-xs text-gray-500" x-text="patient.shift_pattern.details" x-show="patient.shift_pattern.details"></div>
                                            </td>

                                            <!-- Last Appointments -->
                                            <td class="px-3 py-4 text-sm text-gray-500">
                                                <div class="space-y-1">
                                                    <template x-for="last in patient.last_appointments" :key="last.user_name">
                                                        <div class="text-xs">
                                                            <span class="font-medium" x-text="last.user_name + ':'"></span>
                                                            <span x-text="last.date ? new Date(last.date).toLocaleDateString('fr-FR') : 'N/A'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </div>

                <!-- User Comparison Tab -->
                <div x-show="activeTab === 'users'" x-cloak class="p-6">
                    @if($userComparison->isEmpty())
                        <p class="text-gray-500">Aucune paire d'utilisateurs avec des patients partagés.</p>
                    @else
                        <div class="space-y-6">
                            @foreach($userComparison as $pair)
                                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white" x-data="{ showTransfers: false }">
                                    <!-- Header -->
                                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-base font-semibold text-gray-900">
                                                {{ $pair->user_a->name }} &harr; {{ $pair->user_b->name }}
                                            </h3>
                                            @if($pair->flow_a_to_b > 0 || $pair->flow_b_to_a > 0)
                                                <button @click="showTransfers = !showTransfers"
                                                    class="text-sm text-indigo-600 hover:text-indigo-800">
                                                    <span x-text="showTransfers ? 'Masquer les transferts' : 'Voir les transferts'"></span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Stats -->
                                    <div class="p-4">
                                        <dl class="grid grid-cols-2 gap-4">
                                            <div class="text-center">
                                                <dt class="text-sm font-medium text-gray-500">Patients partagés</dt>
                                                <dd class="mt-1 text-2xl font-semibold text-indigo-600">{{ $pair->shared_count }}</dd>
                                            </div>
                                            <div class="text-center">
                                                <dt class="text-sm font-medium text-gray-500">Stables</dt>
                                                <dd class="mt-1 text-2xl font-semibold text-green-600">{{ $pair->stable }}</dd>
                                            </div>
                                        </dl>

                                        <!-- Flow Arrows -->
                                        <div class="mt-4 flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                            <div class="text-center">
                                                <div class="text-sm font-medium text-gray-900">{{ $pair->user_a->name }}</div>
                                            </div>
                                            <div class="flex flex-col items-center">
                                                @if($pair->flow_a_to_b > 0)
                                                    <div class="flex items-center text-orange-600">
                                                        <span class="text-sm font-medium">{{ $pair->flow_a_to_b }}</span>
                                                        <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                @if($pair->flow_b_to_a > 0)
                                                    <div class="flex items-center text-orange-600">
                                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                                        </svg>
                                                        <span class="text-sm font-medium">{{ $pair->flow_b_to_a }}</span>
                                                    </div>
                                                @endif
                                                @if($pair->flow_a_to_b == 0 && $pair->flow_b_to_a == 0)
                                                    <div class="text-sm text-gray-400">Pas de transfert</div>
                                                @endif
                                            </div>
                                            <div class="text-center">
                                                <div class="text-sm font-medium text-gray-900">{{ $pair->user_b->name }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Transfer Details (Expandable) -->
                                    @if($pair->flow_a_to_b > 0 || $pair->flow_b_to_a > 0)
                                        <div x-show="showTransfers" x-collapse class="border-t border-gray-200 bg-gray-50 p-4">
                                            @if($pair->transfers_a_to_b->isNotEmpty())
                                                <div class="mb-4">
                                                    <h4 class="mb-2 flex items-center text-sm font-medium text-gray-700">
                                                        <span>{{ $pair->user_a->name }}</span>
                                                        <svg class="mx-2 h-4 w-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                        </svg>
                                                        <span>{{ $pair->user_b->name }}</span>
                                                    </h4>
                                                    <div class="space-y-2">
                                                        @foreach($pair->transfers_a_to_b as $patient)
                                                            <div class="flex items-center justify-between rounded bg-white p-2 text-sm">
                                                                <div>
                                                                    <a href="{{ route('appointments.show', $patient->entry_id) }}" class="font-medium hover:text-indigo-600 hover:underline">{{ $patient->lastname }} {{ $patient->name }}</a>
                                                                    @if($patient->birthdate)
                                                                        <span class="text-gray-500">({{ $patient->birthdate->format('d/m/Y') }})</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    @foreach($patient->last_appointments as $last)
                                                                        <span class="mr-2">{{ $last->user_name }}: {{ $last->date ? $last->date->format('d/m/Y') : 'N/A' }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif

                                            @if($pair->transfers_b_to_a->isNotEmpty())
                                                <div>
                                                    <h4 class="mb-2 flex items-center text-sm font-medium text-gray-700">
                                                        <span>{{ $pair->user_b->name }}</span>
                                                        <svg class="mx-2 h-4 w-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                        </svg>
                                                        <span>{{ $pair->user_a->name }}</span>
                                                    </h4>
                                                    <div class="space-y-2">
                                                        @foreach($pair->transfers_b_to_a as $patient)
                                                            <div class="flex items-center justify-between rounded bg-white p-2 text-sm">
                                                                <div>
                                                                    <a href="{{ route('appointments.show', $patient->entry_id) }}" class="font-medium hover:text-indigo-600 hover:underline">{{ $patient->lastname }} {{ $patient->name }}</a>
                                                                    @if($patient->birthdate)
                                                                        <span class="text-gray-500">({{ $patient->birthdate->format('d/m/Y') }})</span>
                                                                    @endif
                                                                </div>
                                                                <div class="text-xs text-gray-500">
                                                                    @foreach($patient->last_appointments as $last)
                                                                        <span class="mr-2">{{ $last->user_name }}: {{ $last->date ? $last->date->format('d/m/Y') : 'N/A' }}</span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
