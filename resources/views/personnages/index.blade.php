<x-app-layout>
<x-slot name="title">Personnages</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         search: '',
         elementFilter: '',
         rareteFilter: '',
         armeFilter: '',
     }">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Personnages</h1>
        <p class="text-hub-text-sec">{{ $personnages->count() }} personnage(s)</p>
    </div>

    {{-- Barre de recherche --}}
    <div class="mb-4">
        <input type="text" x-model="search"
               placeholder="Rechercher un personnage..."
               class="w-full px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
    </div>

    {{-- Filtres éléments --}}
    <div class="mb-3 flex flex-wrap gap-2">
        <button @click="elementFilter = ''"
                :class="elementFilter === '' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">Tous</button>
        @foreach($elements as $el)
        <button @click="elementFilter = (elementFilter === '{{ $el->libelle_element }}' ? '' : '{{ $el->libelle_element }}')"
                :class="elementFilter === '{{ $el->libelle_element }}' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">{{ $el->libelle_element }}</button>
        @endforeach
    </div>

    {{-- Filtres rareté --}}
    <div class="mb-3 flex flex-wrap gap-2">
        <button @click="rareteFilter = ''"
                :class="rareteFilter === '' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">Toutes raretés</button>
        @foreach($etoiles as $e)
        <button @click="rareteFilter = (rareteFilter === '{{ $e->libelle }}' ? '' : '{{ $e->libelle }}')"
                :class="rareteFilter === '{{ $e->libelle }}' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">{{ $e->libelle }}</button>
        @endforeach
    </div>

    {{-- Filtres type d'arme --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <button @click="armeFilter = ''"
                :class="armeFilter === '' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">Tous types</button>
        @foreach($typeArmes as $ta)
        <button @click="armeFilter = (armeFilter === '{{ $ta->libelle_TArme }}' ? '' : '{{ $ta->libelle_TArme }}')"
                :class="armeFilter === '{{ $ta->libelle_TArme }}' ? 'bg-hub-primary text-white' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">{{ $ta->libelle_TArme }}</button>
        @endforeach
    </div>

    {{-- Grille personnages --}}
    @if($personnages->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucun personnage trouvé.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
            @foreach($personnages as $perso)
                <div data-nom="{{ strtolower($perso->nom_perso) }}"
                     data-element="{{ $perso->element?->libelle_element ?? '' }}"
                     data-rarete="{{ $perso->etoile?->libelle ?? '' }}"
                     data-arme="{{ $perso->typeArme?->libelle_TArme ?? '' }}"
                     x-show="(!search || $el.dataset.nom.includes(search.toLowerCase())) &&
                              (!elementFilter || $el.dataset.element === elementFilter) &&
                              (!rareteFilter  || $el.dataset.rarete  === rareteFilter)  &&
                              (!armeFilter    || $el.dataset.arme    === armeFilter)">
                    <x-card-personnage :personnage="$perso" />
                </div>
            @endforeach
        </div>
    @endif

</div>
</x-app-layout>

