<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('artefact', function (Blueprint $table) {
            $table->increments('id_artefact');
            $table->string('nom_artefact', 150);
            $table->string('slug', 100)->unique();
            $table->text('bonus_2p')->nullable();
            $table->text('bonus_4p')->nullable();
            $table->unsignedInteger('fid_rareté');

            $table->foreign('fid_rareté')
                ->references('id_rareté')
                ->on('rareté')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artefact');
    }
};
