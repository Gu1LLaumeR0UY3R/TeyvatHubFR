<x-app-layout>
<x-slot name="title">{{ $materiaux->nom_mat }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('materiaux.index') }}" class="hover:text-hub-primary">Matériaux</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $materiaux->nom_mat }}</span>
    </nav>

    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $materiaux->photos->first()?->source_url ?? $materiaux->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                     alt="{{ $materiaux->nom_mat }}"
                     class="w-32 h-32 rounded-xl object-contain border-2 border-hub-border p-2 bg-hub-surface-hover">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $materiaux->nom_mat }}</h1>
                <div class="flex flex-wrap gap-3 mb-4">
                    @if($materiaux->typeMateriaux)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text">
                            {{ $materiaux->typeMateriaux->libelle_TypeM }}
                        </span>
                    @endif
                    @if($materiaux->rarete)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-gold">
                            {{ $materiaux->rarete->libelle_rareté }}
                        </span>
                    @endif
                </div>
                @if($materiaux->descri_mat)
                    <p class="text-hub-text-sec leading-relaxed">{{ $materiaux->descri_mat }}</p>
                @endif
            </div>
        </div>
    </div>

    @if($materiaux->ennemis->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Obtenu depuis</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($materiaux->ennemis as $ennemi)
                    <a href="{{ route('ennemis.show', $ennemi->slug) }}"
                       class="flex items-center gap-3 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $ennemi->nom_ennemi }}"
                             class="w-10 h-10 rounded object-cover flex-shrink-0">
                        <span class="text-hub-text text-sm font-medium">{{ $ennemi->nom_ennemi }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
</x-app-layout>
