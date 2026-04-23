<x-admin-layout>
    <x-slot name="title">Personnages — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des personnages</h1>
        <a href="{{ route('admin.personnages.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">
            + Nouveau
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-800 text-red-100 rounded">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.personnages.index') }}" class="mb-4 flex items-end gap-3">
        <div>
            <label for="sort" class="block text-hub-text-sec text-xs mb-1">Tri rapide</label>
            <select id="sort" name="sort" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="nom_asc" @selected($sort === 'nom_asc')>Nom (A-Z)</option>
                <option value="nom_desc" @selected($sort === 'nom_desc')>Nom (Z-A)</option>
                <option value="element_asc" @selected($sort === 'element_asc')>Element (croissant)</option>
                <option value="element_desc" @selected($sort === 'element_desc')>Element (decroissant)</option>
                <option value="rarete_asc" @selected($sort === 'rarete_asc')>Rareté (4★ vers 5★)</option>
                <option value="rarete_desc" @selected($sort === 'rarete_desc')>Rareté (5★ vers 4★)</option>
                <option value="arme_asc" @selected($sort === 'arme_asc')>Type d'arme (croissant)</option>
                <option value="arme_desc" @selected($sort === 'arme_desc')>Type d'arme (decroissant)</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded text-hub-text text-sm hover:opacity-90">
            Trier
        </button>
    </form>

    <div x-data="{
        selected: {},
        get selectedCount() { return Object.values(this.selected).filter(Boolean).length; },
        toggleAll(checked) { Object.keys(this.selected).forEach(k => this.selected[k] = checked); }
    }" class="space-y-4">
        <!-- Bulk Update Section -->
        <template x-if="selectedCount > 0">
            <div class="p-4 bg-hub-gold/10 border border-hub-gold rounded-lg">
                <h3 class="text-hub-text font-semibold mb-3">Modification en masse (<span x-text="selectedCount"></span> personnage(s) sélectionné(s))</h3>
                <form action="{{ route('admin.personnages.bulk-update') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                    @csrf @method('PATCH')
                    
                    <!-- Hidden checkboxes for selected IDs -->
                    <template x-for="(checked, id) in selected" :key="id">
                        <template x-if="checked">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </template>

                    <div>
                        <label class="block text-hub-text-sec text-xs mb-1">Élément</label>
                        <select name="fid_element" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                            <option value="">(ne pas changer)</option>
                            @foreach($elements as $el)
                                <option value="{{ $el->id_element }}">{{ $el->libelle_element }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-hub-text-sec text-xs mb-1">Rareté</label>
                        <select name="fid_etoile" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                            <option value="">(ne pas changer)</option>
                            @foreach($etoiles as $etoile)
                                @php $nb = (int) preg_replace('/[^0-9]/', '', $etoile->libelle); @endphp
                                <option value="{{ $etoile->id_etoile }}">{{ str_repeat('★', $nb) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-hub-text-sec text-xs mb-1">Type d'arme</label>
                        <select name="fid_TArmes" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                            <option value="">(ne pas changer)</option>
                            @foreach($typeArmes as $ta)
                                <option value="{{ $ta->id_TArmes }}">{{ $ta->libelle_TArme }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90 font-medium text-sm">
                        Appliquer
                    </button>
                </form>
            </div>
        </template>

        <!-- Table -->
        <div class="bg-hub-surface rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-black/20 text-hub-text-sec">
                    <tr>
                        <th class="px-3 py-3 text-center w-8">
                            <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded">
                        </th>
                        <th class="px-3 py-3 w-14"></th>
                        <th class="px-4 py-3 text-left">Nom</th>
                        <th class="px-4 py-3 text-left">Élément</th>
                        <th class="px-4 py-3 text-left">Rareté</th>
                        <th class="px-4 py-3 text-left">Type d'arme</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hub-border">
                    @forelse($personnages as $personnage)
                        <tr class="hub-surface-hover">
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" x-model="selected['{{ $personnage->getKey() }}']" class="rounded">
                            </td>
                            <td class="px-3 py-2 text-center">
                                <img src="{{ $personnage->icone_url }}"
                                     alt="{{ $personnage->nom_perso }}"
                                     class="w-10 h-10 object-contain rounded-full bg-hub-surface-hover mx-auto">
                            </td>
                            <td class="px-4 py-3 font-medium text-hub-text">{{ $personnage->nom_perso }}</td>
                            <td class="px-4 py-3">{{ $personnage->element?->libelle_element ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $personnage->etoile?->libelle ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $personnage->typeArme?->libelle_TArme ?? '—' }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="{{ route('admin.personnages.edit', $personnage) }}" class="text-hub-gold hover:underline">Modifier</a>
                                <form action="{{ route('admin.personnages.destroy', $personnage) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-hub-text-sec">Aucun personnage</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $personnages->links() }}</div>
</x-admin-layout>
