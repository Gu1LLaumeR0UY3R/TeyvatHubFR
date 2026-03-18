<?php

namespace App\Http\Controllers;

use App\Models\Animal;
use App\Models\TypeAnimal;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AnimalController extends Controller
{
    public function index(Request $request): View
    {
        $query = Animal::with(['typeAnimal', 'photos']);

        if ($request->filled('search')) {
            $query->where('nom_animal', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('fid_TAnimal', $request->type);
        }

        match ($request->sort) {
            'nom_desc'  => $query->orderBy('nom_animal', 'desc'),
            'type_asc'  => $query->orderBy('fid_TAnimal', 'asc'),
            default     => $query->orderBy('nom_animal', 'asc'),
        };

        $animaux = $query->paginate(20)->withQueryString();
        $types   = TypeAnimal::orderBy('libelle_TAnimal')->get();

        return view('animaux.index', compact('animaux', 'types'));
    }

    public function show(Animal $animal): View
    {
        $animal->load([
            'typeAnimal',
            'photos',
            'regions.photos',
            'ingredients',
        ]);

        return view('animaux.show', compact('animal'));
    }
}
