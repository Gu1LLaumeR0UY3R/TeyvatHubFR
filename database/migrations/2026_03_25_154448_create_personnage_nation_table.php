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
        if (Schema::hasTable('personnage_nation')) {
            return;
        }

        $nationTable = Schema::hasTable('nation') ? 'nation' : 'région';

        Schema::create('personnage_nation', function (Blueprint $table) use ($nationTable) {
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_nation');

            $table->primary(['fid_perso', 'fid_nation']);

            $table->foreign('fid_perso', 'fk_pn_perso')
                ->references('id_perso')
                ->on('personnage')
                ->onDelete('cascade');

            $table->foreign('fid_nation', 'fk_pn_nation')
                ->references('id_region')
                ->on($nationTable)
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnage_nation');
    }
};
