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
            ->with('calendar') // Charger les relations
            ->with('calendar.user') // Charger les relations
            ->orderBy('name') // Trie par prénom
            ->paginate(50);   // Pagination avec 15 éléments par page

        return view('entries.index', compact('entries'));
    }
}
