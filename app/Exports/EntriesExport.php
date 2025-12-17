<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntriesExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(protected Collection $entries)
    {
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Date de naissance',
            'Email',
            'Téléphone',
            'Nb RDV',
            'Durée totale',
            'Sujet',
            'Calendrier',
        ];
    }

    public function map($entry): array
    {
        $totalMinutes = max(0, $entry->total_duration_minutes ?? 0);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $totalDuration = sprintf('%02d:%02d', $hours, $minutes);

        return [
            $entry->formatted_lastname,
            $entry->formatted_name,
            $entry->birthdate ? \Carbon\Carbon::parse($entry->birthdate)->format('d/m/Y') : '',
            $entry->email,
            $entry->tel,
            $entry->appointments_count,
            $totalDuration,
            $entry->subject,
            optional($entry->calendar)->name ?? '',
        ];
    }
}
