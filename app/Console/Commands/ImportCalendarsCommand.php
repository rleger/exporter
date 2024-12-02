<?php

// app/Console/Commands/ImportCalendarsCommand.php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Entry;
use App\Models\Calendar;
use Sabre\VObject\Reader;
use App\Models\Appointment;
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
                // Verify that SUMMARY and DESCRIPTION exist
                if (!isset($event->SUMMARY) || !isset($event->DESCRIPTION)) {
                    $this->warn('Événement sans SUMMARY ou DESCRIPTION. Ignoré.');
                    Log::warning('Événement sans SUMMARY ou DESCRIPTION.', ['event' => $event->serialize()]);
                    continue;
                }

                // Extract event data
                $summary = $event->SUMMARY->getValue();
                $description = $event->DESCRIPTION->getValue();

                // Process the SUMMARY field
                $summary = str_replace('\\n', "\n", $summary);

                $lastname = '';
                $firstname = '';
                $birthdate = null;
                $eventDescription = '';

                // Adjusted regex
                $regex = '/^(.+?) \((\d{2}\.\d{2}\.\d{4})\)\r?\n \[(.+?)\]/u';

                if (preg_match($regex, $summary, $matches)) {
                    $fullName = trim($matches[1]);

                    // Split and classify name parts
                    $nameParts = preg_split('/\s+/', $fullName);

                    $lastnameParts = [];
                    $firstnameParts = [];

                    foreach ($nameParts as $part) {
                        if (mb_strtoupper($part, 'UTF-8') === $part) {
                            // Uppercase => Last name
                            $lastnameParts[] = $part;
                        } else {
                            // Not all uppercase => First name
                            $firstnameParts[] = $part;
                        }
                    }

                    $lastname = implode(' ', $lastnameParts);
                    $firstname = implode(' ', $firstnameParts);

                    // Ensure last name is fully uppercase
                    $lastname = mb_strtoupper($lastname, 'UTF-8');

                    // Handle birthdate
                    try {
                        $birthdate = Carbon::createFromFormat('d.m.Y', $matches[2])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->warn("Format de date invalide pour l'événement : ".$summary);
                        Log::warning("Format de date invalide pour l'événement : ".$summary, ['error' => $e->getMessage()]);
                        $birthdate = null;
                    }

                    $eventDescription = $matches[3];
                } else {
                    // Log non-conforming summary
                    $this->warn('Format inattendu du résumé : '.$summary);
                    Log::warning('Format inattendu du résumé : '.$summary, ['raw_summary' => $summary]);
                    continue;
                }

                // Extract Telephone
                $tel = '';
                if (preg_match('/Tel ?: ([^\n]+)/i', $description, $matches)) {
                    $tel = trim($matches[1]);
                }

                // Extract Email
                $email = '';
                if (preg_match('/Email ?: ([^\n]+)/i', $description, $matches)) {
                    $email = trim($matches[1]);
                }

                // Check that all necessary fields are present
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

                // Create or update the entry
                $entry = Entry::updateOrCreate(
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

                // Extraire la date de début de l'événement

                $dtstart = null;
                if (isset($event->DTSTART)) {
                    $dtstart = $event->DTSTART->getDateTime();
                }

                // Vérifier que la date est présente
                if ($dtstart) {
                    // Créer ou mettre à jour l'Appointment
                    Appointment::updateOrCreate(
                        [
                            'entry_id' => $entry->id,
                            'date'     => $dtstart,
                        ],
                        [
                            'subject' => $entry->description,
                        ]
                    );
                } else {
                    $this->warn('Date de début manquante pour l\'événement : '.$summary);
                    Log::warning('Date de début manquante.', ['summary' => $summary]);
                }

                $this->info("Événement importé : {$firstname} {$lastname}");
                Log::info('Événement importé', ['firstname' => $firstname, 'lastname' => $lastname]);
            }
        }

        $this->info('Importation terminée.');
        Log::info('Importation des calendriers terminée.');
    }
}
