<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use App\Models\TypeAnimal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnimalController extends Controller
{
    public function index(): View
    {
        $animaux = Animal::with(['typeAnimal', 'photos'])
            ->orderBy('nom_animal')->paginate(20);
        return view('admin.animaux.index', compact('animaux'));
    }

    public function create(): View
    {
        $typesAnimal = TypeAnimal::all();
        return view('admin.animaux.create', compact('typesAnimal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_animal'  => ['required', 'string', 'max:100'],
            'fid_TAnimal' => ['required', 'exists:type_animal,id_TAnimal'],
        ]);

        $animal = Animal::create($data);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/animaux', 'public');
            $animal->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.animaux.index')
            ->with('success', 'Animal créé avec succès.');
    }

    public function show(Animal $animal): View
    {
        $animal->load(['typeAnimal', 'photos', 'regions', 'ingredients']);
        return view('admin.animaux.show', compact('animal'));
    }

    public function edit(Animal $animal): View
    {
        $typesAnimal = TypeAnimal::all();
        return view('admin.animaux.edit', compact('animal', 'typesAnimal'));
    }

    public function update(Request $request, Animal $animal): RedirectResponse
    {
        $data = $request->validate([
            'nom_animal'  => ['required', 'string', 'max:100'],
            'fid_TAnimal' => ['required', 'exists:type_animal,id_TAnimal'],
        ]);

        $animal->update($data);

        if ($request->hasFile('photo')) {
            $old = $animal->photos->first();
            if ($old) {
                if (!filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                    \Illuminate\Support\Facades\Storage::delete($old->chemin_photo);
                }
                $animal->photos()->delete();
            }
            $path = $request->file('photo')->store('photos/animaux', 'public');
            $animal->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.animaux.index')
            ->with('success', 'Animal mis à jour.');
    }

    public function destroy(Animal $animal): RedirectResponse
    {
        $animal->delete();
        return redirect()->route('admin.animaux.index')
            ->with('success', 'Animal supprimé.');
    }
}
