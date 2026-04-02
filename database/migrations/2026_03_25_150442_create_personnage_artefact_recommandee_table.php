<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('personnage_artefact_recommandee')) {
            Schema::create('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->id('id_build');
                $table->unsignedInteger('fid_perso');
                $table->unsignedInteger('fid_artefact_1');
                $table->enum('pieces_1', ['2p', '4p'])->default('4p');
                $table->unsignedInteger('fid_artefact_2')->nullable();
                $table->enum('pieces_2', ['2p'])->nullable();
                $table->tinyInteger('position');

                $table->unique(['fid_perso', 'position'], 'uk_build_position');

                $table->foreign('fid_perso')
                    ->references('id_perso')
                    ->on('personnage')
                    ->onDelete('cascade');

                $table->foreign('fid_artefact_1')
                    ->references('id_artefact')
                    ->on('artefact')
                    ->onDelete('cascade');

                $table->foreign('fid_artefact_2')
                    ->references('id_artefact')
                    ->on('artefact')
                    ->nullOnDelete();
            });

            return;
        }

        $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'personnage_artefact_recommandee')
            ->where('CONSTRAINT_NAME', 'personnage_artefact_recommandee_fid_artefact_2_foreign')
            ->exists();

        if (!$fkExists) {
            Schema::table('personnage_artefact_recommandee', function (Blueprint $table) {
                $table->foreign('fid_artefact_2')
                    ->references('id_artefact')
                    ->on('artefact')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnage_artefact_recommandee');
    }
};
