<x-admin-layout>
    <x-slot name="title">{{ $animal->nom_animal }} — Admin</x-slot>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">{{ $animal->nom_animal }}</h1>
        <a href="{{ route('admin.animaux.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Retour</a>
    </div>
    <div class="bg-hub-surface rounded-lg p-6 text-sm">
        <p><span class="text-hub-text-sec">Type :</span> {{ $animal->typeAnimal?->libelle_TAnimal ?? '—' }}</p>
    </div>
</x-admin-layout>
