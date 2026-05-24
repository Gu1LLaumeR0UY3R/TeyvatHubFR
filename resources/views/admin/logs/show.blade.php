<x-admin-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">

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
    <form id="log-filter-form" method="GET"
          class="bg-hub-surface border border-hub-border rounded-xl p-4 mb-6 flex flex-wrap gap-3 items-end">

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Date</label>
                <select id="single-date-filter" name="date"
                    class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
                <option value="" @selected($selectedDate === '')>
                    Toutes les dates
                </option>
                @forelse($dates as $d)
                    <option value="{{ $d }}" @selected($d === $selectedDate)>
                        {{ \Carbon\Carbon::createFromFormat('Y-m-d', $d)->format('d/m/Y') }}
                    </option>
                @empty
                    <option value="" selected>Toutes les dates</option>
                @endforelse
            </select>
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Début</label>
            <input id="from-date" type="date" name="from" value="{{ request('from') }}"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>

        <div>
            <label class="block text-xs text-hub-text-sec mb-1">Fin</label>
            <input id="to-date" type="date" name="to" value="{{ request('to') }}"
                   class="bg-hub-bg border border-hub-border rounded px-2 py-1.5 text-sm text-hub-text">
        </div>

        <div class="w-full mt-2">
            <p class="text-xs text-hub-text-sec mb-2">
                Calendrier: clic = 1 jour, glisser = plage. Quand une plage est choisie, le filtre Date est ignore.
            </p>
            <div id="logs-range-calendar" class="bg-hub-bg border border-hub-border rounded-lg p-2"></div>
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
                                $isRed    = str_contains($line, ' - CRITICAL - ')  || str_contains($line, ' - EXTREME - ')
                                         || str_contains($line, ' - EMERGENCY - ') || str_contains($line, ' - ALERT - ')
                                         || str_contains($line, ' - ERROR - ');
                                $isOrange = !$isRed && (str_contains($line, ' - WARNING - ') || str_contains($line, ' - DANGER - '));
                                $isGreen  = !$isRed && !$isOrange && str_contains($line, ' - SUCCESS - ');
                                $isBlue   = !$isRed && !$isOrange && !$isGreen && str_contains($line, ' - INFO - ');

                                $colored = preg_replace_callback(
                                    '/ - (ERROR|CRITICAL|ALERT|EMERGENCY|EXTREME|WARNING|DANGER|SUCCESS|INFO|NOTICE|DEBUG) - /',
                                    function ($m) {
                                        return match ($m[1]) {
                                            'EXTREME', 'EMERGENCY', 'ALERT', 'CRITICAL'
                                                => ' - <span class="font-bold text-red-300 bg-red-900/60 px-1 rounded">' . $m[1] . '</span> - ',
                                            'ERROR'
                                                => ' - <span class="font-bold text-red-400 bg-red-900/40 px-1 rounded">' . $m[1] . '</span> - ',
                                            'WARNING', 'DANGER'
                                                => ' - <span class="font-bold text-orange-300 bg-orange-900/50 px-1 rounded">' . $m[1] . '</span> - ',
                                            'SUCCESS'
                                                => ' - <span class="font-bold text-green-300 bg-green-900/40 px-1 rounded">' . $m[1] . '</span> - ',
                                            'INFO'
                                                => ' - <span class="font-bold text-sky-300 bg-sky-900/30 px-1 rounded">' . $m[1] . '</span> - ',
                                            'NOTICE'
                                                => ' - <span class="font-bold text-blue-300">' . $m[1] . '</span> - ',
                                            'DEBUG'
                                                => ' - <span class="text-gray-500">' . $m[1] . '</span> - ',
                                            default => $m[0],
                                        };
                                    },
                                    e($line)
                                );
                            @endphp
                            <tr class="
                                @if($isRed) bg-red-900 border-l-4 border-red-500
                                @elseif($isOrange) bg-orange-900 border-l-4 border-orange-500
                                @elseif($isGreen) bg-green-900 border-l-4 border-green-500
                                @elseif($isBlue) bg-sky-900 border-l-4 border-sky-500
                                @else border-l-4 border-transparent
                                @endif
                                hover:opacity-90 transition-opacity">
                                <td class="pl-0 pr-3 py-1 text-xs whitespace-pre-wrap break-all leading-snug
                                    @if($isRed) text-red-100
                                    @elseif($isOrange) text-orange-100
                                    @elseif($isGreen) text-green-100
                                    @elseif($isBlue) text-sky-100
                                    @else text-hub-text
                                    @endif">
                                    {!! $colored !!}
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

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('logs-range-calendar');
            const form = document.getElementById('log-filter-form');
            const singleDateSelect = document.getElementById('single-date-filter');
            const fromInput = document.getElementById('from-date');
            const toInput = document.getElementById('to-date');

            if (!calendarEl || !form || !singleDateSelect || !fromInput || !toInput) {
                return;
            }

            const pad = (n) => String(n).padStart(2, '0');
            const toYmd = (date) => date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate());
            const addDays = (value, days) => {
                const date = new Date(value + 'T00:00:00');
                date.setDate(date.getDate() + days);
                return toYmd(date);
            };

            const syncDateBounds = () => {
                if (fromInput.value) {
                    toInput.min = fromInput.value;
                    if (toInput.value && toInput.value < fromInput.value) {
                        toInput.value = fromInput.value;
                    }
                } else {
                    toInput.removeAttribute('min');
                }
            };

            let calendarSelection = null;

            const updateCalendarHighlight = (calendar) => {
                const existing = calendar.getEventById('range-selected');
                if (existing) {
                    existing.remove();
                }

                if (!fromInput.value) {
                    calendarSelection = null;
                    return;
                }

                const endValue = toInput.value || fromInput.value;
                calendarSelection = { from: fromInput.value, to: endValue };

                calendar.addEvent({
                    id: 'range-selected',
                    start: calendarSelection.from,
                    end: addDays(calendarSelection.to, 1),
                    display: 'background',
                    backgroundColor: '#0ea5e9',
                });
            };

            const initialDate = fromInput.value || toInput.value || singleDateSelect.value || '{{ now()->format('Y-m-d') }}';

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate,
                locale: 'fr',
                selectable: true,
                selectMirror: true,
                height: 'auto',
                firstDay: 1,
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth'
                },
                dateClick: function (info) {
                    fromInput.value = info.dateStr;
                    toInput.value = info.dateStr;
                    singleDateSelect.value = '';
                    syncDateBounds();
                    updateCalendarHighlight(calendar);
                },
                select: function (info) {
                    const from = info.startStr;
                    const toExclusive = info.endStr;
                    const to = addDays(toExclusive, -1);

                    fromInput.value = from;
                    toInput.value = to;
                    singleDateSelect.value = '';
                    syncDateBounds();
                    updateCalendarHighlight(calendar);
                }
            });

            calendar.render();
            syncDateBounds();
            updateCalendarHighlight(calendar);

            fromInput.addEventListener('change', function () {
                syncDateBounds();
                if (fromInput.value || toInput.value) {
                    singleDateSelect.value = '';
                }
                updateCalendarHighlight(calendar);
            });

            toInput.addEventListener('change', function () {
                syncDateBounds();
                if (fromInput.value || toInput.value) {
                    singleDateSelect.value = '';
                }
                updateCalendarHighlight(calendar);
            });

            singleDateSelect.addEventListener('change', function () {
                if (singleDateSelect.value !== '') {
                    fromInput.value = '';
                    toInput.value = '';
                }
                syncDateBounds();
                updateCalendarHighlight(calendar);
            });

            form.addEventListener('submit', function (event) {
                if (fromInput.value && toInput.value && toInput.value < fromInput.value) {
                    event.preventDefault();
                    toInput.value = fromInput.value;
                }
            });
        });
    </script>
</x-admin-layout>
