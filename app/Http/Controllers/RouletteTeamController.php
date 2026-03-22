<?php

namespace App\Http\Controllers;

use App\Services\TeamCompositionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RouletteTeamController extends Controller
{
    public function __construct(private TeamCompositionService $teamService) {}

    public function index(): View
    {
        $user = auth()->user();
        $personnages = $user->personnages()
            ->with(['photos', 'element', 'roles'])
            ->get();

        $reactions = array_keys(TeamCompositionService::REACTIONS);

        return view('outils.roulette-team', compact('personnages', 'reactions'));
    }

    public function generer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode'     => ['required', 'in:aleatoire,reaction,element'],
            'filtre'   => ['nullable', 'string', 'max:50'],
        ]);

        $user = auth()->user();
        $personnages = $user->personnages()
            ->with(['photos', 'element', 'roles'])
            ->get();

        if ($personnages->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Votre roster est vide. Importez votre UID d\'abord.',
                'team'    => [],
            ]);
        }

        $team = match ($validated['mode']) {
            'reaction' => $this->teamService->buildByReaction($personnages, $validated['filtre'] ?? ''),
            'element'  => $this->teamService->buildByElement($personnages, $validated['filtre'] ?? ''),
            default    => $this->teamService->buildRandom($personnages),
        };

        if (!$this->teamService->hasEnoughPersonnages($team)) {
            return response()->json([
                'success' => false,
                'message' => 'Pas assez de personnages disponibles pour ce filtre.',
                'team'    => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'team'    => $team->map(fn($p) => [
                'id'        => $p->id_perso,
                'nom'       => $p->nom_perso,
                'element'   => $p->element?->libelle_element,
                'icone_url' => $p->icone_url,
                'slug'      => $p->slug,
            ])->values(),
        ]);
    }
}
