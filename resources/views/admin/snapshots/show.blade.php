<x-admin-layout>
    <x-slot name="title">Snapshot #{{ $snapshot->id_snapshot }}</x-slot>

    <div class="flex items-center gap-2 text-sm text-hub-text-sec mb-6">
        <a href="{{ route('admin.personnages.index') }}" class="hover:text-hub-text">Personnages</a>
        <span>/</span>
        <a href="{{ route('admin.personnages.edit', $snapshot->personnage) }}" class="hover:text-hub-text">{{ $snapshot->personnage?->nom_perso ?? 'Personnage' }}</a>
        <span>/</span>
        <a href="{{ route('admin.personnages.snapshots.index', $snapshot->personnage) }}" class="hover:text-hub-text">Snapshots</a>
        <span>/</span>
        <span class="text-hub-text font-semibold">#{{ $snapshot->id_snapshot }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="mb-6 p-4 rounded-lg border border-hub-border bg-hub-surface">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="text-hub-text-sec text-xs">Snapshot</div>
                <div class="text-hub-text font-semibold">#{{ $snapshot->id_snapshot }}</div>
            </div>
            <div>
                <div class="text-hub-text-sec text-xs">Action</div>
                <div class="text-hub-text font-semibold">{{ $snapshot->action_type }}</div>
            </div>
            <div>
                <div class="text-hub-text-sec text-xs">Admin</div>
                <div class="text-hub-text font-semibold">{{ $snapshot->admin?->pseudo_admin ?? 'system' }}</div>
            </div>
            <div>
                <div class="text-hub-text-sec text-xs">Date</div>
                <div class="text-hub-text font-semibold">{{ optional($snapshot->action_at)->format('d/m/Y H:i:s') }}</div>
            </div>
        </div>

        <form action="{{ route('admin.snapshots.restore', $snapshot) }}" method="POST" class="mt-4" onsubmit="return confirm('Restaurer ce snapshot complet ? Cette action écrase la version actuelle.')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:opacity-90 font-medium text-sm">
                Restaurer ce snapshot
            </button>
        </form>
    </div>

    <h2 class="text-lg font-semibold text-hub-text mb-3">Diff détaillé</h2>

    <div class="space-y-4">
        @forelse($snapshot->modifications as $modification)
            <div class="border border-hub-border rounded-lg overflow-hidden bg-hub-surface">
                <div class="px-4 py-3 border-b border-hub-border text-sm text-hub-text-sec">
                    Sous-séquence {{ $modification->sub_sequence }}
                </div>

                @php
                    $old = is_array($modification->old_values) ? $modification->old_values : [];
                    $new = is_array($modification->new_values) ? $modification->new_values : [];
                    $fields = array_values(array_unique(array_merge(array_keys($old), array_keys($new))));
                @endphp

                @if(empty($fields))
                    <div class="px-4 py-4 text-sm text-hub-text-sec">Aucun champ modifié.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-black/20 text-hub-text-sec">
                                <tr>
                                    <th class="px-4 py-3 text-left">Champ</th>
                                    <th class="px-4 py-3 text-left">Ancienne valeur</th>
                                    <th class="px-4 py-3 text-left">Nouvelle valeur</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-hub-border">
                                @foreach($fields as $field)
                                    <tr>
                                        <td class="px-4 py-3 text-hub-text font-medium">{{ $field }}</td>
                                        <td class="px-4 py-3 text-hub-text-sec align-top">
                                            <pre class="whitespace-pre-wrap break-words text-xs">{{ json_encode($old[$field] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                        <td class="px-4 py-3 text-hub-text-sec align-top">
                                            <pre class="whitespace-pre-wrap break-words text-xs">{{ json_encode($new[$field] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}</pre>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @empty
            <div class="p-4 rounded border border-hub-border bg-hub-surface text-hub-text-sec text-sm">
                Aucune modification enregistrée pour ce snapshot.
            </div>
        @endforelse
    </div>
</x-admin-layout>
