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
        Schema::table('personnage', function (Blueprint $table) {
            if (!Schema::hasColumn('personnage', 'block_order')) {
                $table->string('block_order', 255)
                    ->default('main_zone,armes,artefacts,constellations,competences')
                    ->after('slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnage', function (Blueprint $table) {
            //
        });
    }
};
