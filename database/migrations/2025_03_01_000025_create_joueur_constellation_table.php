<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_constellation', function (Blueprint $table) {
            $table->unsignedInteger('fid_joueur');
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_constellation');
            $table->boolean('debloquee')->default(false);
            $table->primary(['fid_joueur', 'fid_perso', 'fid_constellation']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_constellation');
    }
};
