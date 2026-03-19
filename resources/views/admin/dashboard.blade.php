<x-admin-layout>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-hub-text">Dashboard</h1>
    <p class="text-hub-text-sec mt-1">Vue d'ensemble de TeyvatHub</p>
</div>

@if(session('import_success'))
    <div class="mb-4 p-4 bg-green-900 bg-opacity-30 border border-green-700 rounded-xl">
        <p class="text-green-400">{{ session('import_success') }}</p>
    </div>
@endif

@if(session('import_error'))
    <div class="mb-4 p-4 bg-red-900 bg-opacity-30 border border-red-700 rounded-xl">
        <p class="text-red-400">{{ session('import_error') }}</p>
    </div>
@endif

{{-- Statistiques --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    @foreach([
        ['label' => 'Personnages', 'count' => $stats['personnages'], 'route' => 'admin.personnages.index'],
        ['label' => 'Armes', 'count' => $stats['armes'], 'route' => 'admin.armes.index'],
        ['label' => 'Ennemis', 'count' => $stats['ennemis'], 'route' => 'admin.ennemis.index'],
        ['label' => 'Animaux', 'count' => $stats['animaux'], 'route' => 'admin.animaux.index'],
        ['label' => 'Plats', 'count' => $stats['plats'], 'route' => 'admin.cuisine.index'],
        ['label' => 'Ingrédients', 'count' => $stats['ingredients'], 'route' => 'admin.cuisine.index'],
        ['label' => 'Régions', 'count' => $stats['regions'], 'route' => 'admin.regions.index'],
        ['label' => 'Utilisateurs', 'count' => $stats['utilisateurs'], 'route' => 'admin.utilisateurs.index'],
    ] as $stat)
        <a href="{{ route($stat['route']) }}"
           class="bg-hub-surface border border-hub-border rounded-xl p-4 hover:border-hub-primary transition-colors">
            <p class="text-2xl font-bold text-hub-primary">{{ $stat['count'] }}</p>
            <p class="text-hub-text-sec text-sm mt-1">{{ $stat['label'] }}</p>
        </a>
    @endforeach
</div>

{{-- Import Genshin --}}
<div class="bg-hub-surface border border-hub-border rounded-2xl p-6">
    <h2 class="text-lg font-bold text-hub-text mb-2">Import Genshin Impact</h2>
    <p class="text-hub-text-sec text-sm mb-4">Importe les personnages, armes et éléments depuis teyvat-dev API.</p>
    <form method="POST" action="{{ route('admin.import-genshin') }}">
        @csrf
        <button type="submit"
                class="px-5 py-2.5 bg-hub-primary text-white rounded-xl hover:bg-opacity-90 font-medium transition-colors">
            Lancer l'import
        </button>
    </form>
</div>

</x-admin-layout>
