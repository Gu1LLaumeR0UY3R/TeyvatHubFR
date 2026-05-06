<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('team_composition', function (Blueprint $table) {
            if (!Schema::hasColumn('team_composition', 'rotation')) {
                $table->text('rotation')->nullable()->after('tag');
            }
        });
    }

    public function down(): void
    {
        Schema::table('team_composition', function (Blueprint $table) {
            if (Schema::hasColumn('team_composition', 'rotation')) {
                $table->dropColumn('rotation');
            }
        });
    }
};
