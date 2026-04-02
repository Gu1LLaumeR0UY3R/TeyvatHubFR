<x-admin-layout>
    <x-slot name="title">Rôles — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des rôles</h1>
        <a href="{{ route('admin.roles.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouveau</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-800 text-red-100 rounded">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.roles.index') }}" class="mb-4 flex items-end gap-3">
        <div>
            <label for="sort" class="block text-hub-text-sec text-xs mb-1">Tri rapide</label>
            <select id="sort" name="sort" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="libelle_asc" @selected($sort === 'libelle_asc')>Libellé (A-Z)</option>
                <option value="libelle_desc" @selected($sort === 'libelle_desc')>Libellé (Z-A)</option>
                <option value="count_asc" @selected($sort === 'count_asc')>Personnages (croissant)</option>
                <option value="count_desc" @selected($sort === 'count_desc')>Personnages (decroissant)</option>
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
        <template x-if="selectedCount > 0">
            <div class="p-4 bg-hub-gold/10 border border-hub-gold rounded-lg">
                <h3 class="text-hub-text font-semibold mb-3">Modification en masse (<span x-text="selectedCount"></span> rôle(s) sélectionné(s))</h3>
                <form action="{{ route('admin.roles.bulk-update') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                    @csrf @method('PATCH')

                    <template x-for="(checked, id) in selected" :key="id">
                        <template x-if="checked">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </template>

                    <div class="min-w-64">
                        <label class="block text-hub-text-sec text-xs mb-1">Description (appliquée à tous)</label>
                        <input type="text" name="descri_role" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm" placeholder="Nouvelle description">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90 font-medium text-sm">
                        Appliquer
                    </button>
                </form>
            </div>
        </template>

        <div class="bg-hub-surface rounded-lg overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-black/20 text-hub-text-sec">
                    <tr>
                        <th class="px-3 py-3 text-center w-8">
                            <input type="checkbox" @change="toggleAll($event.target.checked)" class="rounded">
                        </th>
                        <th class="px-4 py-3 text-left">Libellé</th>
                        <th class="px-4 py-3 text-left">Personnages</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hub-border">
                    @forelse($roles as $role)
                        <tr class="hub-surface-hover">
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" x-model="selected['{{ $role->id_role }}']" class="rounded">
                            </td>
                            <td class="px-4 py-3">{{ $role->libelle_role }}</td>
                            <td class="px-4 py-3">{{ $role->personnages_count }}</td>
                            <td class="px-4 py-3 flex gap-2">
                                <a href="{{ route('admin.roles.edit', $role) }}" class="text-hub-gold hover:underline">Modifier</a>
                                <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-400 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-hub-text-sec">Aucun rôle</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $roles->links() }}</div>
</x-admin-layout>
