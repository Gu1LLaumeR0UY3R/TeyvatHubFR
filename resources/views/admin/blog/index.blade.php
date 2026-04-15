<x-admin-layout>
    <x-slot name="title">Blog — Admin</x-slot>

    <div x-data="{
            slugOpen: false,
            slugValue: '',
            openSlugModal() {
                this.slugOpen = true;
                this.$nextTick(() => this.$refs.slugInput?.focus());
            }
        }">

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

    <div class="mt-8 overflow-hidden rounded-xl border border-hub-border bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Slugs</h2>
                <p class="text-sm text-slate-500">Préfixes réutilisables pour remplir plus vite les articles.</p>
            </div>
            <button type="button" @click="openSlugModal()" class="rounded border border-slate-300 px-3 py-2 text-sm text-slate-700 hover:bg-slate-100">
                Ajouter un slug
            </button>
        </div>

        @if(session('slug_error'))
            <div class="mx-4 mt-4 rounded border border-red-300 bg-red-50 px-3 py-2 text-sm text-red-700">
                {{ session('slug_error') }}
            </div>
        @endif

        <div class="p-4">
            @if($slugPresets->count())
                <div class="flex flex-wrap gap-3">
                    @foreach($slugPresets as $slugPreset)
                        <div class="inline-flex items-center gap-2 rounded-full border border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700">
                            <span>{{ $slugPreset->slug_base }}</span>
                            <form method="POST" action="{{ route('admin.blog.slugs.destroy', $slugPreset) }}" onsubmit="return confirm('Supprimer ce slug ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-700">×</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-sm italic text-slate-500">Aucun slug enregistré.</div>
            @endif
        </div>
    </div>

    <template x-if="slugOpen">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60" @click.self="slugOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold text-slate-800">Ajouter un slug</h3>
                    <button type="button" @click="slugOpen = false" class="rounded border border-slate-300 px-3 py-1 text-sm text-slate-600 hover:bg-slate-100">Fermer</button>
                </div>

                <form method="POST" action="{{ route('admin.blog.slugs.store') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="slug_base" x-ref="slugInput" x-model="slugValue" placeholder="Ex: guide" class="w-full rounded border border-slate-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-hub-gold" />
                    <div class="flex justify-end">
                        <button type="submit" class="rounded bg-hub-gold px-4 py-2 text-sm text-hub-bg hover:opacity-90">Ajouter un slug</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    </div>
</x-admin-layout>
