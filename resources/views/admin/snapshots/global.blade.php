<x-admin-layout>
    <x-slot name="title">Restauration — Snapshots</x-slot>

    <div class="flex items-center gap-2 text-sm text-hub-text-sec mb-6">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-hub-text">Dashboard</a>
        <span>/</span>
        <span class="text-hub-text font-semibold">Restauration</span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-hub-gold">Restauration globale des snapshots</h1>
        <span class="text-hub-text-sec text-sm">{{ $snapshots->total() }} snapshot(s)</span>
    </div>

    <form method="GET" action="{{ route('admin.snapshots.index') }}" class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
            <label for="personnage" class="block text-hub-text-sec text-xs mb-1">Personnage</label>
            <input id="personnage" type="text" name="personnage" value="{{ request('personnage') }}" placeholder="Ex: Furina"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
        </div>

        <div>
            <label for="admin" class="block text-hub-text-sec text-xs mb-1">Admin</label>
            <input id="admin" type="text" name="admin" value="{{ request('admin') }}" placeholder="Pseudo admin"
                class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
        </div>

        <div>
            <label for="action_type" class="block text-hub-text-sec text-xs mb-1">Action</label>
            <select id="action_type" name="action_type" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="">Toutes</option>
                <option value="update" @selected(request('action_type') === 'update')>Update</option>
                <option value="delete" @selected(request('action_type') === 'delete')>Delete</option>
            </select>
        </div>

        <div>
            <label for="sort" class="block text-hub-text-sec text-xs mb-1">Tri</label>
            <select id="sort" name="sort" class="w-full bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="latest" @selected($sort === 'latest')>Plus récents d'abord</option>
                <option value="oldest" @selected($sort === 'oldest')>Plus anciens d'abord</option>
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded text-hub-text text-sm hover:opacity-90">
                Filtrer
            </button>
            <a href="{{ route('admin.snapshots.index') }}" class="px-4 py-2 border border-hub-border rounded text-hub-text text-sm hover:bg-hub-surface">
                Reset
            </a>
        </div>
    </form>

    <div class="space-y-4">
        @forelse($groupedSnapshots as $group)
            @php $entity = $group->first()->personnage; @endphp
            <section class="bg-hub-surface rounded-lg border border-hub-border overflow-hidden">
                <header class="px-4 py-3 bg-black/20 flex items-center justify-between">
                    <div>
                        <h2 class="text-hub-text font-semibold">{{ $entity?->nom_perso ?? 'Personnage supprimé' }}</h2>
                        <p class="text-xs text-hub-text-sec">ID personnage: {{ $entity?->id_perso ?? 'N/A' }}</p>
                    </div>
                    @if($entity)
                        <a href="{{ route('admin.personnages.snapshots.index', $entity) }}" class="text-sky-400 hover:underline text-sm">Voir l'historique complet</a>
                    @endif
                </header>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-hub-text-sec">
                            <tr>
                                <th class="px-4 py-3 text-left">Snapshot</th>
                                <th class="px-4 py-3 text-left">Action</th>
                                <th class="px-4 py-3 text-left">Admin</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-hub-border">
                            @foreach($group as $snapshot)
                                <tr class="hub-surface-hover">
                                    <td class="px-4 py-3 font-medium text-hub-text">#{{ $snapshot->id_snapshot }}</td>
                                    <td class="px-4 py-3">
                                        @if($snapshot->action_type === 'delete')
                                            <span class="px-2 py-1 rounded text-xs bg-red-900/50 text-red-200">delete</span>
                                        @else
                                            <span class="px-2 py-1 rounded text-xs bg-sky-900/50 text-sky-200">update</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-hub-text-sec">{{ $snapshot->admin?->pseudo_admin ?? 'system' }}</td>
                                    <td class="px-4 py-3 text-hub-text-sec">{{ optional($snapshot->action_at)->format('d/m/Y H:i:s') }}</td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('admin.snapshots.show', $snapshot) }}" class="text-hub-gold hover:underline">Détail / Restaurer</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @empty
            <div class="p-5 rounded border border-hub-border bg-hub-surface text-hub-text-sec text-sm">
                Aucun snapshot trouvé pour les filtres courants.
            </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $snapshots->links() }}</div>
</x-admin-layout>
