<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emprunts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('utilisateur_id')
                  ->constrained('utilisateurs')
                  ->cascadeOnDelete();
            $table->string('livre_isbn');
            $table->foreign('livre_isbn')
                  ->references('isbn')
                  ->on('livres')
                  ->cascadeOnDelete();

            $table->dateTime('date_emprunt');
            $table->dateTime('date_retour_prevue');
            $table->dateTime('date_retour_effective')->nullable();

            $table->integer('renouvellements')->default(0);

            $table->enum('statut', ['en cours', 'retourné', 'perdu'])
                  ->default('en cours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emprunts');
    }
};
