<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('constellation', 'positions_const')) {
            Schema::table('constellation', function (Blueprint $table): void {
                $table->json('positions_const')->nullable()->after('descri_const');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('constellation', 'positions_const')) {
            Schema::table('constellation', function (Blueprint $table): void {
                $table->dropColumn('positions_const');
            });
        }
    }
};
