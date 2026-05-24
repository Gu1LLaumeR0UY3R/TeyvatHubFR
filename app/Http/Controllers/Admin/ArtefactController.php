<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artefact;
use App\Models\Rarete;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArtefactController extends Controller
{
    public function index(): View
    {
        $sort = request('sort', 'nom_asc');

        $artefactsQuery = Artefact::query()->with(['rareté', 'photos']);

        switch ($sort) {
            case 'nom_desc':
                $artefactsQuery->orderByDesc('nom_artefact');
                break;
            case 'rarete_asc':
                $artefactsQuery->orderBy('fid_rareté');
                break;
            case 'rarete_desc':
                $artefactsQuery->orderByDesc('fid_rareté');
                break;
            case 'nom_asc':
            default:
                $artefactsQuery->orderBy('nom_artefact');
                break;
        }

        $artefacts = $artefactsQuery->paginate(20)->withQueryString();
        $raretes = Rarete::query()->orderBy('libelle_rareté')->get();

        return view('admin.artefacts.index', compact('artefacts', 'raretes', 'sort'));
    }

    public function create(): View
    {
        $raretes = Rarete::query()->orderBy('libelle_rareté')->get();

        return view('admin.artefacts.create', compact('raretes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateArtefact($request);

        $artefact = Artefact::create($data);
        $this->syncPhoto($request, $artefact);

        return redirect()->route('admin.artefacts.index')
            ->with('success', 'Artefact créé avec succès.');
    }

    public function edit(Artefact $artefact): View
    {
        $artefact->load('photos');
        $raretes = Rarete::query()->orderBy('libelle_rareté')->get();

        return view('admin.artefacts.edit', compact('artefact', 'raretes'));
    }

    public function update(Request $request, Artefact $artefact): RedirectResponse
    {
        $data = $this->validateArtefact($request);

        $artefact->update($data);
        $this->syncPhoto($request, $artefact);

        return redirect()->route('admin.artefacts.index')
            ->with('success', 'Artefact mis à jour.');
    }

    public function destroy(Artefact $artefact): RedirectResponse
    {
        $photo = $artefact->photos()->first();
        if ($photo && $photo->chemin_photo && !filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($photo->chemin_photo);
        }

        $artefact->photos()->delete();
        $artefact->delete();

        return redirect()->route('admin.artefacts.index')
            ->with('success', 'Artefact supprimé.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucun artefact sélectionné.');
        }

        $action = (string) $request->input('action', 'update');
        if ($action === 'delete') {
            Artefact::whereIn('id_artefact', $ids)->delete();
            return back()->with('success', count($ids) . ' artefact(s) supprimé(s).');
        }

        $data = $request->validate([
            'fid_rareté' => ['nullable', Rule::exists((new Rarete())->getTable(), 'id_rareté')],
        ]);

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Artefact::whereIn('id_artefact', $ids)->update($data);

        return back()->with('success', count($ids) . ' artefact(s) mis à jour.');
    }

    private function validateArtefact(Request $request): array
    {
        return $request->validate([
            'nom_artefact' => ['required', 'string', 'max:150'],
            'bonus_2p' => ['nullable', 'string'],
            'bonus_4p' => ['nullable', 'string'],
            'fid_rareté' => ['required', Rule::exists((new Rarete())->getTable(), 'id_rareté')],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    private function syncPhoto(Request $request, Artefact $artefact): void
    {
        if (!$request->hasFile('photo')) {
            return;
        }

        $old = $artefact->photos()->first();
        if ($old) {
            if ($old->chemin_photo && !filter_var($old->chemin_photo, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($old->chemin_photo);
            }

            $artefact->photos()->delete();
        }

        $path = $request->file('photo')->store('photos/artefacts', 'public');
        $artefact->photos()->create([
            'chemin_photo' => $path,
            'source_url' => null,
            'type' => 'icon',
        ]);
    }
}