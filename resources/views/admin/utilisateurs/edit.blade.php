<x-admin-layout>
    <x-slot name="title">Modifier utilisateur — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $utilisateur->name }}</h1>

    <form action="{{ route('admin.utilisateurs.update', $utilisateur) }}" method="POST" class="max-w-lg space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="name" value="{{ old('name', $utilisateur->name) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('name')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $utilisateur->email) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('email')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Mettre à jour</button>
            <a href="{{ route('admin.utilisateurs.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
