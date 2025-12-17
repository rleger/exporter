<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Illuminate\Console\Command;

class NormalizeEntriesCommand extends Command
{
    protected $signature = 'entries:normalize';

    protected $description = 'Normalize entry fields to lowercase for case-insensitive search';

    public function handle(): int
    {
        $count = Entry::count();
        $this->info("Normalizing {$count} entries to lowercase...");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        Entry::query()->each(function (Entry $entry) use ($bar) {
            // Re-assign to trigger the lowercase mutators
            $entry->name = $entry->name;
            $entry->lastname = $entry->lastname;
            $entry->email = $entry->email;
            $entry->save();

            $bar->advance();
        });

        $bar->finish();
        $this->newLine();
        $this->info('All entries normalized to lowercase.');

        return Command::SUCCESS;
    }
}
