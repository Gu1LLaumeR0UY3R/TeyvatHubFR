<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Elements;
use App\Models\Etoile;
use App\Models\Constellation;
use App\Models\Artefact;
use App\Models\Personnage;
use App\Models\Ennemi;
use App\Models\Reaction;
use App\Models\Role;
use App\Models\TypeArme;
use App\Models\TypePerso;
use App\Models\Nation;
use App\Models\TypeApti;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonnageController extends Controller
{
    public function index(): View
    {
        $sort = request('sort', 'nom_asc');

        $personnagesQuery = Personnage::query()
            ->with(['element', 'etoile', 'photos']);

        switch ($sort) {
            case 'nom_desc':
                $personnagesQuery->orderBy('nom_perso', 'desc');
                break;
            case 'element_asc':
                $personnagesQuery->orderBy('fid_element');
                break;
            case 'element_desc':
                $personnagesQuery->orderByDesc('fid_element');
                break;
            case 'rarete_asc':
                $personnagesQuery->orderBy('fid_etoile');
                break;
            case 'rarete_desc':
                $personnagesQuery->orderByDesc('fid_etoile');
                break;
            case 'arme_asc':
                $personnagesQuery->orderBy('fid_TArmes');
                break;
            case 'arme_desc':
                $personnagesQuery->orderByDesc('fid_TArmes');
                break;
            case 'nom_asc':
            default:
                $personnagesQuery->orderBy('nom_perso');
                break;
        }

        $personnages = $personnagesQuery->paginate(20)->withQueryString();
        $elements = Elements::orderBy('libelle_element')->get();
        $etoiles = Etoile::whereIn('libelle', ['4★', '5★'])->orderBy('libelle')->get();
        $typeArmes = TypeArme::all();

        return view('admin.personnages.index', compact('personnages', 'elements', 'etoiles', 'typeArmes', 'sort'));
    }

    public function create(): RedirectResponse
    {
        $fidElement = Elements::query()->value('id_element');
        $fidEtoile = Etoile::query()->value('id_etoile');
        $fidTypeArme = TypeArme::query()->value('id_TArmes');
        $fidTypePerso = TypePerso::query()->value('id_TP');

        if (!$fidElement || !$fidEtoile || !$fidTypeArme || !$fidTypePerso) {
            return redirect()->route('admin.personnages.index')
                ->with('error', 'Impossible d\'ouvrir un personnage vierge: certaines references (element, etoile, type arme ou type perso) sont manquantes.');
        }

        $draft = Personnage::create([
            'nom_perso' => 'Brouillon ' . uniqid(),
            'fid_etoile' => $fidEtoile,
            'fid_element' => $fidElement,
            'fid_TArmes' => $fidTypeArme,
            'fid_TP' => $fidTypePerso,
        ]);

        return redirect()->route('admin.personnages.edit', ['personnage' => $draft, 'fresh' => 1]);
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

        return redirect()->route('admin.personnages.edit', $personnage)
            ->with('success', 'Personnage créé. Vous pouvez maintenant compléter sa fiche.');
    }

    public function show(Personnage $personnage): View
    {
        $personnage->load(['element', 'etoile', 'photos', 'bio', 'aptitudes', 'constellations']);
        return view('admin.personnages.show', compact('personnage'));
    }

    public function edit(Personnage $personnage): View
    {
        $personnage->load([
            'element',
            'etoile',
            'typeArme',
            'typePerso',
            'photos',
            'videos',
            'histoires',
            'nations',
            'armesRecommandees.arme',
            'artefactsRecommandees.artefact1',
            'artefactsRecommandees.artefact1.photos',
            'artefactsRecommandees.artefact1.rareté',
            'artefactsRecommandees.artefact2',
            'artefactsRecommandees.artefact2.photos',
            'artefactsRecommandees.artefact2.rareté',
            'constellations.photo',
            'aptitudes.typeApti',
            'aptitudes.photos',
        ]);

        $elements  = Elements::all();
        $etoiles   = Etoile::all();
        $typesArme = TypeArme::all();
        $typesPerso= TypePerso::all();
        $roles     = Role::all();
        $nations   = Nation::all();
        $typesApti = TypeApti::orderBy('id_TypeApti')->get();

        $armesDisponibles = \App\Models\Arme::with('typeArme')->orderBy('nom_arme')->get();
        $ennemisDisponibles = Ennemi::with(['typeEnnemi', 'photos'])->orderBy('nom_ennemi')->get();
        $artefactsDisponibles = Artefact::with(['photos', 'rareté'])->orderBy('nom_artefact')->get();
        $allowedTypeId = $personnage->fid_TArmes;
        $reactions = Reaction::orderBy('nom_reaction')->get();

        return view('admin.personnages.edit', compact('personnage', 'elements', 'etoiles', 'typesArme', 'typesPerso', 'roles', 'nations', 'armesDisponibles', 'ennemisDisponibles', 'artefactsDisponibles', 'allowedTypeId', 'typesApti', 'reactions'));
    }

    public function update(Request $request, Personnage $personnage): RedirectResponse
    {
        $data = $request->validate([
            'nom_perso'  => ['required', 'string', 'max:100'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_element'=> ['required', 'exists:elements,id_element'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
            'fid_TP'     => ['required', 'exists:type_perso,id_TP'],
            'positions_const' => ['nullable', 'json'],
            'constellation_map_image' => ['nullable', 'image', 'max:5120'],
            'constellation_map_image_url' => ['nullable', 'url', 'max:500'],
        ]);

        $personnage->update([
            'nom_perso' => $data['nom_perso'],
            'fid_etoile' => $data['fid_etoile'],
            'fid_element' => $data['fid_element'],
            'fid_TArmes' => $data['fid_TArmes'],
            'fid_TP' => $data['fid_TP'],
        ]);

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

        $constCarte = Constellation::where('fid_perso', $personnage->id_perso)
            ->orderBy('id_const')
            ->first();

        if ($constCarte) {
            if ($request->filled('positions_const') && Schema::hasColumn('constellation', 'positions_const')) {
                $decodedPositions = json_decode((string) $request->input('positions_const'), true);

                if (is_array($decodedPositions)) {
                    $rawPoints = is_array($decodedPositions['points'] ?? null)
                        ? $decodedPositions['points']
                        : $decodedPositions;

                    $normalizedPoints = [];
                    for ($i = 1; $i <= 6; $i++) {
                        $rawPoint = $rawPoints[(string) $i] ?? $rawPoints[$i] ?? null;
                        if (!is_array($rawPoint)) {
                            continue;
                        }

                        if (!isset($rawPoint['x']) || !isset($rawPoint['y'])) {
                            continue;
                        }

                        $x = max(0, min(100, (float) $rawPoint['x']));
                        $y = max(0, min(100, (float) $rawPoint['y']));
                        $normalizedPoints[(string) $i] = [
                            'x' => round($x, 1),
                            'y' => round($y, 1),
                        ];
                    }

                    $normalizedLines = [];
                    $seenPairs = [];
                    $rawLines = is_array($decodedPositions['lines'] ?? null) ? $decodedPositions['lines'] : [];
                    foreach ($rawLines as $line) {
                        if (!is_array($line)) {
                            continue;
                        }
                        $from = isset($line['from']) ? (int) $line['from'] : null;
                        $to = isset($line['to']) ? (int) $line['to'] : null;
                        if (!$from || !$to || $from === $to) {
                            continue;
                        }
                        if ($from < 1 || $from > 6 || $to < 1 || $to > 6) {
                            continue;
                        }
                        if (!isset($normalizedPoints[(string) $from]) || !isset($normalizedPoints[(string) $to])) {
                            continue;
                        }

                        $a = min($from, $to);
                        $b = max($from, $to);
                        $pair = $a . '-' . $b;
                        if (isset($seenPairs[$pair])) {
                            continue;
                        }
                        $seenPairs[$pair] = true;
                        $normalizedLines[] = ['from' => $a, 'to' => $b];
                    }

                    $normalizedPositions = [
                        'points' => $normalizedPoints,
                        'lines' => $normalizedLines,
                    ];

                    if ($constCarte->positions_const !== $normalizedPositions) {
                        $constCarte->positions_const = $normalizedPositions;
                    }
                }
            }

            if ($request->filled('constellation_map_image_url')) {
                $url = (string) $request->input('constellation_map_image_url');
                $constCarte->photo()->updateOrCreate(
                    [],
                    [
                        'chemin_photo' => $url,
                        'source_url' => $url,
                    ]
                );
            }

            if ($request->hasFile('constellation_map_image')) {
                $oldPhoto = $constCarte->photo;
                if ($oldPhoto && !filter_var((string) $oldPhoto->chemin_photo, FILTER_VALIDATE_URL)) {
                    Storage::disk('public')->delete($oldPhoto->chemin_photo);
                }

                $storedPath = $request->file('constellation_map_image')
                    ->store('photos/personnages/constellations/maps', 'public');

                $constCarte->photo()->updateOrCreate(
                    [],
                    [
                        'chemin_photo' => $storedPath,
                        'source_url' => null,
                    ]
                );
            }

            if ($constCarte->isDirty('positions_const')) {
                $constCarte->save();
            }
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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucun personnage sélectionné.');
        }

        $data = $request->validate([
            'fid_element' => ['nullable', 'exists:elements,id_element'],
            'fid_etoile'  => ['nullable', 'exists:etoile,id_etoile'],
            'fid_TArmes'  => ['nullable', 'exists:type_armes,id_TArmes'],
            'fid_TP'      => ['nullable', 'exists:type_perso,id_TP'],
        ]);

        $data = array_filter($data, fn($value) => $value !== null);

        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Personnage::whereIn('id_perso', $ids)->update($data);

        return back()->with('success', count($ids) . ' personnage(s) mis à jour.');
    }
}
