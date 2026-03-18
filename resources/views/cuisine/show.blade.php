<x-app-layout>
    <x-slot name="title">{{ $plat->nom_plat }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('cuisine.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour à la cuisine
        </a>

        {{-- En-tête --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $plat->photos->first()?->source_url ?? $plat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $plat->nom_plat }}"
                 class="w-36 h-36 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $plat->nom_plat }}</h1>
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-gold text-sm">
                    {{ $plat->rarete?->{'libelle_rareté'} ?? '—' }}
                </span>
                @if($plat->descri_plat)
                    <p class="text-hub-text-sec mt-3 leading-relaxed">{{ $plat->descri_plat }}</p>
                @endif
            </div>
        </div>

        {{-- Ingrédients --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Ingrédients</h2>
            @if($plat->ingredients->isEmpty())
                <p class="text-hub-text-sec">Aucun ingrédient connu.</p>
            @else
                <ul class="space-y-2">
                    @foreach($plat->ingredients as $ingredient)
                        <li class="flex items-center gap-3 text-hub-text">
                            <span class="w-8 h-8 bg-hub-surface-hover rounded-full flex items-center justify-center text-sm font-bold text-hub-primary">
                                {{ $ingredient->pivot->quantite }}
                            </span>
                            <span>{{ $ingredient->nom_ingre }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Spécialité --}}
        @if($plat->specialite)
            <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
                <h2 class="text-xl font-semibold text-hub-text mb-4">Spécialité</h2>
                @if($plat->specialite->personnage)
                    <div class="flex items-center gap-4">
                        <img src="{{ $plat->specialite->personnage->photos->first()?->source_url ?? $plat->specialite->personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $plat->specialite->personnage->nom_perso }}"
                             class="w-16 h-16 rounded-full object-cover">
                        <div>
                            <p class="font-semibold text-hub-text">{{ $plat->specialite->personnage->nom_perso }}</p>
                            @if($plat->specialite->libelle_spe)
                                <p class="text-sm text-hub-gold">{{ $plat->specialite->libelle_spe }}</p>
                            @endif
                        </div>
                    </div>
                    @if($plat->specialite->descri_spe)
                        <p class="text-hub-text-sec mt-3 leading-relaxed text-sm">{{ $plat->specialite->descri_spe }}</p>
                    @endif
                @endif
            </div>
        @endif

    </div>
</x-app-layout>
