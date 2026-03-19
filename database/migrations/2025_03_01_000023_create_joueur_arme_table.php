<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joueur_arme', function (Blueprint $table) {
            $table->unsignedBigInteger('fid_joueur');
            $table->unsignedInteger('fid_arme');
            $table->tinyInteger('niveau')->default(1);
            $table->tinyInteger('rang')->default(1);

            $table->primary(['fid_joueur', 'fid_arme']);
            $table->foreign('fid_joueur', 'fk_ja_joueur')
                ->references('id')->on('users')
                ->onDelete('cascade');
            $table->foreign('fid_arme', 'fk_ja_arme')
                ->references('id_arme')->on('armes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joueur_arme');
    }
};
