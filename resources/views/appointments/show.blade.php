<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Rendez-vous pour {{ $entry->name }} {{ $entry->lastname }}
        </h2>
    </x-slot>

    <div class="py-4">
        {{-- Patient details --}}
        <div class="mx-auto mb-10 max-w-7xl sm:px-6 lg:px-8">
            <h3 class="mt-10 text-base font-semibold text-gray-900">Informations</h3>
            <div class="mt-5 overflow-hidden bg-white shadow sm:rounded-lg">

                <div class="p-6 text-gray-800">
                    <div class="flex flex-col items-center justify-between space-y-2 md:flex-row md:space-y-0">
                        <div class="font-semibold text-blue-700">
                            <div class="flex items-center text-gray-700">
                                <x-heroicon-s-user class="mr-2 size-6 rounded-full border border-blue-500 p-1 text-blue-500" />
                                {{ $entry->name }} {{ $entry->lastname }}
                            </div>
                        </div>

                        <div class="flex items-center text-gray-700">
                            <x-heroicon-s-phone class="mr-2 size-6 rounded-full border border-blue-500 p-1 text-blue-500" />
                            {{ $entry->tel }}
                        </div>

                        <div class="flex items-center text-gray-700">
                            <x-heroicon-s-envelope class="mr-2 size-6 rounded-full border border-blue-500 p-1 text-blue-500" />
                            {{ $entry->email }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats --}}
            <div>
                <h3 class="mt-10 text-base font-semibold text-gray-900">Statistiques</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <dt class="truncate text-sm font-medium text-gray-500">Temps de consultation</dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $entry->consultation_hours }} h</dd>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <dt class="truncate text-sm font-medium text-gray-500">Temps annulé</dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">{{ $entry->canceled_hours }} h</dd>
                    </div>
                    <div class="overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                        <dt class="truncate text-sm font-medium text-gray-500">Temps perdu</dt>
                        <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                            @php
                                $lostTime = $entry->canceled_hours_not_replaced;
                                $totalCancelled = $entry->canceled_hours;

                                if ($lostTime == 0) {
                                    $colorClass = 'text-green-500';
                                } elseif ($lostTime < $totalCancelled) {
                                    $colorClass = 'text-orange-500';
                                } else {
                                    $colorClass = 'text-red-500';
                            } @endphp <span class="{{ $colorClass }}">
                                {{ number_format($lostTime, 2) }} h
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

        </div>

        {{-- Appointment list --}}
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <h3 class="mt-10 text-base font-semibold text-gray-900">Rendez-vous</h3>
            <div class="mt-5 overflow-hidden bg-white shadow sm:rounded-lg">
                @if ($entry->appointments->count())
                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Date
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Sujet
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Description
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Horaires
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Durée
                                </th>
                                <th class="bg-gray-50 px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                    Changements
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($entry->appointments as $appointment)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <span class="{{ \Carbon\Carbon::parse($appointment->date)->isPast() ? 'text-gray-400' : 'text-gray-800' }}">
                                            <div class="flex flex-col">
                                                <span>
                                                    {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
                                                </span>
                                                <span class="text-xs text-gray-500">
                                                    {{ \Carbon\Carbon::parse($appointment->date)->diffForHumans() }}
                                                </span>
                                            </div>
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <div class="flex flex-col items-start">
                                            <span>
                                                <x-appointments.label :item="$appointment" />
                                            </span>
                                            {{-- If the user has more than one calendar, display the calendar name in a badge. --}}
                                            @if (auth()->user()->calendars->count() > 1)
                                                <x-calendar-label :calendar="$entry->calendar->name" />
                                            @endif
                                        </div>
                                    </td>
                                    <td class="max-w-xs overflow-hidden whitespace-nowrap text-wrap px-6 py-4 text-sm text-gray-900">
                                        {{ $appointment->description }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500">
                                                {{ $appointment->start_date->toTimeString('minute') }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                {{ $appointment->end_date->toTimeString('minute') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="max-w-xs overflow-hidden whitespace-nowrap text-wrap px-6 py-4 text-xs text-gray-500">

                                        {{ $appointment->duration_hours }}h
                                    </td>

                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                                        <div class="flex flex-col">
                                            <span class="text-xs text-gray-500">
                                                <span class="font-semibold">{{ __('created') }} :</span> {{ $appointment->created_at->diffForHumans() }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <span class="font-semibold">{{ __('updated') }} :</span> {{ $appointment->updated_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Aucun rendez-vous trouvé pour cette entrée.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
