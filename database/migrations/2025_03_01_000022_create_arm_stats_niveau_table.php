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
            $table->tinyInteger('lvl_ASN');
            $table->float('main_stat')->nullable();
            $table->float('subs_stats')->nullable();
            $table->unsignedInteger('fid_arme');

            $table->unique(['lvl_ASN', 'fid_arme'], 'uk_asn_arme');
            $table->foreign('fid_arme', 'fk_asn_arme')
                ->references('id_arme')->on('armes')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arm_stats_niveau');
    }
};
