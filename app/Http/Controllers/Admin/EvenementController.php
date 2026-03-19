<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvenementController extends Controller
{
    public function index(): View
    {
        return view('admin.evenements.index');
    }

    public function create(): View
    {
        return view('admin.evenements.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('admin.evenements.index')
            ->with('info', 'Fonctionnalité à implémenter.');
    }

    public function show(string $id): View
    {
        return view('admin.evenements.show');
    }

    public function edit(string $id): View
    {
        return view('admin.evenements.edit');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('admin.evenements.index')
            ->with('info', 'Fonctionnalité à implémenter.');
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('admin.evenements.index')
            ->with('info', 'Fonctionnalité à implémenter.');
    }
}
