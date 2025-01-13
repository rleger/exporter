{{-- filepath: /Users/romainleger/Sites/Exporter/resources/views/components/day-occupancy.blade.php --}}
@props(['day'])

@php
$totalMinutes = 0;
foreach ($day['appointments'] as $appointment) {
if (
!str_contains($appointment->subject, 'Annulé')
&& $appointment->start_date
&& $appointment->end_date
) {
$totalMinutes += $appointment->start_date->diffInMinutes($appointment->end_date);
}
}

// Calcul du nombre d'heures et pourcentage (entier)
$hours = round($totalMinutes / 60, 2);
$percentage = round(($hours / 9) * 100);
$percentage = min($percentage, 100); // Limiter à 100%

// Couleur du badge selon le pourcentage
if ($percentage < 25) { $color='red' ; } elseif ($percentage < 70) { $color='orange' ; } else { $color='green' ; } $bgClass=match ($color) { 'red'=> 'bg-red-200',
  'orange' => 'bg-orange-200',
  'green' => 'bg-green-200',
  };
  $fillClass = match ($color) {
  'red' => 'fill-red-500',
  'orange' => 'fill-orange-500',
  'green' => 'fill-green-500',
  };
  @endphp

  {{-- Hack pour éviter que Tailwind ne purge ces classes dynamiques --}}
  <div style="display: none;">
    <span class="bg-red-200 bg-orange-200 bg-green-200 fill-red-500 fill-orange-500 fill-green-500"></span>
  </div>

  <div class="relative inline-flex items-center justify-center gap-x-1.5
         rounded-full text-xs font-medium text-gray-900
         ring-1 ring-inset ring-gray-200
         overflow-hidden w-32 h-6 px-2">
    <div class="absolute left-0 top-0 h-full {{ $bgClass }}" style="width: {{ $percentage }}%;"></div>

    <svg class="relative z-10 size-1.5 {{ $fillClass }}" viewBox="0 0 6 6">
      <circle cx="3" cy="3" r="3" />
    </svg>

    <span class="relative z-10 text-center">
      {{ $hours }}h ({{ $percentage }}%)
    </span>
  </div>
