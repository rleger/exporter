@if(isset($entries) && $entries->count())
<div class="mt-6 overflow-hidden bg-white shadow-xs sm:rounded-lg">
  <div class="p-6 bg-white border-b border-gray-200">
    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">Moutons noirs</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr class="bg-gray-50">
              <th class="px-3 py-2 text-sm font-medium text-left text-gray-900">Nom</th>
              <th class="px-3 py-2 text-sm font-medium text-left text-gray-900">Nombre d'annulations</th>
              <th class="px-3 py-2 text-sm font-medium text-left text-gray-900">Temps</th>
              <th class="px-3 py-2 text-sm font-medium text-left text-gray-900">Temps perdu</th>
              <th class="px-3 py-2 text-sm font-medium text-left text-gray-900">Dernière annulation</th>
            </tr>
          </thead>

          <tbody class="bg-white">
            @foreach($entries as $entry)
            <tr class="even:bg-gray-50">
              <td class="px-3 py-2 text-sm text-gray-700">
                @if (auth()->user()->calendars->count() > 1)
                <span class="mr-2">
                  <x-calendar-label :calendar="$entry->calendar->name" :short="true" />
                </span>
                @endif

                <x-appointments.entry-name :item="$entry" />
              </td>
              <td class="px-3 py-2 text-sm text-gray-700">
                {{ $entry->total_cancellations }}
              </td>
              <td class="px-3 py-2 text-sm text-gray-700">
                {{-- Display canceled hours rather than total cancellations count --}}
                {{ number_format($entry->canceled_hours, 2) }} h
              </td>
              <td class="px-3 py-2 text-sm text-gray-700">
                {{-- {{ number_format($entry->canceled_hours_not_replaced, 2) }} h --}}
                ***
              </td>
              <td class="px-3 py-2 text-sm text-gray-700">
                {{ \Carbon\Carbon::parse($entry->last_cancellation_date)->diffForHumans() }}
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
  </div>
</div>
@endif
