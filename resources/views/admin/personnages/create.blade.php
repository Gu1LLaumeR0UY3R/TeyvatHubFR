<x-admin-layout>
    <x-slot name="title">Nouveau personnage — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Nouveau personnage</h1>

    <form action="{{ route('admin.personnages.store') }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4"
          x-data="{ elementSelected: '{{ old('fid_element') }}' }">
        @csrf

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_perso" value="{{ old('nom_perso') }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_perso')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-2">Élément</label>
            {{-- Champ caché pour le submit --}}
            <input type="hidden" name="fid_element" x-model="elementSelected">
            <div class="flex flex-wrap gap-2">
                <button type="button" @click="elementSelected = ''"
                    :class="elementSelected === '' ? 'ring-2 ring-hub-gold opacity-100' : 'opacity-50 hover:opacity-80'"
                    class="flex flex-col items-center gap-1 p-2 rounded-lg bg-hub-surface border border-hub-border transition-all duration-150">
                    <span class="w-8 h-8 flex items-center justify-center text-hub-text-sec text-xs">—</span>
                    <span class="text-xs text-hub-text-sec">Aucun</span>
                </button>
                @foreach($elements as $el)
                    @php
                        $photo   = $el->photos->first();
                        $iconUrl = $photo?->source_url
                            ?? ($photo && !filter_var($photo->chemin_photo, FILTER_VALIDATE_URL)
                                ? asset('storage/'.$photo->chemin_photo)
                                : ($photo?->chemin_photo ?? null))
                            ?? asset('images/placeholder.svg');
                    @endphp
                    <button type="button" @click="elementSelected = '{{ $el->id_element }}'"
                        :class="elementSelected === '{{ $el->id_element }}' ? 'ring-2 ring-hub-gold opacity-100 scale-110' : 'opacity-50 hover:opacity-80'"
                        class="flex flex-col items-center gap-1 p-2 rounded-lg bg-hub-surface border border-hub-border transition-all duration-150"
                        title="{{ $el->libelle_element }}">
                        <img src="{{ $iconUrl }}" alt="{{ $el->libelle_element }}" class="w-8 h-8 object-contain">
                        <span class="text-xs text-hub-text">{{ $el->libelle_element }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Rareté</label>
            <select name="fid_etoile" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($etoiles as $etoile)
                    @php $nb = (int) preg_replace('/[^0-9]/', '', $etoile->libelle); @endphp
                    <option value="{{ $etoile->id_etoile }}" {{ old('fid_etoile') == $etoile->id_etoile ? 'selected' : '' }}>
                        {{ str_repeat('★', $nb) }} ({{ $nb }}★)
                    </option>
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
            <label class="block text-hub-text-sec mb-1">Icône (carte &amp; grille)</label>
            <p class="text-xs text-hub-text-sec mb-1">Image carrée 256×256 recommandée.</p>
            <input type="file" name="photo_icone" accept="image/*" class="text-hub-text">
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Portrait (page détail)</label>
            <p class="text-xs text-hub-text-sec mb-1">Image complète du personnage (fond transparent ou splash art).</p>
            <input type="file" name="photo_portrait" accept="image/*" class="text-hub-text">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Créer et ouvrir l'éditeur</button>
            <a href="{{ route('admin.personnages.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
