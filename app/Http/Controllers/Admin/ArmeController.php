<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Arme;
use App\Models\ArmStatsNiveau;
use App\Models\ArmStatsRang;
use App\Models\Etoile;
use App\Models\TypeArme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArmeController extends Controller
{
    public function index(): View
    {
        $sort = request('sort', 'nom_asc');

        $armesQuery = Arme::query()->with(['typeArme', 'etoile', 'photos']);

        switch ($sort) {
            case 'nom_desc':
                $armesQuery->orderByDesc('nom_arme');
                break;
            case 'type_asc':
                $armesQuery->orderBy('fid_TArmes');
                break;
            case 'type_desc':
                $armesQuery->orderByDesc('fid_TArmes');
                break;
            case 'rarete_asc':
                $armesQuery->orderBy('fid_etoile');
                break;
            case 'rarete_desc':
                $armesQuery->orderByDesc('fid_etoile');
                break;
            case 'nom_asc':
            default:
                $armesQuery->orderBy('nom_arme');
                break;
        }

        $armes = $armesQuery->paginate(20)->withQueryString();
        $typesArme = TypeArme::all();
        $etoiles = Etoile::whereIn('libelle', ['1★', '2★', '3★', '4★', '5★'])->orderBy('libelle')->get();

        return view('admin.armes.index', compact('armes', 'typesArme', 'etoiles', 'sort'));
    }

    public function create(): View
    {
        $typesArme = TypeArme::all();
        $etoiles   = Etoile::whereIn('libelle', ['1★', '2★', '3★', '4★', '5★'])->orderBy('libelle')->get();
        return view('admin.armes.create', compact('typesArme', 'etoiles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom_arme'   => ['required', 'string', 'max:100'],
            'descr_arme' => ['nullable', 'string'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
            'stats_niveau_new' => ['nullable', 'array'],
            'stats_niveau_new.*.lvl_ASN' => ['nullable', 'integer', 'min:1', 'max:90'],
            'stats_niveau_new.*.main_stat' => ['nullable', 'numeric'],
            'stats_niveau_new.*.subs_stats' => ['nullable', 'numeric'],
            'stats_rang_new' => ['nullable', 'array'],
            'stats_rang_new.*.rang_ASR' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stats_rang_new.*.descri_ASR' => ['nullable', 'string'],
        ]);

        $arme = Arme::create($data);

        foreach (($data['stats_niveau_new'] ?? []) as $row) {
            if (!empty($row['lvl_ASN'])) {
                $arme->statsNiveaux()->create([
                    'lvl_ASN' => $row['lvl_ASN'],
                    'main_stat' => $row['main_stat'] ?? null,
                    'subs_stats' => $row['subs_stats'] ?? null,
                ]);
            }
        }

        foreach (($data['stats_rang_new'] ?? []) as $row) {
            if (!empty($row['rang_ASR'])) {
                $arme->statsRangs()->create([
                    'rang_ASR' => $row['rang_ASR'],
                    'descri_ASR' => $row['descri_ASR'] ?? null,
                ]);
            }
        }

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos/armes', 'public');
            $arme->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route('admin.armes.index')
            ->with('success', 'Arme créée avec succès.');
    }

    public function show(Arme $arme): View
    {
        $arme->load(['typeArme', 'etoile', 'photos', 'statsNiveaux', 'statsRangs']);
        return view('admin.armes.show', compact('arme'));
    }

    public function edit(Arme $arme): View
    {
        $arme->load(['statsNiveaux', 'statsRangs']);
        $typesArme = TypeArme::all();
        $etoiles   = Etoile::whereIn('libelle', ['1★', '2★', '3★', '4★', '5★'])->orderBy('libelle')->get();
        return view('admin.armes.edit', compact('arme', 'typesArme', 'etoiles'));
    }

    public function update(Request $request, Arme $arme): RedirectResponse
    {
        $data = $request->validate([
            'nom_arme'   => ['required', 'string', 'max:100'],
            'descr_arme' => ['nullable', 'string'],
            'fid_etoile' => ['required', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['required', 'exists:type_armes,id_TArmes'],
            'stats_niveau' => ['nullable', 'array'],
            'stats_niveau.*.id_ASN' => ['nullable', 'integer', 'exists:arm_stats_niveau,id_ASN'],
            'stats_niveau.*.lvl_ASN' => ['nullable', 'integer', 'min:1', 'max:90'],
            'stats_niveau.*.main_stat' => ['nullable', 'numeric'],
            'stats_niveau.*.subs_stats' => ['nullable', 'numeric'],
            'stats_niveau_new' => ['nullable', 'array'],
            'stats_niveau_new.*.lvl_ASN' => ['nullable', 'integer', 'min:1', 'max:90'],
            'stats_niveau_new.*.main_stat' => ['nullable', 'numeric'],
            'stats_niveau_new.*.subs_stats' => ['nullable', 'numeric'],
            'stats_rang' => ['nullable', 'array'],
            'stats_rang.*.id_ASR' => ['nullable', 'integer', 'exists:arm_stats_rang,id_ASR'],
            'stats_rang.*.rang_ASR' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stats_rang.*.descri_ASR' => ['nullable', 'string'],
            'stats_rang_new' => ['nullable', 'array'],
            'stats_rang_new.*.rang_ASR' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stats_rang_new.*.descri_ASR' => ['nullable', 'string'],
        ]);

        $arme->update($data);

        foreach (($data['stats_niveau'] ?? []) as $row) {
            if (empty($row['id_ASN']) || empty($row['lvl_ASN'])) {
                continue;
            }

            ArmStatsNiveau::where('id_ASN', $row['id_ASN'])
                ->where('fid_arme', $arme->id_arme)
                ->update([
                    'lvl_ASN' => $row['lvl_ASN'],
                    'main_stat' => $row['main_stat'] ?? null,
                    'subs_stats' => $row['subs_stats'] ?? null,
                ]);
        }

        foreach (($data['stats_niveau_new'] ?? []) as $row) {
            if (!empty($row['lvl_ASN'])) {
                $arme->statsNiveaux()->create([
                    'lvl_ASN' => $row['lvl_ASN'],
                    'main_stat' => $row['main_stat'] ?? null,
                    'subs_stats' => $row['subs_stats'] ?? null,
                ]);
            }
        }

        foreach (($data['stats_rang'] ?? []) as $row) {
            if (empty($row['id_ASR']) || empty($row['rang_ASR'])) {
                continue;
            }

            ArmStatsRang::where('id_ASR', $row['id_ASR'])
                ->where('fid_arme', $arme->id_arme)
                ->update([
                    'rang_ASR' => $row['rang_ASR'],
                    'descri_ASR' => $row['descri_ASR'] ?? null,
                ]);
        }

        foreach (($data['stats_rang_new'] ?? []) as $row) {
            if (!empty($row['rang_ASR'])) {
                $arme->statsRangs()->create([
                    'rang_ASR' => $row['rang_ASR'],
                    'descri_ASR' => $row['descri_ASR'] ?? null,
                ]);
            }
        }

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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucune arme sélectionnée.');
        }

        $data = $request->validate([
            'fid_etoile' => ['nullable', 'exists:etoile,id_etoile'],
            'fid_TArmes' => ['nullable', 'exists:type_armes,id_TArmes'],
        ]);

        $data = array_filter($data, fn($v) => $v !== null);
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Arme::whereIn('id_arme', $ids)->update($data);

        return back()->with('success', count($ids) . ' arme(s) mise(s) à jour.');
    }
}
