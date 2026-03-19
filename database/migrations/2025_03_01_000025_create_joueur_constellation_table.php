<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_constellation', function (Blueprint $table) {
            $table->unsignedBigInteger('fid_joueur');
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_constellation');
            $table->boolean('debloquee')->default(false);

            $table->primary(['fid_joueur', 'fid_perso', 'fid_constellation']);
            $table->foreign('fid_joueur', 'fk_jc_joueur')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('fid_perso', 'fk_jc_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
            $table->foreign('fid_constellation', 'fk_jc_const')
                ->references('id_const')->on('constellation')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_constellation');
    }
};
