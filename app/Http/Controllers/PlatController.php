<?php

namespace App\Http\Controllers;

use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatController extends Controller
{
    public function index(Request $request): View
    {
        $query = Plat::with(['rarete', 'photos', 'specialite']);

        if ($request->search) {
            $query->where('nom_plat', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->rarete) {
            $query->where('fid_rareté', $request->rarete);
        }

        switch ($request->sort) {
            case 'nom_desc':
                $query->orderBy('nom_plat', 'desc');
                break;
            case 'rarete_desc':
                $query->orderBy('fid_rareté', 'desc');
                break;
            case 'rarete_asc':
                $query->orderBy('fid_rareté', 'asc');
                break;
            default:
                $query->orderBy('nom_plat');
        }

        $plats = $query->paginate(20)->withQueryString();
        $raretés = Rarete::orderBy('id_rareté')->get();

        return view('cuisine.index', compact('plats', 'raretés'));
    }

    public function show(Plat $plat): View
    {
        $plat->load([
            'rarete',
            'photos',
            'ingredients.photos',
            'specialite.photos',
            'specialite.personnage.photos',
        ]);

        return view('cuisine.show', compact('plat'));
    }
}
