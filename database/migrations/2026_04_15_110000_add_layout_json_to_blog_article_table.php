<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_article', function (Blueprint $table): void {
            $table->longText('layout_json')->nullable()->after('contenu_article');
        });
    }

    public function down(): void
    {
        Schema::table('blog_article', function (Blueprint $table): void {
            $table->dropColumn('layout_json');
        });
    }
};