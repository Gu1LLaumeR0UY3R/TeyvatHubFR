<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $regions = Region::with('photos')->orderBy('nom_region')->get();

        return view('histoire.regions.index', compact('regions'));
    }

    public function show(Region $region): View
    {
        $region->load([
            'photos',
            'sousRegions.photos',
            'ennemis.photos',
            'animaux.photos',
            'produits',
        ]);

        return view('histoire.regions.show', compact('region'));
    }
}
