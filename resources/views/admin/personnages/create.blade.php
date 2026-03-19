<x-admin-layout>
    <x-slot name="title">Nouveau personnage — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouveau personnage</h1>

    <form action="{{ route('admin.personnages.store') }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_perso" value="{{ old('nom_perso') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_perso')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Élément</label>
            <select name="fid_element" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                <option value="">— Aucun —</option>
                @foreach($elements as $el)
                    <option value="{{ $el->id_element }}" {{ old('fid_element') == $el->id_element ? 'selected' : '' }}>{{ $el->libelle_element }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Rareté</label>
            <select name="fid_etoile" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($etoiles as $etoile)
                    <option value="{{ $etoile->id_etoile }}" {{ old('fid_etoile') == $etoile->id_etoile ? 'selected' : '' }}>{{ $etoile->libelle }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Type d'arme</label>
            <select name="fid_TArmes" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                <option value="">— Aucun —</option>
                @foreach($typesArme as $ta)
                    <option value="{{ $ta->id_TArmes }}" {{ old('fid_TArmes') == $ta->id_TArmes ? 'selected' : '' }}>{{ $ta->libelle_TArme }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Type de personnage</label>
            <select name="fid_TP" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($typesPerso as $tp)
                    <option value="{{ $tp->id_TP }}" {{ old('fid_TP') == $tp->id_TP ? 'selected' : '' }}>{{ $tp->libelle_TP }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Photo</label>
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.personnages.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
