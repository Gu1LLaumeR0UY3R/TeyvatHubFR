<x-app-layout>
<x-slot name="title">Personnage du jour</x-slot>
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12 text-center">

    <h1 class="text-3xl font-bold text-hub-text mb-2">Personnage du jour</h1>
    <p class="text-hub-text-sec mb-8">Un nouveau personnage est mis en avant chaque jour.</p>

    @if($personnage)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-8 inline-flex flex-col items-center gap-4">
            <img src="{{ $personnage->photos->first()?->source_url ?? $personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $personnage->nom_perso }}"
                 class="w-48 h-48 rounded-2xl object-contain">
            <h2 class="text-2xl font-bold text-hub-text">{{ $personnage->nom_perso }}</h2>
            @if($personnage->element)
                <span class="px-3 py-1 bg-hub-surface-hover border border-hub-border rounded-full text-hub-text text-sm">
                    {{ $personnage->element->libelle_element }}
                </span>
            @endif
            @if($personnage->etoile)
                <span class="text-hub-gold">{{ $personnage->etoile->libelle }}</span>
            @endif
            <a href="{{ route('personnages.show', $personnage->slug) }}"
               class="px-5 py-2 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 transition-colors font-medium">
                Voir la fiche
            </a>
        </div>
    @else
        <p class="text-hub-text-sec">Aucun personnage disponible pour l'instant.</p>
    @endif

</div>
</x-app-layout>
