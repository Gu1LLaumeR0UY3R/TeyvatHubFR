<x-admin-layout>
    <x-slot name="title">Artefacts — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des artefacts</h1>
        <a href="{{ route('admin.artefacts.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouveau</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-800 text-red-100 rounded">{{ session('error') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.artefacts.index') }}" class="mb-4 flex items-end gap-3">
        <div>
            <label for="sort" class="block text-hub-text-sec text-xs mb-1">Tri rapide</label>
            <select id="sort" name="sort" class="bg-hub-surface border border-hub-border rounded px-3 py-2 text-hub-text text-sm">
                <option value="nom_asc" @selected($sort === 'nom_asc')>Nom (A-Z)</option>
                <option value="nom_desc" @selected($sort === 'nom_desc')>Nom (Z-A)</option>
                <option value="rarete_asc" @selected($sort === 'rarete_asc')>Rareté croissante</option>
                <option value="rarete_desc" @selected($sort === 'rarete_desc')>Rareté décroissante</option>
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-hub-surface-hover border border-hub-border rounded text-hub-text text-sm hover:opacity-90">
            Trier
        </button>
    </form>

    <div class="bg-hub-surface rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/20 text-hub-text-sec">
                <tr>
                    <th class="px-3 py-3 w-14"></th>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Rareté</th>
                    <th class="px-4 py-3 text-left">Bonus 2P</th>
                    <th class="px-4 py-3 text-left">Bonus 4P</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hub-border">
                @forelse($artefacts as $artefact)
                    @php
                        $photo = $artefact->photos->first();
                        $icon = $photo?->source_url ?: ($photo?->chemin_photo ? asset('storage/'.$photo->chemin_photo) : asset('images/placeholder.webp'));
                    @endphp
                    <tr class="hub-surface-hover">
                        <td class="px-3 py-2 text-center">
                            <img src="{{ $icon }}" alt="{{ $artefact->nom_artefact }}" class="w-10 h-10 object-cover rounded-lg bg-hub-surface-hover mx-auto">
                        </td>
                        <td class="px-4 py-3">{{ $artefact->nom_artefact }}</td>
                        <td class="px-4 py-3">{{ $artefact->rareté?->libelle_rareté ?? '—' }}</td>
                        <td class="px-4 py-3 text-hub-text-sec">{{ \Illuminate\Support\Str::limit($artefact->bonus_2p, 70) ?: '—' }}</td>
                        <td class="px-4 py-3 text-hub-text-sec">{{ \Illuminate\Support\Str::limit($artefact->bonus_4p, 70) ?: '—' }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('admin.artefacts.edit', $artefact) }}" class="text-hub-gold hover:underline">Modifier</a>
                            <form action="{{ route('admin.artefacts.destroy', $artefact) }}" method="POST" onsubmit="return confirm('Supprimer cet artefact ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-hub-text-sec">Aucun artefact</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $artefacts->links() }}</div>
</x-admin-layout>
