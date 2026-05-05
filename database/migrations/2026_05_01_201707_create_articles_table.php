<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id('id_article');
            $table->string('titre', 255);
            $table->string('slug', 255)->unique();
            $table->text('extrait')->nullable();
            $table->json('content')->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->enum('statut', ['brouillon', 'publié'])->default('brouillon');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('fid_admin')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
