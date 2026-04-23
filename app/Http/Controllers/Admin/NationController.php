<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Nation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NationController extends Controller
{
    private function indexRouteName(): string
    {
        return request()->routeIs('admin.regions.*') ? 'admin.regions.index' : 'admin.nations.index';
    }

    public function index(): View
    {
        $sort = request('sort', 'nom_asc');

        $nationsQuery = Nation::query()->with(['photos']);

        switch ($sort) {
            case 'nom_desc':
                $nationsQuery->orderByDesc('nom_region');
                break;
            case 'nom_asc':
            default:
                $nationsQuery->orderBy('nom_region');
                break;
        }

        $nations = $nationsQuery->paginate(20)->withQueryString();
        return view('admin.nations.index', compact('nations', 'sort'));
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
            $nation->photos()->create(['chemin_photo' => $path, 'source_url' => null]);
        }

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Nation créée avec succès.');
    }

    public function show(Nation $nation): View
    {
        $nation->load(['photos', 'sousRegions', 'ennemis', 'animaux']);
        return view('admin.nations.show', compact('nation'));
    }

    public function edit(Nation $nation): View
    {
        return view('admin.nations.edit', ['region' => $nation]);
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

        return redirect()->route($this->indexRouteName())
            ->with('success', 'Nation mise à jour.');
    }

    public function destroy(Nation $nation): RedirectResponse
    {
        $nation->delete();
        return redirect()->route($this->indexRouteName())
            ->with('success', 'Région supprimée.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucune nation sélectionnée.');
        }

        $data = $request->validate([
            'descri_region' => ['nullable', 'string'],
        ]);

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Nation::whereIn('id_region', $ids)->update($data);

        return back()->with('success', count($ids) . ' nation(s) mise(s) à jour.');
    }
}
