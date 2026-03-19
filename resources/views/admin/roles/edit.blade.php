<x-admin-layout>
    <x-slot name="title">Modifier {{ $role->libelle_role }} — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $role->libelle_role }}</h1>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="max-w-lg space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-hub-text-sec mb-1">Libellé</label>
            <input type="text" name="libelle_role" value="{{ old('libelle_role', $role->libelle_role) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Description</label>
            <textarea name="descri_role" rows="3"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('descri_role', $role->descri_role) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Mettre à jour</button>
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
