@php
    // Déterminer si $item est un Appointment ou un Entry
    $isAppointment = $item instanceof \App\Models\Appointment;

    $entryName = $isAppointment ? $item->entry->formatted_name : $item->formatted_name;
    $entryLastName = $isAppointment ? $item->entry->formatted_lastname : $item->formatted_lastname;
    $subject = $isAppointment ? $item->subject : null;
    $linkRoute = $isAppointment ? route('appointments.show', $item->entry->id) : route('appointments.show', $item->id);

    $isCancelled = $isAppointment && preg_match('/annul[eé]/i', $subject);
@endphp

@if ($isCancelled)
    <a href="{{ $linkRoute }}" class="inline-block font-semibold text-blue-800/70 line-through hover:text-blue-700 hover:line-through">
        {{ $entryLastName }} {{ $entryName }}
    </a>
@else
    <a href="{{ $linkRoute }}" class="inline-block font-semibold text-blue-800 hover:text-blue-700 hover:underline">
        {{ $entryLastName }} {{ $entryName }}
    </a>
@endif
