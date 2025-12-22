<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class UniqueEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected Collection $groupedEntries;

    public function __construct(protected Collection $entries)
    {
        $this->groupedEntries = $this->groupEntries();
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
     * Group entries by patient identity and aggregate data.
     */
    protected function groupEntries(): Collection
    {
        return $this->entries->groupBy(fn ($entry) => $this->getGroupKey($entry))
            ->map(function (Collection $group) {
                $first = $group->first();

                $calendars = $group->map(fn ($e) => optional($e->calendar)->name)->filter()->unique()->values();
                $users = $group->map(fn ($e) => optional($e->calendar->user)->name)->filter()->unique()->values();

                $firstAppointment = $group->min('first_appointment_date');
                $lastAppointment = $group->max('last_appointment_date');

                return (object) [
                    'formatted_lastname' => $first->formatted_lastname,
                    'formatted_name' => $first->formatted_name,
                    'birthdate' => $first->birthdate,
                    'email' => $group->pluck('email')->filter()->first() ?? '',
                    'tel' => $group->pluck('tel')->filter()->first() ?? '',
                    'appointments_count' => $group->sum('appointments_count'),
                    'total_duration_minutes' => $group->sum('total_duration_minutes'),
                    'canceled_hours' => $group->sum('canceled_hours'),
                    'canceled_hours_not_replaced' => $group->sum('canceled_hours_not_replaced'),
                    'first_appointment_date' => $firstAppointment,
                    'last_appointment_date' => $lastAppointment,
                    'calendars_count' => $calendars->count(),
                    'calendars' => $calendars->implode(', '),
                    'users_count' => $users->count(),
                    'users' => $users->implode(', '),
                ];
            })
            ->values();
    }

    public function collection(): Collection
    {
        return $this->groupedEntries;
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prénom',
            'Date de naissance',
            'Age',
            'Email',
            'Téléphone',
            'Nb RDV total',
            'Durée totale',
            'Heures annulées',
            'Temps perdu',
            'Premier RDV',
            'Dernier RDV',
            'Nb calendriers',
            'Calendriers',
            'Nb utilisateurs',
            'Utilisateurs',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $totalMinutes = max(0, $row->total_duration_minutes ?? 0);
        $hours = intdiv($totalMinutes, 60);
        $minutes = $totalMinutes % 60;
        $totalDuration = sprintf('%02d:%02d', $hours, $minutes);

        $birthdate = $row->birthdate ? Carbon::parse($row->birthdate) : null;

        return [
            $row->formatted_lastname,
            $row->formatted_name,
            $birthdate ? $birthdate->format('d/m/Y') : '',
            $birthdate ? $birthdate->age : '',
            $row->email,
            $row->tel,
            $row->appointments_count,
            $totalDuration,
            number_format($row->canceled_hours ?? 0, 2),
            number_format($row->canceled_hours_not_replaced ?? 0, 2),
            $row->first_appointment_date ? Carbon::parse($row->first_appointment_date)->format('d/m/Y') : '',
            $row->last_appointment_date ? Carbon::parse($row->last_appointment_date)->format('d/m/Y') : '',
            $row->calendars_count,
            $row->calendars,
            $row->users_count,
            $row->users,
        ];
    }

    public function title(): string
    {
        return 'Patients uniques';
    }
}
