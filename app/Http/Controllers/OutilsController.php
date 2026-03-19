<?php

namespace App\Http\Controllers;

use App\Models\Personnage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OutilsController extends Controller
{
    // ─── Public ───────────────────────────────────────────────────────────────

    public function personnageDuJour(): View
    {
        // Seed by date so the same character appears all day
        $count = Personnage::count();
        if ($count === 0) {
            $personnage = null;
        } else {
            $seed = crc32(now()->format('Y-m-d'));
            $index = abs($seed) % $count;
            $personnage = Personnage::with(['element', 'etoile', 'photos'])
                ->skip($index)
                ->first();
        }

        return view('outils.personnage-du-jour', compact('personnage'));
    }

    public function quiz(): View
    {
        $personnages = Personnage::with(['element', 'etoile', 'photos'])->get();

        if ($personnages->count() < 4) {
            return view('outils.quiz', ['question' => null, 'choices' => []]);
        }

        $shuffled = $personnages->shuffle();
        $correct  = $shuffled->first();
        $choices  = $shuffled->take(4);

        return view('outils.quiz', compact('correct', 'choices'));
    }

    public function quizResultat(Request $request): View
    {
        $request->validate([
            'reponse'  => ['required', 'string'],
            'correct'  => ['required', 'string'],
        ]);

        $estCorrect = $request->reponse === $request->correct;

        return view('outils.quiz-resultat', [
            'reponse'    => $request->reponse,
            'correct'    => $request->correct,
            'estCorrect' => $estCorrect,
        ]);
    }

    // ─── Authentifié ─────────────────────────────────────────────────────────

    public function roulette(): View
    {
        $personnages = Personnage::with(['element', 'etoile', 'photos'])
            ->orderBy('nom_perso')
            ->get();

        return view('outils.roulette', compact('personnages'));
    }

    public function rouletteSauvegarder(Request $request): RedirectResponse
    {
        $request->validate([
            'preference' => ['nullable', 'string', 'max:255'],
        ]);

        return redirect()->route('outils.roulette')->with('success', 'Préférence sauvegardée.');
    }

    public function team(): View
    {
        $personnages = Personnage::with(['element', 'etoile', 'photos'])
            ->orderBy('nom_perso')
            ->get();

        return view('outils.team', compact('personnages'));
    }

    public function teamGenerer(Request $request): View
    {
        $request->validate([
            'perso_fixe' => ['nullable', 'exists:personnage,id_perso'],
        ]);

        $all = Personnage::with(['element', 'etoile', 'photos'])->get()->shuffle();

        if ($request->perso_fixe) {
            $fixe  = $all->firstWhere('id_perso', $request->perso_fixe);
            $reste = $all->where('id_perso', '!=', $request->perso_fixe)->take(3)->values();
            $team  = collect([$fixe])->concat($reste);
        } else {
            $team = $all->take(4);
        }

        return view('outils.team', [
            'personnages' => Personnage::with(['element', 'etoile', 'photos'])->orderBy('nom_perso')->get(),
            'team'        => $team,
        ]);
    }

    public function comparateur(): View
    {
        $personnages = Personnage::with(['element', 'etoile', 'photos'])
            ->orderBy('nom_perso')
            ->get();

        return view('outils.comparateur', compact('personnages'));
    }
}
