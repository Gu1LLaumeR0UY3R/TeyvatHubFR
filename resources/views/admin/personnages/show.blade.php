<x-admin-layout>
    <x-slot name="title">{{ $personnage->nom_perso }} — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">{{ $personnage->nom_perso }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.personnages.edit', $personnage) }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Modifier</a>
            <a href="{{ route('admin.personnages.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Retour</a>
        </div>
    </div>

    <div class="bg-hub-surface rounded-lg p-6 space-y-3 text-sm">
        <p><span class="text-hub-text-sec">Slug :</span> {{ $personnage->slug }}</p>
        <p><span class="text-hub-text-sec">Élément :</span> {{ $personnage->element?->libelle_element ?? '—' }}</p>
        <p><span class="text-hub-text-sec">Rareté :</span> {{ $personnage->etoile?->libelle ?? '—' }}</p>
    </div>
</x-admin-layout>
