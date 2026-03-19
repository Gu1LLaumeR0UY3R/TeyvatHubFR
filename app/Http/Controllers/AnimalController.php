<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\TypeAnimal;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnimalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Animal::with(['typeAnimal', 'photos']);

        if ($request->search) {
            $query->where('nom_animal', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->type) {
            $query->where('fid_TAnimal', $request->type);
        }

        switch ($request->sort) {
            case 'nom_desc':
                $query->orderBy('nom_animal', 'desc');
                break;
            case 'type':
                $query->orderBy('fid_TAnimal');
                break;
            default:
                $query->orderBy('nom_animal');
        }

        $animaux = $query->paginate(20)->withQueryString();
        $types = TypeAnimal::orderBy('libelle_TAnimal')->get();

        return view('animaux.index', compact('animaux', 'types'));
    }

    public function show(Animal $animal): View
    {
        $animal->load([
            'typeAnimal',
            'photos',
            'regions.photos',
            'ingredients.photos',
        ]);

        return view('animaux.show', compact('animal'));
    }
}
