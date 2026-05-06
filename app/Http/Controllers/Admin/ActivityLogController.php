<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /** Catégories disponibles par scope. */
    private const CATEGORIES = [
        'admin' => [
            'auth'          => ['label' => 'Authentification', 'icon' => '🔐'],
            'personnages'   => ['label' => 'Personnages',      'icon' => '🧙'],
            'armes'         => ['label' => 'Armes',            'icon' => '⚔️'],
            'blog'          => ['label' => 'Blog',             'icon' => '📝'],
            'ennemis'       => ['label' => 'Ennemis',          'icon' => '👾'],
            'animaux'       => ['label' => 'Animaux',          'icon' => '🐾'],
            'regions'       => ['label' => 'Nations & Régions','icon' => '🗺️'],
            'cuisine'       => ['label' => 'Cuisine',          'icon' => '🍜'],
            'materiaux'     => ['label' => 'Matériaux',        'icon' => '💎'],
            'evenements'    => ['label' => 'Événements',       'icon' => '📅'],
            'utilisateurs'  => ['label' => 'Utilisateurs',     'icon' => '👥'],
            'import'        => ['label' => 'Import',           'icon' => '📥'],
            'roles'         => ['label' => 'Rôles',            'icon' => '🏷️'],
            'general'       => ['label' => 'Général',          'icon' => '📋'],
        ],
        'public' => [
            'auth'    => ['label' => 'Authentification', 'icon' => '🔐'],
            'blog'    => ['label' => 'Blog',             'icon' => '📝'],
            'outils'  => ['label' => 'Outils',           'icon' => '🛠️'],
            'general' => ['label' => 'Général',          'icon' => '📋'],
        ],
    ];

    /**
     * Page d'accueil des logs — cards admin / public avec sous-cards par catégorie.
     */
    public function index(): View
    {
        $base = storage_path('logs/activity');
        $scopes = [];

        foreach (self::CATEGORIES as $scope => $categories) {
            $scopeData = [];
            foreach ($categories as $cat => $meta) {
                $dir   = "{$base}/{$scope}/{$cat}";
                $files = is_dir($dir) ? glob("{$dir}/*.log") : [];
                $dates = array_map(fn($f) => basename($f, '.log'), $files);
                rsort($dates);

                // Comptage des lignes du dernier fichier dispo
                $lastCount = 0;
                $lastDate  = $dates[0] ?? null;
                if ($lastDate && file_exists("{$dir}/{$lastDate}.log")) {
                    $lastCount = count(array_filter(
                        file("{$dir}/{$lastDate}.log", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []
                    ));
                }

                // Niveau le plus haut dans le dernier fichier
                $highestLevel = 'info';
                if ($lastDate && file_exists("{$dir}/{$lastDate}.log")) {
                    $content = file_get_contents("{$dir}/{$lastDate}.log");
                    if (str_contains($content, ' - CRITICAL - ')) $highestLevel = 'critical';
                    elseif (str_contains($content, ' - ERROR - '))    $highestLevel = 'error';
                    elseif (str_contains($content, ' - WARNING - '))  $highestLevel = 'warning';
                }

                $scopeData[$cat] = [
                    'label'        => $meta['label'],
                    'icon'         => $meta['icon'],
                    'dates'        => $dates,
                    'lastDate'     => $lastDate,
                    'lastCount'    => $lastCount,
                    'highestLevel' => $highestLevel,
                    'hasLogs'      => !empty($dates),
                ];
            }
            $scopes[$scope] = $scopeData;
        }

        return view('admin.logs.index', compact('scopes'));
    }

    /**
     * Affiche les logs d'un scope + catégorie spécifique avec filtres.
     */
    public function show(Request $request, string $scope, string $category): View
    {
        abort_unless(
            isset(self::CATEGORIES[$scope][$category]),
            404,
            'Catégorie de log introuvable.'
        );

        $meta = self::CATEGORIES[$scope][$category];
        $dir  = storage_path("logs/activity/{$scope}/{$category}");

        // ── Dates disponibles ─────────────────────────────────────────────
        $files = is_dir($dir) ? glob("{$dir}/*.log") : [];
        $dates = array_map(fn($f) => basename($f, '.log'), $files);
        rsort($dates);

        $selectedDate = $request->filled('date') && in_array($request->date, $dates, true)
            ? $request->date
            : ($dates[0] ?? now()->format('Y-m-d'));

        // Lecture multi-fichiers pour permettre un filtrage par période (début/fin).
        $records = [];
        foreach ($dates as $date) {
            $path = "{$dir}/{$date}.log";
            if (!file_exists($path)) {
                continue;
            }

            $fileLines = array_reverse(
                array_filter(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [])
            );

            foreach ($fileLines as $line) {
                $records[] = $this->parseLogLine($line, $date);
            }
        }

        // ── Filtres ────────────────────────────────────────────────────────
        $from = $this->parseDateTimeFilter($request->input('from'), false);
        $to   = $this->parseDateTimeFilter($request->input('to'), true);

        if ($request->filled('date')) {
            $dateFilter = $request->date;
            $records = array_values(array_filter($records, fn($r) => $r['date'] === $dateFilter));
        }

        if ($from) {
            $records = array_values(array_filter($records, function (array $record) use ($from) {
                return $record['dateTime'] && $record['dateTime']->greaterThanOrEqualTo($from);
            }));
        }

        if ($to) {
            $records = array_values(array_filter($records, function (array $record) use ($to) {
                return $record['dateTime'] && $record['dateTime']->lessThanOrEqualTo($to);
            }));
        }

        if ($request->filled('level')) {
            $level = strtoupper((string) $request->level);
            $records = array_values(array_filter($records, fn($r) => $r['level'] === $level));
        }

        if ($request->filled('event')) {
            $event = mb_strtolower((string) $request->event);
            $records = array_values(array_filter($records, fn($r) => str_contains(mb_strtolower($r['event']), $event)));
        }

        if ($request->filled('user')) {
            $user = mb_strtolower((string) $request->user);
            $records = array_values(array_filter($records, fn($r) => str_contains(mb_strtolower($r['user']), $user)));
        }

        if ($request->filled('ip')) {
            $ip = mb_strtolower((string) $request->ip);
            $records = array_values(array_filter($records, fn($r) => str_contains(mb_strtolower($r['ip']), $ip)));
        }

        if ($request->filled('search')) {
            $search = mb_strtolower((string) $request->search);
            $records = array_values(array_filter($records, function (array $record) use ($search) {
                return str_contains(mb_strtolower($record['payload']), $search)
                    || str_contains(mb_strtolower($record['line']), $search);
            }));
        }

        $total   = count($records);
        $perPage = 100;
        $page    = max(1, (int) $request->input('page', 1));
        $records = array_slice($records, ($page - 1) * $perPage, $perPage);
        $lines   = array_map(fn($r) => $r['line'], $records);
        $levels  = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];

        return view('admin.logs.show', compact(
            'lines', 'dates', 'selectedDate', 'total', 'page', 'perPage', 'levels',
            'scope', 'category', 'meta'
        ));
    }

    /**
     * Parse une ligne de log pour extraire les segments filtrables.
     *
     * Retour attendu (format ActivityLogService):
     * [06/05/2026 09:44:14] - admin:1 (admin@teyvathub.fr) - INFO - admin_login_success | {...} - IP:127.0.0.1
     */
    private function parseLogLine(string $line, string $fallbackDate): array
    {
        $dateTime = null;
        $userPart = '';
        $level = '';
        $message = '';
        $ip = '';

        if (preg_match('/^\[(?<dt>[^\]]+)\]\s-\s(?<user>.*?)\s-\s(?<level>[A-Z]+)\s-\s(?<message>.*)\s-\sIP:(?<ip>.+)$/', $line, $m)) {
            $userPart = trim((string) $m['user']);
            $level    = trim((string) $m['level']);
            $message  = trim((string) $m['message']);
            $ip       = trim((string) $m['ip']);
            $parsed = Carbon::createFromFormat('d/m/Y H:i:s', trim((string) $m['dt']));
            if ($parsed instanceof Carbon) {
                $dateTime = $parsed;
            }
        }

        $event = trim(strtok($message, '|') ?: $message);
        $payload = '';
        if (str_contains($message, '|')) {
            $parts = explode('|', $message);
            array_shift($parts);
            $payload = trim(implode('|', $parts));
        }

        $user = $userPart;
        if (preg_match('/\((?<email>[^)]+)\)/', $userPart, $u)) {
            $user = trim((string) $u['email']);
        }

        return [
            'line'     => $line,
            'date'     => $dateTime?->format('Y-m-d') ?? $fallbackDate,
            'dateTime' => $dateTime,
            'level'    => $level,
            'event'    => $event,
            'user'     => $user,
            'ip'       => $ip,
            'payload'  => $payload,
        ];
    }

    /**
     * Accepte "Y-m-d" (date), "d/m/Y H:i:s" et "Y-m-d\TH:i".
     */
    private function parseDateTimeFilter(?string $value, bool $endOfDay = false): ?Carbon
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            $parsed = Carbon::createFromFormat('Y-m-d', $value);
            if (!$parsed instanceof Carbon) {
                return null;
            }

            return $endOfDay ? $parsed->endOfDay() : $parsed->startOfDay();
        }

        if (preg_match('/^\d{2}\/\d{2}\/\d{4}\s\d{2}:\d{2}:\d{2}$/', $value)) {
            $parsed = Carbon::createFromFormat('d/m/Y H:i:s', $value);
            return $parsed instanceof Carbon ? $parsed : null;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value)) {
            $parsed = Carbon::createFromFormat('Y-m-d\TH:i', $value);
            return $parsed instanceof Carbon ? $parsed : null;
        }

        return null;
    }
}


