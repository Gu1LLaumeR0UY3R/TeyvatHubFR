<x-admin-layout>
    <x-slot name="title">Nouvel animal — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouvel animal</h1>

    <form action="{{ route('admin.animaux.store') }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_animal" value="{{ old('nom_animal') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_animal')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Type</label>
            <select name="fid_TAnimal" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($typesAnimal as $ta)
                    <option value="{{ $ta->id_TAnimal }}" {{ old('fid_TAnimal') == $ta->id_TAnimal ? 'selected' : '' }}>{{ $ta->libelle_TAnimal }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Photo</label>
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.animaux.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
