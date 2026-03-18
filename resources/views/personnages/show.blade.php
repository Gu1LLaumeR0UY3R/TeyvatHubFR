<x-app-layout>
    <x-slot name="title">{{ $personnage->nom_perso }}</x-slot>
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Bouton retour --}}
    <a href="{{ route('personnages.index') }}"
       class="inline-flex items-center gap-2 text-hub-text-sec hover:text-hub-text mb-6 transition-colors">
        ← Retour aux personnages
    </a>

    {{-- ===== BLC 1 : EN-TÊTE ===== --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 flex flex-col sm:flex-row gap-6 mb-6">
        <img src="{{ $personnage->photos->first()?->source_url ?? $personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
             alt="{{ $personnage->nom_perso }}"
             class="w-40 h-40 object-cover rounded-xl self-start">
        <div class="flex-1">
            <h1 class="text-3xl font-bold text-hub-primary mb-2">{{ $personnage->nom_perso }}</h1>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                    {{ $personnage->element?->libelle_element ?? 'Sans élément' }}
                </span>
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-gold">
                    {{ $personnage->etoile?->libelle ?? '—' }}
                </span>
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                    {{ $personnage->typePerso?->libelle_TP ?? '—' }}
                </span>
                <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                    {{ $personnage->typeArme?->libelle_TArme ?? '—' }}
                </span>
                @if($personnage->affinite_perso)
                    <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-hub-text-sec">
                        Affinité : {{ $personnage->affinite_perso }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== BLC 2 : BIO ===== --}}
    @if($personnage->bio)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-3">{{ $personnage->bio->titre_bio }}</h2>
            <p class="text-hub-text-sec leading-relaxed">{{ $personnage->bio->descri_bio }}</p>
        </div>
    @endif

    {{-- ===== BLC 3 : COMPÉTENCES ===== --}}
    @if($personnage->aptitudes->isNotEmpty())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Compétences</h2>
            @foreach($personnage->aptitudes->groupBy(fn($a) => $a->typeApti?->libelle_Apti ?? 'Autre') as $type => $aptitudes)
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-hub-primary uppercase tracking-wider mb-2">{{ $type }}</h3>
                    <div class="space-y-3">
                        @foreach($aptitudes as $apt)
                            <div class="bg-hub-surface-hover rounded-xl p-4">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="font-medium text-hub-text">{{ $apt->titre_apti }}</span>
                                    @if($apt->lvl_apt)
                                        <span class="text-xs text-hub-text-sec shrink-0">Niv. {{ $apt->lvl_apt }}</span>
                                    @endif
                                </div>
                                <p class="text-hub-text-sec text-sm mt-1">{{ $apt->descri_apti }}</p>
                                @if($apt->sub_Apt)
                                    <p class="text-hub-text-sec text-xs mt-2 italic">{{ $apt->sub_Apt }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ===== BLC 4 : CONSTELLATIONS ===== --}}
    @if($personnage->constellations->isNotEmpty())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Constellations</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($personnage->constellations as $i => $const)
                    <div class="bg-hub-surface-hover rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-xs font-bold text-hub-primary">C{{ $i + 1 }}</span>
                            <span class="font-medium text-hub-text">{{ $const->titre_const }}</span>
                        </div>
                        <p class="text-hub-text-sec text-sm">{{ $const->descri_const }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== BLC 5 : SPÉCIALITÉ CULINAIRE ===== --}}
    @if($personnage->specialite)
        @php $specialite = $personnage->specialite; $plat = $specialite->plat; @endphp
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-semibold text-hub-text mb-4">Spécialité culinaire</h2>
            <div class="flex flex-col sm:flex-row gap-4 items-start">

                {{-- Image cliquable → modal --}}
                <div x-data="{ open: false }">
                    <button @click="open = true" class="focus:outline-none">
                        <img src="{{ $plat?->photos->first()?->source_url ?? $plat?->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $plat?->nom_plat }}"
                             class="w-24 h-24 rounded-xl object-cover cursor-pointer hover:ring-2 hover:ring-hub-primary transition-all">
                    </button>

                    {{-- Modal --}}
                    <div x-show="open"
                         x-transition
                         @click.self="open = false"
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
                        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 max-w-md w-full relative">
                            <button @click="open = false"
                                    class="absolute top-3 right-3 text-hub-text-sec hover:text-hub-text text-xl leading-none">✕</button>
                            <img src="{{ $plat?->photos->first()?->source_url ?? $plat?->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $plat?->nom_plat }}"
                                 class="w-32 h-32 rounded-xl object-cover mx-auto mb-4">
                            <h3 class="text-lg font-bold text-hub-primary text-center mb-1">{{ $specialite->libelle_spe }}</h3>
                            @if($specialite->descri_spe)
                                <p class="text-hub-text-sec text-sm text-center mb-2">{{ $specialite->descri_spe }}</p>
                            @endif
                            <h4 class="font-semibold text-hub-text text-center mb-1">{{ $plat?->nom_plat }}</h4>
                            @if($plat?->descri_plat)
                                <p class="text-hub-text-sec text-sm text-center mb-4">{{ $plat->descri_plat }}</p>
                            @endif
                            @if($plat)
                                <div class="text-center">
                                    <a href="{{ route('cuisine.show', $plat->slug) }}"
                                       class="inline-block px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg text-sm font-medium transition-colors">
                                        Voir le plat
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex-1">
                    <h3 class="font-semibold text-hub-text text-lg">{{ $specialite->libelle_spe }}</h3>
                    @if($specialite->descri_spe)
                        <p class="text-hub-text-sec text-sm mt-1">{{ $specialite->descri_spe }}</p>
                    @endif
                    @if($plat)
                        <p class="text-hub-text-sec text-sm mt-2">Plat : <span class="text-hub-text">{{ $plat->nom_plat }}</span></p>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>
</x-app-layout>
