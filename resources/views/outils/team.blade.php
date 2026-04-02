<x-app-layout>
<x-slot name="title">Générateur de Team</x-slot>
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <h1 class="text-3xl font-bold text-hub-text mb-2">Générateur de Team</h1>
    <p class="text-hub-text-sec mb-8">Générez une équipe aléatoire de 4 personnages.</p>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('outils.team.generer') }}" class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-8">
        @csrf
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-hub-text-sec text-sm mb-1">Personnage fixe (optionnel)</label>
                <select name="perso_fixe"
                        class="w-full bg-hub-surface-hover border border-hub-border rounded-xl px-4 py-2 text-hub-text focus:outline-none focus:border-hub-primary">
                    <option value="">Aléatoire</option>
                    @foreach($personnages as $p)
                        <option value="{{ $p->id_perso }}" {{ (isset($team) && $team->firstWhere('id_perso', $p->id_perso) && $team->first()->id_perso == $p->id_perso) ? 'selected' : '' }}>
                            {{ $p->nom_perso }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="px-6 py-2 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium">
                Générer
            </button>
        </div>
    </form>

    {{-- Team générée --}}
    @isset($team)
        <div class="mb-8">
            <h2 class="text-xl font-bold text-hub-text mb-4">Votre équipe</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($team as $perso)
                    <a href="{{ route('personnages.show', $perso->slug) }}"
                       class="bg-hub-surface border border-hub-border rounded-2xl p-4 hover:border-hub-primary transition-all flex flex-col items-center gap-2">
                        <img src="{{ $perso->photos->first()?->source_url ?? $perso->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                             alt="{{ $perso->nom_perso }}"
                             class="w-24 h-24 rounded-xl object-contain">
                        <span class="text-hub-text font-medium text-sm text-center">{{ $perso->nom_perso }}</span>
                        @if($perso->element)
                            <span class="px-2 py-0.5 bg-hub-surface-hover rounded text-xs text-hub-text-sec">{{ $perso->element->libelle_element }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endisset

</div>
</x-app-layout>
