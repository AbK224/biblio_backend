<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
             // Transformer les anciens "retard" en "en cours"
        DB::table('emprunts')
            ->where('statut', 'retard')
            ->update(['statut' => 'en cours']);

        // Modifier l'enum
        DB::statement("ALTER TABLE emprunts MODIFY statut ENUM('en cours', 'retourné', 'perdu') DEFAULT 'en cours'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emprunts', function (Blueprint $table) {
            //
        DB::statement("ALTER TABLE emprunts MODIFY statut ENUM('en cours', 'retourné', 'retard', 'perdu') DEFAULT 'en cours'");
        });
    }
};
