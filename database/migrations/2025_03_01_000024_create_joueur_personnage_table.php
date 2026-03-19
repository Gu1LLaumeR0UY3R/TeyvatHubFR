<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_personnage', function (Blueprint $table) {
            $table->unsignedBigInteger('fid_joueur');
            $table->unsignedInteger('fid_perso');
            $table->tinyInteger('niveau')->default(1);
            $table->tinyInteger('affinite')->default(0);
            $table->boolean('perso_amelioration')->default(false);
            $table->unsignedBigInteger('fid_joueur_arme_joueur')->nullable();
            $table->unsignedInteger('fid_joueur_arme_arme')->nullable();

            $table->primary(['fid_joueur', 'fid_perso']);
            $table->foreign('fid_joueur', 'fk_jp_joueur')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('fid_perso', 'fk_jp_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_personnage');
    }
};
