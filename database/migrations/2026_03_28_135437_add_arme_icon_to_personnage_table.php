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
            if (!Schema::hasColumn('personnage', 'arme_icon')) {
                $table->string('arme_icon', 255)->nullable()->after('fid_TArmes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('personnage', function (Blueprint $table) {
            if (Schema::hasColumn('personnage', 'arme_icon')) {
                $table->dropColumn('arme_icon');
            }
        });
    }
};
