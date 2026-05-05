<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SurveyResultsMail;
use App\Models\Article;
use App\Models\ImprovementMeta;
use App\Models\PatchNoteMeta;
use App\Models\Survey;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private function authorizeWrite(): void
    {
        if (!Gate::check('write_articles')) {
            abort(403, 'Accès réservé aux administrateurs autorisés.');
        }
    }

    // ── Index ─────────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $this->authorizeWrite();

        $articles = Article::with('admin')
            ->when($request->type,   fn($q) => $q->byType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    // ── Create ────────────────────────────────────────────────────────
    public function create(): View
    {
        $this->authorizeWrite();
        return view('admin.articles.edit', ['article' => null]);
    }

    // ── Store ─────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeWrite();

        $base = $this->validateBase($request);
        $base['author_id']  = session('admin_id');
        $base['content']    = $request->input('content') ? json_decode($request->input('content'), true) : null;
        $base['is_pinned']  = $request->boolean('is_pinned');

        if ($base['status'] === 'published') {
            $base['published_at'] = now();
        }

        $article = Article::create($base);
        $this->storeSatellite($request, $article);

        return redirect()->route('admin.articles.index')->with('success', 'Article créé.');
    }

    // ── Edit ──────────────────────────────────────────────────────────
    public function edit(Article $article): View
    {
        $this->authorizeWrite();
        $article->load(['patchNoteMeta', 'improvementMeta', 'survey.questions']);
        return view('admin.articles.edit', compact('article'));
    }

    // ── Update ────────────────────────────────────────────────────────
    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeWrite();

        $base = $this->validateBase($request);
        $base['content']   = $request->input('content') ? json_decode($request->input('content'), true) : null;
        $base['is_pinned'] = $request->boolean('is_pinned');

        if ($base['status'] === 'published' && $article->published_at === null) {
            $base['published_at'] = now();
        }

        $article->update($base);
        $this->storeSatellite($request, $article);

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Article mis à jour.');
    }

    // ── Destroy ───────────────────────────────────────────────────────
    public function destroy(Article $article): RedirectResponse
    {
        $this->authorizeWrite();
        $article->delete();
        return redirect()->route('admin.articles.index')->with('success', 'Article supprimé.');
    }

    // ── Autosave (JSON) ───────────────────────────────────────────────
    public function autosave(Request $request, Article $article): JsonResponse
    {
        $this->authorizeWrite();
        $raw = $request->input('content');
        if ($raw !== null) {
            $article->update(['content' => is_string($raw) ? json_decode($raw, true) : $raw]);
        }
        return response()->json(['saved_at' => now()->format('H\hi')]);
    }

    // ── Upload image (JSON) ───────────────────────────────────────────
    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorizeWrite();
        $request->validate(['image' => 'required|image|max:4096']);
        $path = $request->file('image')->store('uploads/articles', 'public');
        return response()->json(['url' => Storage::url($path)]);
    }

    // ── Calendrier ────────────────────────────────────────────────────
    public function calendar(): View
    {
        $this->authorizeWrite();
        $pinned = Article::select('id', 'title', 'type', 'is_pinned', 'pinned_until', 'status')
            ->where(fn($q) => $q->where('is_pinned', true)->orWhereNotNull('pinned_until'))
            ->get();
        return view('admin.articles.calendar', compact('pinned'));
    }

    // ── Validation commune ────────────────────────────────────────────
    private function validateBase(Request $request): array
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:patch_note,annonce,amelioration,questionnaire',
            'status'       => 'required|in:draft,published,archived',
            'is_pinned'    => 'boolean',
            'pinned_until' => 'nullable|date',
            'scheduled_at' => 'nullable|date',
        ]);
        $data['title'] = strip_tags($data['title']);
        return $data;
    }

    // ── Logique satellite ─────────────────────────────────────────────
    private function storeSatellite(Request $request, Article $article): void
    {
        match ($article->type) {
            'patch_note'    => $this->storePatchNote($request, $article),
            'amelioration'  => $this->storeImprovement($request, $article),
            'questionnaire' => $this->storeSurveyMeta($request, $article),
            default         => null,
        };
    }

    private function storePatchNote(Request $request, Article $article): void
    {
        $data = $request->validate([
            'version'             => ['required', 'string', 'max:20', 'regex:/^\d+\.\d+\.\d+$/'],
            'release_date'        => 'required|date',
            'changelog_added'     => 'nullable|array',
            'changelog_added.*'   => 'string|max:500',
            'changelog_fixed'     => 'nullable|array',
            'changelog_fixed.*'   => 'string|max:500',
            'changelog_removed'   => 'nullable|array',
            'changelog_removed.*' => 'string|max:500',
        ]);

        $sanitize = fn(array $items): array => array_map(
            fn($s) => htmlspecialchars(strip_tags($s), ENT_QUOTES, 'UTF-8'),
            $items
        );

        PatchNoteMeta::updateOrCreate(
            ['article_id' => $article->id],
            [
                'version'      => $data['version'],
                'release_date' => $data['release_date'],
                'changelog'    => [
                    'added'   => $sanitize($data['changelog_added']   ?? []),
                    'fixed'   => $sanitize($data['changelog_fixed']   ?? []),
                    'removed' => $sanitize($data['changelog_removed'] ?? []),
                ],
            ]
        );
    }

    private function storeImprovement(Request $request, Article $article): void
    {
        $data = $request->validate([
            'planning_status' => 'required|in:prevu,en_cours,annule,livre',
        ]);

        ImprovementMeta::updateOrCreate(
            ['article_id' => $article->id],
            ['planning_status' => $data['planning_status']]
        );
    }

    private function storeSurveyMeta(Request $request, Article $article): void
    {
        $data = $request->validate([
            'notification_email'          => 'required|email|max:255',
            'closes_at'                   => 'nullable|date',
            'questions'                   => 'nullable|array',
            'questions.*.type'            => 'required_with:questions|in:qcm,checkbox,text,rating,boolean',
            'questions.*.label'           => 'required_with:questions|string|max:500',
            'questions.*.options'         => 'nullable|array',
            'questions.*.options.*'       => 'string|max:255',
        ]);

        $survey = Survey::updateOrCreate(
            ['article_id' => $article->id],
            ['notification_email' => $data['notification_email'], 'closes_at' => $data['closes_at'] ?? null]
        );

        $survey->questions()->delete();
        foreach ($data['questions'] ?? [] as $pos => $q) {
            $survey->questions()->create([
                'type'     => $q['type'],
                'label'    => htmlspecialchars(strip_tags($q['label']), ENT_QUOTES, 'UTF-8'),
                'position' => $pos,
                'options'  => array_map(
                    fn($o) => htmlspecialchars(strip_tags($o), ENT_QUOTES, 'UTF-8'),
                    $q['options'] ?? []
                ),
            ]);
        }
    }
}
