<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arm_stats_rang', function (Blueprint $table) {
            $table->increments('id_ASR');
            $table->unsignedTinyInteger('rang_ASR');
            $table->text('descri_ASR');
            $table->unsignedInteger('fid_arme');
            $table->unique(['fid_arme', 'rang_ASR'], 'uq_arme_rang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arm_stats_rang');
    }
};
