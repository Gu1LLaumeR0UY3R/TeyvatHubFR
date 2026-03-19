<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UtilisateurController extends Controller
{
    public function index(): View
    {
        $utilisateurs = User::orderBy('name')->paginate(20);
        return view('admin.utilisateurs.index', compact('utilisateurs'));
    }

    public function create(): View
    {
        return view('admin.utilisateurs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make($data['password']),
        ]);

        return redirect()->route('admin.utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $utilisateur): View
    {
        return view('admin.utilisateurs.show', compact('utilisateur'));
    }

    public function edit(User $utilisateur): View
    {
        return view('admin.utilisateurs.edit', compact('utilisateur'));
    }

    public function update(Request $request, User $utilisateur): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $utilisateur->id],
        ]);

        $utilisateur->update($data);

        return redirect()->route('admin.utilisateurs.index')
            ->with('success', 'Utilisateur mis à jour.');
    }

    public function destroy(User $utilisateur): RedirectResponse
    {
        $utilisateur->delete();
        return redirect()->route('admin.utilisateurs.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function bannir(User $utilisateur): RedirectResponse
    {
        $utilisateur->update(['banni_le' => now()]);
        return redirect()->route('admin.utilisateurs.index')
            ->with('success', 'Utilisateur banni.');
    }

    public function debannir(User $utilisateur): RedirectResponse
    {
        $utilisateur->update(['banni_le' => null]);
        return redirect()->route('admin.utilisateurs.index')
            ->with('success', 'Ban levé.');
    }
}
