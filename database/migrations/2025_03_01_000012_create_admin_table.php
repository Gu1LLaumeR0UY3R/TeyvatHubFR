<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->increments('id_admin');
            $table->string('pseudo_admin', 100)->unique();
            $table->string('email_admin', 255)->unique();
            $table->string('mot_de_passe_admin', 255);
            $table->string('role', 20)->default('moderateur'); // super_admin | moderateur
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
