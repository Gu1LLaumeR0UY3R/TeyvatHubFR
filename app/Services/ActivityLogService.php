<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Écrit une ligne dans le fichier de log du jour.
     *
     * Format : [DD/MM/YYYY HH:MM:SS] - {type}:{id} - {LEVEL} - {action} - {message} - IP:{ip}
     *
     * @param string       $action      Identifiant de l'action (login_failed, article_published…)
     * @param string       $level       debug|info|notice|warning|error|critical
     * @param array        $context     Informations libres (sérialisées en JSON sur la ligne)
     * @param string|null  $userType    'admin'|'user'|null (guest)
     * @param int|null     $userId
     * @param string|null  $userLabel   Email ou pseudo (pour lisibilité du log)
     * @param string|null  $subjectType Classe du modèle concerné
     * @param int|null     $subjectId
     * @param Request|null $request
     */
    public static function log(
        string   $action,
        string   $level       = 'info',
        array    $context     = [],
        ?string  $userType    = null,
        ?int     $userId      = null,
        ?string  $userLabel   = null,
        ?string  $subjectType = null,
        ?int     $subjectId   = null,
        ?Request $request     = null,
    ): void {
        try {
            $req = $request ?? request();
            $now = now();

            // ── Partie utilisateur ────────────────────────────────────────
            $userPart = $userType && $userId
                ? "{$userType}:{$userId}" . ($userLabel ? " ({$userLabel})" : '')
                : 'guest';

            // ── Partie message ────────────────────────────────────────────
            $parts = [$action];

            if ($subjectType && $subjectId) {
                $shortClass = class_basename($subjectType);
                $parts[] = "subject:{$shortClass}#{$subjectId}";
            }

            if ($context) {
                $parts[] = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            $ip   = $req?->ip() ?? '-';
            $line = sprintf(
                "[%s] - %s - %s - %s - IP:%s",
                $now->format('d/m/Y H:i:s'),
                $userPart,
                strtoupper($level),
                implode(' | ', $parts),
                $ip,
            ) . PHP_EOL;

            // ── Écriture dans storage/logs/activity/{scope}/{categorie}/YYYY-MM-DD.log ──
            $dir = storage_path('logs/activity') . '/' . static::resolveLogDir($action, $userType);

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $file = $dir . '/' . $now->format('Y-m-d') . '.log';

            file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Ne jamais crasher l'app si le logging échoue
            Log::error('ActivityLogService::log failed: ' . $e->getMessage());
        }
    }

    /** Raccourci pour un admin connecté via session. */
    public static function adminLog(
        string   $action,
        string   $level       = 'info',
        array    $context     = [],
        ?string  $subjectType = null,
        ?int     $subjectId   = null,
        ?Request $request     = null,
    ): void {
        $req     = $request ?? request();
        $adminId = session('admin_id');
        $label   = session('admin_pseudo') ?? session('admin_email');

        static::log(
            action:      $action,
            level:       $level,
            context:     $context,
            userType:    'admin',
            userId:      $adminId ? (int) $adminId : null,
            userLabel:   $label,
            subjectType: $subjectType,
            subjectId:   $subjectId,
            request:     $req,
        );
    }

    /** Raccourci pour un joueur authentifié. */
    public static function userLog(
        string   $action,
        string   $level   = 'info',
        array    $context = [],
        ?Request $request  = null,
    ): void {
        $req  = $request ?? request();
        $user = auth()->user();

        static::log(
            action:    $action,
            level:     $level,
            context:   $context,
            userType:  $user ? 'user' : null,
            userId:    $user?->id,
            userLabel: $user?->email ?? $user?->pseudo,
            request:   $req,
        );
    }

    /**
     * Détecte une activité suspecte (X tentatives échouées depuis une IP).
     * Utilise le Cache Laravel — pas de BDD nécessaire.
     */
    public static function isSuspiciousLogin(string $ip, int $threshold = 5, int $windowMinutes = 15): bool
    {
        $key   = 'failed_logins:' . $ip;
        $count = (int) Cache::get($key, 0);

        Cache::put($key, $count + 1, now()->addMinutes($windowMinutes));

        return ($count + 1) >= $threshold;
    }

    /**
     * Résout le dossier relatif de log selon le scope et la catégorie de l'action.
     *
     * Scope  : admin/  si userType === 'admin' ou action préfixée 'admin_'
     *          public/ sinon
     *
     * Catégories admin : auth | personnages | armes | blog | ennemis | animaux
     *                    regions | cuisine | materiaux | evenements | utilisateurs
     *                    import | roles | general
     *
     * Catégories public : auth | blog | outils | general
     *
     * @param  string      $action
     * @param  string|null $userType 'admin'|'user'|null
     * @return string                chemin relatif depuis storage/logs/activity/
     */
    private static function resolveLogDir(string $action, ?string $userType): string
    {
        $isAdmin = ($userType === 'admin') || str_starts_with($action, 'admin_');
        $scope   = $isAdmin ? 'admin' : 'public';

        // Supprime le préfixe admin_ pour simplifier la correspondance
        $key = strtolower($isAdmin ? preg_replace('/^admin_/', '', $action) : $action);

        if ($isAdmin) {
            $category = match (true) {
                (bool) preg_match('/login|logout|auth|password|2fa|suspicious/', $key) => 'auth',
                (bool) preg_match('/personnage|perso/',                           $key) => 'personnages',
                (bool) preg_match('/arme/',                                       $key) => 'armes',
                (bool) preg_match('/article|blog|post|comment/',                  $key) => 'blog',
                (bool) preg_match('/ennemi/',                                     $key) => 'ennemis',
                (bool) preg_match('/animal/',                                     $key) => 'animaux',
                (bool) preg_match('/region|nation|sous_region/',                  $key) => 'regions',
                (bool) preg_match('/cuisine|plat|specialite/',                    $key) => 'cuisine',
                (bool) preg_match('/materiaux|materiau/',                         $key) => 'materiaux',
                (bool) preg_match('/evenement|chronologie/',                      $key) => 'evenements',
                (bool) preg_match('/utilisateur|user/',                           $key) => 'utilisateurs',
                (bool) preg_match('/import/',                                     $key) => 'import',
                (bool) preg_match('/role/',                                       $key) => 'roles',
                default                                                                 => 'general',
            };
        } else {
            $category = match (true) {
                (bool) preg_match('/login|logout|register|password|2fa|suspicious/', $key) => 'auth',
                (bool) preg_match('/article|blog|post|comment/',                     $key) => 'blog',
                (bool) preg_match('/roulette|quiz|team|comparateur|outil/',          $key) => 'outils',
                default                                                                    => 'general',
            };
        }

        return "{$scope}/{$category}";
    }
}
