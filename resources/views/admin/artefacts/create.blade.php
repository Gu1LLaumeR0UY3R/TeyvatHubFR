<x-admin-layout>
    <x-slot name="title">Nouvel artefact — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouvel artefact</h1>

    <form action="{{ route('admin.artefacts.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl space-y-4">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_artefact" value="{{ old('nom_artefact') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_artefact')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Rareté</label>
            <select name="fid_rareté" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($raretes as $rarete)
                    <option value="{{ $rarete->id_rareté }}" @selected(old('fid_rareté') == $rarete->id_rareté)>{{ $rarete->libelle_rareté }}</option>
                @endforeach
            </select>
            @error('fid_rareté')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Bonus 2 pièces</label>
            <textarea name="bonus_2p" rows="4" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('bonus_2p') }}</textarea>
            @error('bonus_2p')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Bonus 4 pièces</label>
            <textarea name="bonus_4p" rows="6" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('bonus_4p') }}</textarea>
            @error('bonus_4p')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Icône</label>
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
            @error('photo')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer</button>
            <a href="{{ route('admin.artefacts.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
