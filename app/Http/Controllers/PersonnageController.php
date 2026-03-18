<?php

namespace App\Http\Controllers;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PersonnageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Personnage::with(['element', 'etoile', 'typeArme', 'photos']);

        if ($request->filled('search')) {
            $query->where('nom_perso', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('element')) {
            $query->where('fid_element', $request->element);
        }

        if ($request->filled('etoile')) {
            $query->where('fid_etoile', $request->etoile);
        }

        match ($request->sort) {
            'nom_asc'     => $query->orderBy('nom_perso', 'asc'),
            'nom_desc'    => $query->orderBy('nom_perso', 'desc'),
            'rarete_asc'  => $query->orderBy('fid_etoile', 'asc'),
            'rarete_desc' => $query->orderBy('fid_etoile', 'desc'),
            default       => $query->orderBy('nom_perso', 'asc'),
        };

        $personnages = $query->paginate(20)->withQueryString();
        $elements    = Elements::orderBy('libelle_element')->get();
        $etoiles     = Etoile::orderBy('libelle')->get();

        return view('personnages.index', compact('personnages', 'elements', 'etoiles'));
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load([
            'element',
            'etoile',
            'typePerso',
            'typeArme',
            'photos',
            'bio',
            'aptitudes.typeApti',
            'constellations',
            'specialite.plat.photos',
            'roles',
        ]);

        return view('personnages.show', compact('personnage'));
    }
}
