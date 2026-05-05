<x-admin-layout>
    @php
        $typeLabels = [
            'patch_note'    => ['label' => 'Patch Note',    'color' => 'bg-blue-900/50 text-blue-300 border-blue-700/50'],
            'annonce'       => ['label' => 'Annonce',       'color' => 'bg-hub-primary/20 text-hub-gold border-hub-primary/30'],
            'amelioration'  => ['label' => 'Amélioration',  'color' => 'bg-green-900/50 text-green-300 border-green-700/50'],
            'questionnaire' => ['label' => 'Questionnaire', 'color' => 'bg-purple-900/50 text-purple-300 border-purple-700/50'],
        ];
    @endphp

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-text">Articles</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.articles.calendar') }}"
               class="px-4 py-2 bg-hub-surface border border-hub-border hover:border-hub-primary/50 text-hub-text-sec hover:text-hub-text text-sm font-medium rounded-lg transition">
                📅 Calendrier
            </a>
            <a href="{{ route('admin.articles.create') }}"
               class="px-4 py-2 bg-hub-primary hover:bg-hub-primary-hover text-white text-sm font-medium rounded-lg transition">
                + Nouvel article
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-green-900/40 border border-green-700 text-green-300 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filtres --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="type" onchange="this.form.submit()"
                class="bg-hub-surface border border-hub-border text-hub-text-sec text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
            <option value="">Tous les types</option>
            @foreach($typeLabels as $value => $cfg)
                <option value="{{ $value }}" {{ request('type') === $value ? 'selected' : '' }}>{{ $cfg['label'] }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
                class="bg-hub-surface border border-hub-border text-hub-text-sec text-sm rounded-lg px-3 py-2 focus:ring-hub-primary focus:border-hub-primary outline-none">
            <option value="">Tous les statuts</option>
            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Brouillon</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Publié</option>
            <option value="archived"  {{ request('status') === 'archived'  ? 'selected' : '' }}>Archivé</option>
        </select>
        @if(request('type') || request('status'))
            <a href="{{ route('admin.articles.index') }}" class="px-3 py-2 text-sm text-hub-text-sec hover:text-hub-text">× Effacer</a>
        @endif
    </form>

    <div class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden">
        @if($articles->isEmpty())
            <p class="text-hub-text-sec text-center py-12">Aucun article pour l'instant.</p>
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-hub-border">
                    <tr>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Titre</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Type</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Statut</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Épinglé</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Auteur</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Date</th>
                        <th class="text-right px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hub-border">
                    @foreach($articles as $article)
                        @php
                            $tc = $typeLabels[$article->type] ?? ['label' => $article->type, 'color' => 'bg-hub-surface-hover text-hub-text-sec border-hub-border'];
                        @endphp
                        <tr class="hover:bg-hub-surface-hover transition">
                            <td class="px-4 py-3 text-hub-text font-medium max-w-xs truncate">
                                {{ $article->title }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs border rounded-full {{ $tc['color'] }}">{{ $tc['label'] }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($article->status === 'published')
                                    <span class="px-2 py-0.5 text-xs bg-green-900/50 text-green-400 border border-green-700/50 rounded-full">Publié</span>
                                @elseif($article->status === 'archived')
                                    <span class="px-2 py-0.5 text-xs bg-orange-900/50 text-orange-400 border border-orange-700/50 rounded-full">Archivé</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs bg-hub-surface-hover text-hub-text-sec border border-hub-border rounded-full">Brouillon</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($article->is_pinned)
                                    <span class="text-hub-gold" title="{{ $article->pinned_until ? 'jusqu\'au '.$article->pinned_until->format('d/m/Y') : '' }}">📌</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-hub-text-sec">{{ $article->admin?->pseudo ?? '—' }}</td>
                            <td class="px-4 py-3 text-hub-text-sec">
                                {{ $article->published_at?->format('d/m/Y') ?? $article->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.articles.edit', $article) }}"
                                   class="text-hub-text-sec hover:text-hub-primary transition text-xs mr-3">Éditer</a>
                                <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                                      class="inline"
                                      onsubmit="return confirm('Supprimer « {{ addslashes($article->title) }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-400 transition text-xs">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-4 border-t border-hub-border">
                {{ $articles->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
