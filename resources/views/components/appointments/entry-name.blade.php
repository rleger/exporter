 @if(str_contains($item->subject, 'Annulé'))
 {{-- Appointment is canceelled --}}
 <a class="inline-block font-semibold line-through text-blue-800/70 hover:line-through hover:text-blue-700" href="{{ route('appointments.show', $item->entry->id) }}">
   {{ $item->entry->name }} {{ $item->entry->lastname }}
 </a>
 @else
 {{-- Appointment is not canceelled --}}
 <a class="inline-block font-semibold text-blue-800 hover:text-blue-700 hover:underline" href="{{ route('appointments.show', $item->entry->id) }}">
   {{ $item->entry->name }} {{ $item->entry->lastname }}
 </a>
 @endif