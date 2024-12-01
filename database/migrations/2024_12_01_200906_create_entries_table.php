<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calendar_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('lastname');
            $table->date('birthdate')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['calendar_id', 'name', 'lastname', 'birthdate']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('entries');
    }
};
