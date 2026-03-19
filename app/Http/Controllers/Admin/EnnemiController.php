<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elements;
use App\Models\Ennemi;
use App\Models\TypeEnnemi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnnemiController extends Controller
{
    public function index(): View
    {
        $ennemis = Ennemi::with(['typeEnnemi', 'photos'])
            ->orderBy('nom_ennemi')->paginate(20);
        return view('admin.ennemis.index', compact('ennemis'));
    }

    public function create(): View
    {
        $typesEnnemi = TypeEnnemi::all();
        $elements    = Elements::all();
        return view('admin.ennemis.create', compact('typesEnnemi', 'elements'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_ennemi'   => ['required', 'string', 'max:100'],
            'fid_typeEnne' => ['required', 'exists:type_ennemi,id_typeEnnemi'],
            'fid_element'  => ['nullable', 'exists:elements,id_element'],
        ]);

        $ennemi = Ennemi::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/ennemis', 'public');
            $ennemi->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.ennemis.index')
            ->with('success', 'Ennemi créé avec succès.');
    }

    public function show(Ennemi $ennemi): View
    {
        $ennemi->load(['typeEnnemi', 'photos', 'regions']);
        return view('admin.ennemis.show', compact('ennemi'));
    }

    public function edit(Ennemi $ennemi): View
    {
        $typesEnnemi = TypeEnnemi::all();
        $elements    = Elements::all();
        return view('admin.ennemis.edit', compact('ennemi', 'typesEnnemi', 'elements'));
    }

    public function update(Request $request, Ennemi $ennemi): RedirectResponse
    {
        $data = $request->validate([
            'nom_ennemi'   => ['required', 'string', 'max:100'],
            'fid_typeEnne' => ['required', 'exists:type_ennemi,id_typeEnnemi'],
            'fid_element'  => ['nullable', 'exists:elements,id_element'],
        ]);

        $ennemi->update($data);

        if ($request->hasFile('photo')) {
            $old = $ennemi->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $ennemi->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/ennemis', 'public');
            $ennemi->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.ennemis.index')
            ->with('success', 'Ennemi mis à jour.');
    }

    public function destroy(Ennemi $ennemi): RedirectResponse
    {
        $ennemi->delete();
        return redirect()->route('admin.ennemis.index')
            ->with('success', 'Ennemi supprimé.');
    }
}
