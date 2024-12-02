<?php

namespace App\Http\Controllers;

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
        // Récupérer les Calendars avec le nom de l'utilisateur et le nombre d'Entries
        $calendars = Calendar::with('user')
            ->withCount('entries')
            ->get();

        // Récupérer les 20 derniers rendez-vous ajoutés
        $recentAppointments = Appointment::with('entry')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Récupérer les 20 derniers rendez-vous modifiés
        $updatedAppointments = Appointment::with('entry')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard', compact('calendars', 'recentAppointments', 'updatedAppointments'));
    }
}
