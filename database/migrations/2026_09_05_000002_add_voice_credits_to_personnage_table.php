<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('personnage', 'voix_va')) {
            Schema::table('personnage', function (Blueprint $table) {
                $table->string('voix_va', 150)->nullable()->after('nom_perso');
                $table->string('voix_vj', 150)->nullable()->after('voix_va');
                $table->string('voix_vc', 150)->nullable()->after('voix_vj');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('personnage', 'voix_va')) {
            Schema::table('personnage', function (Blueprint $table) {
                $table->dropColumn(['voix_va', 'voix_vj', 'voix_vc']);
            });
        }
    }
};
