<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.articles.edit', $article) }}" class="text-hub-text-sec hover:text-hub-text text-sm">&larr; Retour à l'article</a>
    </div>

    <div class="flex items-start justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-hub-text">Résultats du questionnaire</h1>
            <p class="text-hub-text-sec mt-1">{{ $article->title }}</p>
        </div>
        <div class="text-right">
            <div class="text-3xl font-bold text-hub-text">{{ $totalResponses }}</div>
            <div class="text-hub-text-sec text-sm">réponses</div>
            @if($survey->isClosed())
                <span class="mt-1 inline-block px-2 py-0.5 bg-gray-700 text-gray-300 rounded text-xs">Fermé</span>
            @else
                <span class="mt-1 inline-block px-2 py-0.5 bg-green-800 text-green-300 rounded text-xs">Ouvert</span>
            @endif
        </div>
    </div>

    @if(!$results)
        <div class="text-hub-text-sec text-center py-16">Aucun résultat à afficher.</div>
    @else
        <div class="space-y-8" id="charts-container">
            @foreach($results as $i => $result)
                <div class="bg-hub-surface border border-hub-border rounded-xl p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-hub-text font-semibold">{{ $result['label'] }}</h2>
                        <span class="text-hub-text-sec text-xs">{{ $result['count'] }} réponse(s)</span>
                    </div>

                    @if($result['type'] === 'qcm' || $result['type'] === 'checkbox')
                        @if(!empty($result['tally']))
                            <div class="relative h-64">
                                <canvas id="chart-{{ $i }}"></canvas>
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                                @foreach($result['tally'] as $option => $count)
                                    <div class="flex justify-between px-3 py-1.5 bg-hub-bg rounded border border-hub-border">
                                        <span class="text-hub-text">{{ $option ?: '(vide)' }}</span>
                                        <span class="text-hub-text-sec font-mono">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-hub-text-sec text-sm">Aucune réponse.</p>
                        @endif

                    @elseif($result['type'] === 'rating')
                        <div class="flex items-center gap-6 mb-4">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-hub-text">{{ $result['average'] }}</div>
                                <div class="text-hub-text-sec text-xs">/ 5 moyenne</div>
                            </div>
                            <div class="flex-1 relative h-48">
                                <canvas id="chart-{{ $i }}"></canvas>
                            </div>
                        </div>

                    @elseif($result['type'] === 'boolean')
                        @php $total = $result['yes'] + $result['no']; @endphp
                        <div class="flex items-center gap-6">
                            <div class="relative h-48 w-48">
                                <canvas id="chart-{{ $i }}"></canvas>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    <span class="text-hub-text">Oui : {{ $result['yes'] }}</span>
                                    <span class="text-hub-text-sec">({{ $total > 0 ? round($result['yes']/$total*100) : 0 }}%)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                    <span class="text-hub-text">Non : {{ $result['no'] }}</span>
                                    <span class="text-hub-text-sec">({{ $total > 0 ? round($result['no']/$total*100) : 0 }}%)</span>
                                </div>
                            </div>
                        </div>

                    @elseif($result['type'] === 'text')
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @forelse($result['responses'] as $resp)
                                <div class="px-3 py-2 bg-hub-bg border border-hub-border rounded text-sm text-hub-text">
                                    {{ $resp }}
                                </div>
                            @empty
                                <p class="text-hub-text-sec text-sm">Aucune réponse textuelle.</p>
                            @endforelse
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
    const PALETTE = [
        '#6366f1','#8b5cf6','#a78bfa','#38bdf8','#34d399',
        '#fbbf24','#f87171','#fb923c','#e879f9','#67e8f9'
    ];

    const results = @json($results);

    results.forEach((result, i) => {
        const canvas = document.getElementById(`chart-${i}`);
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#9ca3af' } } },
            scales: {},
        };

        if (result.type === 'qcm' || result.type === 'checkbox') {
            const labels = Object.keys(result.tally || {});
            const data   = Object.values(result.tally || {});
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Réponses', data, backgroundColor: PALETTE }],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
                        x: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
                    },
                },
            });
        } else if (result.type === 'rating') {
            const dist = result.distribution || {};
            const labels = ['1','2','3','4','5'];
            const data   = labels.map(l => dist[parseInt(l)] || 0);
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{ label: 'Votes', data, backgroundColor: PALETTE[0] }],
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { ticks: { color: '#9ca3af' }, grid: { color: '#374151' } },
                        x: { ticks: { color: '#9ca3af' } },
                    },
                },
            });
        } else if (result.type === 'boolean') {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Oui', 'Non'],
                    datasets: [{
                        data: [result.yes, result.no],
                        backgroundColor: ['#22c55e', '#ef4444'],
                    }],
                },
                options: { ...commonOptions, plugins: { legend: { labels: { color: '#9ca3af' } } } },
            });
        }
    });
</script>
