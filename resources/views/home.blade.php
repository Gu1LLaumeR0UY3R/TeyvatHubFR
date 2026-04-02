<x-app-layout>
    <x-slot name="title">Accueil</x-slot>

    {{-- Hero Banner --}}
    <section class="relative bg-gradient-to-br from-hub-bg via-hub-surface to-hub-bg overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-hub-primary/10 to-hub-accent/5"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="max-w-2xl">
                <h1 class="text-4xl lg:text-6xl font-bold text-hub-text leading-tight">
                    L'encyclopédie
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-hub-primary to-hub-accent">
                        Genshin Impact
                    </span>
                    en français
                </h1>
                <p class="mt-4 text-lg text-hub-text-sec">
                    Personnages, armes, ennemis, cuisine, régions — tout ce qu'il faut savoir sur le monde de Teyvat.
                </p>
                <div class="mt-8 flex flex-wrap gap-4">
                          <a href="{{ route('personnages.index') }}"
                              class="px-6 py-3 bg-hub-primary hover:bg-hub-primary/80 text-black font-semibold rounded-xl transition-colors duration-150 shadow-lg">
                        Explorer
                    </a>
                    @guest
                        <a href="{{ route('register') }}"
                           class="px-6 py-3 bg-hub-surface hover:bg-hub-surface-hover border border-hub-border text-hub-text font-semibold rounded-xl transition-colors duration-150">
                            Créer un compte
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </section>

    {{-- Accès rapide encyclopédie --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="text-2xl font-bold text-hub-text mb-8">Encyclopédie</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            @php
                $sections = [
                    ['label' => 'Personnages', 'route' => 'personnages.index', 'count' => $compteurs['personnages'], 'color' => 'hub-primary'],
                    ['label' => 'Armes',        'route' => 'armes.index',       'count' => $compteurs['armes'] ?? 0,       'color' => 'hub-gold'],
                    ['label' => 'Cuisine',      'route' => 'cuisine.index',     'count' => $compteurs['plats'] ?? 0,       'color' => 'pyro'],
                    ['label' => 'Ennemis',      'route' => 'ennemis.index',     'count' => $compteurs['ennemis'] ?? 0,     'color' => 'electro'],
                    ['label' => 'Animaux',      'route' => 'animaux.index',     'count' => $compteurs['animaux'] ?? 0,     'color' => 'dendro'],
                    ['label' => 'Histoire',     'route' => 'histoire.index',    'count' => null,                           'color' => 'hub-accent'],
                ];
            @endphp
            @foreach($sections as $section)
                <a href="{{ route($section['route']) }}"
                   class="group bg-hub-surface border border-hub-border hover:border-{{ $section['color'] }}/50 rounded-xl p-4 text-center transition-all duration-150 hover:bg-hub-surface-hover">
                    <p class="text-2xl font-bold text-{{ $section['color'] }} group-hover:scale-110 transition-transform inline-block">
                        {{ $section['count'] !== null ? $section['count'] : '—' }}
                    </p>
                    <p class="text-sm text-hub-text-sec mt-1">{{ $section['label'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Derniers personnages --}}
    @if($derniers_personnages->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-hub-text">Derniers personnages</h2>
            <a href="{{ route('personnages.index') }}" class="text-sm text-hub-accent hover:underline">Voir tous →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($derniers_personnages as $perso)
            <a href="{{ route('personnages.show', $perso->slug) }}"
               class="group bg-hub-surface border border-hub-border hover:border-hub-primary/40 rounded-xl overflow-hidden transition-all duration-150 hover:bg-hub-surface-hover">
                <div class="aspect-square bg-hub-bg flex items-center justify-center overflow-hidden">
                    <img src="{{ $perso->photos->first()?->source_url ?? $perso->photos->first()?->chemin_photo ?? asset('images/placeholder.svg') }}"
                         alt="{{ $perso->nom_perso }}"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                         loading="lazy">
                </div>
                <div class="p-3">
                    <p class="text-sm font-medium text-hub-text truncate">{{ $perso->nom_perso }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-hub-muted">{{ $perso->element?->libelle_element ?? '—' }}</span>
                        <span class="text-xs text-hub-gold">{{ $perso->etoile?->libelle ?? '' }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Événements --}}
    @if($prochains_evenements->isNotEmpty())
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <h2 class="text-2xl font-bold text-hub-text mb-6">Événements</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($prochains_evenements as $evt)
            @php
                $enCours = now()->between($evt->date_debut, $evt->date_fin);
            @endphp
            <div class="bg-hub-surface border border-hub-border rounded-xl p-4 flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    @if($enCours)
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-success/15 text-success border border-success/30">En cours</span>
                    @else
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-info/15 text-info border border-info/30">À venir</span>
                    @endif
                </div>
                <h3 class="text-sm font-semibold text-hub-text leading-snug">{{ $evt->titre }}</h3>
                @if($evt->descri_courte)
                    <p class="text-xs text-hub-muted">{{ $evt->descri_courte }}</p>
                @endif
                <p class="text-xs text-hub-muted mt-auto">
                    {{ $evt->date_debut->format('d/m/Y') }} — {{ $evt->date_fin->format('d/m/Y') }}
                </p>
            </div>
            @endforeach
        </div>
    </section>
    @endif

</x-app-layout>
