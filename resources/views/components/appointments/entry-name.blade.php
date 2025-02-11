@php
    // Déterminer si $item est un Appointment ou un Entry
    $isAppointment = $item instanceof \App\Models\Appointment;

    $entryName = $isAppointment ? $item->entry->name : $item->name;
    $entryLastName = $isAppointment ? $item->entry->lastname : $item->lastname;
    $subject = $isAppointment ? $item->subject : null;
    $linkRoute = $isAppointment ? route('appointments.show', $item->entry->id) : route('appointments.show', $item->id);

    $isCancelled = $isAppointment && preg_match('/annul[eé]/i', $subject);
@endphp

@if ($isCancelled)
    <a href="{{ $linkRoute }}" class="inline-block font-semibold text-blue-800/70 line-through hover:text-blue-700 hover:line-through">
        {{ $entryName }} {{ $entryLastName }}
    </a>
@else
    <a href="{{ $linkRoute }}" class="inline-block font-semibold text-blue-800 hover:text-blue-700 hover:underline">
        {{ $entryName }} {{ $entryLastName }}
    </a>
@endif
