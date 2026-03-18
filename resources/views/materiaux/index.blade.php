<x-app-layout>
    <x-slot name="title">Matériaux</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-3xl font-bold text-hub-primary mb-6">Matériaux</h1>

        <form method="GET" action="{{ route('materiaux.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher un matériau..."
                       class="w-full rounded-lg bg-hub-surface border border-hub-border px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
            </div>

            <select name="type" class="rounded-lg bg-hub-surface border border-hub-border px-3 py-2 text-hub-text focus:outline-none focus:ring-2 focus:ring-hub-primary">
                <option value="">Tous les types</option>
                @foreach($types as $type)
                    <option value="{{ $type->id_typeM }}" {{ request('type') == $type->id_typeM ? 'selected' : '' }}>
                        {{ $type->libelle_TypeM }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg font-medium transition-colors">Filtrer</button>
        </form>

        @if($materiaux->isEmpty())
            <div class="text-center py-16 text-hub-text-sec">
                <p class="text-xl">Aucun matériau trouvé.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($materiaux as $mat)
                    <a href="{{ route('materiaux.show', $mat->slug) }}"
                       class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden hover:border-hub-primary transition-colors group">
                        <div class="aspect-square overflow-hidden bg-hub-surface-hover">
                            <img src="{{ $mat->photos->first()?->source_url ?? $mat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                                 alt="{{ $mat->nom_mat }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                        </div>
                        <div class="p-3">
                            <h3 class="font-semibold text-hub-text text-sm truncate">{{ $mat->nom_mat }}</h3>
                            <p class="text-hub-gold text-xs mt-1">{{ $mat->rarete?->{'libelle_rareté'} ?? '—' }}</p>
                            <p class="text-hub-text-sec text-xs">{{ $mat->typeMateriaux?->libelle_TypeM ?? '—' }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
            <div class="mt-8">{{ $materiaux->links() }}</div>
        @endif

    </div>
</x-app-layout>
