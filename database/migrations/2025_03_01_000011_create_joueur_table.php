<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur', function (Blueprint $table) {
            $table->increments('id_joueur');
            $table->string('pseudo', 50)->unique();
            $table->string('email', 100)->unique();
            $table->string('mot_de_passe', 255);
            $table->string('avatar', 255)->nullable();
            $table->string('banniere', 255)->nullable();
            $table->text('bio_joueur')->nullable();
            $table->string('uid_genshin', 20)->nullable()->unique();
            $table->dateTime('date_inscription')->useCurrent();
            $table->dateTime('derniere_connexion')->nullable();
            $table->dateTime('banni_le')->nullable();
            $table->string('motif_ban', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur');
    }
};
