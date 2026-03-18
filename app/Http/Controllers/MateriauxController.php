<?php

namespace App\Http\Controllers;

use App\Models\Materiaux;
use App\Models\TypeMateriaux;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class MateriauxController extends Controller
{
    public function index(Request $request): View
    {
        $query = Materiaux::with(['typeMateriaux', 'rarete', 'photos']);

        if ($request->filled('search')) {
            $query->where('nom_mat', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('fid_typeM', $request->type);
        }

        $materiaux = $query->orderBy('nom_mat')->paginate(20)->withQueryString();
        $types     = TypeMateriaux::orderBy('libelle_TypeM')->get();

        return view('materiaux.index', compact('materiaux', 'types'));
    }

    public function show(Materiaux $materiaux): View
    {
        $materiaux->load(['typeMateriaux', 'rarete', 'photos', 'ennemis.photos']);

        return view('materiaux.show', compact('materiaux'));
    }
}
