<?php

namespace App\Http\Controllers;

use App\Models\Arme;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArmeController extends Controller
{
    public function index(Request $request): View
    {
        $query = Arme::with(['typeArme', 'etoile', 'photos']);

        if ($request->search) {
            $query->where('nom_arme', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->type) {
            $query->where('fid_TArmes', $request->type);
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
                $query->orderBy('nom_arme', 'desc');
                break;
            case 'type':
                $query->orderBy('fid_TArmes');
                break;
            default:
                $query->orderBy('nom_arme');
        }

        $armes   = $query->get();
        $types   = TypeArme::orderBy('libelle_TArme')->get();
        $etoiles = Etoile::query()
            ->select('libelle')
            ->whereIn('libelle', ['1★', '2★', '3★', '4★', '5★'])
            ->distinct()
            ->orderBy('libelle')
            ->get();

        return view('armes.index', compact('armes', 'types', 'etoiles'));
    }

    public function show(Arme $arme): View
    {
        $arme->load([
            'typeArme',
            'etoile',
            'photos',
            'statsNiveaux',
            'statsRangs',
        ]);

        return view('armes.show', compact('arme'));
    }
}
