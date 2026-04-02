<x-admin-layout>
    <x-slot name="title">Modifier {{ $arme->nom_arme }} — Admin</x-slot>

    <h1 class="text-2xl font-bold text-hub-gold mb-6">Modifier : {{ $arme->nom_arme }}</h1>

    <form action="{{ route('admin.armes.update', $arme) }}" method="POST" enctype="multipart/form-data" class="max-w-lg space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="block text-hub-text-sec mb-1">Nom</label>
            <input type="text" name="nom_arme" value="{{ old('nom_arme', $arme->nom_arme) }}" required
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">
            @error('nom_arme')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Description</label>
            <textarea name="descr_arme" rows="4" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text focus:outline-none focus:border-hub-gold">{{ old('descr_arme', $arme->descr_arme) }}</textarea>
            @error('descr_arme')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Type d'arme</label>
            <select name="fid_TArmes" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($typesArme as $ta)
                    <option value="{{ $ta->id_TArmes }}" {{ old('fid_TArmes', $arme->fid_TArmes) == $ta->id_TArmes ? 'selected' : '' }}>{{ $ta->libelle_TArme }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Rareté</label>
            <select name="fid_etoile" required class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                @foreach($etoiles as $etoile)
                    @php $nb = (int) preg_replace('/[^0-9]/', '', $etoile->libelle); @endphp
                    <option value="{{ $etoile->id_etoile }}" {{ old('fid_etoile', $arme->fid_etoile) == $etoile->id_etoile ? 'selected' : '' }}>
                        {{ str_repeat('★', $nb) }} ({{ $nb }}★)
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-hub-text-sec mb-1">Photo (laisser vide pour ne pas changer)</label>
            @if($arme->photos->first())
                <img src="{{ $arme->photos->first()->source_url ?? asset('storage/'.$arme->photos->first()->chemin_photo) }}"
                     alt="{{ $arme->nom_arme }}" class="w-16 h-16 object-contain mb-2">
            @endif
            <input type="file" name="photo" accept="image/*" class="text-hub-text">
        </div>

        <div class="pt-4 border-t border-hub-border">
            <h2 class="text-lg font-semibold text-hub-gold mb-3">Stats par niveau</h2>
            <div class="space-y-2">
                @foreach($arme->statsNiveaux->sortBy('lvl_ASN') as $stat)
                    <div class="grid grid-cols-3 gap-2">
                        <input type="hidden" name="stats_niveau[{{ $loop->index }}][id_ASN]" value="{{ $stat->id_ASN }}">
                        <input type="number" name="stats_niveau[{{ $loop->index }}][lvl_ASN]" value="{{ old('stats_niveau.'.$loop->index.'.lvl_ASN', $stat->lvl_ASN) }}" min="1" max="90" placeholder="Niveau"
                               class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                        <input type="number" step="0.01" name="stats_niveau[{{ $loop->index }}][main_stat]" value="{{ old('stats_niveau.'.$loop->index.'.main_stat', $stat->main_stat) }}" placeholder="Main stat"
                               class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                        <input type="number" step="0.01" name="stats_niveau[{{ $loop->index }}][subs_stats]" value="{{ old('stats_niveau.'.$loop->index.'.subs_stats', $stat->subs_stats) }}" placeholder="Sub stat"
                               class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                    </div>
                @endforeach
            </div>

            <p class="text-hub-text-sec text-sm mt-4 mb-2">Ajouter des stats niveau</p>
            @for($i = 0; $i < 3; $i++)
                <div class="grid grid-cols-3 gap-2 mb-2">
                    <input type="number" name="stats_niveau_new[{{ $i }}][lvl_ASN]" value="{{ old('stats_niveau_new.'.$i.'.lvl_ASN') }}" min="1" max="90" placeholder="Niveau"
                           class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                    <input type="number" step="0.01" name="stats_niveau_new[{{ $i }}][main_stat]" value="{{ old('stats_niveau_new.'.$i.'.main_stat') }}" placeholder="Main stat"
                           class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                    <input type="number" step="0.01" name="stats_niveau_new[{{ $i }}][subs_stats]" value="{{ old('stats_niveau_new.'.$i.'.subs_stats') }}" placeholder="Sub stat"
                           class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                </div>
            @endfor
        </div>

        <div class="pt-4 border-t border-hub-border">
            <h2 class="text-lg font-semibold text-hub-gold mb-3">Effets par rang</h2>
            <div class="space-y-2">
                @foreach($arme->statsRangs->sortBy('rang_ASR') as $rang)
                    <div class="grid grid-cols-5 gap-2">
                        <input type="hidden" name="stats_rang[{{ $loop->index }}][id_ASR]" value="{{ $rang->id_ASR }}">
                        <input type="number" min="1" max="5" name="stats_rang[{{ $loop->index }}][rang_ASR]" value="{{ old('stats_rang.'.$loop->index.'.rang_ASR', $rang->rang_ASR) }}" placeholder="Rang"
                               class="col-span-1 bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                        <input type="text" name="stats_rang[{{ $loop->index }}][descri_ASR]" value="{{ old('stats_rang.'.$loop->index.'.descri_ASR', $rang->descri_ASR) }}" placeholder="Description effet"
                               class="col-span-4 bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                    </div>
                @endforeach
            </div>

            <p class="text-hub-text-sec text-sm mt-4 mb-2">Ajouter des rangs</p>
            @for($i = 0; $i < 2; $i++)
                <div class="grid grid-cols-5 gap-2 mb-2">
                    <input type="number" min="1" max="5" name="stats_rang_new[{{ $i }}][rang_ASR]" value="{{ old('stats_rang_new.'.$i.'.rang_ASR') }}" placeholder="Rang"
                           class="col-span-1 bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                    <input type="text" name="stats_rang_new[{{ $i }}][descri_ASR]" value="{{ old('stats_rang_new.'.$i.'.descri_ASR') }}" placeholder="Description effet"
                           class="col-span-4 bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text">
                </div>
            @endfor
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">Mettre à jour</button>
            <a href="{{ route('admin.armes.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text hover:bg-hub-surface">Annuler</a>
        </div>
    </form>
</x-admin-layout>
