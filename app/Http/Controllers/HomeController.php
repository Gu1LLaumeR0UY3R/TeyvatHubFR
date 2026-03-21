<?php

namespace App\Http\Controllers;

use App\Models\Evenement;
use App\Models\Personnage;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $derniers_personnages = Personnage::with(['element', 'etoile', 'photos'])
            ->latest('id_perso')
            ->take(6)
            ->get();

        $prochains_evenements = Evenement::where('date_fin', '>=', now()->toDateString())
            ->orderBy('date_debut')
            ->take(4)
            ->get();

        $compteurs = [
            'personnages' => Personnage::count(),
        ];

        return view('home', compact('derniers_personnages', 'prochains_evenements', 'compteurs'));
    }
}
