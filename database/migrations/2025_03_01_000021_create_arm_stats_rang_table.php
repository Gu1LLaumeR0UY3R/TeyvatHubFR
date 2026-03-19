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
            $table->tinyInteger('rang_ASR');
            $table->text('descri_ASR')->nullable();
            $table->unsignedInteger('fid_arme');

            $table->unique(['rang_ASR', 'fid_arme'], 'uk_asr_arme');
            $table->foreign('fid_arme', 'fk_asr_arme')
                ->references('id_arme')->on('armes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arm_stats_rang');
    }
};
