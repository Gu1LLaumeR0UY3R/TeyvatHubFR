<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ingredient::with(['photos']);

        if ($request->filled('search')) {
            $query->where('nom_ingre', 'LIKE', '%' . $request->search . '%');
        }

        $ingredients = $query->orderBy('nom_ingre')->paginate(20)->withQueryString();

        return view('ingredients.index', compact('ingredients'));
    }

    public function show(Ingredient $ingredient): View
    {
        $ingredient->load(['photos', 'plats.photos']);

        return view('ingredients.show', compact('ingredient'));
    }
}
