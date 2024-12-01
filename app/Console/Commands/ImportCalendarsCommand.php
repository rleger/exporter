<?php

// app/Console/Commands/ImportCalendarsCommand.php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Calendar;
use Sabre\VObject\Reader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportCalendarsCommand extends Command
{
    protected $signature = 'calendars:import';

    protected $description = 'Importer les données des calendriers ICS.';

    public function handle()
    {
        $calendars = Calendar::with('user')->get();

        foreach ($calendars as $calendar) {
            $this->info("Importation du calendrier : {$calendar->name} (Utilisateur : {$calendar->user->name})");

            try {
                $icsContent = file_get_contents($calendar->url);
                $vcalendar = Reader::read($icsContent);
            } catch (\Exception $e) {
                $this->error("Erreur lors de la lecture du calendrier {$calendar->name} : ".$e->getMessage());
                Log::error("Erreur lors de la lecture du calendrier {$calendar->name}", ['error' => $e->getMessage()]);
                continue;
            }

            foreach ($vcalendar->VEVENT as $event) {
                // Vérifier si SUMMARY et DESCRIPTION existent
                if (!isset($event->SUMMARY) || !isset($event->DESCRIPTION)) {
                    $this->warn('Événement sans SUMMARY ou DESCRIPTION. Ignoré.');
                    Log::warning('Événement sans SUMMARY ou DESCRIPTION.', ['event' => $event->serialize()]);
                    continue;
                }

                // Extraire les données de l'événement
                $summary = $event->SUMMARY->getValue();
                $description = $event->DESCRIPTION->getValue();

                // Traiter le champ SUMMARY
                $summary = str_replace('\\n', "\n", $summary);

                $lastname = '';
                $firstname = '';
                $birthdate = null;
                $eventDescription = '';

                // Expression régulière améliorée pour gérer les LASTNAME à plusieurs mots, prénoms composés et parenthèses
                $regex = '/^([A-ZÀ-Ÿ\s\-\(\)]+) ((?:[A-ZÀ-Ÿ][a-zà-ÿ\-]+(?:\s[A-ZÀ-Ÿ][a-zà-ÿ\-]+)*)) \((\d{2}\.\d{2}\.\d{4})\)\r?\n \[(.+?)\]/u';

                if (preg_match($regex, $summary, $matches)) {
                    $lastname = trim($matches[1]);       // e.g., "LAMBELET (YARMYSH)"
                    $firstname = trim($matches[2]);      // e.g., "Anastasia"

                    // Gestion de Carbon avec fallback à null en cas d'échec
                    try {
                        $birthdate = Carbon::createFromFormat('d.m.Y', $matches[3])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->warn("Format de date invalide pour l'événement : ".$summary);
                        Log::warning("Format de date invalide pour l'événement : ".$summary, ['error' => $e->getMessage()]);
                        $birthdate = null;
                    }

                    $eventDescription = $matches[4];
                } else {
                    // Loguer le résumé non conforme
                    $this->warn('Format inattendu du résumé : '.$summary);
                    Log::warning('Format inattendu du résumé : '.$summary, ['raw_summary' => $summary]);
                    continue;
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

                // Vérifier que tous les champs nécessaires sont présents
                if (empty($lastname) || empty($firstname) || empty($email) || empty($tel)) {
                    $this->warn('Événement incomplet : '.$summary);
                    Log::warning('Événement incomplet.', [
                        'summary'   => $summary,
                        'lastname'  => $lastname,
                        'firstname' => $firstname,
                        'email'     => $email,
                        'tel'       => $tel,
                    ]);
                    continue;
                }

                // Créer ou mettre à jour l'entrée
                Entry::updateOrCreate(
                    [
                        'calendar_id' => $calendar->id,
                        'lastname'    => $lastname,
                        'name'        => $firstname,
                        'birthdate'   => $birthdate,
                    ],
                    [
                        'tel'         => $tel,
                        'email'       => $email,
                        'description' => $eventDescription,
                    ]
                );

                $this->info("Événement importé : {$firstname} {$lastname}");
                Log::info('Événement importé', ['firstname' => $firstname, 'lastname' => $lastname]);
            }
        }

        $this->info('Importation terminée.');
        Log::info('Importation des calendriers terminée.');
    }
}
