<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plat_ingredient', function (Blueprint $table) {
            $table->unsignedInteger('fid_plat');
            $table->unsignedInteger('fid_ingredient');
            $table->unsignedTinyInteger('quantite')->default(1);
            $table->primary(['fid_plat', 'fid_ingredient']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plat_ingredient');
    }
};
