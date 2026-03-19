<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arme;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArmeController extends Controller
{
    public function index(): View
    {
        $armes = Arme::with(['typeArme', 'etoile', 'photos'])
            ->orderBy('nom_arme')->paginate(20);
        return view('admin.armes.index', compact('armes'));
    }

    public function create(): View
    {
        $typesArme = TypeArme::all();
        $etoiles   = Etoile::all();
        return view('admin.armes.create', compact('typesArme', 'etoiles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_arme'   => ['required', 'string', 'max:100'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
        ]);

        $arme = Arme::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/armes', 'public');
            $arme->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.armes.index')
            ->with('success', 'Arme créée avec succès.');
    }

    public function show(Arme $arme): View
    {
        $arme->load(['typeArme', 'etoile', 'photos', 'statsNiveau', 'statsRang']);
        return view('admin.armes.show', compact('arme'));
    }

    public function edit(Arme $arme): View
    {
        $typesArme = TypeArme::all();
        $etoiles   = Etoile::all();
        return view('admin.armes.edit', compact('arme', 'typesArme', 'etoiles'));
    }

    public function update(Request $request, Arme $arme): RedirectResponse
    {
        $data = $request->validate([
            'nom_arme'   => ['required', 'string', 'max:100'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
        ]);

        $arme->update($data);

        if ($request->hasFile('photo')) {
            $old = $arme->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $arme->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/armes', 'public');
            $arme->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.armes.index')
            ->with('success', 'Arme mise à jour.');
    }

    public function destroy(Arme $arme): RedirectResponse
    {
        $arme->delete();
        return redirect()->route('admin.armes.index')
            ->with('success', 'Arme supprimée.');
    }
}
