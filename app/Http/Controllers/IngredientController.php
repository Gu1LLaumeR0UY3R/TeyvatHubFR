<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $ingredients = Ingredient::with(['photos'])
            ->when($request->search, fn($q) => $q->where('nom_ingre', 'LIKE', '%' . $request->search . '%'))
            ->orderBy('nom_ingre')
            ->paginate(20)
            ->withQueryString();

        return view('ingredients.index', compact('ingredients'));
    }

    public function show(Ingredient $ingredient): View
    {
        $ingredient->load(['photos', 'animaux.photos', 'plats.photos']);

        return view('ingredients.show', compact('ingredient'));
    }
}
