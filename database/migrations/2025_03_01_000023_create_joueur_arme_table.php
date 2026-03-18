<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_arme', function (Blueprint $table) {
            $table->unsignedInteger('fid_joueur');
            $table->unsignedInteger('fid_arme');
            $table->unsignedTinyInteger('niveau')->default(1);
            $table->unsignedTinyInteger('rang')->default(1);
            $table->primary(['fid_joueur', 'fid_arme']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_arme');
    }
};
