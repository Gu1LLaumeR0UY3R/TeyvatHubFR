<x-app-layout>
<x-slot name="title">{{ $personnage->nom_perso }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Breadcrumb --}}
    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('personnages.index') }}" class="hover:text-hub-primary">Personnages</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $personnage->nom_perso }}</span>
    </nav>

    {{-- Bloc 1 : Header personnage --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6">
            <div class="flex-shrink-0">
                <img src="{{ $personnage->photos->first()?->source_url ?? $personnage->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                     alt="{{ $personnage->nom_perso }}"
                     class="w-40 h-40 rounded-xl object-cover border-2 border-hub-border">
            </div>
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $personnage->nom_perso }}</h1>
                <div class="flex flex-wrap gap-3 mb-4">
                    @if($personnage->element)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text">
                            {{ $personnage->element->libelle_element }}
                        </span>
                    @endif
                    @if($personnage->etoile)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-gold">
                            {{ $personnage->etoile->libelle }}
                        </span>
                    @endif
                    @if($personnage->typeArme)
                        <span class="px-3 py-1 bg-hub-surface-hover rounded-full text-sm text-hub-text-sec">
                            {{ $personnage->typeArme->libelle_TArme }}
                        </span>
                    @endif
                </div>
                @if($personnage->roles->count())
                    <div class="flex flex-wrap gap-2">
                        @foreach($personnage->roles as $role)
                            <span class="px-2 py-1 bg-hub-primary/20 text-hub-primary text-xs rounded-md">
                                {{ $role->libelle_role }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Bio --}}
    @if($personnage->bio)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-3">{{ $personnage->bio->titre_bio }}</h2>
            <p class="text-hub-text-sec leading-relaxed">{{ $personnage->bio->descri_bio }}</p>
        </div>
    @endif

    {{-- Bloc 3 : Aptitudes --}}
    @if($personnage->aptitudes->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Aptitudes</h2>
            <div class="space-y-4">
                @foreach($personnage->aptitudes as $aptitude)
                    <div class="border border-hub-border rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            @if($aptitude->typeApti)
                                <span class="text-xs text-hub-text-sec bg-hub-surface-hover px-2 py-0.5 rounded">
                                    {{ $aptitude->typeApti->libelle_Apti }}
                                </span>
                            @endif
                            <h3 class="font-semibold text-hub-text">{{ $aptitude->titre_apti }}</h3>
                        </div>
                        <p class="text-hub-text-sec text-sm">{{ $aptitude->descri_apti }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 4 : Constellations --}}
    @if($personnage->constellations->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Constellations</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach($personnage->constellations as $i => $constellation)
                    <div class="border border-hub-border rounded-xl p-4">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-6 h-6 flex items-center justify-center rounded-full bg-hub-primary text-white text-xs font-bold">
                                C{{ $i + 1 }}
                            </span>
                            <h3 class="font-semibold text-hub-text">{{ $constellation->titre_const }}</h3>
                        </div>
                        <p class="text-hub-text-sec text-sm">{{ $constellation->descri_const }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 5 : Armes recommandées --}}
    @if($personnage->armesRecommandees->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Armes recommandées</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach($personnage->armesRecommandees as $armeRec)
                    @php
                        $arme = $armeRec->arme;
                        $rarity = (int) optional($arme)->fid_etoile ?: 1;
                        $rarityClass = match($rarity) {
                            1 => 'bg-slate-600',
                            2 => 'bg-emerald-500',
                            3 => 'bg-cyan-500',
                            4 => 'bg-violet-500',
                            5 => 'bg-yellow-500',
                            default => 'bg-slate-600',
                        };
                        $origineIcon = match($armeRec->origine) {
                            'tirage' => '🎯',
                            'evenement' => '🧭',
                            'creation' => '🛠️',
                            'achat' => '🏪',
                            default => '❔',
                        };
                    @endphp
                    <a href="{{ $arme ? route('armes.show', $arme->slug) : '#' }}" class="block border border-hub-border rounded-xl p-4 transition hover:shadow-lg bg-hub-surface-hover">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <div class="w-11 h-11 rounded-lg overflow-hidden bg-slate-800 flex items-center justify-center">
                                    <img src="{{ optional($arme)->photos->first()?->source_url ?? optional($arme)->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}" alt="{{ optional($arme)->nom_arme ?? 'arme' }}" class="w-full h-full object-cover" />
                                </div>
                                <div>
                                    <h3 class="font-semibold text-hub-text">{{ optional($arme)->nom_arme ?? 'N/A' }}</h3>
                                    <p class="text-xs text-hub-text-sec">{{ optional($arme)->typeArme?->libelle_TArme ?? 'Type inconnu' }}</p>
                                </div>
                            </div>
                            <span class="text-xs px-2 py-1 font-bold text-white rounded {{ $rarityClass }}">{{ $rarity }}★</span>
                        </div>
                        <div class="mt-3 flex items-center justify-between text-sm">
                            <span class="inline-flex items-center gap-1 text-hub-text-sec">{{ $origineIcon }} {{ ucfirst($armeRec->origine ?? 'tirage') }}</span>
                            @if($armeRec->starter)
                                <span class="inline-flex items-center gap-1 text-green-300">🌱 Starter</span>
                            @endif
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs text-hub-text-sec">Position {{ $armeRec->position }}</span>
                            <span class="text-xs text-hub-text-sec">Source : {{ $armeRec->origine ?? 'tirage' }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 6 : Spécialité culinaire --}}
    @if($personnage->specialite && $personnage->specialite->plat)
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6" x-data="{ open: false }">
            <h2 class="text-xl font-bold text-hub-text mb-4">Spécialité culinaire</h2>
            <div class="flex items-center gap-4">
                <img src="{{ $personnage->specialite->plat->photos->first()?->source_url ?? $personnage->specialite->plat->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                     alt="{{ $personnage->specialite->plat->nom_plat }}"
                     class="w-16 h-16 rounded-lg object-cover border border-hub-border">
                <div>
                    <a href="{{ route('cuisine.show', $personnage->specialite->plat->slug) }}"
                       class="font-semibold text-hub-primary hover:underline">
                        {{ $personnage->specialite->libelle_spe }}
                    </a>
                    <p class="text-hub-text-sec text-sm mt-1">{{ $personnage->specialite->descri_spe }}</p>
                </div>
                <button @click="open = !open"
                        class="ml-auto px-4 py-2 border border-hub-border rounded-lg text-sm text-hub-text-sec hover:text-hub-text transition-colors">
                    Voir plat original
                </button>
            </div>
            <div x-show="open" x-transition class="mt-4 p-4 bg-hub-surface-hover rounded-xl">
                <p class="text-hub-text font-medium">{{ $personnage->specialite->plat->nom_plat }}</p>
                <p class="text-hub-text-sec text-sm mt-1">{{ $personnage->specialite->plat->descri_plat }}</p>
            </div>
        </div>
    @endif

</div>
</x-app-layout>
