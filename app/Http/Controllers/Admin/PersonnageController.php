<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Personnage;
use App\Models\Role;
use App\Models\TypeArme;
use App\Models\TypePerso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    public function index(): View
    {
        $personnages = Personnage::with(['element', 'etoile', 'photos'])
            ->orderBy('nom_perso')->paginate(20);
        return view('admin.personnages.index', compact('personnages'));
    }

    public function create(): View
    {
        $elements  = Elements::all();
        $etoiles   = Etoile::all();
        $typesArme = TypeArme::all();
        $typesPerso= TypePerso::all();
        $roles     = Role::all();
        return view('admin.personnages.create', compact('elements', 'etoiles', 'typesArme', 'typesPerso', 'roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_perso'  => ['required', 'string', 'max:100'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_element'=> ['required', 'exists:elements,id_element'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
            'fid_TP'     => ['required', 'exists:type_perso,id_TP'],
        ]);

        $personnage = Personnage::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/personnages', 'public');
            $personnage->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.personnages.index')
            ->with('success', 'Personnage créé avec succès.');
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load(['element', 'etoile', 'photos', 'bio', 'aptitudes', 'constellations']);
        return view('admin.personnages.show', compact('personnage'));
    }

    public function edit(Personnage $personnage): View
    {
        $elements  = Elements::all();
        $etoiles   = Etoile::all();
        $typesArme = TypeArme::all();
        $typesPerso= TypePerso::all();
        $roles     = Role::all();
        return view('admin.personnages.edit', compact('personnage', 'elements', 'etoiles', 'typesArme', 'typesPerso', 'roles'));
    }

    public function update(Request $request, Personnage $personnage): RedirectResponse
    {
        $data = $request->validate([
            'nom_perso'  => ['required', 'string', 'max:100'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_element'=> ['required', 'exists:elements,id_element'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
            'fid_TP'     => ['required', 'exists:type_perso,id_TP'],
        ]);

        $personnage->update($data);

        if ($request->hasFile('photo')) {
            $old = $personnage->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $personnage->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/personnages', 'public');
            $personnage->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.personnages.index')
            ->with('success', 'Personnage mis à jour.');
    }

    public function destroy(Personnage $personnage): RedirectResponse
    {
        $personnage->delete();
        return redirect()->route('admin.personnages.index')
            ->with('success', 'Personnage supprimé.');
    }
}
