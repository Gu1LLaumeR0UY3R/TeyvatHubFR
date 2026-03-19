<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plat;
use App\Models\Rarete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CuisineController extends Controller
{
    public function index(): View
    {
        $plats = Plat::with(['rarete', 'photos'])
            ->orderBy('nom_plat')->paginate(20);
        return view('admin.cuisine.index', compact('plats'));
    }

    public function create(): View
    {
        $raretes = Rarete::all();
        return view('admin.cuisine.create', compact('raretes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_plat'    => ['required', 'string', 'max:100'],
            'descri_plat' => ['nullable', 'string'],
            'fid_rareté'  => ['required', 'exists:rareté,id_rareté'],
        ]);

        $plat = Plat::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/cuisine', 'public');
            $plat->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.cuisine.index')
            ->with('success', 'Plat créé avec succès.');
    }

    public function show(Plat $plat): View
    {
        $plat->load(['rarete', 'photos', 'ingredients']);
        return view('admin.cuisine.show', compact('plat'));
    }

    public function edit(Plat $plat): View
    {
        $raretes = Rarete::all();
        return view('admin.cuisine.edit', compact('plat', 'raretes'));
    }

    public function update(Request $request, Plat $plat): RedirectResponse
    {
        $data = $request->validate([
            'nom_plat'    => ['required', 'string', 'max:100'],
            'descri_plat' => ['nullable', 'string'],
            'fid_rareté'  => ['required', 'exists:rareté,id_rareté'],
        ]);

        $plat->update($data);

        if ($request->hasFile('photo')) {
            $old = $plat->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $plat->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/cuisine', 'public');
            $plat->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.cuisine.index')
            ->with('success', 'Plat mis à jour.');
    }

    public function destroy(Plat $plat): RedirectResponse
    {
        $plat->delete();
        return redirect()->route('admin.cuisine.index')
            ->with('success', 'Plat supprimé.');
    }
}
