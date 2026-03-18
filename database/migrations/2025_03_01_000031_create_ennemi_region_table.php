<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ennemi_region', function (Blueprint $table) {
            $table->unsignedInteger('fid_ennemi');
            $table->unsignedInteger('fid_region');
            $table->primary(['fid_ennemi', 'fid_region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ennemi_region');
    }
};
