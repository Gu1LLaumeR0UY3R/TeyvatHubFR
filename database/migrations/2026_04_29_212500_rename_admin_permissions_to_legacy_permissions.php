<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('admin', 'permissions')) {
            Schema::table('admin', static function (Blueprint $table): void {
                $table->renameColumn('permissions', 'legacy_permissions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admin', 'legacy_permissions')) {
            Schema::table('admin', static function (Blueprint $table): void {
                $table->renameColumn('legacy_permissions', 'permissions');
            });
        }
    }
};
