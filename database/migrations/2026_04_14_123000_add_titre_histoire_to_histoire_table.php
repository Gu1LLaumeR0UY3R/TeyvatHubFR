<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('histoire', function (Blueprint $table): void {
            if (!Schema::hasColumn('histoire', 'titre_histoire')) {
                $table->string('titre_histoire', 200)->nullable()->after('fid_perso');
            }
        });
    }

    public function down(): void
    {
        Schema::table('histoire', function (Blueprint $table): void {
            if (Schema::hasColumn('histoire', 'titre_histoire')) {
                $table->dropColumn('titre_histoire');
            }
        });
    }
};
