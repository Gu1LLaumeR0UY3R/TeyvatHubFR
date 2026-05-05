<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $articles = Article::published()
            ->where('published_at', '<=', now())
            ->with('admin')
            ->when($request->type, fn($q) => $q->byType($request->type))
            ->pinnedFirst()
            ->paginate(12)
            ->withQueryString();

        return view('articles.index', compact('articles'));
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->load(['admin', 'patchNoteMeta', 'improvementMeta.votes', 'survey.questions']);

        return view('articles.show', compact('article'));
    }
}
