<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Affiche les logs d'activité depuis les fichiers storage/logs/activity/.
     * Accès réservé via middleware admin.can:manage_logs (super_admin uniquement).
     */
    public function index(Request $request): View
    {
        $dir = storage_path('logs/activity');

        // ── Liste des fichiers disponibles (triés du plus récent) ─────────
        $files = is_dir($dir) ? glob($dir . '/*.log') : [];
        $dates = array_map(fn($f) => basename($f, '.log'), $files);
        rsort($dates);

        // ── Fichier sélectionné (défaut : aujourd'hui) ────────────────────
        $selectedDate = $request->filled('date') && in_array($request->date, $dates, true)
            ? $request->date
            : (count($dates) > 0 ? $dates[0] : now()->format('Y-m-d'));

        $logPath = $dir . '/' . $selectedDate . '.log';

        // ── Lecture des lignes (ordre anti-chronologique) ─────────────────
        $lines = [];
        if (file_exists($logPath)) {
            $lines = array_reverse(
                array_filter(
                    file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [],
                )
            );
        }

        // ── Filtre texte ──────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = mb_strtolower($request->search);
            $lines  = array_values(
                array_filter($lines, fn($l) => str_contains(mb_strtolower($l), $search))
            );
        }

        // ── Filtre niveau ─────────────────────────────────────────────────
        if ($request->filled('level')) {
            $needle = ' - ' . strtoupper($request->level) . ' - ';
            $lines  = array_values(
                array_filter($lines, fn($l) => str_contains($l, $needle))
            );
        }

        $total  = count($lines);
        $perPage = 100;
        $page    = max(1, (int) $request->get('page', 1));
        $lines   = array_slice($lines, ($page - 1) * $perPage, $perPage);

        $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];

        return view('admin.logs.index', compact('lines', 'dates', 'selectedDate', 'total', 'page', 'perPage', 'levels'));
    }
}

class ActivityLogController extends Controller
{
    /**
     * Liste paginée des logs avec filtres.
     * Accès réservé aux admins ayant la permission 'manage_logs' (super_admin uniquement via Spatie).
     * La vérification est déléguée au middleware admin.can:manage_logs sur la route.
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::recent();

        if ($request->filled('level')) {
            $query->level($request->level);
        }

        if ($request->filled('action')) {
            $query->where('action', 'LIKE', '%' . $request->action . '%');
        }

        if ($request->filled('user_type')) {
            $query->byUserType($request->user_type);
        }

        if ($request->filled('user_label')) {
            $query->where('user_label', 'LIKE', '%' . $request->user_label . '%');
        }

        if ($request->filled('ip')) {
            $query->where('ip_address', $request->ip_filter);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->paginate(50)->withQueryString();

        $levels  = ['debug', 'info', 'notice', 'warning', 'error', 'critical'];
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('admin.logs.index', compact('logs', 'levels', 'actions'));
    }
}
