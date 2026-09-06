<x-admin-layout>
    <x-slot name="title">{{ $personnage->nom_perso }} — Aperçu — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.personnages.index') }}" class="text-sm text-hub-text-sec hover:text-hub-primary">
                ← Retour à la liste
            </a>
            <h1 class="text-2xl font-bold text-hub-gold mt-1">{{ $personnage->nom_perso }} — Aperçu</h1>
        </div>
        <a href="{{ route('admin.personnages.edit', $personnage) }}"
           class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">
            Modifier
        </a>
    </div>

    @include('personnages.partials.book')
</x-admin-layout>
