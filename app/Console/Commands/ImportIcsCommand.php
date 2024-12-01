<?php

namespace App\Console\Commands;

use Sabre\VObject\Reader;
use Illuminate\Console\Command;

class ImportIcsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:ics {icsFile} {csvFile}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importe un fichier ICS et exporte les données en CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $icsFile = $this->argument('icsFile');
        $csvFile = $this->argument('csvFile');

        if (!file_exists($icsFile)) {
            $this->error("Le fichier ICS n'existe pas.");

            return;
        }

        $icsContent = file_get_contents($icsFile);
        $vcalendar = Reader::read($icsContent);

        $csvData = [];
        // Ajout de la colonne "Description"
        $csvData[] = ['Nom', 'Prénom', 'Date de naissance', 'Téléphone', 'Email', 'Description'];

        foreach ($vcalendar->VEVENT as $event) {
            $summary = $event->SUMMARY->getValue();
            $description = $event->DESCRIPTION->getValue();
            $nom = '';
            $prenom = '';
            $naissance = '';
            $eventDescription = '';

            // Remplacer les séquences '\n' par des sauts de ligne réels
            $summary = str_replace('\\n', "\n", $summary);

            // Débogage : Afficher le contenu de $summary
            // var_dump($summary);

            if (preg_match('/^(.+?) (.+?) \((.+?)\)\n \[(.+?)\]\n/u', $summary, $matches)) {
                $nom = trim($matches[1]);
                $prenom = trim($matches[2]);
                $naissance = isset($matches[3]) ? $matches[3] : '';
                $eventDescription = $matches[4];
            } elseif (preg_match('/^(.+?) (.+?) \((.+?)\)\n/u', $summary, $matches)) {
                $nom = trim($matches[1]);
                $prenom = trim($matches[2]);
                $naissance = $matches[3];
            } elseif (preg_match('/^\[(.+?)\]/u', $summary, $matches)) {
                // Cas où le SUMMARY contient uniquement la description entre crochets
                $eventDescription = $matches[1];
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

            $csvData[] = [$nom, $prenom, $naissance, $tel, $email, $eventDescription];
        }

        // Écrire les données dans le fichier CSV
        $file = fopen($csvFile, 'w');

        // Ajouter l'encodage UTF-8 BOM pour Excel
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        foreach ($csvData as $row) {
            fputcsv($file, $row);
        }

        fclose($file);

        $this->info('Les données ont été exportées avec succès dans le fichier CSV.');
    }
}
