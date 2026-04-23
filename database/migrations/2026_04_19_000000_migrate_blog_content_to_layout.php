<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migrer contenu_article → layout_json
     * Chaque contenu_article devient un bloc de texte simple dans layout_json
     */
    public function up(): void
    {
        // Migration des données
        $articles = DB::table('blog_article')
            ->whereNotNull('contenu_article')
            ->where('contenu_article', '!=', '')
            ->get();

        foreach ($articles as $article) {
            if ($article->layout_json === null) {
                $layout = [
                    'blocks' => [
                        [
                            'type' => 'text',
                            'text' => $article->contenu_article,
                            'align' => 'left',
                        ],
                    ],
                ];

                DB::table('blog_article')
                    ->where('id_article', $article->id_article)
                    ->update(['layout_json' => json_encode($layout)]);
            }
        }

        // Supprimer la colonne contenu_article
        Schema::table('blog_article', function (Blueprint $table) {
            $table->dropColumn('contenu_article');
        });
    }

    /**
     * Rollback: restaurer contenu_article depuis layout_json
     */
    public function down(): void
    {
        // Restaurer la colonne contenu_article
        Schema::table('blog_article', function (Blueprint $table) {
            $table->longText('contenu_article')->nullable()->after('slug');
        });

        // Reverser la migration: extraire le texte du layout
        $articles = DB::table('blog_article')
            ->whereNotNull('layout_json')
            ->get();

        foreach ($articles as $article) {
            $layout = json_decode($article->layout_json, true);
            if (is_array($layout) && isset($layout['blocks'])) {
                $text = collect($layout['blocks'])
                    ->filter(fn($b) => isset($b['text']))
                    ->map(fn($b) => $b['text'])
                    ->implode("\n\n");

                if (!empty($text)) {
                    DB::table('blog_article')
                        ->where('id_article', $article->id_article)
                        ->update(['contenu_article' => $text]);
                }
            }
        }
    }
};
