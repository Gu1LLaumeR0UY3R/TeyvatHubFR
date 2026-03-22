<?php

namespace App\Http\Controllers;

use App\Models\Nation;
use Illuminate\View\View;

class NationController extends Controller
{
    public function index(): View
    {
        $nations = Nation::with('photos')->orderBy('nom_region')->get();

        return view('nations.index', compact('nations'));
    }

    public function show(Nation $nation): View
    {
        $nation->load([
            'photos',
            'sousRegions.photos',
            'ennemis.photos',
            'animaux.photos',
            'produits',
        ]);

        return view('nations.show', compact('nation'));
    }
}
