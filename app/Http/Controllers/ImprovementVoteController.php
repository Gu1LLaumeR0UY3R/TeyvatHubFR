<?php

namespace App\Http\Controllers;

use App\Models\ImprovementMeta;
use App\Models\ImprovementVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ImprovementVoteController extends Controller
{
    /**
     * Toggle le vote du joueur connecté sur une amélioration.
     * Répond en JSON pour Alpine.js.
     */
    public function toggle(ImprovementMeta $improvement): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Connexion requise.'], 401);
        }

        $existing = ImprovementVote::where('improvement_id', $improvement->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            // Downvote — retirer le vote
            $existing->delete();
            $improvement->decrement('upvotes_count');
            $voted = false;
        } else {
            // Upvote — ajouter le vote
            ImprovementVote::create([
                'improvement_id' => $improvement->id,
                'user_id'        => $user->id,
                'voted_at'       => now(),
            ]);
            $improvement->increment('upvotes_count');
            $voted = true;
        }

        return response()->json([
            'voted'  => $voted,
            'count'  => $improvement->fresh()->upvotes_count,
        ]);
    }
}
