@props(['arme'])

@php
    $stars = $arme->etoile ? preg_replace('/[^0-9]/', '', $arme->etoile->libelle) : '3';
@endphp

<a href="{{ route('armes.show', $arme->slug) }}"
   class="group block card-entity rarity-{{ $stars }} bg-hub-surface rounded-xl overflow-hidden">
    <div class="aspect-square overflow-hidden bg-hub-surface-hover p-3">
        <img src="{{ $arme->icone_url }}"
             alt="{{ $arme->nom_arme }}"
             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200"
             loading="lazy">
    </div>
    <div class="p-3">
        <p class="font-semibold text-hub-text text-sm truncate">{{ $arme->nom_arme }}</p>
        <p class="text-hub-text-sec text-xs mt-1">{{ $arme->typeArme?->libelle_TArme ?? '' }}</p>
        <p class="text-hub-gold text-xs">{{ $arme->etoile?->libelle ?? '' }}</p>
    </div>
</a>
