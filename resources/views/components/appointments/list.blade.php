<!-- resources/views/components/appointments/list.blade.php -->

<div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
  <div class="p-6 bg-white border-b border-gray-200">
    <h3 class="mb-4 text-lg font-medium leading-6 text-gray-900">{{ $title }}</h3>
    @if($appointments->count())
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr>
            <th class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Date</th>
            <th class="px-3 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Entrée</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          @foreach($appointments as $appointment)
          <tr>
            <td class="px-3 py-4 text-sm whitespace-nowrap">
              <div class="flex flex-col">
                <span class="{{ \Carbon\Carbon::parse($appointment->date)->isPast() ? 'text-gray-400' : 'text-gray-800' }}">
                  {{ \Carbon\Carbon::parse($appointment->date)->format('d/m/Y H:i') }}
                </span>
                <span class="text-xs text-gray-500">
                  {{ $isUpdated ? $appointment->updated_at->diffForHumans() : $appointment->created_at->diffForHumans() }}
                </span>
              </div>
            </td>
            <td class="px-3 py-4 text-sm whitespace-nowrap">
              <div class="flex flex-col">
                <span class="font-semibold">
                  <a class="text-blue-800 hover:text-blue-700 hover:underline" href="{{ route('appointments.show', $appointment->entry->id) }}">
                    {{ $appointment->entry->name }} {{ $appointment->entry->lastname }}
                  </a>
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
