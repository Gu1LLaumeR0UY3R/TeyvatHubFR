<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arm_stats_niveau', function (Blueprint $table) {
            $table->increments('id_ASN');
            $table->unsignedTinyInteger('lvl_ASN');
            $table->float('main_stat');
            $table->float('subs_stats');
            $table->unsignedInteger('fid_arme');
            $table->unique(['fid_arme', 'lvl_ASN'], 'uq_arme_niveau');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arm_stats_niveau');
    }
};
