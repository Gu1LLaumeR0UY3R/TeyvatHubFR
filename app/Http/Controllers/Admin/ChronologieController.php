<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chronologie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChronologieController extends Controller
{
    public function index(): View
    {
        $chronologies = Chronologie::orderBy('ordre')->paginate(20);
        return view('admin.chronologie.index', compact('chronologies'));
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
}
