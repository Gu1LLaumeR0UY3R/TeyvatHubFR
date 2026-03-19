<x-admin-layout>
    <x-slot name="title">Nouvel utilisateur — Admin</x-slot>
    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouvel utilisateur</h1>
    <form action="{{ route('admin.utilisateurs.store') }}" method="POST" class="max-w-lg space-y-4">
        @csrf
        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
        </div>
        <div>
            <label class="block text-hub-text-sec mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
        </div>
        <div>
            <label class="block text-hub-text-sec mb-1">Mot de passe</label>
            <input type="password" name="password" required minlength="8" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
        </div>
        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.utilisateurs.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
