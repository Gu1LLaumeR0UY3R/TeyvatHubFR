<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\Artefact;
use App\Models\Arme;
use App\Models\Ennemi;
use App\Models\Ingredient;
use App\Models\Nation;
use App\Models\Personnage;
use App\Models\Plat;
use App\Models\Article;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $stats = [
            'personnages'  => Personnage::count(),
            'armes'        => Arme::count(),
            'artefacts'    => Artefact::count(),
            'ennemis'      => Ennemi::count(),
            'animaux'      => Animal::count(),
            'plats'        => Plat::count(),
            'ingredients'  => Ingredient::count(),
            'nations'      => Nation::count(),
            'utilisateurs' => User::count(),
            'articles'     => Article::count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    public function importGenshin(Request $request): JsonResponse|RedirectResponse
    {
        try {

            $exitCode = Artisan::call('import:genshin');
            $output = Artisan::output();

            if ($exitCode === 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Import Genshin terminé avec succès.',
                        'output'  => $output,
                    ]);
                }
                return redirect()->route('admin.dashboard')
                    ->with('import_success', 'Import Genshin terminé avec succès.');
            }

            throw new \RuntimeException('La commande import:genshin a échoué. ' . $output);
        } catch (\Throwable $e) {
            Log::error('Import Genshin échoué: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'import: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->route('admin.dashboard')
                ->with('import_error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }
}
