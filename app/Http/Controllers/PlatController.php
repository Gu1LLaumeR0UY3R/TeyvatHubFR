<?php

namespace App\Http\Controllers;

use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PlatController extends Controller
{
    public function index(Request $request): View
    {
        $query = Plat::with(['rarete', 'photos']);

        if ($request->filled('search')) {
            $query->where('nom_plat', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('rarete')) {
            $query->where('fid_rareté', $request->rarete);
        }

        match ($request->sort) {
            'nom_desc'    => $query->orderBy('nom_plat', 'desc'),
            'rarete_asc'  => $query->orderBy('fid_rareté', 'asc'),
            'rarete_desc' => $query->orderBy('fid_rareté', 'desc'),
            default       => $query->orderBy('nom_plat', 'asc'),
        };

        $plats   = $query->paginate(20)->withQueryString();
        $raretés = Rarete::orderBy('id_rareté')->get();

        return view('cuisine.index', compact('plats', 'raretés'));
    }

    public function show(Plat $plat): View
    {
        $plat->load([
            'rarete',
            'photos',
            'ingredients',
            'specialite.personnage.photos',
        ]);

        return view('cuisine.show', compact('plat'));
    }
}
