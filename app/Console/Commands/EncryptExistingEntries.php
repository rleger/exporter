<?php

namespace App\Console\Commands;

use App\Models\Entry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class EncryptExistingEntries extends Command
{
    protected $signature = 'entries:encrypt
                            {--batch=100 : Number of entries to process per batch}
                            {--dry-run : Preview changes without saving}';

    protected $description = 'Encrypt existing entries and populate blind indexes for CipherSweet';

    public function handle(): int
    {
        $batchSize = (int) $this->option('batch');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be saved');
        }

        $total = DB::table('entries')->count();

        if ($total === 0) {
            $this->info('No entries to process.');

            return self::SUCCESS;
        }

        $this->info("Processing {$total} entries in batches of {$batchSize}...");
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $processed = 0;
        $skipped = 0;
        $errors = 0;

        // Process in chunks using raw DB to avoid CipherSweet decryption
        DB::table('entries')
            ->select(['id', 'name', 'lastname', 'birthdate', 'tel', 'email'])
            ->orderBy('id')
            ->chunk($batchSize, function ($entries) use (&$processed, &$skipped, &$errors, $dryRun, $bar) {
                foreach ($entries as $entry) {
                    try {
                        // Check if already encrypted (CipherSweet adds a prefix)
                        $isEncrypted = $this->isEncrypted($entry->name);

                        if ($isEncrypted) {
                            $skipped++;
                            $bar->advance();
                            continue;
                        }

                        if (!$dryRun) {
                            // Create a new Entry model instance and set raw values
                            // This will trigger CipherSweet encryption on save
                            $model = new Entry();
                            $model->exists = true;
                            $model->id = $entry->id;

                            // Set the values - CipherSweet will encrypt on save
                            $model->name = $entry->name;
                            $model->lastname = $entry->lastname;
                            $model->tel = $entry->tel;
                            $model->email = $entry->email;

                            // Use query builder to update without triggering model events
                            // that might cause issues with the encrypted fields
                            $encryptedData = [];

                            // Get the encrypted values by accessing the model's attributes
                            // after CipherSweet processes them
                            $model->syncOriginal();

                            // Force the model to encrypt by marking attributes as dirty
                            $model->name = $entry->name;
                            $model->lastname = $entry->lastname;
                            $model->tel = $entry->tel;
                            $model->email = $entry->email;

                            // Save without touching timestamps
                            $model->timestamps = false;
                            $model->saveQuietly();
                        }

                        $processed++;
                    } catch (\Exception $e) {
                        $errors++;
                        $this->newLine();
                        $this->error("Error processing entry ID {$entry->id}: {$e->getMessage()}");
                    }

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine(2);

        $this->info("Processed: {$processed}");
        $this->info("Skipped (already encrypted): {$skipped}");

        if ($errors > 0) {
            $this->error("Errors: {$errors}");

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('This was a dry run. Run without --dry-run to apply changes.');
        } else {
            $this->info('All entries have been encrypted and blind indexes populated.');
        }

        return self::SUCCESS;
    }

    /**
     * Check if a value appears to be encrypted by CipherSweet.
     */
    protected function isEncrypted(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        // CipherSweet modern crypto header starts with "brng:"
        return str_starts_with($value, 'brng:');
    }
}
