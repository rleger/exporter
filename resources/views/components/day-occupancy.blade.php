{{-- filepath: /Users/romainleger/Sites/Exporter/resources/views/components/day-occupancy.blade.php --}}
@props(['day'])

@php
    $uniqueSlots = [];
    $totalMinutes = 0;

    foreach ($day['appointments'] as $appointment) {
        if (!preg_match('/annul[eé]/i', $appointment->subject) && $appointment->start_date && $appointment->end_date) {
            // Construire une signature unique pour ce créneau et cette personne
            $signature =
                $appointment->start_date->format('Y-m-d H:i') . '-' . $appointment->end_date->format('Y-m-d H:i') . '-' . ($appointment->entry_id ?? '');

            // Si on n'a pas déjà compté ce créneau
        if (!isset($uniqueSlots[$signature])) {
            $uniqueSlots[$signature] = true;
            $totalMinutes += $appointment->start_date->diffInMinutes($appointment->end_date);
        }
    }
}

// Calcul du nombre d'heures et pourcentage (entier)
    $hours = round($totalMinutes / 60, 2);
    $percentage = round(($hours / 9) * 100);
    $percentage = min($percentage, 100); // Limiter à 100%

    // Couleur du badge selon le pourcentage
    if ($percentage < 25) {
        $color = 'red';
    } elseif ($percentage < 70) {
        $color = 'orange';
    } else {
        $color = 'green';
    }
    if ($color === 'red') {
        $bgClass = 'bg-red-200';
        $textClass = 'text-red-800';
        $fillClass = 'fill-red-500';
        $ringClass = 'ring-red-200';
    } elseif ($color === 'orange') {
        $bgClass = 'bg-orange-200';
        $textClass = 'text-orange-800';
        $fillClass = 'fill-orange-500';
        $ringClass = 'ring-orange-200';
    } else {
        $bgClass = 'bg-green-200';
        $textClass = 'text-green-800';
        $fillClass = 'fill-green-500';
        $ringClass = 'ring-green-200';
    }
@endphp {{-- Hack pour éviter que Tailwind ne purge ces classes dynamiques --}} <div style="display: none;">

    <span
        class="bg-green-200 bg-orange-200 bg-red-200 fill-green-500 fill-orange-500 fill-red-500 text-green-800 text-orange-800 text-red-800 ring-green-200 ring-orange-200 ring-red-200"></span>

</div>

<div
    class="{{ $textClass }} {{ $ringClass }} relative inline-flex h-6 w-32 items-center justify-center gap-x-1.5 overflow-hidden rounded-full px-2 text-xs font-medium ring-1 ring-inset">
    <!-- Barre de progression en arrière-plan -->
    <div class="{{ $bgClass }} absolute left-0 top-0 h-full" style="width: {{ $percentage }}%;"></div>

    <!-- Indicateur (rond coloré) -->
    <svg class="{{ $fillClass }} relative z-10 size-1.5" viewBox="0 0 6 6">
        <circle cx="3" cy="3" r="3" />
    </svg>

    <!-- Texte : heures et pourcentage -->
    <span class="relative z-10 text-center">
        {{ $hours }}h ({{ $percentage }}%)
    </span>
</div>
