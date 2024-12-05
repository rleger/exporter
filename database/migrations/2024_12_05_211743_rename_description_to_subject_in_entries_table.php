<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('description', 'subject');
        });
    }

    public function down()
    {
        Schema::table('entries', function (Blueprint $table) {
            $table->renameColumn('subject', 'description');
        });
    }
};
