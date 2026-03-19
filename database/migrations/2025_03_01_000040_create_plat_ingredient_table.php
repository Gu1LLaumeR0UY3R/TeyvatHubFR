<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plat_ingredient', function (Blueprint $table) {
            $table->unsignedInteger('fid_plat');
            $table->unsignedInteger('fid_ingredient');
            $table->tinyInteger('quantite')->default(1);

            $table->primary(['fid_plat', 'fid_ingredient']);
            $table->foreign('fid_plat', 'fk_pi_plat')
                ->references('id_plat')->on('plat')
                ->onDelete('cascade');
            $table->foreign('fid_ingredient', 'fk_pi_ingre')
                ->references('id_ingredient')->on('ingrédient')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plat_ingredient');
    }
};
