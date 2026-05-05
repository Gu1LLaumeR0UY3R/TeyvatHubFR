<x-admin-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-text">Modération des commentaires</h1>
        <span class="text-hub-text-sec text-sm">{{ $comments->total() }} commentaire(s)</span>
    </div>

    {{-- Onglets statuts --}}
    <div class="flex gap-2 mb-6">
        @foreach(['pending' => 'En attente', 'approved' => 'Approuvés', 'rejected' => 'Rejetés'] as $s => $label)
            <a href="{{ route('admin.comments.index', ['status' => $s]) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium border transition
                      {{ $status === $s
                          ? 'bg-hub-accent text-white border-hub-accent'
                          : 'bg-hub-surface border-hub-border text-hub-text-sec hover:bg-hub-surface-hover' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-900/40 border border-green-700 rounded text-green-300 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4">
        @forelse($comments as $comment)
            <div class="bg-hub-surface border border-hub-border rounded-xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-hub-text font-medium text-sm">{{ $comment->display_name }}</span>
                            <span class="text-hub-text-sec text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                            <span class="text-hub-text-sec text-xs font-mono">{{ $comment->ip_address }}</span>
                        </div>
                        <div class="text-sm text-hub-text-sec mb-2">
                            Article : <a href="{{ route('articles.show', $comment->article) }}" target="_blank"
                                         class="text-hub-accent hover:underline">{{ $comment->article->title }}</a>
                        </div>
                        <p class="text-hub-text text-sm bg-hub-bg rounded px-3 py-2 border border-hub-border">
                            {{ $comment->content }}
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 shrink-0">
                        @if($comment->status !== 'approved')
                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">
                                @csrf @method('PATCH')
                                <button class="w-full px-3 py-1.5 bg-green-700 text-white rounded text-xs font-medium hover:bg-green-600">
                                    Approuver
                                </button>
                            </form>
                        @endif
                        @if($comment->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">
                                @csrf @method('PATCH')
                                <button class="w-full px-3 py-1.5 bg-yellow-700 text-white rounded text-xs font-medium hover:bg-yellow-600">
                                    Rejeter
                                </button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}"
                              onsubmit="return confirm('Supprimer ce commentaire ?')">
                            @csrf @method('DELETE')
                            <button class="w-full px-3 py-1.5 bg-red-800 text-white rounded text-xs font-medium hover:bg-red-700">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-hub-text-sec py-16">
                Aucun commentaire avec ce statut.
            </div>
        @endforelse
    </div>

    @if($comments->hasPages())
        <div class="mt-6">{{ $comments->links() }}</div>
    @endif
</x-admin-layout>
