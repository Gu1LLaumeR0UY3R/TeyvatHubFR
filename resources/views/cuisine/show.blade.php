<x-app-layout>
<x-slot name="title">{{ $plat->nom_plat }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('cuisine.index') }}" class="hover:text-hub-primary">Cuisine</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $plat->nom_plat }}</span>
    </nav>

    {{-- Bloc 1 : Header --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $plat->photos->first()?->source_url ?? $plat->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                     alt="{{ $plat->nom_plat }}"
                     class="w-40 h-40 rounded-xl object-contain border-2 border-hub-border p-2 bg-hub-surface-hover">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $plat->nom_plat }}</h1>
                @if($plat->rarete)
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-gold">
                        {{ $plat->rarete->{'libelle_rareté'} }}
                    </span>
                @endif
                @if($plat->descri_plat)
                    <p class="text-hub-text-sec leading-relaxed mt-4">{{ $plat->descri_plat }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Ingrédients --}}
    @if($plat->ingredients->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Ingrédients</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($plat->ingredients as $ingredient)
                    <a href="{{ route('ingredients.show', $ingredient->slug) }}"
                       class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $ingredient->photos->first()?->source_url ?? $ingredient->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                             alt="{{ $ingredient->nom_ingre }}"
                             class="w-12 h-12 object-contain">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $ingredient->nom_ingre }}</span>
                        <span class="text-hub-text-sec text-xs">x{{ $ingredient->pivot->quantite }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 3 : Spécialité --}}
    @if($plat->specialite)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Spécialité de personnage</h2>
            <div class="flex items-center gap-4">
                @if($plat->specialite->personnage)
                    <a href="{{ route('personnages.show', $plat->specialite->personnage->slug) }}"
                       class="flex items-center gap-4 hover:opacity-80 transition-opacity">
                        <img src="{{ $plat->specialite->personnage->photos->first()?->source_url ?? $plat->specialite->personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                             alt="{{ $plat->specialite->personnage->nom_perso }}"
                             class="w-16 h-16 rounded-xl object-cover border border-hub-border">
                        <div>
                            <p class="font-semibold text-hub-text">{{ $plat->specialite->libelle_spe }}</p>
                            <p class="text-hub-text-sec text-sm">par {{ $plat->specialite->personnage->nom_perso }}</p>
                            @if($plat->specialite->descri_spe)
                                <p class="text-hub-text-sec text-sm mt-1">{{ $plat->specialite->descri_spe }}</p>
                            @endif
                        </div>
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
</x-app-layout>
