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

            // This array will hold appointment IDs grouped by date
            // Example structure:  [ '2025-01-13' => [3, 7, 10], '2025-01-14' => [...], ... ]
            $importedAppointmentsByDate = [];

            foreach ($vcalendar->VEVENT as $event) {
                // Vérifier que SUMMARY et DESCRIPTION existent
                if (!isset($event->SUMMARY) || !isset($event->DESCRIPTION)) {
                    $this->warn('Événement sans SUMMARY ou DESCRIPTION. Ignoré.');
                    Log::warning('Événement sans SUMMARY ou DESCRIPTION.', ['event' => $event->serialize()]);
                    continue;
                }

                // Extraire les données de l'événement
                $summary = $event->SUMMARY->getValue();
                $description = $event->DESCRIPTION->getValue();

                // Remplacer les séquences '\n' par des sauts de ligne réels
                $summary = str_replace('\\n', "\n", $summary);
                $description = str_replace('\\n', "\n", $description);

                $lastname = '';
                $firstname = '';
                $birthdate = null;
                $eventSubject = '';

                // Expression régulière ajustée pour gérer les caractères spéciaux
                $regex = '/^(.+?) \((\d{2}\.\d{2}\.\d{4})\)\n \[(.+?)\]/u';

                if (preg_match($regex, $summary, $matches)) {
                    $fullName = trim($matches[1]);

                    // Séparer les parties du nom en utilisant mb_split pour UTF-8
                    $nameParts = mb_split('\s+', $fullName);

                    $lastnameParts = [];
                    $firstnameParts = [];

                    foreach ($nameParts as $part) {
                        if (mb_strtoupper($part, 'UTF-8') === $part) {
                            // Majuscules => Nom de famille
                            $lastnameParts[] = $part;
                        } else {
                            // Sinon => Prénom
                            $firstnameParts[] = $part;
                        }
                    }

                    $lastname = implode(' ', $lastnameParts);
                    $firstname = implode(' ', $firstnameParts);

                    // S'assurer que le nom de famille est entièrement en majuscules
                    $lastname = mb_strtoupper($lastname, 'UTF-8');

                    // Gérer la date de naissance
                    try {
                        $birthdate = Carbon::createFromFormat('d.m.Y', $matches[2])->format('Y-m-d');
                    } catch (\Exception $e) {
                        $this->warn("Format de date invalide pour l'événement : ".$summary);
                        Log::warning("Format de date invalide pour l'événement : ".$summary, ['error' => $e->getMessage()]);
                        $birthdate = null;
                    }

                    $eventSubject = $matches[3];
                } else {
                    // Log format non conforme
                    $this->warn('Format inattendu du résumé : '.$summary);
                    Log::warning('Format inattendu du résumé : '.$summary, ['raw_summary' => $summary]);
                    continue;
                }

                // Extraire Téléphone
                if (preg_match('/Tel ?: ([^\n]+)/i', $description, $matches)) {
                    $tel = trim($matches[1]);
                } else {
                    $tel = '';
                }

                // Extraire Email
                if (preg_match('/Email ?: ([^\n]+)/i', $description, $matches)) {
                    $email = trim($matches[1]);
                } else {
                    $email = '';
                }

                // Extraire la partie restante de la DESCRIPTION
                // Supprimer Tel et Email de la description
                $remainingDescription = preg_replace('/Tel ?: [^\n]+\n?/', '', $description);
                $remainingDescription = preg_replace('/Email ?: [^\n]+\n?/', '', $remainingDescription);
                $remainingDescription = trim($remainingDescription);

                // Vérifier que tous les champs nécessaires sont présents
                if (empty($lastname) || empty($firstname)) {
                    $this->warn('Événement incomplet : '.$summary);
                    Log::warning('Événement incomplet.', [
                        'summary'   => $summary,
                        'lastname'  => $lastname,
                        'firstname' => $firstname,
                    ]);
                    continue;
                }

                // Find existing entry using blind indexes (for encrypted fields)
                $entry = Entry::query()
                    ->where('calendar_id', $calendar->id)
                    ->whereBlind('lastname', 'lastname_index', $lastname)
                    ->whereBlind('name', 'name_index', $firstname)
                    ->when($birthdate, function ($query) use ($birthdate) {
                        // For birthdate, we compare the date value directly since it's not encrypted
                        $query->whereDate('birthdate', $birthdate);
                    }, function ($query) {
                        $query->whereNull('birthdate');
                    })
                    ->first();

                if ($entry) {
                    // Update existing entry
                    $entry->update([
                        'tel'     => $tel,
                        'email'   => $email,
                        'subject' => $eventSubject,
                    ]);
                } else {
                    // Create new entry
                    $entry = Entry::create([
                        'calendar_id' => $calendar->id,
                        'lastname'    => $lastname,
                        'name'        => $firstname,
                        'birthdate'   => $birthdate,
                        'tel'         => $tel,
                        'email'       => $email,
                        'subject'     => $eventSubject,
                    ]);
                }

                $this->info("Événement importé : {$firstname} {$lastname}");
                Log::info('Événement importé', ['firstname' => $firstname, 'lastname' => $lastname]);

                // Extraire la date de début et de fin de l'événement
                $dtstart = isset($event->DTSTART) ? $event->DTSTART->getDateTime() : null;
                $dtend = isset($event->DTEND) ? $event->DTEND->getDateTime() : null;

                // Extraire les champs CREATED et LAST-MODIFIED
                $createdAt = isset($event->CREATED) ? $event->CREATED->getDateTime() : null;
                $lastModified = isset($event->{'LAST-MODIFIED'}) ? $event->{'LAST-MODIFIED'}->getDateTime() : null;

                // Vérifier que la date de début est présente
                if ($dtstart) {
                    // Préparer les données pour Appointment
                    $appointmentData = [
                        'subject'     => $entry->subject,
                        'description' => $remainingDescription,
                        'start_date'  => $dtstart,
                        'end_date'    => $dtend,
                    ];

                    if ($createdAt) {
                        $appointmentData['created_at'] = $createdAt;
                    }

                    if ($lastModified) {
                        $appointmentData['updated_at'] = $lastModified;
                    }

                    // Créer ou mettre à jour l'Appointment
                    $appointment = Appointment::updateOrCreate(
                        [
                            'entry_id' => $entry->id,
                            'date'     => $dtstart,
                        ],
                        $appointmentData
                    );
                    // Convert the event's date to YYYY-MM-DD so we can group them
                    $dateKey = $dtstart->format('Y-m-d');
                    $importedAppointmentsByDate[$dateKey][] = $appointment->id;
                } else {
                    $this->warn('Date de début manquante pour l\'événement : '.$summary);
                    Log::warning('Date de début manquante pour l\'événement : '.$summary);
                    continue;
                }
            }

            /*
             * ====================
             *   CLEAN-UP STEP
             * ====================.
             *
             *  For *each date* that appeared in the ICS feed, remove any existing
             *  appointments (on that date + in this calendar) that are not in the new import list.
             */
            foreach ($importedAppointmentsByDate as $dateKey => $importedIds) {
                Appointment::whereHas('entry', function ($query) use ($calendar) {
                    // Only appointments for the same calendar
                    $query->where('calendar_id', $calendar->id);
                })
                    ->whereDate('date', $dateKey)
                    ->whereNotIn('id', $importedIds)
                    ->delete();
            }

            $this->info('Importation terminée.');
            Log::info('Importation des calendriers terminée.');
        }
    }
}
