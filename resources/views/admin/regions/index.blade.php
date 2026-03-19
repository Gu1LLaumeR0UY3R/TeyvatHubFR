<x-admin-layout>
    <x-slot name="title">Régions — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des régions</h1>
        <a href="{{ route('admin.regions.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouveau</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-hub-surface rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/20 text-hub-text-sec">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hub-border">
                @forelse($regions as $region)
                    <tr class="hub-surface-hover">
                        <td class="px-4 py-3">{{ $region->nom_region }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('admin.regions.edit', $region) }}" class="text-hub-gold hover:underline">Modifier</a>
                            <form action="{{ route('admin.regions.destroy', $region) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="px-4 py-6 text-center text-hub-text-sec">Aucune région</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $regions->links() }}</div>
</x-admin-layout>
