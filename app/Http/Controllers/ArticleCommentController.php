<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class ArticleCommentController extends Controller
{
    /**
     * Poster un commentaire sur un article.
     * - Rate limiting : 5 commentaires / 10 minutes par IP
     * - Sanitisation : strip_tags() — pas de HTML utilisateur stocké
     * - Statut par défaut : pending (modération)
     */
    public function store(Request $request, Article $article): RedirectResponse
    {
        $key = 'comment:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()
                ->withErrors(['comment' => "Trop de commentaires. Réessayez dans {$seconds} secondes."])
                ->withInput();
        }

        // L'article doit être publié pour accepter des commentaires
        abort_unless($article->isPublished(), 404);

        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        // Sanitisation : on ne stocke jamais de HTML brut
        $sanitized = htmlspecialchars(strip_tags(trim($data['content'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $user = auth()->user();

        ArticleComment::create([
            'article_id'  => $article->id,
            'user_id'     => $user?->id,
            'author_name' => $user?->pseudo ?? 'Anonyme',
            'content'     => $sanitized,
            'status'      => 'pending',
            'ip_address'  => $request->ip(),
        ]);

        RateLimiter::hit($key, 600); // fenêtre 10 minutes

        ActivityLogService::userLog('comment_posted', 'info', ['article_id' => $article->id]);

        return back()->with('success', 'Commentaire soumis. Il sera visible après modération.');
    }
}
