<x-admin-layout>
    <x-slot name="title">Blog — Admin</x-slot>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-gold">Blog</h1>
        <a href="{{ route('admin.blog.create') }}" class="px-4 py-2 bg-hub-gold text-hub-bg rounded hover:opacity-90">+ Nouvel article</a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded border border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-hub-border bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-slate-700">Titre</th>
                    <th class="px-4 py-2 text-left font-semibold text-slate-700">Statut</th>
                    <th class="px-4 py-2 text-left font-semibold text-slate-700">Publication</th>
                    <th class="px-4 py-2 text-right font-semibold text-slate-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($articles as $article)
                    <tr>
                        <td class="px-4 py-2 text-slate-800">{{ $article->titre_article }}</td>
                        <td class="px-4 py-2">
                            <span class="rounded px-2 py-1 text-xs {{ $article->statut === 'publie' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $article->statut === 'publie' ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-slate-600">{{ optional($article->date_publication)->format('d/m/Y H:i') ?: '-' }}</td>
                        <td class="px-4 py-2">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.blog.edit', $article) }}" class="rounded border border-slate-300 px-2 py-1 text-xs text-slate-700 hover:bg-slate-100">Éditer</a>
                                <form method="POST" action="{{ route('admin.blog.destroy', $article) }}" onsubmit="return confirm('Supprimer cet article ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded border border-red-300 px-2 py-1 text-xs text-red-700 hover:bg-red-50">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Aucun article blog.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $articles->links() }}
    </div>
</x-admin-layout>
