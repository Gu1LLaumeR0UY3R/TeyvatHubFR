<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('team_composition')) {
            Schema::create('team_composition', function (Blueprint $table) {
                $table->id('id_team');
                $table->unsignedInteger('fid_perso');
                $table->string('type_reaction', 80);
                $table->enum('tag', ['recommended', 'f2p'])->nullable();
                $table->timestamps();

                $table->index(['fid_perso', 'type_reaction'], 'idx_team_comp_perso_reaction');
                $table->unique(['fid_perso', 'type_reaction', 'tag'], 'uk_team_comp_perso_reaction_tag');

                $table->foreign('fid_perso', 'fk_team_comp_fid_perso')
                    ->references('id_perso')
                    ->on('personnage')
                    ->cascadeOnDelete();
            });

            return;
        }

        Schema::table('team_composition', function (Blueprint $table) {
            if (!Schema::hasColumn('team_composition', 'tag')) {
                $table->enum('tag', ['recommended', 'f2p'])->nullable()->after('type_reaction');
            }
            if (!Schema::hasColumn('team_composition', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            if (!Schema::hasColumn('team_composition', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        if (Schema::hasColumn('team_composition', 'est_recommande')) {
            DB::statement("UPDATE team_composition SET tag = 'recommended' WHERE est_recommande = 1");
        }
        if (Schema::hasColumn('team_composition', 'est_f2p')) {
            DB::statement("UPDATE team_composition SET tag = 'f2p' WHERE tag IS NULL AND est_f2p = 1");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_composition');
    }
};
