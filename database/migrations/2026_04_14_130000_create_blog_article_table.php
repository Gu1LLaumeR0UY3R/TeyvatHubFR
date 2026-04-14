<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_article', function (Blueprint $table): void {
            $table->increments('id_article');
            $table->string('titre_article', 180);
            $table->string('slug', 200)->unique('uk_blog_article_slug');
            $table->text('extrait')->nullable();
            $table->longText('contenu_article');
            $table->string('statut', 20)->default('brouillon');
            $table->timestamp('date_publication')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_publication'], 'idx_blog_article_statut_publication');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_article');
    }
};
