<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NationController extends Controller
{
    public function index(): View
    {
        $nations = Nation::with(['photos'])->orderBy('nom_region')->paginate(20);
        return view('admin.nations.index', compact('nations'));
    }

    public function create(): View
    {
        return view('admin.nations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_region'    => ['required', 'string', 'max:100'],
            'descri_region' => ['nullable', 'string'],
        ]);

        $nation = Nation::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/regions', 'public');
            $region->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.nations.index')
            ->with('success', 'Nation créée avec succès.');
    }

    public function show(Nation $nation): View
    {
        $nation->load(['photos', 'sousRegions', 'ennemis', 'animaux']);
        return view('admin.nations.show', compact('nation'));
    }

    public function edit(Nation $nation): View
    {
        return view('admin.nations.edit', compact('nation'));
    }

    public function update(Request $request, Nation $nation): RedirectResponse
    {
        $data = $request->validate([
            'nom_region'    => ['required', 'string', 'max:100'],
            'descri_region' => ['nullable', 'string'],
        ]);

        $nation->update($data);

        if ($request->hasFile('photo')) {
            $old = $nation->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $nation->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/regions', 'public');
            $nation->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.nations.index')
            ->with('success', 'Nation mise à jour.');
    }

    public function destroy(Nation $nation): RedirectResponse
    {
        $nation->delete();
        return redirect()->route('admin.regions.index')
            ->with('success', 'Région supprimée.');
    }
}
