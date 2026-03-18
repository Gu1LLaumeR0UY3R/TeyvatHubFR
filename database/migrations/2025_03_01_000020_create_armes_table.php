<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('armes', function (Blueprint $table) {
            $table->increments('id_arme');
            $table->string('nom_arme', 100);
            $table->string('slug', 100)->unique();
            $table->text('descr_arme')->nullable();
            $table->string('nom_competence', 100)->nullable();
            $table->string('main_stat_type', 50)->nullable();
            $table->string('sub_stat_type', 50)->nullable();
            $table->unsignedInteger('fid_TArmes');
            $table->unsignedInteger('fid_etoile');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('armes');
    }
};
