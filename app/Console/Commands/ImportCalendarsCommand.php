<?php

// app/Console/Commands/ImportCalendarsCommand.php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Calendar;
use Sabre\VObject\Reader;
use Illuminate\Console\Command;

class ImportCalendarsCommand extends Command
{
    protected $signature = 'calendars:import';

    protected $description = 'Importer les données des calendriers ICS.';

    public function handle()
    {
        $calendars = Calendar::all();

        foreach ($calendars as $calendar) {
            $this->info("Importation du calendrier : {$calendar->name} (Utilisateur : {$calendar->user->name})");

            $icsContent = file_get_contents($calendar->url);
            $vcalendar = Reader::read($icsContent);

            foreach ($vcalendar->VEVENT as $event) {
                // Extraire les données de l'événement
                $summary = $event->SUMMARY->getValue();
                $description = $event->DESCRIPTION->getValue();

                // Traiter le champ SUMMARY
                $summary = str_replace('\\n', "\n", $summary);
                $nom = '';
                $prenom = '';
                $naissance = null;
                $eventDescription = '';

                if (preg_match('/^(.+?) \((.+?)\)\n \[(.+?)\]/u', $summary, $matches)) {
                    $fullName = trim($matches[1]);
                    $nameParts = explode(' ', $fullName);
                    $prenom = array_pop($nameParts); // The first name is the last word
                    $nom = implode(' ', $nameParts); // The last name is the rest
                    $nom = strtoupper($nom); // Capitalize the last name

                    // Gestion de Carbon avec fallback à null en cas d'échec
                    try {
                        $birthdate = Carbon::createFromFormat('d.m.Y', $matches[3])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->warn("Format de date invalide pour l'événement : ".$summary);
                        $birthdate = null;
                    }
                    $eventDescription = $matches[3];
                }

                // Extraire Téléphone
                $tel = '';
                if (preg_match('/Tel ?: ([^\n]+)/i', $description, $matches)) {
                    $tel = trim($matches[1]);
                }

                // Extraire Email
                $email = '';
                if (preg_match('/Email ?: ([^\n]+)/i', $description, $matches)) {
                    $email = trim($matches[1]);
                }

                // Créer ou mettre à jour l'entrée
                Entry::updateOrCreate(
                    [
                        'calendar_id' => $calendar->id,
                        'name'        => $nom,
                        'lastname'    => $prenom,
                        'birthdate'   => $naissance,
                    ],
                    [
                        'tel'         => $tel,
                        'email'       => $email,
                        'description' => $eventDescription,
                    ]
                );
            }
        }

        $this->info('Importation terminée.');
    }
}
