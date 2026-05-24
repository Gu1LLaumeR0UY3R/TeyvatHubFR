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
        $sort = request('sort', 'nom_asc');

        $animauxQuery = Animal::query()->with(['typeAnimal', 'photos']);

        switch ($sort) {
            case 'nom_desc':
                $animauxQuery->orderByDesc('nom_animal');
                break;
            case 'type_asc':
                $animauxQuery->orderBy('fid_TAnimal');
                break;
            case 'type_desc':
                $animauxQuery->orderByDesc('fid_TAnimal');
                break;
            case 'nom_asc':
            default:
                $animauxQuery->orderBy('nom_animal');
                break;
        }

        $animaux = $animauxQuery->paginate(20)->withQueryString();
        $typesAnimal = TypeAnimal::all();

        return view('admin.animaux.index', compact('animaux', 'typesAnimal', 'sort'));
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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucun animal sélectionné.');
        }

        $action = (string) $request->input('action', 'update');
        if ($action === 'delete') {
            Animal::whereIn('id_animal', $ids)->delete();
            return back()->with('success', count($ids) . ' animal(aux) supprimé(s).');
        }

        $data = $request->validate([
            'fid_TAnimal' => ['nullable', 'exists:type_animal,id_TAnimal'],
        ]);

        $data = array_filter($data, fn($v) => $v !== null);
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Animal::whereIn('id_animal', $ids)->update($data);

        return back()->with('success', count($ids) . ' animal(aux) mis à jour.');
    }
}
