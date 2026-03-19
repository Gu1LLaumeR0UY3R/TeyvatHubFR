<?php

namespace App\Http\Controllers;

use App\Models\Elements;
use App\Models\Ennemi;
use App\Models\TypeEnnemi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnnemiController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ennemi::with(['typeEnnemi', 'element', 'photos']);

        if ($request->search) {
            $query->where('nom_ennemi', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->type) {
            $query->where('fid_typeEnne', $request->type);
        }

        if ($request->element) {
            $query->where('fid_element', $request->element);
        }

        switch ($request->sort) {
            case 'nom_desc':
                $query->orderBy('nom_ennemi', 'desc');
                break;
            case 'type':
                $query->orderBy('fid_typeEnne');
                break;
            default:
                $query->orderBy('nom_ennemi');
        }

        $ennemis = $query->paginate(20)->withQueryString();
        $types = TypeEnnemi::orderBy('libelle_Type')->get();
        $elements = Elements::orderBy('libelle_element')->get();

        return view('ennemis.index', compact('ennemis', 'types', 'elements'));
    }

    public function show(Ennemi $ennemi): View
    {
        $ennemi->load([
            'typeEnnemi',
            'element',
            'photos',
            'regions.photos',
            'materiaux.rarete',
            'materiaux.typeMateriaux',
            'materiaux.photos',
        ]);

        return view('ennemis.show', compact('ennemi'));
    }
}
