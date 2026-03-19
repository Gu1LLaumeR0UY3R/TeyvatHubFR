<x-admin-layout>
    <x-slot name="title">Modifier {{ $ennemi->nom_ennemi }} — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $ennemi->nom_ennemi }}</h1>

    <form action="{{ route('admin.ennemis.update', $ennemi) }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_ennemi" value="{{ old('nom_ennemi', $ennemi->nom_ennemi) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Type</label>
            <select name="fid_typeEnne" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($typesEnnemi as $te)
                    <option value="{{ $te->id_typeEnnemi }}" {{ old('fid_typeEnne', $ennemi->fid_typeEnne) == $te->id_typeEnnemi ? 'selected' : '' }}>{{ $te->libelle_Type }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Élément</label>
            <select name="fid_element" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                <option value="">— Aucun —</option>
                @foreach($elements as $el)
                    <option value="{{ $el->id_element }}" {{ old('fid_element', $ennemi->fid_element) == $el->id_element ? 'selected' : '' }}>{{ $el->libelle_element }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Photo (laisser vide pour ne pas changer)</label>
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Mettre à jour</button>
            <a href="{{ route('admin.ennemis.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
