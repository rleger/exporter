<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
  <div class="p-6 bg-white border-b border-gray-200">
    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>

    @if ($appointments->count())
    @foreach($appointments as $day)
    <!-- Day Header -->
    <h4 class="my-4 text-lg font-semibold leading-6 text-center text-sky-700">
      {{ $day['formatted_date'] }}
      <span class="text-xs font-normal text-gray-500">
        ({{ $day['relative_date'] }})
      </span>
    </h4>

    <!-- Table of appointments for this day -->
    <table class="min-w-full mb-12 border border-gray-200 divide-y divide-gray-200 table-fixed">
      <thead>
        <tr class="bg-gray-50">
          <!-- 1) Heure -->
          <th class="w-20 px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Heure
          </th>

          <!-- 2) Entrée -->
          <th class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase w-72">
            Patient
          </th>

          <!-- 3) Rendez-vous -->
          <th class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
            Rendez-vous
          </th>

          <!-- 4) Calendrier (EXTREME RIGHT, only if multiple calendars) -->
          @if (auth()->user()->calendars->count() > 1)
          <th class="w-32 px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
          </th>
          @endif
        </tr>
      </thead>

      <tbody class="bg-white divide-y divide-gray-200">
        @foreach($day['appointments'] as $appointment)
        <tr>
          <!-- Heure -->
          <td class="px-3 py-4 text-sm whitespace-nowrap">

            <span class="inline-flex items-center gap-x-1.5 rounded-md py-0.5  text-gray-800">
              {{ $appointment->date->format('H:i') }}
              @if($appointment->is_new)
              <svg class="size-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                <circle cx="3" cy="3" r="3" />
              </svg>
              @endif
            </span>
          </td>

          <!-- Entrée -->
          <td class="px-3 py-4 text-sm whitespace-nowrap">
            <div class="flex flex-col">
              <span class="font-semibold">
                <a class="flex justify-between text-blue-800 hover:text-blue-700 hover:underline" href="{{ route('appointments.show', $appointment->entry->id) }}">
                  {{ $appointment->entry->name }} {{ $appointment->entry->lastname }}
                </a>
              </span>
            </div>
          </td>

          <!-- Rendez-vous -->
          <td class="px-3 py-4 text-sm whitespace-nowrap">
            <x-appointments.label :item="$appointment" />
          </td>

          <!-- Calendrier (only if multiple calendars) -->
          @if (auth()->user()->calendars->count() > 1)
          <td class="px-3 py-4 text-sm text-right whitespace-nowrap">
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
