<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Calendar;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class DashboardController extends Controller
{
    public function importCalendars(Request $request)
    {
        // Exécuter la commande Artisan
        Artisan::call('calendars:import');

        // Récupérer la sortie de la commande
        $output = Artisan::output();

        // Rediriger en arrière avec la sortie de la commande
        return redirect()->back()->with('importOutput', $output);
    }

    public function index()
    {
        $user = auth()->user();

        $threeDaysAgo = Carbon::now()->subDays(3);

        // Récupérer les Calendars appartenant à l'utilisateur
        $calendars = Calendar::with('user')
            ->where('user_id', $user->id)
            ->withCount('entries')
            ->get();

        // Récupérer les IDs des Calendars de l'utilisateur
        $calendarIds = $calendars->pluck('id');

        // Récupérer les Calendars avec le nom de l'utilisateur et le nombre d'Entries
        $calendars = Calendar::with('user')
            ->withCount('entries')
            ->get();

        // Récupérer les 10 derniers rendez-vous ajoutés pour l'utilisateur connecté
        $recentAppointments = Appointment::with('entry')
            ->whereHas('entry', function ($query) use ($calendarIds) {
                $query->whereIn('calendar_id', $calendarIds);
            })
            ->where('date', '>=', $threeDaysAgo)
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        // Récupérer les 10 derniers rendez-vous modifiés pour l'utilisateur connecté
        $updatedAppointments = Appointment::with('entry')
            ->whereHas('entry', function ($query) use ($calendarIds) {
                $query->whereIn('calendar_id', $calendarIds);
            })
            ->where('date', '>=', $threeDaysAgo)
            ->whereColumn('created_at', '!=', 'updated_at')
            ->orderBy('date', 'asc')
            ->orderBy('updated_at', 'asc')
            ->take(10)
            ->get();

        return view('dashboard', compact('calendars', 'recentAppointments', 'updatedAppointments'));
    }
}
