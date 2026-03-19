<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plat', function (Blueprint $table) {
            $table->increments('id_plat');
            $table->string('nom_plat', 150);
            $table->string('slug', 100)->unique();
            $table->text('descri_plat')->nullable();
            $table->unsignedInteger('fid_rareté');

            $table->foreign('fid_rareté', 'fk_plat_rarete')
                ->references('id_rareté')->on('rareté');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plat');
    }
};
