<x-admin-layout>
    <x-slot name="title">Nouveau rôle — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouveau rôle</h1>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="max-w-lg space-y-4">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Libellé</label>
            <input type="text" name="libelle_role" value="{{ old('libelle_role') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('libelle_role')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Description</label>
            <textarea name="descri_role" rows="3"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('descri_role') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
