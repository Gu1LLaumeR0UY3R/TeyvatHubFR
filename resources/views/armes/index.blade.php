<x-app-layout>
<x-slot name="title">Armes</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Armes</h1>
        <p class="text-hub-text-sec">{{ $armes->total() }} arme(s) trouvée(s)</p>
    </div>

    <form method="GET" action="{{ route('armes.index') }}" class="mb-8 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher une arme..."
               class="flex-1 min-w-48 px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">

        <select name="type" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Tous les types</option>
            @foreach($types as $type)
                <option value="{{ $type->id_TArmes }}" @selected(request('type') == $type->id_TArmes)>
                    {{ $type->libelle_TArme }}
                </option>
            @endforeach
        </select>

        <select name="rarete" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Toutes les raretés</option>
            @foreach($etoiles as $e)
                <option value="{{ $e->id_etoile }}" @selected(request('rarete') == $e->id_etoile)>
                    {{ $e->libelle }}
                </option>
            @endforeach
        </select>

        <select name="sort" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="nom_asc" @selected(!request('sort') || request('sort') === 'nom_asc')>Nom A-Z</option>
            <option value="nom_desc" @selected(request('sort') === 'nom_desc')>Nom Z-A</option>
            <option value="rarete_desc" @selected(request('sort') === 'rarete_desc')>Rareté ↑</option>
            <option value="rarete_asc" @selected(request('sort') === 'rarete_asc')>Rareté ↓</option>
            <option value="type" @selected(request('sort') === 'type')>Par type</option>
        </select>

        <button type="submit"
                class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    @if($armes->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucune arme trouvée.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($armes as $arme)
                <a href="{{ route('armes.show', $arme->slug) }}"
                   class="group bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary hover:shadow-lg transition-all duration-200">
                    <div class="aspect-square bg-hub-surface-hover overflow-hidden p-4">
                        <img src="{{ $arme->photos->first()?->source_url ?? $arme->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $arme->nom_arme }}"
                             class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-hub-text text-sm truncate">{{ $arme->nom_arme }}</p>
                        <p class="text-hub-text-sec text-xs mt-1">{{ $arme->typeArme?->libelle_TArme ?? '—' }}</p>
                        <p class="text-hub-gold text-xs">{{ $arme->etoile?->libelle ?? '' }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $armes->links() }}
        </div>
    @endif

</div>
</x-app-layout>
