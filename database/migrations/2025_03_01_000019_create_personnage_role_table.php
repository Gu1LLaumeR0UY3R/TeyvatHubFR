<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personnage_role', function (Blueprint $table) {
            $table->unsignedInteger('fid_perso');
            $table->unsignedInteger('fid_role');
            $table->primary(['fid_perso', 'fid_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personnage_role');
    }
};
