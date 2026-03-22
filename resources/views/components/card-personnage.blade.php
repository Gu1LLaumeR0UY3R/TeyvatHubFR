@props(['personnage'])

@php
    $stars   = $personnage->etoile  ? preg_replace('/[^0-9]/', '', $personnage->etoile->libelle)          : '3';
    $element = strtolower($personnage->element?->libelle_element ?? 'anemo');
@endphp

<a href="{{ route('personnages.show', $personnage->slug) }}"
   class="group block card-entity rarity-{{ $stars }} element-{{ $element }} bg-hub-surface rounded-xl overflow-hidden">
    <div class="aspect-square overflow-hidden bg-hub-surface-hover">
        <img src="{{ $personnage->icone_url }}"
             alt="{{ $personnage->nom_perso }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
             loading="lazy">
    </div>
    <div class="p-3">
        <p class="font-semibold text-hub-text text-sm truncate">{{ $personnage->nom_perso }}</p>
        @if($personnage->element)
            <p class="text-xs mt-1" style="color: var(--element-color, #888)">{{ $personnage->element->libelle_element }}</p>
        @endif
        <p class="text-hub-gold text-xs">{{ $personnage->etoile?->libelle ?? '' }}</p>
    </div>
</a>
