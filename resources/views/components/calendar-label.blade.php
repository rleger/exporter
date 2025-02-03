@php
switch ($calendar) {
case 'Laclinic':
$badgeBg = 'bg-blue-100';
$badgeText = 'text-blue-700';
$iconFill = 'fill-blue-500';
break;
case 'Entourage':
$badgeBg = 'bg-yellow-100';
$badgeText = 'text-yellow-800';
$iconFill = 'fill-yellow-500';
break;
default:
$badgeBg = 'bg-gray-100';
$badgeText = 'text-gray-800';
$iconFill = 'fill-gray-500';
}
@endphp

@if(!$short)
<span class="inline-flex items-center gap-x-1.5 rounded-full {{ $badgeBg }} px-1.5 py-0.5 text-xs font-medium {{ $badgeText }}">

  <svg class="size-1.5 {{ $iconFill }}" viewBox="0 0 6 6" aria-hidden="true">
    <circle cx="3" cy="3" r="3" />
  </svg>
  {{ $calendar }}
</span>
@else
<span class="inline-flex items-center justify-center rounded-full {{ $badgeBg }} w-5 h-5 text-xs font-bold {{ $badgeText }}">
  {{ strtoupper(substr($calendar, 0, 1)) }}
</span>
@endif
