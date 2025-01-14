 @if(str_contains($item->subject, 'Annulé'))
 <a class="inline-block text-blue-800 line-through hover:line-through hover:text-blue-700" href="{{ route('appointments.show', $item->entry->id) }}">
   {{ $item->entry->name }} {{ $item->entry->lastname }}
 </a>
 @else
 <a class="inline-block text-blue-800 hover:text-blue-700 hover:underline" href="{{ route('appointments.show', $item->entry->id) }}">

   {{ $item->entry->name }} {{ $item->entry->lastname }}
 </a>
 @endif
