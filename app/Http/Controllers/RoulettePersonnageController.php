<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoulettePersonnageController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $personnages = $user->personnages()
            ->with(['photos', 'element'])
            ->get();

        return view('outils.roulette-personnage', compact('personnages'));
    }

    public function confirmer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fid_perso' => ['required', 'integer', 'exists:personnage,id_perso'],
        ]);

        $user = auth()->user();

        $exists = DB::table('joueur_personnage')
            ->where('fid_joueur', $user->id)
            ->where('fid_perso', $validated['fid_perso'])
            ->exists();

        if (!$exists) {
            abort(403, 'Ce personnage ne vous appartient pas.');
        }

        DB::table('joueur_personnage')
            ->where('fid_joueur', $user->id)
            ->where('fid_perso', $validated['fid_perso'])
            ->update(['perso_amelioration' => true]);

        $personnage = \App\Models\Personnage::find($validated['fid_perso']);

        return redirect()->route('outils.roulette-personnage')
            ->with('success', 'Personnage "' . ($personnage?->nom_perso ?? '') . '" marqué à monter !');
    }
}
