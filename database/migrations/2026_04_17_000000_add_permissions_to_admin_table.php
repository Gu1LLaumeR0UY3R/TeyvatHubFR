<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            // JSON array of permission keys granted to this admin.
            // super_admin role ignores this column and has all permissions.
            // Possible values: encyclopedie | blog | evenements | utilisateurs | admins | import
            $table->json('permissions')->nullable()->after('role');
        });

        // Grant all existing super_admin accounts every permission so nothing breaks.
        DB::table('admin')
            ->where('role', 'super_admin')
            ->update(['permissions' => json_encode(['encyclopedie', 'blog', 'evenements', 'utilisateurs', 'admins', 'import'])]);
    }

    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('permissions');
        });
    }
};
