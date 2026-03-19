<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegionController extends Controller
{
    public function index(): View
    {
        $regions = Region::with(['photos'])->orderBy('nom_region')->paginate(20);
        return view('admin.regions.index', compact('regions'));
    }

    public function create(): View
    {
        return view('admin.regions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_region'    => ['required', 'string', 'max:100'],
            'descri_region' => ['nullable', 'string'],
        ]);

        $region = Region::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/regions', 'public');
            $region->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.regions.index')
            ->with('success', 'Région créée avec succès.');
    }

    public function show(Region $region): View
    {
        $region->load(['photos', 'sousRegions', 'ennemis', 'animaux']);
        return view('admin.regions.show', compact('region'));
    }

    public function edit(Region $region): View
    {
        return view('admin.regions.edit', compact('region'));
    }

    public function update(Request $request, Region $region): RedirectResponse
    {
        $data = $request->validate([
            'nom_region'    => ['required', 'string', 'max:100'],
            'descri_region' => ['nullable', 'string'],
        ]);

        $region->update($data);

        if ($request->hasFile('photo')) {
            $old = $region->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $region->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/regions', 'public');
            $region->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.regions.index')
            ->with('success', 'Région mise à jour.');
    }

    public function destroy(Region $region): RedirectResponse
    {
        $region->delete();
        return redirect()->route('admin.regions.index')
            ->with('success', 'Région supprimée.');
    }
}
