<x-admin-layout>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-hub-text">Calendrier des articles épinglés</h1>
        <a href="{{ route('admin.articles.index') }}"
           class="text-hub-text-sec hover:text-hub-text text-sm transition">← Retour aux articles</a>
    </div>

    <div class="bg-hub-surface border border-hub-border rounded-xl p-4">
        <div id="calendar"></div>
    </div>

    {{-- FullCalendar depuis jsDelivr CDN --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const typeColors = {
            patch_note:    '#3b82f6',
            annonce:       '#f59e0b',
            amelioration:  '#22c55e',
            questionnaire: '#a855f7',
        };

        const events = @json($pinned->map(fn($a) => [
            'id'    => $a->id,
            'title' => $a->title,
            'start' => $a->created_at ?? now(),
            'end'   => $a->pinned_until,
            'url'   => route('admin.articles.edit', $a),
            'type'  => $a->type,
        ]));

        const eventsWithColors = events.map(ev => ({
            ...ev,
            backgroundColor: typeColors[ev.type] ?? '#6b7280',
            borderColor:     typeColors[ev.type] ?? '#6b7280',
        }));

        const cal = new FullCalendar.Calendar(document.getElementById('calendar'), {
            locale:          'fr',
            initialView:     'dayGridMonth',
            headerToolbar: {
                left:   'prev,next today',
                center: 'title',
                right:  'dayGridMonth,listMonth',
            },
            events: eventsWithColors,
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                if (info.event.url) {
                    window.location.href = info.event.url;
                }
            },
            eventDidMount: function(info) {
                info.el.title = info.event.title;
            },
        });

        cal.render();
    });
    </script>
</x-admin-layout>
