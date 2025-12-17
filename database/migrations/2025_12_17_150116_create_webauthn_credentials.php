<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laragear\WebAuthn\Models\WebAuthnCredential;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('webauthn_credentials')) {
            return;
        }

        WebAuthnCredential::migration()->with(function (Blueprint $table) {
            // Here you can add custom columns to the WebAuthn table.
        })->up();
    }

    public function down(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }
};
