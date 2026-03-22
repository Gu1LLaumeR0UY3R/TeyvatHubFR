<?php

namespace App\Http\Controllers;

use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\TypeArme;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Personnage::with(['element', 'etoile', 'typeArme', 'photos']);

        if ($request->search) {
            $query->where('nom_perso', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->element) {
            $query->where('fid_element', $request->element);
        }

        if ($request->rarete) {
            $query->where('fid_etoile', $request->rarete);
        }

        switch ($request->sort) {
            case 'rarete_desc':
                $query->orderBy('fid_etoile', 'desc');
                break;
            case 'rarete_asc':
                $query->orderBy('fid_etoile', 'asc');
                break;
            case 'nom_desc':
                $query->orderBy('nom_perso', 'desc');
                break;
            case 'element':
                $query->orderBy('fid_element');
                break;
            default:
                $query->orderBy('nom_perso');
        }

        $personnages = $query->get();
        $elements    = Elements::orderBy('libelle_element')->get();
        $etoiles     = Etoile::query()
            ->select('libelle')
            ->whereIn('libelle', ['4★', '5★'])
            ->distinct()
            ->orderBy('libelle')
            ->get();
        $typeArmes   = TypeArme::orderBy('libelle_TArme')->get();

        return view('personnages.index', compact('personnages', 'elements', 'etoiles', 'typeArmes'));
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load([
            'element',
            'etoile',
            'bio',
            'aptitudes.typeApti',
            'constellations',
            'specialite.plat.photos',
            'roles',
            'photos',
        ]);

        return view('personnages.show', compact('personnage'));
    }
}
