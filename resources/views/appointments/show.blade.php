<x-app-layout>
  <x-slot name="header">
    <h2 class="text-xl font-semibold leading-tight text-gray-800">
      Rendez-vous pour {{ $entry->name }} {{ $entry->lastname }}
    </h2>
  </x-slot>

  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      @if($entry->appointments->count())
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
              Date
            </th>
            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase bg-gray-50">
              Sujet
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($entry->appointments as $appointment)
          <tr>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
              {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
            </td>
            <td class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap">
              {{ $appointment->subject }}
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
</x-app-layout>
