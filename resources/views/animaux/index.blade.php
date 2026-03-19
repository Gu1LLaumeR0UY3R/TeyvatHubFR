<x-app-layout>
<x-slot name="title">Animaux</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-3xl font-bold text-hub-text mb-2">Animaux</h1>
        <p class="text-hub-text-sec">{{ $animaux->total() }} animal/animaux trouvé(s)</p>
    </div>

    <form method="GET" action="{{ route('animaux.index') }}" class="mb-8 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Rechercher un animal..."
               class="flex-1 min-w-48 px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">

        <select name="type" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="">Tous les types</option>
            @foreach($types as $type)
                <option value="{{ $type->id_TAnimal }}" @selected(request('type') == $type->id_TAnimal)>
                    {{ $type->libelle_TAnimal }}
                </option>
            @endforeach
        </select>

        <select name="sort" class="px-4 py-2 bg-hub-surface border border-hub-border rounded-lg text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
            <option value="nom_asc" @selected(!request('sort') || request('sort') === 'nom_asc')>Nom A-Z</option>
            <option value="nom_desc" @selected(request('sort') === 'nom_desc')>Nom Z-A</option>
            <option value="type" @selected(request('sort') === 'type')>Par type</option>
        </select>

        <button type="submit"
                class="px-6 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white rounded-lg font-medium transition-colors">
            Filtrer
        </button>
    </form>

    @if($animaux->isEmpty())
        <div class="text-center py-16 text-hub-text-sec">
            <p class="text-lg">Aucun animal trouvé.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($animaux as $animal)
                <a href="{{ route('animaux.show', $animal->slug) }}"
                   class="group bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary hover:shadow-lg transition-all duration-200">
                    <div class="aspect-square bg-hub-surface-hover overflow-hidden">
                        <img src="{{ $animal->photos->first()?->source_url ?? $animal->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $animal->nom_animal }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                    </div>
                    <div class="p-3">
                        <p class="font-semibold text-hub-text text-sm truncate">{{ $animal->nom_animal }}</p>
                        <p class="text-hub-text-sec text-xs mt-1">{{ $animal->typeAnimal?->libelle_TAnimal ?? '—' }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $animaux->links() }}
        </div>
    @endif

</div>
</x-app-layout>
