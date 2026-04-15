<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogArticle;
use App\Models\BlogSlug;
use App\Models\Photo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
        $slugPresets = BlogSlug::query()
            ->orderBy('slug_base')
            ->get();

        return view('admin.blog.index', compact('articles', 'slugPresets'));
    }

    public function create(): View
    {
        $slugPresets = BlogSlug::query()
            ->orderBy('slug_base')
            ->get();

        return view('admin.blog.create', compact('slugPresets'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre_article' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:blog_article,slug'],
            'contenu_article' => ['required', 'string'],
            'statut' => ['required', Rule::in(['brouillon', 'publie'])],
            'date_publication' => ['nullable', 'date'],
            'featured_images.*' => ['nullable', 'image', 'max:5120'],
            'inline_images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['titre_article']);

        if ($data['statut'] === 'publie' && empty($data['date_publication'])) {
            $data['date_publication'] = now();
        }

        $article = BlogArticle::create($data);
        $this->storeUploadedPhotos($article, $request->file('featured_images', []), 'featured');
        $this->storeUploadedPhotos($article, $request->file('inline_images', []), 'inline');

        return redirect()->route('admin.blog.edit', $article)
            ->with('success', 'Article blog créé.');
    }

    public function edit(BlogArticle $blog): View
    {
        $article = $blog;
        $article->load('photos');
        $slugPresets = BlogSlug::query()
            ->orderBy('slug_base')
            ->get();

        return view('admin.blog.edit', compact('article', 'slugPresets'));
    }

    public function update(Request $request, BlogArticle $blog): RedirectResponse
    {
        $data = $request->validate([
            'titre_article' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('blog_article', 'slug')->ignore($blog->id_article, 'id_article')],
            'contenu_article' => ['required', 'string'],
            'statut' => ['required', Rule::in(['brouillon', 'publie'])],
            'date_publication' => ['nullable', 'date'],
            'featured_images.*' => ['nullable', 'image', 'max:5120'],
            'inline_images.*' => ['nullable', 'image', 'max:5120'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['titre_article']);

        if ($data['statut'] === 'publie' && empty($data['date_publication'])) {
            $data['date_publication'] = now();
        }

        $blog->update($data);
        $this->storeUploadedPhotos($blog, $request->file('featured_images', []), 'featured');
        $this->storeUploadedPhotos($blog, $request->file('inline_images', []), 'inline');

        return redirect()->route('admin.blog.index')
            ->with('success', 'Article blog mis à jour.');
    }

    public function destroy(BlogArticle $blog): RedirectResponse
    {
        foreach ($blog->photos as $photo) {
            $this->deleteLocalPhotoFile($photo);
        }
        $blog->photos()->delete();
        $blog->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Article blog supprimé.');
    }

    public function storeSlug(Request $request): JsonResponse|RedirectResponse
    {
        $request->validate([
            'slug_base' => ['required', 'string', 'max:120'],
        ]);

        $slugBase = Str::slug((string) $request->input('slug_base'));

        if ($slugBase === '') {
            $message = 'Le slug doit contenir au moins un caractère valide.';

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('admin.blog.index')->with('slug_error', $message);
        }

        $slugPreset = BlogSlug::query()->firstOrCreate(['slug_base' => $slugBase]);
        $message = $slugPreset->wasRecentlyCreated ? 'Slug ajouté.' : 'Slug déjà présent.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'slug' => [
                    'id_blog_slug' => (int) $slugPreset->id_blog_slug,
                    'slug_base' => $slugPreset->slug_base,
                ],
            ]);
        }

        return redirect()->route('admin.blog.index')->with('success', $message);
    }

    public function destroySlug(BlogSlug $blogSlug): RedirectResponse
    {
        $blogSlug->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Slug supprimé.');
    }

    public function destroyImage(BlogArticle $blog, Photo $photo): RedirectResponse
    {
        abort_unless(
            $photo->photoable_type === BlogArticle::class && (int) $photo->photoable_id === (int) $blog->id_article,
            404
        );

        $this->deleteLocalPhotoFile($photo);
        $photo->delete();

        return redirect()->route('admin.blog.edit', $blog)->with('success', 'Image supprimée.');
    }

    /**
     * @param  array<int, UploadedFile|null>|UploadedFile|null  $files
     */
    private function storeUploadedPhotos(BlogArticle $article, array|UploadedFile|null $files, string $type): void
    {
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        foreach (array_filter((array) $files) as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $path = $file->store('photos/blog/' . $type, 'public');

            $article->photos()->create([
                'chemin_photo' => $path,
                'source_url' => null,
                'type' => $type,
            ]);
        }
    }

    private function deleteLocalPhotoFile(Photo $photo): void
    {
        if (!filled($photo->chemin_photo) || filter_var((string) $photo->chemin_photo, FILTER_VALIDATE_URL)) {
            return;
        }

        Storage::disk('public')->delete((string) $photo->chemin_photo);
    }
}
