<x-admin-layout>
    <x-slot name="title">Snapshots — {{ $personnage->nom_perso }}</x-slot>

    <div class="flex items-center gap-2 text-sm text-hub-text-sec mb-6">
        <a href="{{ route('admin.personnages.index') }}" class="hover:text-hub-text">Personnages</a>
        <span>/</span>
        <a href="{{ route('admin.personnages.edit', $personnage) }}" class="hover:text-hub-text">{{ $personnage->nom_perso }}</a>
        <span>/</span>
        <span class="text-hub-text font-semibold">Snapshots</span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="flex items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl font-bold text-hub-gold">Historique des snapshots</h1>
        <span class="text-hub-text-sec text-sm">{{ $snapshots->total() }} snapshot(s)</span>
    </div>

    <form method="GET" action="{{ route('admin.personnages.snapshots.index', $personnage) }}" class="mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label for="action_type" class="block text-hub-text-sec text-xs mb-1">Type d'action</label>
            <select id="action_type" name="action_type" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="">Tous</option>
                <option value="update" @selected(request('action_type') === 'update')>Update</option>
                <option value="delete" @selected(request('action_type') === 'delete')>Delete</option>
            </select>
        </div>

        <button type="submit" class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded text-hub-text text-sm hover:opacity-90">
            Filtrer
        </button>
        <a href="{{ route('admin.personnages.snapshots.index', $personnage) }}" class="px-4 py-2 border border-hub-border rounded text-hub-text text-sm hover:bg-hub-surface">
            Reset
        </a>
    </form>

    <div class="bg-hub-surface rounded-lg overflow-hidden border border-hub-border">
        <table class="w-full text-sm">
            <thead class="bg-black/20 text-hub-text-sec">
                <tr>
                    <th class="px-4 py-3 text-left">#</th>
                    <th class="px-4 py-3 text-left">Action</th>
                    <th class="px-4 py-3 text-left">Admin</th>
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hub-border">
                @forelse($snapshots as $snapshot)
                    <tr class="hub-surface-hover">
                        <td class="px-4 py-3 font-medium text-hub-text">{{ $snapshot->id_snapshot }}</td>
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
                            <a href="{{ route('admin.snapshots.show', $snapshot) }}" class="text-hub-gold hover:underline">Voir détail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-hub-text-sec">Aucun snapshot trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $snapshots->links() }}</div>
</x-admin-layout>
