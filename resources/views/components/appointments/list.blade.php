<!-- resources/views/components/appointments/list.blade.php -->
<div class="shadow-xs overflow-hidden bg-white sm:rounded-lg">
    <div class="border-b border-gray-200 bg-white p-6">
        <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>
        @if ($appointments->count())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                            <th class="px-3 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Entrée</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <div class="flex flex-col">
                                        <span class="{{ \Carbon\Carbon::parse($appointment->date)->isPast() ? 'text-gray-400' : 'text-gray-800' }}">
                                            {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            {{ $isUpdated ? $appointment->updated_at->diffForHumans() : $appointment->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">
                                            <div class="flex justify-between">
                                                {{-- Entry name --}}
                                                <x-appointments.entry-name :item="$appointment" />

                                                {{-- If the user has more than one calendar, display the calendar name in a badge. --}}
                                                @if (auth()->user()->calendars->count() > 1)
                                                    <x-calendar-label :calendar="$appointment->entry->calendar->name" :short="true" />
                                                @endif
                                            </div>
                                        </span>
                                        <x-appointments.label :item="$appointment" />
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p>{{ $emptyMessage }}</p>
        @endif
    </div>
</div>
