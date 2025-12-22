<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntriesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Collection $entries,
        protected bool $includeUserColumn = false
    ) {
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        $headings = [
            'Nom',
            'Prénom',
            'Date de naissance',
            'Age',
            'Email',
            'Téléphone',
            'Nb RDV',
            'Durée totale',
            'Heures annulées',
            'Temps perdu',
            'Premier RDV',
            'Dernier RDV',
            'Calendrier',
        ];

        if ($this->includeUserColumn) {
            array_unshift($headings, 'Utilisateur');
        }

        return $headings;
    }

    public function map($entry): array
    {
        $totalMinutes = max(0, $entry->total_duration_minutes ?? 0);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $totalDuration = sprintf('%02d:%02d', $hours, $minutes);

        $birthdate = $entry->birthdate ? \Carbon\Carbon::parse($entry->birthdate) : null;

        $data = [
            $entry->formatted_lastname,
            $entry->formatted_name,
            $birthdate ? $birthdate->format('d/m/Y') : '',
            $birthdate ? $birthdate->age : '',
            $entry->email,
            $entry->tel,
            $entry->appointments_count,
            $totalDuration,
            number_format($entry->canceled_hours ?? 0, 2),
            number_format($entry->canceled_hours_not_replaced ?? 0, 2),
            $entry->first_appointment_date ? \Carbon\Carbon::parse($entry->first_appointment_date)->format('d/m/Y') : '',
            $entry->last_appointment_date ? \Carbon\Carbon::parse($entry->last_appointment_date)->format('d/m/Y') : '',
            optional($entry->calendar)->name ?? '',
        ];

        if ($this->includeUserColumn) {
            array_unshift($data, optional($entry->calendar->user)->name ?? 'Utilisateur inconnu');
        }

        return $data;
    }
}
