<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PatientAnalyticsController extends Controller
{
    protected Carbon $sixMonthsAgo;

    public function __construct()
    {
        $this->sixMonthsAgo = now()->subMonths(6);
    }

    public function index(): View
    {
        $allEntries = $this->getAllEntriesWithAppointments();
        $groupedPatients = $this->groupByPatient($allEntries);

        $allPatients = $this->getAllPatients($groupedPatients);
        $sharedPatients = $allPatients->filter(fn ($p) => $p->users->count() > 1)->values();
        $exclusivePatients = $allPatients->filter(fn ($p) => $p->users->count() === 1)->values();
        $userComparison = $this->getUserComparison($sharedPatients);
        $summaryStats = $this->getSummaryStats($groupedPatients, $sharedPatients);
        $users = User::all();

        return view('analytics.patients', compact(
            'allPatients',
            'sharedPatients',
            'exclusivePatients',
            'userComparison',
            'summaryStats',
            'users'
        ));
    }

    /**
     * Get all entries with their appointments and related data.
     */
    protected function getAllEntriesWithAppointments(): Collection
    {
        return Entry::query()
            ->with(['calendar.user', 'appointments'])
            ->get();
    }

    /**
     * Generate a unique key for grouping entries by patient identity.
     */
    protected function getPatientKey($entry): string
    {
        $name = mb_strtolower($entry->name ?? '');
        $lastname = mb_strtolower($entry->lastname ?? '');
        $birthdate = $entry->birthdate ? $entry->birthdate->format('Y-m-d') : '';

        return "{$name}|{$lastname}|{$birthdate}";
    }

    /**
     * Group entries by patient identity.
     */
    protected function groupByPatient(Collection $entries): Collection
    {
        return $entries->groupBy(fn ($entry) => $this->getPatientKey($entry));
    }

    /**
     * Get all patients with their statistics.
     */
    protected function getAllPatients(Collection $groupedPatients): Collection
    {
        return $groupedPatients
            ->map(function (Collection $entries) {
                return $this->buildPatientStats($entries);
            })
            ->sortByDesc('total_appointments')
            ->values();
    }

    /**
     * Build statistics for a patient across all their entries.
     */
    protected function buildPatientStats(Collection $entries): object
    {
        $firstEntry = $entries->first();

        // Collect all appointments across all entries for this patient
        $allAppointments = $entries->flatMap(fn ($e) => $e->appointments);

        // Get unique users
        $users = $entries
            ->map(fn ($e) => optional($e->calendar->user))
            ->filter()
            ->unique('id')
            ->values();

        // Calculate distribution per user
        $distribution = $this->calculateDistribution($entries, $allAppointments);

        // Detect shift pattern
        $shiftPattern = $this->detectShiftPattern($entries, $allAppointments);

        // Get last appointment per user
        $lastAppointmentPerUser = $this->getLastAppointmentPerUser($entries);

        // Get first non-empty email
        $email = $entries->pluck('email')->filter()->first() ?? '';

        // Get entry IDs for linking
        $entryIds = $entries->pluck('id')->values();

        return (object) [
            'name' => $firstEntry->formatted_name,
            'lastname' => $firstEntry->formatted_lastname,
            'birthdate' => $firstEntry->birthdate,
            'email' => $email,
            'entry_id' => $firstEntry->id,
            'entry_ids' => $entryIds,
            'users' => $users,
            'distribution' => $distribution,
            'shift_pattern' => $shiftPattern,
            'last_appointments' => $lastAppointmentPerUser,
            'total_appointments' => $allAppointments->count(),
            'entries_count' => $entries->count(),
        ];
    }

    /**
     * Calculate appointment distribution percentage per user.
     *
     * @return Collection<int, object>
     */
    protected function calculateDistribution(Collection $entries, Collection $allAppointments): Collection
    {
        $totalAppointments = $allAppointments->count();

        if (0 === $totalAppointments) {
            return collect();
        }

        return $entries
            ->groupBy(fn ($e) => optional($e->calendar->user)->id)
            ->filter(fn ($group, $key) => null !== $key)
            ->map(function (Collection $userEntries, $userId) use ($totalAppointments) {
                $user = $userEntries->first()->calendar->user;
                $appointmentCount = $userEntries->sum(fn ($e) => $e->appointments->count());
                $percentage = round(($appointmentCount / $totalAppointments) * 100, 1);

                return (object) [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'count' => $appointmentCount,
                    'percentage' => $percentage,
                ];
            })
            ->sortByDesc('percentage')
            ->values();
    }

    /**
     * Detect if a patient is shifting from one user to another.
     */
    protected function detectShiftPattern(Collection $entries, Collection $allAppointments): object
    {
        if ($allAppointments->isEmpty()) {
            return (object) [
                'type' => 'no_data',
                'label' => 'Pas de données',
                'details' => null,
            ];
        }

        // Split appointments into recent vs older
        $recentAppointments = $allAppointments->filter(
            fn ($apt) => $apt->start_date && Carbon::parse($apt->start_date)->gte($this->sixMonthsAgo)
        );
        $olderAppointments = $allAppointments->filter(
            fn ($apt) => $apt->start_date && Carbon::parse($apt->start_date)->lt($this->sixMonthsAgo)
        );

        // If no older appointments, patient is new - can't detect shift
        if ($olderAppointments->isEmpty()) {
            return (object) [
                'type' => 'new_patient',
                'label' => 'Patient récent',
                'details' => null,
            ];
        }

        // If no recent appointments, patient may have left
        if ($recentAppointments->isEmpty()) {
            return (object) [
                'type' => 'inactive',
                'label' => 'Inactif',
                'details' => null,
            ];
        }

        // Get dominant user for each period
        $recentDominant = $this->getDominantUser($recentAppointments, $entries);
        $olderDominant = $this->getDominantUser($olderAppointments, $entries);

        if (!$recentDominant || !$olderDominant) {
            return (object) [
                'type' => 'stable',
                'label' => 'Partagé',
                'details' => null,
            ];
        }

        // Check for shift
        if ($recentDominant['user_id'] !== $olderDominant['user_id']) {
            // Check if it's a temporary visit (few appointments with new user then back)
            if ($recentDominant['percentage'] < 30) {
                return (object) [
                    'type' => 'temporary_visit',
                    'label' => 'Visite temporaire',
                    'details' => "Passage chez {$recentDominant['user_name']}",
                ];
            }

            if ($recentDominant['percentage'] >= 70 && $olderDominant['percentage'] >= 70) {
                return (object) [
                    'type' => 'shifting',
                    'label' => 'Transfert',
                    'details' => "De {$olderDominant['user_name']} vers {$recentDominant['user_name']}",
                ];
            }
        }

        return (object) [
            'type' => 'stable',
            'label' => 'Partagé',
            'details' => null,
        ];
    }

    /**
     * Get the dominant user for a set of appointments.
     *
     * @return array{user_id: int, user_name: string, percentage: float}|null
     */
    protected function getDominantUser(Collection $appointments, Collection $entries): ?array
    {
        if ($appointments->isEmpty()) {
            return null;
        }

        // Map entry_id to user
        $entryToUser = $entries->mapWithKeys(function ($entry) {
            $user = optional($entry->calendar->user);

            return [$entry->id => ['id' => $user->id, 'name' => $user->name]];
        });

        $countByUser = $appointments
            ->groupBy(fn ($apt) => $entryToUser[$apt->entry_id]['id'] ?? null)
            ->filter(fn ($group, $key) => null !== $key)
            ->map->count();

        if ($countByUser->isEmpty()) {
            return null;
        }

        $total = $countByUser->sum();
        $dominantUserId = $countByUser->keys()->first();
        $dominantCount = $countByUser->first();

        // Sort to get actual dominant
        $countByUser = $countByUser->sortDesc();
        $dominantUserId = $countByUser->keys()->first();
        $dominantCount = $countByUser->first();

        $userName = $entryToUser->first(fn ($u) => $u['id'] === $dominantUserId)['name'] ?? 'Inconnu';

        return [
            'user_id' => $dominantUserId,
            'user_name' => $userName,
            'percentage' => round(($dominantCount / $total) * 100, 1),
        ];
    }

    /**
     * Get last appointment date per user for a patient.
     */
    protected function getLastAppointmentPerUser(Collection $entries): Collection
    {
        return $entries
            ->groupBy(fn ($e) => optional($e->calendar->user)->id)
            ->filter(fn ($group, $key) => null !== $key)
            ->map(function (Collection $userEntries) {
                $user = $userEntries->first()->calendar->user;
                $lastAppointment = $userEntries
                    ->flatMap(fn ($e) => $e->appointments)
                    ->filter(fn ($apt) => $apt->start_date)
                    ->sortByDesc('start_date')
                    ->first();

                return (object) [
                    'user_name' => $user->name,
                    'date' => $lastAppointment ? Carbon::parse($lastAppointment->start_date) : null,
                ];
            })
            ->values();
    }

    /**
     * Build user comparison statistics.
     */
    protected function getUserComparison(Collection $sharedPatients): Collection
    {
        $userPairs = collect();

        foreach ($sharedPatients as $patient) {
            $users = $patient->users;

            // Create pairs
            for ($i = 0; $i < $users->count(); ++$i) {
                for ($j = $i + 1; $j < $users->count(); ++$j) {
                    $userA = $users[$i];
                    $userB = $users[$j];

                    // Ensure consistent ordering
                    if ($userA->id > $userB->id) {
                        [$userA, $userB] = [$userB, $userA];
                    }

                    $pairKey = "{$userA->id}-{$userB->id}";

                    if (!$userPairs->has($pairKey)) {
                        $userPairs[$pairKey] = (object) [
                            'user_a' => $userA,
                            'user_b' => $userB,
                            'shared_count' => 0,
                            'flow_a_to_b' => 0,
                            'flow_b_to_a' => 0,
                            'stable' => 0,
                            'transfers_a_to_b' => collect(),
                            'transfers_b_to_a' => collect(),
                        ];
                    }

                    ++$userPairs[$pairKey]->shared_count;

                    // Categorize by shift pattern
                    if ('shifting' === $patient->shift_pattern->type) {
                        $details = $patient->shift_pattern->details ?? '';
                        if (str_contains($details, "vers {$userB->name}")) {
                            ++$userPairs[$pairKey]->flow_a_to_b;
                            $userPairs[$pairKey]->transfers_a_to_b->push($patient);
                        } elseif (str_contains($details, "vers {$userA->name}")) {
                            ++$userPairs[$pairKey]->flow_b_to_a;
                            $userPairs[$pairKey]->transfers_b_to_a->push($patient);
                        }
                    } elseif (in_array($patient->shift_pattern->type, ['stable', 'new_patient'])) {
                        ++$userPairs[$pairKey]->stable;
                    }
                }
            }
        }

        return $userPairs->sortByDesc('shared_count')->values();
    }

    /**
     * Calculate summary statistics.
     */
    protected function getSummaryStats(Collection $groupedPatients, Collection $sharedPatients): object
    {
        $totalUniquePatients = $groupedPatients->count();
        $sharedCount = $sharedPatients->count();
        $exclusiveCount = $totalUniquePatients - $sharedCount;

        $shiftingCount = $sharedPatients->filter(
            fn ($p) => 'shifting' === $p->shift_pattern->type
        )->count();

        return (object) [
            'total_unique' => $totalUniquePatients,
            'shared' => $sharedCount,
            'exclusive' => $exclusiveCount,
            'shifting' => $shiftingCount,
        ];
    }
}
