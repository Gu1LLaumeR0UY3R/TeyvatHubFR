<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('pseudo', 100)->nullable()->unique()->after('name');
            $table->string('avatar', 255)->nullable()->after('email');
            $table->string('banniere', 255)->nullable()->after('avatar');
            $table->text('bio_joueur')->nullable()->after('banniere');
            $table->string('uid_genshin', 20)->nullable()->unique()->after('bio_joueur');
            $table->dateTime('date_inscription')->useCurrent()->after('uid_genshin');
            $table->dateTime('derniere_connexion')->nullable()->after('date_inscription');
            $table->dateTime('banni_le')->nullable()->after('derniere_connexion');
            $table->text('motif_ban')->nullable()->after('banni_le');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pseudo', 'avatar', 'banniere', 'bio_joueur', 'uid_genshin', 'date_inscription', 'derniere_connexion', 'banni_le', 'motif_ban']);
        });
    }
};
