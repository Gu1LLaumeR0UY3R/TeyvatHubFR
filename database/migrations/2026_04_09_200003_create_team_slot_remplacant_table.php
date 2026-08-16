<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('team_slot_remplacant')) {
            Schema::create('team_slot_remplacant', function (Blueprint $table) {
                $table->id('id_rpl');
                $table->unsignedBigInteger('fid_team');
                $table->unsignedTinyInteger('slot');
                $table->unsignedInteger('fid_perso_remplacant');
                $table->string('role_override', 100)->nullable();

                $table->unique(['fid_team', 'slot', 'fid_perso_remplacant'], 'uk_team_rpl_team_slot_perso');
                $table->index(['fid_team', 'slot'], 'idx_team_rpl_team_slot');

                $table->foreign('fid_team', 'fk_team_rpl_fid_team')
                    ->references('id_team')
                    ->on('team_composition')
                    ->cascadeOnDelete();

                $table->foreign('fid_perso_remplacant', 'fk_team_rpl_fid_perso')
                    ->references('id_perso')
                    ->on('personnage')
                    ->restrictOnDelete();
            });

            return;
        }

        Schema::table('team_slot_remplacant', function (Blueprint $table) {
            if (!Schema::hasColumn('team_slot_remplacant', 'role_override')) {
                $table->string('role_override', 100)->nullable()->after('fid_perso_remplacant');
            }
        });

        if (Schema::hasColumn('team_slot_remplacant', 'role_in_team')) {
            DB::statement('UPDATE team_slot_remplacant SET role_override = role_in_team WHERE role_override IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_slot_remplacant');
    }
};
