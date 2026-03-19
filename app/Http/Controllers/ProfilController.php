<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $persos_possedes = $user->personnages()->count();
        $armes_possedees = $user->armes()->count();
        $constellations_debloquees = DB::table('joueur_constellation')
            ->where('fid_joueur', $user->id)
            ->where('debloquee', true)
            ->count();
        $persos_c6 = DB::table('joueur_constellation')
            ->where('fid_joueur', $user->id)
            ->where('debloquee', true)
            ->selectRaw('fid_perso, COUNT(*) as cnt')
            ->groupBy('fid_perso')
            ->havingRaw('cnt >= 6')
            ->count();

        $stats = compact('persos_possedes', 'armes_possedees', 'constellations_debloquees', 'persos_c6');

        return view('profil.index', compact('user', 'stats'));
    }

    public function personnages(): View
    {
        $user = auth()->user();
        $personnages = $user->personnages()
            ->with(['element', 'etoile', 'photos'])
            ->orderByPivot('niveau', 'desc')
            ->paginate(20);

        return view('profil.personnages', compact('user', 'personnages'));
    }

    public function armes(): View
    {
        $user = auth()->user();
        $armes = $user->armes()
            ->with(['typeArme', 'etoile', 'photos'])
            ->orderByPivot('niveau', 'desc')
            ->paginate(20);

        return view('profil.armes', compact('user', 'armes'));
    }

    public function parametres(): View
    {
        $user = auth()->user();
        return view('profil.parametres', compact('user'));
    }
}
