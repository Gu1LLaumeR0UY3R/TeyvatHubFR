<x-admin-layout>
    <x-slot name="title">Modifier chronologie — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $chronologie->titre }}</h1>

    <form action="{{ route('admin.chronologie.update', $chronologie) }}" method="POST" class="max-w-lg space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-hub-text-sec mb-1">Titre</label>
            <input type="text" name="titre" value="{{ old('titre', $chronologie->titre) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Période</label>
            <input type="text" name="periode" value="{{ old('periode', $chronologie->periode) }}"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Résumé</label>
            <textarea name="resume" rows="4"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('resume', $chronologie->resume) }}</textarea>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Ordre</label>
            <input type="number" name="ordre" value="{{ old('ordre', $chronologie->ordre) }}" min="1" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Mettre à jour</button>
            <a href="{{ route('admin.chronologie.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
