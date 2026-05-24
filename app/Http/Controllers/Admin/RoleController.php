<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $sort = request('sort', 'libelle_asc');

        $rolesQuery = Role::query()->withCount('personnages');

        switch ($sort) {
            case 'libelle_desc':
                $rolesQuery->orderByDesc('libelle_role');
                break;
            case 'count_asc':
                $rolesQuery->orderBy('personnages_count');
                break;
            case 'count_desc':
                $rolesQuery->orderByDesc('personnages_count');
                break;
            case 'libelle_asc':
            default:
                $rolesQuery->orderBy('libelle_role');
                break;
        }

        $roles = $rolesQuery->paginate(20)->withQueryString();
        return view('admin.roles.index', compact('roles', 'sort'));
    }

    public function create(): View
    {
        return view('admin.roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'libelle_role' => ['required', 'string', 'max:100'],
            'descri_role'  => ['nullable', 'string'],
        ]);

        Role::create($data);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle créé avec succès.');
    }

    public function show(Role $role): View
    {
        $role->load('personnages.photos');
        return view('admin.roles.show', compact('role'));
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'libelle_role' => ['required', 'string', 'max:100'],
            'descri_role'  => ['nullable', 'string'],
        ]);

        $role->update($data);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle mis à jour.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', 'Rôle supprimé.');
    }

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucun rôle sélectionné.');
        }

        $action = (string) $request->input('action', 'update');
        if ($action === 'delete') {
            Role::whereIn('id_role', $ids)->delete();
            return back()->with('success', count($ids) . ' rôle(s) supprimé(s).');
        }

        $data = $request->validate([
            'descri_role' => ['nullable', 'string'],
        ]);

        $data = array_filter($data, fn($v) => $v !== null && $v !== '');
        if (empty($data)) {
            return back()->with('error', 'Aucune modification à appliquer.');
        }

        Role::whereIn('id_role', $ids)->update($data);

        return back()->with('success', count($ids) . ' rôle(s) mis à jour.');
    }
}
