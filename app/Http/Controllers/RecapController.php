<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RecapController extends Controller
{
    public function index()
    {
        $calendars = $this->getAllCalendars();

        return view('recap.index', ['calendars' => $calendars]);
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
}
