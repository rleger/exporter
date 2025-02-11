<div class="shadow-xs overflow-hidden bg-white sm:rounded-lg">
    <div class="border-b border-gray-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>

        @if ($appointments->count())
            @foreach ($appointments as $day)
                <!-- Day Header -->
                <div class="relative flex items-center justify-center">
                    <h4 class="my-4 text-lg font-semibold leading-6 text-sky-700">
                        {{ $day['formatted_date'] }}
                        <span class="text-xs font-normal text-gray-500">
                            ({{ $day['relative_date'] }})
                        </span>
                    </h4>
                    <div class="absolute right-0">
                        <x-day-occupancy :day="$day" />
                    </div>
                </div>

                <!-- Table of appointments for this day -->
                <table class="mb-12 min-w-full table-fixed divide-y divide-gray-200 border border-gray-200">
                    <thead>
                        <tr class="bg-gray-50">
                            <!-- 1) Heure -->
                            <th class="w-20 px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Heure
                            </th>

                            <!-- 2) Entrée -->
                            <th class="w-72 px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Patient
                            </th>

                            <!-- 3) Rendez-vous -->
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                Rendez-vous
                            </th>

                            <!-- 4) Calendrier (EXTREME RIGHT, only if multiple calendars) -->
                            @if (auth()->user()->calendars->count() > 1)
                                <th class="w-32 px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                </th>
                            @endif
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($day['appointments'] as $appointment)
                            <tr>
                                <!-- Heure -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm">

                                    <span class="inline-flex items-center gap-x-1.5 rounded-md py-0.5 text-gray-800">
                                        {{ $appointment->date->format('H:i') }}
                                        @if ($appointment->is_new)
                                            <svg class="size-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                        @endif
                                    </span>
                                </td>

                                <!-- Entrée -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <div class="flex flex-col">
                                        <x-appointments.entry-name :item="$appointment" />
                                    </div>
                                </td>

                                <!-- Rendez-vous -->
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <x-appointments.label :item="$appointment" />
                                </td>

                                <!-- Calendrier (only if multiple calendars) -->
                                @if (auth()->user()->calendars->count() > 1)
                                    <td class="whitespace-nowrap px-3 py-4 text-right text-sm">
                                        <x-calendar-label :calendar="$appointment->entry->calendar->name" :short="true" />
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @else
            <p>Aucun rendez-vous prochainement</p>
        @endif
    </div>
</div>
