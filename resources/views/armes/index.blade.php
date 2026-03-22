<x-app-layout>
<x-slot name="title">Armes</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
     x-data="{
         search: '',
         typeFilter: '',
         rareteFilter: '',
     }">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Armes</h1>
        <p class="text-hub-text-sec">{{ $armes->count() }} arme(s)</p>
    </div>

    {{-- Recherche --}}
    <div class="mb-4">
        <input type="text" x-model="search"
               placeholder="Rechercher une arme..."
               class="w-full px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
    </div>

    {{-- Filtres type --}}
    <div class="mb-3 flex flex-wrap gap-2">
        <button type="button" @click.prevent="typeFilter = ''"
            :class="typeFilter === '' ? 'bg-hub-primary text-black border border-hub-primary' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">Tous types</button>
        @foreach($types as $type)
        <button type="button" @click.prevent="typeFilter = (typeFilter === '{{ $type->libelle_TArme }}' ? '' : '{{ $type->libelle_TArme }}')"
            :class="typeFilter === '{{ $type->libelle_TArme }}' ? 'bg-hub-primary text-black border border-hub-primary' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">{{ $type->libelle_TArme }}</button>
        @endforeach
    </div>

    {{-- Filtres rareté --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <button type="button" @click.prevent="rareteFilter = ''"
            :class="rareteFilter === '' ? 'bg-hub-primary text-black border border-hub-primary' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">Toutes raretés</button>
        @foreach($etoiles as $e)
        <button type="button" @click.prevent="rareteFilter = (rareteFilter === '{{ $e->libelle }}' ? '' : '{{ $e->libelle }}')"
            :class="rareteFilter === '{{ $e->libelle }}' ? 'bg-hub-primary text-black border border-hub-primary' : 'bg-hub-surface text-hub-text-sec border border-hub-border'"
                class="px-3 py-1 rounded-full text-sm transition-colors">{{ $e->libelle }}</button>
        @endforeach
    </div>

    @if($armes->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucune arme trouvée.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($armes as $arme)
                <div data-nom="{{ strtolower($arme->nom_arme) }}"
                     data-type="{{ $arme->typeArme?->libelle_TArme ?? '' }}"
                     data-rarete="{{ $arme->etoile?->libelle ?? '' }}"
                     x-show="(!search    || $el.dataset.nom.includes(search.toLowerCase())) &&
                              (!typeFilter  || $el.dataset.type   === typeFilter)  &&
                              (!rareteFilter || $el.dataset.rarete === rareteFilter)">
                    <x-card-arme :arme="$arme" />
                </div>
            @endforeach
        </div>
    @endif

</div>
</x-app-layout>
