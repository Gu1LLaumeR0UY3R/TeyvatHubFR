<x-admin-layout>
    <x-slot name="title">Utilisateurs — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Gestion des utilisateurs</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-800 text-green-100 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-hub-surface rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-black/20 text-hub-text-sec">
                <tr>
                    <th class="px-4 py-3 text-left">Nom</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Statut</th>
                    <th class="px-4 py-3 text-left">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-hub-border">
                @forelse($utilisateurs as $utilisateur)
                    <tr class="hub-surface-hover">
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
                    <tr><td colspan="4" class="px-4 py-6 text-center text-hub-text-sec">Aucun utilisateur</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $utilisateurs->links() }}</div>
</x-admin-layout>
