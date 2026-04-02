<x-admin-layout>
    <x-slot name="title">Utilisateurs — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des utilisateurs</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-800 text-red-100 rounded">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.utilisateurs.index') }}" class="mb-4 flex items-end gap-3">
        <div>
            <label for="sort" class="block text-hub-text-sec text-xs mb-1">Tri rapide</label>
            <select id="sort" name="sort" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="nom_asc" @selected($sort === 'nom_asc')>Nom (A-Z)</option>
                <option value="nom_desc" @selected($sort === 'nom_desc')>Nom (Z-A)</option>
                <option value="email_asc" @selected($sort === 'email_asc')>Email (A-Z)</option>
                <option value="email_desc" @selected($sort === 'email_desc')>Email (Z-A)</option>
                <option value="status_asc" @selected($sort === 'status_asc')>Statut (actifs d'abord)</option>
                <option value="status_desc" @selected($sort === 'status_desc')>Statut (bannis d'abord)</option>
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
                <h3 class="text-hub-text font-semibold mb-3">Action en masse (<span x-text="selectedCount"></span> utilisateur(s) sélectionné(s))</h3>
                <form action="{{ route('admin.utilisateurs.bulk-update') }}" method="POST" class="flex flex-wrap gap-3 items-end">
                    @csrf @method('PATCH')

                    <template x-for="(checked, id) in selected" :key="id">
                        <template x-if="checked">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                    </template>

                    <div>
                        <label class="block text-hub-text-sec text-xs mb-1">Action</label>
                        <select name="action" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                            <option value="bannir">Bannir</option>
                            <option value="debannir">Débannir</option>
                        </select>
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
                        <th class="px-4 py-3 text-left">Nom</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Statut</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hub-border">
                    @forelse($utilisateurs as $utilisateur)
                        <tr class="hub-surface-hover">
                            <td class="px-3 py-2 text-center">
                                <input type="checkbox" x-model="selected['{{ $utilisateur->id }}']" class="rounded">
                            </td>
                            <td class="px-4 py-3">{{ $utilisateur->name }}</td>
                            <td class="px-4 py-3">{{ $utilisateur->email }}</td>
                            <td class="px-4 py-3">
                                @if($utilisateur->banni_le)
                                    <span class="text-red-400">Banni</span>
                                @else
                                    <span class="text-green-400">Actif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 flex gap-2 flex-wrap">
                                <a href="{{ route('admin.utilisateurs.edit', $utilisateur) }}" class="text-hub-gold hover:underline">Modifier</a>
                                @if($utilisateur->banni_le)
                                    <form action="{{ route('admin.utilisateurs.debannir', $utilisateur) }}" method="POST">
                                        @csrf
                                        <button class="text-green-400 hover:underline">Débannir</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.utilisateurs.bannir', $utilisateur) }}" method="POST" onsubmit="return confirm('Bannir cet utilisateur ?')">
                                        @csrf
                                        <button class="text-red-400 hover:underline">Bannir</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.utilisateurs.destroy', $utilisateur) }}" method="POST" onsubmit="return confirm('Supprimer définitivement ?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-hub-text-sec">Aucun utilisateur</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $utilisateurs->links() }}</div>
</x-admin-layout>
