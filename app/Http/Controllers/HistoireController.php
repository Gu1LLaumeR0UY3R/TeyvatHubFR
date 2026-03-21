<?php

namespace App\Http\Controllers;

use App\Models\Chronologie;
use App\Models\Nation;
use Illuminate\View\View;

class HistoireController extends Controller
{
    public function index(): View
    {
        $chronologie = Chronologie::with(['nation', 'photos'])->orderBy('ordre')->get();
        $nations = Nation::orderBy('nom_region')->get();

        return view('histoire.index', compact('chronologie', 'nations'));
    }
}
