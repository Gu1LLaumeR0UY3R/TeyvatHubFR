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
        $sort = request('sort', 'nom_asc');

        $utilisateursQuery = User::query();

        switch ($sort) {
            case 'nom_desc':
                $utilisateursQuery->orderByDesc('name');
                break;
            case 'email_asc':
                $utilisateursQuery->orderBy('email');
                break;
            case 'email_desc':
                $utilisateursQuery->orderByDesc('email');
                break;
            case 'status_asc':
                $utilisateursQuery->orderBy('banni_le');
                break;
            case 'status_desc':
                $utilisateursQuery->orderByDesc('banni_le');
                break;
            case 'nom_asc':
            default:
                $utilisateursQuery->orderBy('name');
                break;
        }

        $utilisateurs = $utilisateursQuery->paginate(20)->withQueryString();
        return view('admin.utilisateurs.index', compact('utilisateurs', 'sort'));
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

    public function bulkUpdate(Request $request): RedirectResponse
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Aucun utilisateur sélectionné.');
        }

        $data = $request->validate([
            'action' => ['required', 'in:bannir,debannir,supprimer'],
        ]);

        if ($data['action'] === 'supprimer') {
            User::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' utilisateur(s) supprimé(s).');
        }

        if ($data['action'] === 'bannir') {
            User::whereIn('id', $ids)->update(['banni_le' => now()]);
            return back()->with('success', count($ids) . ' utilisateur(s) banni(s).');
        }

        User::whereIn('id', $ids)->update(['banni_le' => null]);
        return back()->with('success', count($ids) . ' utilisateur(s) débanni(s).');
    }
}
