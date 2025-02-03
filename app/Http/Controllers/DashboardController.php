<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Calendar;
use App\Models\Appointment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    /**
     * Import calendars by running the 'calendars:import' Artisan command.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importCalendars(Request $request)
    {
        Artisan::call('calendars:import');
        $output = Artisan::output();

        return redirect()->back()->with('importOutput', $output);
    }

    /**
     * Display the main dashboard page with calendars and recent/updated appointments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $userCalendarIds = $this->getUserCalendarIds();
        $calendars = $this->getAllCalendars();
        $recentAppointments = $this->getRecentAppointments($userCalendarIds);
        $updatedAppointments = $this->getUpdatedAppointments($userCalendarIds);

        // Upcoming appointments: only from "today onward"
        $allAppointments = $this->getAllFutureAppointments($userCalendarIds);

        // Keep a maximum of 50 appointments, but don't cut off mid-day
        $selectedAppointments = $this->limitAppointmentsWithoutCuttingDay($allAppointments, 90);

        // Mark newly created (< 2 days) appointments, then group and format by day
        $selectedAppointments = $this->markNewAppointments($selectedAppointments);
        $groupedAppointments = $this->groupAndFormatAppointments($selectedAppointments);

        // Retrieve the top 20 entries with the most cancellations
        $topCancelledEntries = $this->getTopCancelledEntries(20);

        return view('dashboard', [
            'calendars'           => $calendars,
            'recentAppointments'  => $recentAppointments,
            'updatedAppointments' => $updatedAppointments,
            'groupedAppointments' => $groupedAppointments,
            'topCancelledEntries' => $topCancelledEntries,
        ]);
    }

    /**
     * Get IDs of the current user's calendars.
     */
    protected function getUserCalendarIds(): Collection
    {
        $user = Auth::user();

        return Calendar::where('user_id', $user->id)
            ->pluck('id');
    }

    /**
     * Optionally fetch ALL calendars (with entry counts) for display in the view.
     */
    protected function getAllCalendars()
    {
        return Calendar::with('user')
            ->withCount('entries')
            ->get();
    }

    /**
     * Fetch recent (created in last 3 days) appointments that have not been edited.
     */
    protected function getRecentAppointments(Collection $userCalendarIds)
    {
        $threeDaysAgo = Carbon::now()->subDays(3);

        return Appointment::with('entry')
            ->whereHas(
                'entry',
                fn ($query) => $query->whereIn('calendar_id', $userCalendarIds)
            )
            ->where('date', '>=', $threeDaysAgo)
            ->whereColumn('created_at', '=', 'updated_at')
            ->orderBy('created_at', 'desc')
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();
    }

    /**
     * Fetch recently updated (edited in last 3 days) appointments.
     */
    protected function getUpdatedAppointments(Collection $userCalendarIds)
    {
        $threeDaysAgo = Carbon::now()->subDays(3);

        return Appointment::with('entry')
            ->whereHas(
                'entry',
                fn ($query) => $query->whereIn('calendar_id', $userCalendarIds)
            )
            ->where('date', '>=', $threeDaysAgo)
            ->whereColumn('created_at', '!=', 'updated_at')
            ->orderBy('updated_at', 'desc')
            ->orderBy('date', 'asc')
            ->take(10)
            ->get();
    }

    /**
     * Retrieve all future appointments (from "today onward") for the given calendar IDs,
     * BUT if it's already 22:00 or later, skip "today" entirely and start from tomorrow.
     */
    protected function getAllFutureAppointments(Collection $userCalendarIds): Collection
    {
        $now = Carbon::now();

        // If local time is 22:00 or later, skip today; otherwise include from today's start.
        $startDate = $now->hour >= 22
            ? $now->copy()->addDay()->startOfDay()  // tomorrow 00:00
            : $now->copy()->startOfDay();           // today 00:00

        return Appointment::with('entry')
            ->whereHas(
                'entry',
                fn ($q) => $q->whereIn('calendar_id', $userCalendarIds)
            )
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Fetch the top n entries (default 20) with the most cancellations.
     */
    protected function getTopCancelledEntries(int $limit = 20)
    {
        // Retrieve entries that have at least one canceled appointment.
        // We load all appointments for each entry so we can check for replacements.
        $entries = Entry::query()
            ->whereHas('appointments', function ($query) {
                $query->where('subject', 'like', '%annul%');
            })
            ->with('appointments')
            ->withCount([
                'appointments as total_cancellations' => function ($query) {
                    $query->where('subject', 'like', '%annul%');
                },
            ])
            ->withMax([
                'appointments as last_cancellation_date' => function ($query) {
                    $query->where('subject', 'like', '%annul%');
                },
            ], 'updated_at')
            ->orderByDesc('total_cancellations')
            ->limit($limit)
            ->get();

        // For each entry, compute the canceled hours and the hours not replaced.
        $entries->each(function ($entry) {
            // Get only the canceled appointments for this entry.

            $canceledAppointments = $entry->appointments->filter(function ($app) {
                // Lowercase the subject and check for the substring 'annul'
                return Str::contains(mb_strtolower($app->subject), 'annul');
            });

            // Sum up total canceled hours.
            $entry->canceled_hours = $canceledAppointments->sum(function ($app) {
                return $app->duration_hours;
            });

            $canceledNotReplaced = 0;

            // Process each canceled appointment individually.
            foreach ($canceledAppointments as $cancel) {
                // Find potential replacement appointments:
                //  - They do NOT have 'annul' in their subject.
                //  - They were created AFTER the cancellation (using $cancel->updated_at).
                //  - Their time range overlaps with the canceled appointment.

                $replacements = $entry->appointments->filter(function ($app) use ($cancel) {
                    return !Str::contains($app->subject, 'annul')
                        && $app->created_at->gt($cancel->updated_at)
                        && $app->start_date <= $cancel->end_date
                        && $app->end_date >= $cancel->start_date;
                });

                // Compute the total overlapping duration (in hours) from these replacements.
                $overlapHours = $this->computeUnionOverlap(
                    $cancel->start_date,
                    $cancel->end_date,
                    $replacements
                );

                // For this canceled appointment, the not-replaced portion is the original duration minus any overlap.
                $notReplaced = $cancel->duration_hours - $overlapHours;
                if ($notReplaced < 0) {
                    $notReplaced = 0;
                }
                $canceledNotReplaced += $notReplaced;
            }

            $entry->canceled_hours_not_replaced = $canceledNotReplaced;
        });

        return $entries;
    }

    /**
     * Given a canceled appointment's start and end times and a collection
     * of replacement appointments, compute the total (union) overlapping duration in hours.
     *
     * @param Carbon     $cancelStart
     * @param Carbon     $cancelEnd
     * @param Collection $replacements
     *
     * @return float
     */
    private function computeUnionOverlap($cancelStart, $cancelEnd, $replacements)
    {
        $intervals = [];

        // For each replacement appointment, compute its overlap with the canceled interval.
        foreach ($replacements as $r) {
            // Determine the overlap start and end.
            $overlapStart = $cancelStart->gt($r->start_date) ? $cancelStart : $r->start_date;
            $overlapEnd = $cancelEnd->lt($r->end_date) ? $cancelEnd : $r->end_date;

            // Only consider if there is an actual overlap.
            if ($overlapStart->lt($overlapEnd)) {
                $intervals[] = [$overlapStart, $overlapEnd];
            }
        }

        // Sort intervals by their start time.
        usort($intervals, function ($a, $b) {
            return $a[0]->timestamp - $b[0]->timestamp;
        });

        // Merge overlapping intervals to avoid double-counting.
        $merged = [];
        foreach ($intervals as $interval) {
            if (empty($merged)) {
                $merged[] = $interval;
            } else {
                // Get the last merged interval.
                $last = $merged[count($merged) - 1];
                // If the current interval starts before (or exactly when) the last ends, merge them.
                if ($interval[0]->timestamp <= $last[1]->timestamp) {
                    // Extend the last interval's end if needed.
                    $merged[count($merged) - 1][1] = $interval[1]->timestamp > $last[1]->timestamp
                        ? $interval[1]
                        : $last[1];
                } else {
                    $merged[] = $interval;
                }
            }
        }

        // Sum up the total duration (in minutes) of the merged intervals.
        $totalMinutes = 0;
        foreach ($merged as $interval) {
            $totalMinutes += $interval[1]->diffInMinutes($interval[0]);
        }

        // Return the duration in hours.
        return $totalMinutes / 60;
    }

    /**
     * Limit appointments to a maximum count without cutting off in the middle of a day.
     *
     * @param Collection $appointments (sorted in ascending date)
     */
    protected function limitAppointmentsWithoutCuttingDay(Collection $appointments, int $maxAppointments): Collection
    {
        $selected = collect();

        foreach ($appointments as $appointment) {
            if ($selected->count() < $maxAppointments) {
                // Under the limit, just add
                $selected->push($appointment);
            } else {
                // At or over the limit; check if it's the same day as the last one
                $currentDay = $appointment->date->format('Y-m-d');
                $lastDay = $selected->last()->date->format('Y-m-d');

                if ($currentDay === $lastDay) {
                    // Same day => keep it to avoid a partial day
                    $selected->push($appointment);
                } else {
                    // Different day => stop
                    break;
                }
            }
        }

        return $selected;
    }

    /**
     * Mark appointments as "new" if created < 2 days ago.
     */
    protected function markNewAppointments(Collection $appointments): Collection
    {
        $twoDaysAgo = Carbon::now()->subDays(2);

        return $appointments->transform(function ($appointment) use ($twoDaysAgo) {
            $appointment->is_new = $appointment->created_at->greaterThan($twoDaysAgo);

            return $appointment;
        });
    }

    /**
     * Group appointments by day (Y-m-d), then map each day into a formatted structure.
     */
    protected function groupAndFormatAppointments(Collection $appointments): Collection
    {
        return $appointments
            ->groupBy(fn ($appointment) => $appointment->date->format('Y-m-d'))
            ->map(function ($dailyAppointments, $date) {
                $dateObj = Carbon::parse($date);

                return [
                    'formatted_date' => $dateObj->translatedFormat('l d F'),
                    'relative_date'  => $dateObj->diffForHumans(),
                    'appointments'   => $dailyAppointments,
                ];
            });
    }
}
