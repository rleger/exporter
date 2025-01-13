{{-- filepath: /Users/romainleger/Sites/Exporter/resources/views/components/day-occupancy.blade.php --}}
@props(['day'])

@php
$uniqueSlots = [];
$totalMinutes = 0;

foreach ($day['appointments'] as $appointment) {
if (
!str_contains($appointment->subject, 'Annulé')
&& $appointment->start_date
&& $appointment->end_date
) {
// Construire une signature unique pour ce créneau et cette personne
$signature = $appointment->start_date->format('Y-m-d H:i') . '-' .
$appointment->end_date->format('Y-m-d H:i') . '-' .
($appointment->entry_id ?? '');

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
if ($percentage < 25) { $color='red' ; } elseif ($percentage < 70) { $color='orange' ; } else { $color='green' ; } if ($color==='red' ) { $bgClass='bg-red-200' ; $textClass='text-red-800' ; $fillClass='fill-red-500' ; $ringClass="ring-red-200" ;} elseif ($color==='orange' ) { $bgClass='bg-orange-200' ; $textClass='text-orange-800' ; $fillClass='fill-orange-500' ; $ringClass="ring-orange-200" ; } else { $bgClass='bg-green-200' ; $textClass='text-green-800' ; $fillClass='fill-green-500' ; $ringClass="ring-green-200" ; } @endphp {{-- Hack pour éviter que Tailwind ne purge ces classes dynamiques --}} <div style="display: none;">







  <span class="text-red-800 text-orange-800 text-green-800 bg-red-200 bg-orange-200 bg-green-200 ring-red-200 ring-orange-200 ring-green-200 fill-red-500 fill-orange-500 fill-green-500"></span>





  </div>

  <div class="relative inline-flex items-center justify-center gap-x-1.5
         rounded-full text-xs font-medium {{ $textClass  }}
         ring-1 ring-inset {{ $ringClass  }}
         overflow-hidden w-32 h-6 px-2">
    <!-- Barre de progression en arrière-plan -->
    <div class="absolute left-0 top-0 h-full {{ $bgClass }}" style="width: {{ $percentage }}%;"></div>

    <!-- Indicateur (rond coloré) -->
    <svg class="relative z-10 size-1.5 {{ $fillClass }}" viewBox="0 0 6 6">
      <circle cx="3" cy="3" r="3" />
    </svg>

    <!-- Texte : heures et pourcentage -->
    <span class="relative z-10 text-center">
      {{ $hours }}h ({{ $percentage }}%)
    </span>
  </div>
