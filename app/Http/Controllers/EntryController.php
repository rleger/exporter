<?php

namespace App\Http\Controllers;

use App\Models\Entry;

class EntryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Récupérer les entrées liées aux calendriers de l'utilisateur
        $entries = Entry::whereIn('calendar_id', $user->calendars->pluck('id'))
            ->with('calendar.user')
            ->orderBy('lastname')
            ->paginate(50);

        return view('entries.index', compact('entries'));
    }
}
