<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ennemi_region', function (Blueprint $table) {
            $table->unsignedInteger('fid_ennemi');
            $table->unsignedInteger('fid_region');

            $table->primary(['fid_ennemi', 'fid_region']);
            $table->foreign('fid_ennemi', 'fk_er_ennemi')
                ->references('id_ennemi')->on('ennemi')
                ->onDelete('cascade');
            $table->foreign('fid_region', 'fk_er_region')
                ->references('id_region')->on('région')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ennemi_region');
    }
};
