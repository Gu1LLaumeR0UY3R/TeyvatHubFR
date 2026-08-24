<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personnage', function (Blueprint $table) {
            $table->unsignedTinyInteger('versatilite')->nullable()->default(null)->after('fid_etoile');
        });
    }

    public function down(): void
    {
        Schema::table('personnage', function (Blueprint $table) {
            $table->dropColumn('versatilite');
        });
    }
};