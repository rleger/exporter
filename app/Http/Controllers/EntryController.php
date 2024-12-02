<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');

        // Récupérer les paramètres de tri avec des valeurs par défaut
        $sort = $request->input('sort', 'lastname');
        $direction = $request->input('direction', 'asc');

        // Définir les colonnes autorisées pour le tri
        $allowedSorts = ['name', 'lastname', 'created_at', 'updated_atj', 'birthdate', 'appointments_count'];

        // Valider les paramètres de tri
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'lastname';
        }

        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }

        // Construire la requête de base avec les calendriers de l'utilisateur
        $query = Entry::whereIn('calendar_id', $user->calendars->pluck('id'))
               ->with('calendar.user')
               ->withCount('appointments')
               ->orderBy('appointments_count' === $sort ? 'appointments_count' : $sort, $direction);

        // Appliquer le filtre de recherche si un terme est fourni
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Paginer les résultats et conserver les paramètres de requête
        $entries = $query->paginate(50)->withQueryString();

        return view('entries.index', compact('entries', 'sort', 'direction', 'search'));
    }
}
