<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnage', function (Blueprint $table): void {
            if (!Schema::hasColumn('personnage', 'deleted_at')) {
                $table->timestamp('deleted_at')->nullable();
            }

            if (!Schema::hasColumn('personnage', 'deleted_by')) {
                $table->unsignedInteger('deleted_by')->nullable();

                $table->foreign('deleted_by', 'fk_personnage_deleted_by_admin')
                    ->references('id_admin')->on('admin')
                    ->nullOnDelete();
            }
        });

        if (!Schema::hasTable('snapshots')) {
            Schema::create('snapshots', function (Blueprint $table): void {
                $table->increments('id_snapshot');
                $table->unsignedInteger('fid_perso');
                $table->unsignedInteger('fid_admin')->nullable();
                $table->enum('action_type', ['update', 'delete']);
                $table->timestamp('action_at')->useCurrent();

                $table->foreign('fid_perso', 'fk_snapshots_personnage')
                    ->references('id_perso')->on('personnage')
                    ->cascadeOnDelete();

                $table->foreign('fid_admin', 'fk_snapshots_admin')
                    ->references('id_admin')->on('admin')
                    ->nullOnDelete();
            });
        }

        if (!Schema::hasTable('snapshot_modifications')) {
            Schema::create('snapshot_modifications', function (Blueprint $table): void {
                $table->increments('id_snapshot_modification');
                $table->unsignedInteger('fid_snapshot');
                $table->unsignedInteger('sub_sequence');
                $table->json('old_values')->nullable();
                $table->json('new_values')->nullable();

                $table->foreign('fid_snapshot', 'fk_snapshot_modifications_snapshot')
                    ->references('id_snapshot')->on('snapshots')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshot_modifications');
        Schema::dropIfExists('snapshots');

        Schema::table('personnage', function (Blueprint $table): void {
            if (Schema::hasColumn('personnage', 'deleted_by')) {
                $table->dropForeign('fk_personnage_deleted_by_admin');
                $table->dropColumn('deleted_by');
            }

            if (Schema::hasColumn('personnage', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }
        });
    }
};
