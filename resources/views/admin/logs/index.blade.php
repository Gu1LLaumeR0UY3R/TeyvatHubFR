<x-admin-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-text">Logs d'activité</h1>
        <span class="text-hub-text-sec text-sm">{{ $total }} ligne(s) affichée(s)</span>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-hub-surface border border-hub-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Date</label>
            <select name="date" class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                @foreach($dates as $d)
                    <option value="{{ $d }}" @selected($d === $selectedDate)>
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $d)->format('d/m/Y') }}
                    </option>
                @endforeach
                @if(!$dates)
                    <option value="{{ $selectedDate }}">{{ now()->format('d/m/Y') }}</option>
                @endif
            </select>
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Niveau</label>
            <select name="level" class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                <option value="">Tous</option>
                @foreach($levels as $level)
                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ strtoupper($level) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Recherche</label>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="email, IP, action…"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text w-56">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-1.5 bg-hub-accent text-white rounded text-sm font-medium hover:opacity-90">Filtrer</button>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-1.5 border border-hub-border text-hub-text-sec rounded text-sm hover:bg-hub-surface-hover">Reset</a>
        </div>
    </form>

    {{-- Lignes de log --}}
    <div class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden">
        @if($lines)
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <tbody class="divide-y divide-hub-border">
                        @foreach($lines as $line)
                            @php
                                $isError    = str_contains($line, ' - ERROR - ')    || str_contains($line, ' - CRITICAL - ');
                                $isWarning  = str_contains($line, ' - WARNING - ');
                                $isCritical = str_contains($line, ' - CRITICAL - ');
                            @endphp
                            <tr class="@if($isCritical) bg-red-950 @elseif($isError) bg-red-950/40 @elseif($isWarning) bg-yellow-950/30 @endif hover:bg-hub-surface-hover">
                                <td class="px-4 py-2 text-hub-text whitespace-pre-wrap break-all leading-relaxed">{{ $line }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination manuelle --}}
            @if($total > $perPage)
                <div class="px-4 py-3 border-t border-hub-border flex items-center gap-3 text-sm text-hub-text-sec">
                    @if($page > 1)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                           class="px-3 py-1 border border-hub-border rounded hover:bg-hub-surface-hover text-hub-text">← Précédent</a>
                    @endif
                    <span>Page {{ $page }} / {{ ceil($total / $perPage) }}</span>
                    @if($page * $perPage < $total)
                        <a href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                           class="px-3 py-1 border border-hub-border rounded hover:bg-hub-surface-hover text-hub-text">Suivant →</a>
                    @endif
                </div>
            @endif
        @else
            <div class="px-4 py-16 text-center text-hub-text-sec">
                Aucun log pour cette date{{ request('search') || request('level') ? ' avec ces filtres' : '' }}.
            </div>
        @endif
    </div>
</x-admin-layout>

    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-text">Logs d'activité</h1>
        <span class="text-hub-text-sec text-sm">{{ $logs->total() }} entrées</span>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-hub-surface border border-hub-border rounded-xl p-4 mb-6 grid grid-cols-2 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Niveau</label>
            <select name="level" class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                <option value="">Tous</option>
                @foreach($levels as $level)
                    <option value="{{ $level }}" @selected(request('level') === $level)>{{ ucfirst($level) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Action</label>
            <input type="text" name="action" value="{{ request('action') }}"
                   placeholder="ex: login_failed"
                   class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Type utilisateur</label>
            <select name="user_type" class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                <option value="">Tous</option>
                <option value="admin" @selected(request('user_type') === 'admin')>Admin</option>
                <option value="user"  @selected(request('user_type') === 'user')>Joueur</option>
            </select>
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Utilisateur (email/pseudo)</label>
            <input type="text" name="user_label" value="{{ request('user_label') }}"
                   class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">IP</label>
            <input type="text" name="ip_filter" value="{{ request('ip_filter') }}"
                   class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Du</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>
        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Au</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-1.5 bg-hub-accent text-white rounded text-sm font-medium hover:opacity-90">Filtrer</button>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-1.5 border border-hub-border text-hub-text-sec rounded text-sm hover:bg-hub-surface-hover">Reset</a>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-hub-surface border border-hub-border rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-hub-bg border-b border-hub-border">
                    <tr>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Date</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Niveau</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Action</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Utilisateur</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">IP</th>
                        <th class="text-left px-4 py-3 text-hub-text-sec font-medium">Détails</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-hub-border">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-hub-surface-hover @if($log->level === 'critical') bg-red-950 @elseif($log->level === 'error') bg-red-950/40 @elseif($log->level === 'warning') bg-yellow-950/30 @endif">
                            <td class="px-4 py-2 text-hub-text-sec whitespace-nowrap">
                                {{ $log->created_at->format('d/m/y H:i:s') }}
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log->levelBadgeClass() }}">
                                    {{ $log->level }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-hub-text font-mono text-xs">{{ $log->action }}</td>
                            <td class="px-4 py-2 text-hub-text-sec text-xs">
                                @if($log->user_type)
                                    <span class="text-hub-text">{{ $log->user_label ?? '—' }}</span>
                                    <span class="text-hub-text-sec">({{ $log->user_type }})</span>
                                @else
                                    <span class="text-hub-text-sec italic">Guest</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-hub-text-sec font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-4 py-2">
                                @if($log->properties)
                                    <details class="text-xs text-hub-text-sec">
                                        <summary class="cursor-pointer hover:text-hub-text">Voir</summary>
                                        <pre class="mt-1 p-2 bg-hub-bg rounded text-xs overflow-x-auto max-w-xs">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @else
                                    <span class="text-hub-text-sec">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-hub-text-sec">Aucun log trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-4 py-3 border-t border-hub-border">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>
