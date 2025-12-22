<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AllEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    /** @var array<string, int> */
    protected array $duplicateCounts;

    public function __construct(
        protected Collection $entries,
        protected bool $includeUserColumn = false
    ) {
        $this->duplicateCounts = $this->calculateDuplicateCounts();
    }

    /**
     * Generate a unique key for grouping entries by patient identity.
     */
    protected function getGroupKey($entry): string
    {
        $name = mb_strtolower($entry->name ?? '');
        $lastname = mb_strtolower($entry->lastname ?? '');
        $birthdate = $entry->birthdate ? $entry->birthdate->format('Y-m-d') : '';

        return "{$name}|{$lastname}|{$birthdate}";
    }

    /**
     * Calculate how many times each patient appears.
     *
     * @return array<string, int>
     */
    protected function calculateDuplicateCounts(): array
    {
        return $this->entries
            ->groupBy(fn ($entry) => $this->getGroupKey($entry))
            ->map(fn (Collection $group) => $group->count())
            ->toArray();
    }

    public function collection(): Collection
    {
        return $this->entries;
    }

    public function headings(): array
    {
        $headings = [
            'Est dupliqué',
            'Nb occurrences',
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
            $headings[] = 'Utilisateur';
        }

        return $headings;
    }

    public function map($entry): array
    {
        $totalMinutes = max(0, $entry->total_duration_minutes ?? 0);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $totalDuration = sprintf('%02d:%02d', $hours, $minutes);

        $birthdate = $entry->birthdate ? Carbon::parse($entry->birthdate) : null;

        $groupKey = $this->getGroupKey($entry);
        $occurrences = $this->duplicateCounts[$groupKey] ?? 1;
        $isDuplicate = $occurrences > 1;

        $data = [
            $isDuplicate ? 'Oui' : 'Non',
            $occurrences,
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
            $entry->first_appointment_date ? Carbon::parse($entry->first_appointment_date)->format('d/m/Y') : '',
            $entry->last_appointment_date ? Carbon::parse($entry->last_appointment_date)->format('d/m/Y') : '',
            optional($entry->calendar)->name ?? '',
        ];

        if ($this->includeUserColumn) {
            $data[] = optional($entry->calendar->user)->name ?? 'Utilisateur inconnu';
        }

        return $data;
    }

    public function title(): string
    {
        return 'Toutes les entrées';
    }
}
