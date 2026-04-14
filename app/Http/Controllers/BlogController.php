<?php

namespace App\Http\Controllers;

use App\Models\BlogArticle;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $articles = BlogArticle::query()
            ->where('statut', 'publie')
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = (string) $request->input('search');
                $query->where(function ($q) use ($term) {
                    $q->where('titre_article', 'like', '%' . $term . '%')
                        ->orWhere('extrait', 'like', '%' . $term . '%')
                        ->orWhere('contenu_article', 'like', '%' . $term . '%');
                });
            })
            ->orderByDesc('date_publication')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('blog.index', compact('articles'));
    }

    public function show(BlogArticle $article): View
    {
        abort_unless($article->statut === 'publie', 404);

        return view('blog.show', compact('article'));
    }
}
