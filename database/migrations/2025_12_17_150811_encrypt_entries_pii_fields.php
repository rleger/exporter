<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if already migrated (check for one of the new columns)
        if (Schema::hasColumn('entries', 'name_index')) {
            return;
        }

        // Disable foreign key checks temporarily (MySQL only)
        $isMysql = DB::getDriverName() === 'mysql';
        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        // Try to drop unique constraint if it exists
        try {
            Schema::table('entries', function (Blueprint $table) {
                $table->dropUnique(['calendar_id', 'name', 'lastname', 'birthdate']);
            });
        } catch (\Exception $e) {
            // Constraint may not exist, continue
        }

        if ($isMysql) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        Schema::table('entries', function (Blueprint $table) {
            // Add blind index columns for searchable encrypted fields
            $table->string('name_index')->nullable()->after('name');
            $table->string('lastname_index')->nullable()->after('lastname');
            $table->string('birthdate_index')->nullable()->after('birthdate');
            $table->string('tel_index')->nullable()->after('tel');
            $table->string('email_index')->nullable()->after('email');

            // Add indexes on blind index columns
            $table->index('name_index');
            $table->index('lastname_index');
            $table->index('birthdate_index');
            $table->index('tel_index');
            $table->index('email_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('entries', 'name_index')) {
            return;
        }

        Schema::table('entries', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['name_index']);
            $table->dropIndex(['lastname_index']);
            $table->dropIndex(['birthdate_index']);
            $table->dropIndex(['tel_index']);
            $table->dropIndex(['email_index']);

            // Drop blind index columns
            $table->dropColumn([
                'name_index',
                'lastname_index',
                'birthdate_index',
                'tel_index',
                'email_index',
            ]);

            // Restore original unique constraint
            $table->unique(['calendar_id', 'name', 'lastname', 'birthdate']);
        });
    }
};
