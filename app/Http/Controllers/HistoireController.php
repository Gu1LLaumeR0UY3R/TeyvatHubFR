<?php

namespace App\Http\Controllers;

use App\Models\Chronologie;
use App\Models\Region;
use Illuminate\View\View;

class HistoireController extends Controller
{
    public function index(): View
    {
        $chronologie = Chronologie::with(['region', 'photos'])->orderBy('ordre')->get();
        $regions = Region::with('photos')->orderBy('nom_region')->get();

        return view('histoire.index', compact('chronologie', 'regions'));
    }
}
