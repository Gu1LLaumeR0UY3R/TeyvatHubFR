<x-app-layout>
<x-slot name="title">{{ $nation->nom_region }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('histoire.index') }}" class="hover:text-hub-primary">Histoire</a>
        <span class="mx-2">/</span>
        <a href="{{ route('nations.index') }}" class="hover:text-hub-primary">Nations</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $nation->nom_region }}</span>
    </nav>

    {{-- Bloc 1 : Header --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $nation->photos->first()?->source_url ?? $nation->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                     alt="{{ $nation->nom_region }}"
                     class="w-40 h-40 rounded-xl object-contain">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-3">{{ $nation->nom_region }}</h1>
                @if($nation->descri_region)
                    <p class="text-hub-text-sec leading-relaxed">{{ $nation->descri_region }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Sous-régions --}}
    @if($nation->sousRegions->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Zones</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($nation->sousRegions as $sr)
                    <div class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border">
                        @if($sr->photos->first())
                            <img src="{{ $sr->photos->first()->source_url ?? $sr->photos->first()->chemin_photo }}"
                                 alt="{{ $sr->nom_sousregion }}"
                                 class="w-16 h-16 object-contain">
                        @endif
                        <span class="text-hub-text text-xs text-center font-medium">{{ $sr->nom_sousregion }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 3 : Ennemis --}}
    @if($nation->ennemis->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Ennemis</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($nation->ennemis as $ennemi)
                    <a href="{{ route('ennemis.show', $ennemi->slug) }}"
                       class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $ennemi->nom_ennemi }}"
                             class="w-16 h-16 object-contain">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $ennemi->nom_ennemi }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 4 : Animaux --}}
    @if($nation->animaux->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Faune</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($nation->animaux as $animal)
                    <a href="{{ route('animaux.show', $animal->slug) }}"
                       class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $animal->photos->first()?->source_url ?? $animal->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $animal->nom_animal }}"
                             class="w-16 h-16 object-contain">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $animal->nom_animal }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 5 : Produits locaux --}}
    @if($nation->produits->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Spécialités locales</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($nation->produits as $produit)
                    <span class="px-3 py-1.5 bg-hub-surface-hover border border-hub-border rounded-lg text-hub-text text-sm">
                        {{ $produit->nom_produit }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

</div>
</x-app-layout>
