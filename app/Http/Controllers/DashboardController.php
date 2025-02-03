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
     * Fetch the top n entries (default 20) with the most cancellations,
     * limited to the current user's calendars.
     */
    protected function getTopCancelledEntries(int $limit = 20)
    {
        // Get the current user's calendar IDs.
        $userCalendarIds = $this->getUserCalendarIds()->toArray();

        // Retrieve entries belonging to the user's calendars that have at least one canceled appointment.
        $entries = Entry::query()
            ->whereIn('calendar_id', $userCalendarIds)
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
            // Filter the entry's appointments to get only the canceled ones.
            $canceledAppointments = $entry->appointments->filter(function ($app) {
                // Use a case-insensitive check for 'annul'
                return Str::contains(mb_strtolower($app->subject), 'annul');
            });

            // Sum up total canceled hours.
            $entry->canceled_hours = $canceledAppointments->sum(function ($app) {
                return $app->duration_hours;
            });
        });

        return $entries;
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
