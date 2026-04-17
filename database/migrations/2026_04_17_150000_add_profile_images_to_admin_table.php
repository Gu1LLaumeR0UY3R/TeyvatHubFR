<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->string('photo_profil')->nullable()->after('two_factor_confirmed_at');
            $table->string('banniere_admin')->nullable()->after('photo_profil');
        });
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn(['photo_profil', 'banniere_admin']);
        });
    }
};
