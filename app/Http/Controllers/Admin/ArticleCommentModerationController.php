<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ArticleComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArticleCommentModerationController extends Controller
{
    /** Liste paginée : pending par défaut, filtrable par statut. */
    public function index(Request $request): View
    {
        $status = in_array($request->status, ['pending', 'approved', 'rejected'])
            ? $request->status
            : 'pending';

        $comments = ArticleComment::with(['article:id,title', 'user:id,pseudo'])
            ->where('status', $status)
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.comments.index', compact('comments', 'status'));
    }

    /** Approuver un commentaire. */
    public function approve(ArticleComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'approved']);
        return back()->with('success', 'Commentaire approuvé.');
    }

    /** Rejeter un commentaire. */
    public function reject(ArticleComment $comment): RedirectResponse
    {
        $comment->update(['status' => 'rejected']);
        return back()->with('success', 'Commentaire rejeté.');
    }

    /** Supprimer définitivement. */
    public function destroy(ArticleComment $comment): RedirectResponse
    {
        $comment->delete();
        return back()->with('success', 'Commentaire supprimé.');
    }
}
