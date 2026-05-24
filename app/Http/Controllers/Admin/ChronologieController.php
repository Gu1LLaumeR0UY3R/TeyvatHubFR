<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chronologie;
use App\Models\Nation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChronologieController extends Controller
{
    public function index(): View
    {
        $sort = request('sort', 'ordre_asc');

        $chronologiesQuery = Chronologie::query();

        switch ($sort) {
            case 'ordre_desc':
                $chronologiesQuery->orderByDesc('ordre');
                break;
            case 'titre_asc':
                $chronologiesQuery->orderBy('titre');
                break;
            case 'titre_desc':
                $chronologiesQuery->orderByDesc('titre');
                break;
            case 'ordre_asc':
            default:
                $chronologiesQuery->orderBy('ordre');
                break;
        }

        $chronologies = $chronologiesQuery->paginate(20)->withQueryString();
        $nations = Nation::orderBy('nom_region')->get();

        return view('admin.chronologie.index', compact('chronologies', 'nations', 'sort'));
    }

    public function create(): View
    {
        return view('admin.chronologie.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'titre'       => ['required', 'string', 'max:200'],
            'resume'      => ['nullable', 'string'],
            'periode'     => ['nullable', 'string', 'max:100'],
            'ordre'       => ['required', 'integer', 'min:1'],
            'fid_region'  => ['nullable', 'exists:région,id_region'],
        ]);

        Chronologie::create($data);

        return redirect()->route('admin.chronologie.index')
            ->with('success', 'Entrée de chronologie créée.');
    }

    public function show(Chronologie $chronologie): View
    {
        return view('admin.chronologie.show', compact('chronologie'));
    }

    public function edit(Chronologie $chronologie): View
    {
        return view('admin.chronologie.edit', compact('chronologie'));
    }

    public function update(Request $request, Chronologie $chronologie): RedirectResponse
    {
        $data = $request->validate([
            'titre'       => ['required', 'string', 'max:200'],
            'resume'      => ['nullable', 'string'],
            'periode'     => ['nullable', 'string', 'max:100'],
            'ordre'       => ['required', 'integer', 'min:1'],
            'fid_region'  => ['nullable', 'exists:région,id_region'],
        ]);

        $chronologie->update($data);

        return redirect()->route('admin.chronologie.index')
            ->with('success', 'Entrée de chronologie mise à jour.');
    }

    public function destroy(Chronologie $chronologie): RedirectResponse
    {
        $chronologie->delete();
        return redirect()->route('admin.chronologie.index')
            ->with('success', 'Entrée de chronologie supprimée.');
    }

    public function updateOrdre(Request $request, Chronologie $chronologie): RedirectResponse
    {
        $data = $request->validate([
            'ordre' => ['required', 'integer', 'min:1'],
        ]);

        $chronologie->update(['ordre' => $data['ordre']]);

        return redirect()->route('admin.chronologie.index')
            ->with('success', 'Ordre mis à jour.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucune entrée sélectionnée.');
        }

        $action = (string) $request->input('action', 'update');
        if ($action === 'delete') {
            Chronologie::whereIn('id_chrono', $ids)->delete();
            return back()->with('success', count($ids) . ' entrée(s) supprimée(s).');
        }

        $data = $request->validate([
            'periode' => ['nullable', 'string', 'max:100'],
            'fid_region' => ['nullable', 'exists:région,id_region'],
        ]);

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Chronologie::whereIn('id_chrono', $ids)->update($data);

        return back()->with('success', count($ids) . ' entrée(s) mise(s) à jour.');
    }
}
