<x-admin-layout>
    <x-slot name="title">Nouveau plat — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouveau plat</h1>

    <form action="{{ route('admin.cuisine.store') }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_plat" value="{{ old('nom_plat') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_plat')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Rareté</label>
            <select name="fid_rareté" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($raretes as $rarete)
                    <option value="{{ $rarete->{'id_rareté'} }}" {{ old('fid_rareté') == $rarete->{'id_rareté'} ? 'selected' : '' }}>{{ $rarete->{'libelle_rareté'} }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Photo</label>
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.cuisine.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
