<x-app-layout>
    <x-slot name="title">{{ $ennemi->nom_ennemi }}</x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <a href="{{ route('ennemis.index') }}"
           class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
            ← Retour aux ennemis
        </a>

        {{-- BLC 1 : EN-TÊTE --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
            <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                 alt="{{ $ennemi->nom_ennemi }}"
                 class="w-40 h-40 object-cover rounded-xl self-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $ennemi->nom_ennemi }}</h1>
                <div class="flex flex-wrap gap-3 text-sm">
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                        {{ $ennemi->typeEnnemi?->libelle_Type ?? '—' }}
                    </span>
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                        {{ $ennemi->element?->libelle_element ?? 'Neutre' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- BLC 2 : DESCRIPTION --}}
        @if($ennemi->descri_enn)
            <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
                <h2 class="text-xl font-semibold text-hub-text mb-3">Description</h2>
                <p class="text-hub-text-sec leading-relaxed">{{ $ennemi->descri_enn }}</p>
            </div>
        @endif

        {{-- BLC 3 : RÉGIONS --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Régions</h2>
            @if($ennemi->regions->isEmpty())
                <p class="text-hub-text-sec">Aucune région connue.</p>
            @else
                <div class="flex flex-wrap gap-3">
                    @foreach($ennemi->regions as $region)
                        <a href="{{ route('regions.show', $region->slug) }}"
                           class="flex items-center gap-2 bg-hub-surface-hover hover:bg-hub-border rounded-xl px-4 py-2 transition-colors">
                            <img src="{{ $region->photos->first()?->source_url ?? $region->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $region->nom_region }}" class="w-8 h-8 rounded-full object-cover">
                            <span class="text-hub-text text-sm font-medium">{{ $region->nom_region }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- BLC 4 : MATÉRIAUX DROPTÉS --}}
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Matériaux droptés</h2>
            @if($ennemi->materiaux->isEmpty())
                <p class="text-hub-text-sec">Aucun matériau connu.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    @foreach($ennemi->materiaux as $mat)
                        <a href="{{ route('materiaux.show', $mat->slug) }}"
                           class="bg-hub-surface-hover rounded-xl p-3 hover:ring-1 hover:ring-hub-primary transition-all">
                            <img src="{{ $mat->photos->first()?->source_url ?? $mat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $mat->nom_mat }}"
                                 class="w-16 h-16 object-cover rounded-lg mx-auto mb-2">
                            <h3 class="text-hub-text text-sm font-medium text-center truncate">{{ $mat->nom_mat }}</h3>
                            <p class="text-hub-gold text-xs text-center">{{ $mat->rarete?->{'libelle_rareté'} ?? '—' }}</p>
                            <p class="text-hub-muted text-xs text-center">{{ $mat->typeMateriaux?->libelle_TypeM ?? '—' }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
