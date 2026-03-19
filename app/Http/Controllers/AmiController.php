<?php

namespace App\Http\Controllers;

use App\Models\Amitie;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmiController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        // Amis acceptés (dans les deux sens)
        $amis = Amitie::with(['demandeur', 'receveur'])
            ->where(function ($q) use ($user) {
                $q->where('fid_demandeur', $user->id)
                  ->orWhere('fid_receveur', $user->id);
            })
            ->where('statut', 'accepte')
            ->get()
            ->map(function ($a) use ($user) {
                return $a->fid_demandeur === $user->id ? $a->receveur : $a->demandeur;
            });

        // Demandes reçues en attente
        $demandesRecues = Amitie::with('demandeur')
            ->where('fid_receveur', $user->id)
            ->where('statut', 'en_attente')
            ->get();

        // Demandes envoyées en attente
        $demandesEnvoyees = Amitie::with('receveur')
            ->where('fid_demandeur', $user->id)
            ->where('statut', 'en_attente')
            ->get();

        return view('profil.amis', compact('amis', 'demandesRecues', 'demandesEnvoyees'));
    }
}
