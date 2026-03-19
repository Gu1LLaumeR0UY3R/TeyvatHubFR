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
        $roles = Role::withCount('personnages')->orderBy('nom_role')->paginate(20);
        return view('admin.roles.index', compact('roles'));
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
}
