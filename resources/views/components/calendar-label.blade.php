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

@if (!$short)
    <span class="{{ $badgeBg }} {{ $badgeText }} inline-flex items-center gap-x-1.5 rounded-full px-1.5 py-0.5 text-xs font-medium">

        <svg class="{{ $iconFill }} size-1.5" viewBox="0 0 6 6" aria-hidden="true">
            <circle cx="3" cy="3" r="3" />
        </svg>
        {{ $calendar }}
    </span>
@else
    <span class="{{ $badgeBg }} {{ $badgeText }} inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-bold">
        {{ strtoupper(substr($calendar, 0, 1)) }}
    </span>
@endif
