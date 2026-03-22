<?php

namespace App\Http\Controllers;

use App\Services\MotusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MotusController extends Controller
{
    public function __construct(private MotusService $motusService) {}

    public function index(): View
    {
        $dailyWord = $this->motusService->getDailyWord();
        $wordLength = mb_strlen($dailyWord);

        return view('jeux.motus', compact('dailyWord', 'wordLength'));
    }

    public function valider(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guess' => ['required', 'string', 'max:100'],
            'word'  => ['required', 'string', 'max:100'],
        ]);

        $result = $this->motusService->validateGuess($validated['guess'], $validated['word']);
        $won    = collect($result)->every(fn($r) => $r['status'] === 'correct');

        return response()->json([
            'result' => $result,
            'won'    => $won,
        ]);
    }
}
