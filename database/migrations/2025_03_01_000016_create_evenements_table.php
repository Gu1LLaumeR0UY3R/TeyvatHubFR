<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evenement', function (Blueprint $table) {
            $table->increments('id_evenement');
            $table->string('titre', 100);
            $table->string('descri_courte', 150)->nullable();
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evenement');
    }
};
