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
