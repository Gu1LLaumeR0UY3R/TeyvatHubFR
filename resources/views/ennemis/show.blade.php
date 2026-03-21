<x-app-layout>
<x-slot name="title">{{ $ennemi->nom_ennemi }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('ennemis.index') }}" class="hover:text-hub-primary">Ennemis</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $ennemi->nom_ennemi }}</span>
    </nav>

    {{-- Bloc 1 : Header --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $ennemi->photos->first()?->source_url ?? $ennemi->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                     alt="{{ $ennemi->nom_ennemi }}"
                     class="w-40 h-40 rounded-xl object-cover border-2 border-hub-border">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $ennemi->nom_ennemi }}</h1>
                <div class="flex flex-wrap gap-3">
                    @if($ennemi->typeEnnemi)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text">
                            {{ $ennemi->typeEnnemi->libelle_Type }}
                        </span>
                    @endif
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text-sec">
                        {{ $ennemi->element?->libelle_element ?? 'Neutre' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Description --}}
    @if($ennemi->descri_enn)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-3">Description</h2>
            <p class="text-hub-text-sec leading-relaxed">{{ $ennemi->descri_enn }}</p>
        </div>
    @endif

    {{-- Bloc 3 : Nations --}}
    @if($ennemi->nations->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Nations</h2>
            <div class="flex flex-wrap gap-3">
                @foreach($ennemi->nations as $nation)
                    <a href="{{ route('nations.show', $nation->slug) }}"
                       class="flex items-center gap-2 px-4 py-2 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $nation->photos->first()?->source_url ?? $nation->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $nation->nom_region }}"
                             class="w-8 h-8 rounded object-cover">
                        <span class="text-hub-text text-sm font-medium">{{ $nation->nom_region }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 4 : Matériaux droppés --}}
    @if($ennemi->materiaux->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Matériaux droppés</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                @foreach($ennemi->materiaux as $mat)
                    <a href="{{ route('materiaux.show', $mat->slug) }}"
                       class="group flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $mat->photos->first()?->source_url ?? $mat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $mat->nom_mat }}"
                             class="w-12 h-12 object-cover rounded">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $mat->nom_mat }}</span>
                        @if($mat->rarete)
                            <span class="text-hub-gold text-xs">{{ $mat->rarete->libelle_rareté }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
</x-app-layout>
