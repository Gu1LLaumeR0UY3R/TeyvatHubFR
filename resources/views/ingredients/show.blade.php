<x-app-layout>
<x-slot name="title">{{ $ingredient->nom_ingre }}</x-slot>
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <nav class="mb-6 text-sm text-hub-text-sec">
        <a href="{{ route('ingredients.index') }}" class="hover:text-hub-primary">Ingrédients</a>
        <span class="mx-2">/</span>
        <span class="text-hub-text">{{ $ingredient->nom_ingre }}</span>
    </nav>

    {{-- Bloc 1 : Header --}}
    <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-6 items-center sm:items-start">
            <div class="flex-shrink-0">
                <img src="{{ $ingredient->photos->first()?->source_url ?? $ingredient->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                     alt="{{ $ingredient->nom_ingre }}"
                     class="w-32 h-32 object-contain">
            </div>
            <div class="flex-1 text-center sm:text-left">
                <h1 class="text-3xl font-bold text-hub-text mb-2">{{ $ingredient->nom_ingre }}</h1>
            </div>
        </div>
    </div>

    {{-- Bloc 2 : Animaux sources --}}
    @if($ingredient->animaux->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Obtenu depuis</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($ingredient->animaux as $animal)
                    <a href="{{ route('animaux.show', $animal->slug) }}"
                       class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $animal->photos->first()?->source_url ?? $animal->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $animal->nom_animal }}"
                             class="w-16 h-16 object-contain">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $animal->nom_animal }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Bloc 3 : Plats utilisant cet ingrédient --}}
    @if($ingredient->plats->count())
        <div class="bg-hub-surface border border-hub-border rounded-2xl p-6 mb-6">
            <h2 class="text-xl font-bold text-hub-text mb-4">Utilisé dans</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($ingredient->plats as $plat)
                    <a href="{{ route('cuisine.show', $plat->slug) }}"
                       class="flex flex-col items-center gap-2 p-3 bg-hub-surface-hover rounded-xl border border-hub-border hover:border-hub-primary transition-colors">
                        <img src="{{ $plat->photos->first()?->source_url ?? $plat->photos->first()?->chemin_photo ?? asset('images/placeholder.webp') }}"
                             alt="{{ $plat->nom_plat }}"
                             class="w-16 h-16 object-contain">
                        <span class="text-hub-text text-xs text-center font-medium">{{ $plat->nom_plat }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

</div>
</x-app-layout>
