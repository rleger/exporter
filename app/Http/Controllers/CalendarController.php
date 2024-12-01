<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $calendars = $user->calendars;

        return view('calendars.index', compact('calendars'));
    }

    public function create()
    {
        return view('calendars.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'url'  => 'required|url',
        ]);

        $user = auth()->user();

        $user->calendars()->create($request->only('name', 'url'));

        return redirect()->route('calendars.index')->with('success', 'Calendrier ajouté avec succès.');
    }

    public function destroy(Calendar $calendar)
    {
        $user = auth()->user();

        // Vérifier que l'utilisateur est propriétaire du calendrier
        if ($calendar->user_id !== $user->id) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer ce calendrier.');
        }

        $calendar->delete();

        return redirect()->route('calendars.index')->with('success', 'Calendrier supprimé avec succès.');
    }
}
