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
            $table->string('nom_plat', 100);
            $table->string('slug', 100)->unique();
            $table->text('descri_plat')->nullable();
            $table->unsignedInteger('fid_rareté');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plat');
    }
};
