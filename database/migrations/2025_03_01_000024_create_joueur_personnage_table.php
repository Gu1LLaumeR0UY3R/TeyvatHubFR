<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_personnage', function (Blueprint $table) {
            $table->unsignedInteger('fid_joueur');
            $table->unsignedInteger('fid_perso');
            $table->unsignedTinyInteger('niveau')->default(1);
            $table->unsignedTinyInteger('affinite')->default(0)->nullable();
            $table->boolean('perso_amelioration')->default(false);
            $table->unsignedInteger('fid_joueur_arme_joueur')->nullable();
            $table->unsignedInteger('fid_joueur_arme_arme')->nullable();
            $table->primary(['fid_joueur', 'fid_perso']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_personnage');
    }
};
