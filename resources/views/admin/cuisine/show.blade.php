<x-admin-layout>
    <x-slot name="title">{{ $plat->nom_plat }} — Admin</x-slot>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">{{ $plat->nom_plat }}</h1>
        <a href="{{ route('admin.cuisine.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Retour</a>
    </div>
</x-admin-layout>
