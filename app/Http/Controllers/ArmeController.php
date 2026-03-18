<?php

namespace App\Http\Controllers;

use App\Models\Arme;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ArmeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Arme::with(['typeArme', 'etoile', 'photos']);

        if ($request->filled('search')) {
            $query->where('nom_arme', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('fid_TArmes', $request->type);
        }

        if ($request->filled('etoile')) {
            $query->where('fid_etoile', $request->etoile);
        }

        match ($request->sort) {
            'nom_desc'   => $query->orderBy('nom_arme', 'desc'),
            'rarete_asc' => $query->orderBy('fid_etoile', 'asc'),
            'rarete_desc'=> $query->orderBy('fid_etoile', 'desc'),
            default      => $query->orderBy('nom_arme', 'asc'),
        };

        $armes   = $query->paginate(20)->withQueryString();
        $types   = TypeArme::orderBy('libelle_TArme')->get();
        $etoiles = Etoile::orderBy('id_etoile')->get();

        return view('armes.index', compact('armes', 'types', 'etoiles'));
    }

    public function show(Arme $arme): View
    {
        $arme->load([
            'typeArme',
            'etoile',
            'photos',
            'statsNiveaux',
            'statsRangs',
        ]);

        return view('armes.show', compact('arme'));
    }
}
