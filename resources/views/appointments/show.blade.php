<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      Rendez-vous pour {{ $entry->name }} {{ $entry->lastname }}
    </h2>
  </x-slot>

  <div class="py-12">
    {{-- Patient details --}}
    <div class="mx-auto mb-10 max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-800">
          <div class="flex flex-col items-center justify-between space-y-2 md:space-y-0 md:flex-row">
            <div class="font-semibold text-blue-700">
              <div class="flex items-center text-gray-700">
                <x-heroicon-s-user class="p-1 mr-2 text-blue-500 border border-blue-500 rounded-full size-6" />
                {{ $entry->name }} {{ $entry->lastname }}
              </div>
            </div>

            <div class="flex items-center text-gray-700">
              <x-heroicon-s-phone class="p-1 mr-2 text-blue-500 border border-blue-500 rounded-full size-6" />
              {{ $entry->tel }}
            </div>

            <div class="flex items-center text-gray-700">
              <x-heroicon-s-envelope class="p-1 mr-2 text-blue-500 border border-blue-500 rounded-full size-6" />
              {{ $entry->email }}
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- Appointment list --}}
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        @if($entry->appointments->count())
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
          <thead>

            <tr>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                Date
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                Sujet
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                Description
              </th>
              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                Horaires
              </th>

              <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
                Changements
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            @foreach($entry->appointments as $appointment)
            <tr>
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
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
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                <x-appointments.label :item="$appointment" />
              </td>
              <td class="max-w-xs px-6 py-4 overflow-hidden text-sm text-gray-900 text-wrap whitespace-nowrap">
                {{ $appointment->description }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500">
                    {{ $appointment->start_date->toTimeString('minute') }}
                  </span>
                  <span class="text-xs text-gray-500">
                    {{ $appointment->end_date->toTimeString('minute') }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="text-xs text-gray-500">
                    <span class="font-semibold">{{ __('created')  }} :</span> {{ $appointment->created_at->diffForHumans() }}
                  </span>
                  <span class="text-xs text-gray-500">
                    <span class="font-semibold">{{ __('updated')  }} :</span> {{ $appointment->updated_at->diffForHumans() }}
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
