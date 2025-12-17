<?php

namespace App\Http\Controllers;

use App\Exports\EntriesExport;
use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');
        $allEntries = $request->has('all_entries');

        // Sorting parameters with defaults (removed name/lastname/birthdate due to encryption)
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        // Allowed sort columns (encrypted fields removed)
        $allowedSorts = ['created_at', 'updated_at', 'appointments_count', 'total_duration'];

        // Validate sort parameters
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // Build base query
        $query = $this->buildQuery($user, $allEntries, $sort, $direction);

        // Apply search filter using blind indexes (exact match only, case-insensitive)
        if ($search) {
            $searchLower = mb_strtolower($search);
            $query->where(function ($q) use ($search, $searchLower) {
                $q->whereBlind('name', 'name_index', $searchLower)
                  ->orWhereBlind('lastname', 'lastname_index', $searchLower)
                  ->orWhereBlind('tel', 'tel_index', $search)
                  ->orWhereBlind('email', 'email_index', $searchLower);
            });
        }

        // Paginate results and preserve query parameters
        $entries = $query->paginate(10)->appends($request->all());

        // Convert total_duration_minutes to HH:MM format
        foreach ($entries as $entry) {
            $totalMinutes = max(0, $entry->total_duration_minutes ?? 0);

            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            $entry->total_duration = sprintf('%02d:%02d', $hours, $minutes);
        }

        return view('entries.index', compact('entries', 'sort', 'direction', 'search'));
    }

    /**
     * Build the query to retrieve entries.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable $user
     * @param bool                                       $allEntries
     * @param string                                     $sort
     * @param string                                     $direction
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildQuery($user, $allEntries, $sort, $direction)
    {
        $entryQuery = Entry::query()
            ->with('calendar.user', 'appointments')
            ->withCount('appointments')
            ->addSelect([
                'total_duration_minutes' => DB::table('appointments')
                    ->selectRaw('COALESCE(SUM(TIMESTAMPDIFF(MINUTE, start_date, end_date)), 0)')
                    ->whereColumn('appointments.entry_id', 'entries.id'),
            ]);

        if (!$allEntries) {
            $entryQuery->whereIn('calendar_id', $user->calendars->pluck('id'));
        }

        $entryQuery->when('appointments_count' !== $sort && 'total_duration' !== $sort, function ($q) use ($sort, $direction) {
            $q->orderBy($sort, $direction);
        }, function ($q) use ($sort, $direction) {
            if ('appointments_count' === $sort) {
                $q->orderBy('appointments_count', $direction);
            } elseif ('total_duration' === $sort) {
                $q->orderBy('total_duration_minutes', $direction);
            }
        });

        return $entryQuery;
    }

    public function export(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');
        $allEntries = $request->has('all_entries');

        // Sorting parameters
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $allowedSorts = ['created_at', 'updated_at', 'appointments_count', 'total_duration'];

        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'desc';
        }

        // Build query
        $query = $this->buildQuery($user, $allEntries, $sort, $direction);

        // Apply search filter (case-insensitive)
        if ($search) {
            $searchLower = mb_strtolower($search);
            $query->where(function ($q) use ($search, $searchLower) {
                $q->whereBlind('name', 'name_index', $searchLower)
                  ->orWhereBlind('lastname', 'lastname_index', $searchLower)
                  ->orWhereBlind('tel', 'tel_index', $search)
                  ->orWhereBlind('email', 'email_index', $searchLower);
            });
        }

        $entries = $query->get();

        return Excel::download(
            new EntriesExport($entries),
            'patients-'.now()->format('Y-m-d').'.xlsx'
        );
    }
}
