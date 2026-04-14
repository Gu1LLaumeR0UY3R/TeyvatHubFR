<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogArticleController extends Controller
{
    public function index(): View
    {
        $articles = BlogArticle::query()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.blog.index', compact('articles'));
    }

    public function create(): View
    {
        return view('admin.blog.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre_article' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:blog_article,slug'],
            'extrait' => ['nullable', 'string'],
            'contenu_article' => ['required', 'string'],
            'statut' => ['required', Rule::in(['brouillon', 'publie'])],
            'date_publication' => ['nullable', 'date'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['titre_article']);

        if ($data['statut'] === 'publie' && empty($data['date_publication'])) {
            $data['date_publication'] = now();
        }

        BlogArticle::create($data);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Article blog créé.');
    }

    public function edit(BlogArticle $blog): View
    {
        $article = $blog;

        return view('admin.blog.edit', compact('article'));
    }

    public function update(Request $request, BlogArticle $blog): RedirectResponse
    {
        $data = $request->validate([
            'titre_article' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('blog_article', 'slug')->ignore($blog->id_article, 'id_article')],
            'extrait' => ['nullable', 'string'],
            'contenu_article' => ['required', 'string'],
            'statut' => ['required', Rule::in(['brouillon', 'publie'])],
            'date_publication' => ['nullable', 'date'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['titre_article']);

        if ($data['statut'] === 'publie' && empty($data['date_publication'])) {
            $data['date_publication'] = now();
        }

        $blog->update($data);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Article blog mis à jour.');
    }

    public function destroy(BlogArticle $blog): RedirectResponse
    {
        $blog->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Article blog supprimé.');
    }
}
