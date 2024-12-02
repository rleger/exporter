<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');

        // Récupérer les paramètres de tri avec des valeurs par défaut
        $sort = $request->input('sort', 'lastname');
        $direction = $request->input('direction', 'asc');

        // Définir les colonnes autorisées pour le tri
        $allowedSorts = ['name', 'lastname', 'created_at', 'updated_at', 'birthdate', 'appointments_count', 'total_duration'];

        // Valider les paramètres de tri
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'lastname';
        }

        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }

        // Construire la requête de base avec les calendriers de l'utilisateur
        $query = Entry::whereIn('calendar_id', $user->calendars->pluck('id'))
               ->with('calendar.user', 'appointments') // Charger les relations
               // ->withCount('appointments') // Supprimé pour éviter les conflits
               ->leftJoin('appointments', 'entries.id', '=', 'appointments.entry_id')
               ->select(
                   'entries.*',
                   DB::raw('COUNT(appointments.id) as appointments_count'),
                   DB::raw('SUM(TIMESTAMPDIFF(MINUTE, appointments.start_date, appointments.end_date)) as total_duration_minutes')
               )
               ->groupBy('entries.id')
               ->when('appointments_count' !== $sort && 'total_duration' !== $sort, function ($q) use ($sort, $direction) {
                   $q->orderBy($sort, $direction);
               }, function ($q) use ($sort, $direction) {
                   if ('appointments_count' === $sort) {
                       $q->orderBy('appointments_count', $direction);
                   } elseif ('total_duration' === $sort) {
                       $q->orderBy('total_duration_minutes', $direction);
                   }
               });

        // Appliquer le filtre de recherche si un terme est fourni
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('tel', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Paginer les résultats et conserver les paramètres de requête
        $entries = $query->paginate(50)->withQueryString();

        // Convertir total_duration_minutes en format HH:MM
        foreach ($entries as $entry) {
            $totalMinutes = max(0, $entry->total_duration_minutes ?? 0);

            $hours = intdiv($totalMinutes, 60);
            $minutes = $totalMinutes % 60;

            $entry->total_duration = sprintf('%02d:%02d', $hours, $minutes);
        }

        return view('entries.index', compact('entries', 'sort', 'direction', 'search'));
    }
}
