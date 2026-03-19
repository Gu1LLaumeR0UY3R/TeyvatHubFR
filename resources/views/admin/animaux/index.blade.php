<x-admin-layout>
    <x-slot name="title">Animaux — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des animaux</h1>
        <a href="{{ route('admin.animaux.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouveau</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-hub-surface rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/20 text-hub-text-sec">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hub-border">
                @forelse($animaux as $animal)
                    <tr class="hub-surface-hover">
                        <td class="px-4 py-3">{{ $animal->nom_animal }}</td>
                        <td class="px-4 py-3">{{ $animal->typeAnimal?->libelle_TAnimal ?? '—' }}</td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('admin.animaux.edit', $animal) }}" class="text-hub-gold hover:underline">Modifier</a>
                            <form action="{{ route('admin.animaux.destroy', $animal) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:underline">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-hub-text-sec">Aucun animal</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $animaux->links() }}</div>
</x-admin-layout>
