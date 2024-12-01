<?php

namespace App\Http\Controllers;

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
}
