<x-app-layout>
    <x-slot name="title">{{ $materiaux->nom_mat }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('materiaux.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour aux matériaux
        </a>

        {{-- En-tête --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $materiaux->photos->first()?->source_url ?? $materiaux->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $materiaux->nom_mat }}"
                 class="w-32 h-32 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $materiaux->nom_mat }}</h1>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-gold">
                        {{ $materiaux->rarete?->{'libelle_rareté'} ?? '—' }}
                    </span>
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                        {{ $materiaux->typeMateriaux?->libelle_TypeM ?? '—' }}
                    </span>
                </div>
                @if($materiaux->descri_mat)
                    <p class="text-hub-text-sec mt-3 leading-relaxed">{{ $materiaux->descri_mat }}</p>
                @endif
            </div>
        </div>

        {{-- Ennemis sources --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Ennemis sources</h2>
            @if($materiaux->ennemis->isEmpty())
                <p class="text-hub-text-sec">Aucun ennemi source connu.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($materiaux->ennemis as $ennemi)
                        <a href="{{ route('ennemis.show', $ennemi->slug) }}"
                           class="bg-hub-surface-hover rounded-xl p-3 hover:ring-1 hover:ring-hub-primary transition-all text-center">
                            <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $ennemi->nom_ennemi }}"
                                 class="w-16 h-16 object-cover rounded-lg mx-auto mb-2">
                            <p class="text-hub-text text-sm font-medium truncate">{{ $ennemi->nom_ennemi }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
