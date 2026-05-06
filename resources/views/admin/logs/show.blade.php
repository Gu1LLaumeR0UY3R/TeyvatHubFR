<x-admin-layout>
    {{-- ── Fil d'ariane ─────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 text-sm text-hub-text-sec mb-6">
        <a href="{{ route('admin.logs.index') }}" class="hover:text-hub-text">Logs</a>
        <span>/</span>
        <span class="{{ $scope === 'admin' ? 'text-violet-400' : 'text-sky-400' }} font-medium uppercase">{{ $scope }}</span>
        <span>/</span>
        <span class="text-hub-text font-semibold">{{ $meta['icon'] }} {{ $meta['label'] }}</span>
    </div>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-bold text-hub-text">
            {{ $meta['icon'] }} {{ $meta['label'] }}
            <span class="text-base font-normal text-hub-text-sec ml-2">— {{ $scope }}</span>
        </h1>
        <span class="text-hub-text-sec text-sm">{{ number_format($total) }} ligne(s) affichée(s)</span>
    </div>

    {{-- ── Filtres ────────────────────────────────────────────────────────── --}}
    <form method="GET"
          class="bg-hub-surface border border-hub-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Date</label>
            <select name="date"
                    class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                @forelse($dates as $d)
                    <option value="{{ $d }}" @selected($d === $selectedDate)>
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $d)->format('d/m/Y') }}
                    </option>
                @empty
                    <option value="{{ $selectedDate }}">{{ now()->format('d/m/Y') }}</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Début</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Fin</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Niveau</label>
            <select name="level"
                    class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                <option value="">Tous</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" @selected(request('level') === $lvl)>
                        {{ strtoupper($lvl) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Événement</label>
            <input type="text" name="event" value="{{ request('event') }}"
                   placeholder="admin_login_success"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text w-56">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Utilisateur</label>
            <input type="text" name="user" value="{{ request('user') }}"
                   placeholder="admin@teyvathub.fr"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text w-56">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Adresse IP</label>
            <input type="text" name="ip" value="{{ request('ip') }}"
                   placeholder="127.0.0.1"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text w-40">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Recherche libre (payload JSON)</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="wrong_password, action, metadata..."
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text w-56">
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-4 py-1.5 bg-sky-600 text-white rounded text-sm font-semibold hover:bg-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-400/70">
                Filtrer
            </button>
            <a href="{{ route('admin.logs.show', ['scope' => $scope, 'category' => $category]) }}"
               class="px-4 py-1.5 border border-hub-border text-hub-text-sec rounded text-sm hover:bg-hub-surface-hover">
                Reset
            </a>
        </div>
    </form>

    {{-- ── Tableau des lignes ─────────────────────────────────────────────── --}}
    <div class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden">
        @if($lines)
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <tbody class="divide-y divide-hub-border">
                        @foreach($lines as $line)
                            @php
                                $isCritical = str_contains($line, ' - CRITICAL - ');
                                $isError    = !$isCritical && str_contains($line, ' - ERROR - ');
                                $isWarning  = !$isCritical && !$isError && str_contains($line, ' - WARNING - ');
                            @endphp
                            <tr class="
                                @if($isCritical) bg-red-950
                                @elseif($isError) bg-red-950/40
                                @elseif($isWarning) bg-yellow-950/30
                                @endif
                                hover:bg-hub-surface-hover">
                                <td class="px-4 py-2 text-hub-text whitespace-pre-wrap break-all leading-relaxed">
                                    {{ $line }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination ──────────────────────────────────────────────── --}}
            @if($total > $perPage)
                <div class="px-4 py-3 border-t border-hub-border flex items-center gap-3 text-sm text-hub-text-sec">
                    @if($page > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                           class="px-3 py-1 border border-hub-border rounded hover:bg-hub-surface-hover text-hub-text">
                            ← Précédent
                        </a>
                    @endif
                    <span>Page {{ $page }} / {{ ceil($total / $perPage) }}</span>
                    @if($page * $perPage < $total)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                           class="px-3 py-1 border border-hub-border rounded hover:bg-hub-surface-hover text-hub-text">
                            Suivant →
                        </a>
                    @endif
                </div>
            @endif
        @else
            <div class="px-4 py-16 text-center text-hub-text-sec">
                Aucun log pour cette sélection{{ request('search') || request('level') || request('event') || request('user') || request('ip') || request('from') || request('to') ? ' avec ces filtres' : '' }}.
            </div>
        @endif
    </div>

    {{-- ── Retour ─────────────────────────────────────────────────────────── --}}
    <div class="mt-6">
        <a href="{{ route('admin.logs.index') }}"
           class="text-sm text-hub-text-sec hover:text-hub-text">
            ← Retour aux logs
        </a>
    </div>
</x-admin-layout>
