<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage_role', function (Blueprint $table) {
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_role');

            $table->primary(['fid_perso', 'fid_role']);

            $table->foreign('fid_perso', 'fk_pr_perso')
                ->references('id_perso')->on('personnage')
                ->onDelete('cascade');
            $table->foreign('fid_role', 'fk_pr_role')
                ->references('id_role')->on('role')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_role');
    }
};
