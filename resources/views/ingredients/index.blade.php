<x-app-layout>
    <x-slot name="title">Ingrédients</x-slot>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <h1 class="text-3xl font-bold text-hub-primary mb-6">Ingrédients</h1>

        <form method="GET" action="{{ route('ingredients.index') }}" class="mb-6 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Rechercher un ingrédient..."
                       class="w-full rounded-lg bg-hub-surface border border-hub-border px-4 py-2 text-hub-text placeholder-hub-text-sec focus:outline-none focus:ring-2 focus:ring-hub-primary">
            </div>
            <button type="submit" class="px-4 py-2 bg-hub-primary hover:bg-hub-accent text-white rounded-lg font-medium transition-colors">Filtrer</button>
        </form>

        @if($ingredients->isEmpty())
            <div class="text-center py-16 text-hub-text-sec">
                <p class="text-xl">Aucun ingrédient trouvé.</p>
            </div>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @foreach($ingredients as $ingredient)
                    <a href="{{ route('ingredients.show', $ingredient->slug) }}"
                       class="bg-hub-surface border border-hub-border rounded-2xl overflow-hidden hover:border-hub-primary hover:-translate-y-1 transition-all">
                        <img src="{{ $ingredient->photos->first()?->source_url ?? $ingredient->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $ingredient->nom_ingre }}"
                             class="w-full aspect-square object-cover">
                        <div class="p-3">
                            <p class="font-semibold text-hub-text text-sm truncate">{{ $ingredient->nom_ingre }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $ingredients->links() }}
            </div>
        @endif

    </div>
</x-app-layout>
