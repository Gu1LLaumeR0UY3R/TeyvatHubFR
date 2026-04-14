<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('team_composition_membre')) {
            Schema::create('team_composition_membre', function (Blueprint $table) {
                $table->id('id_membre');
                $table->unsignedBigInteger('fid_team');
                $table->unsignedBigInteger('fid_perso');
                $table->unsignedTinyInteger('slot');
                $table->string('role_override', 100)->nullable();

                $table->unique(['fid_team', 'slot'], 'uk_team_membre_team_slot');
                $table->unique(['fid_team', 'fid_perso'], 'uk_team_membre_team_perso');
                $table->index(['fid_team', 'slot'], 'idx_team_membre_team_slot');

                $table->foreign('fid_team', 'fk_team_membre_fid_team')
                    ->references('id_team')
                    ->on('team_composition')
                    ->cascadeOnDelete();

                $table->foreign('fid_perso', 'fk_team_membre_fid_perso')
                    ->references('id_perso')
                    ->on('personnage')
                    ->restrictOnDelete();
            });

            return;
        }

        Schema::table('team_composition_membre', function (Blueprint $table) {
            if (!Schema::hasColumn('team_composition_membre', 'fid_perso')) {
                $table->unsignedBigInteger('fid_perso')->nullable()->after('fid_team');
            }
            if (!Schema::hasColumn('team_composition_membre', 'role_override')) {
                $table->string('role_override', 100)->nullable()->after('slot');
            }
        });

        if (Schema::hasColumn('team_composition_membre', 'fid_perso_membre')) {
            DB::statement('UPDATE team_composition_membre SET fid_perso = fid_perso_membre WHERE fid_perso IS NULL');
        }
        if (Schema::hasColumn('team_composition_membre', 'role_in_team')) {
            DB::statement('UPDATE team_composition_membre SET role_override = role_in_team WHERE role_override IS NULL');
        }

    }

    public function down(): void
    {
        Schema::dropIfExists('team_composition_membre');
    }
};
