<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Log une action utilisateur ou admin en BDD.
     *
     * @param  string       $action     Identifiant de l'action (login_success, article_created…)
     * @param  string       $level      debug|info|notice|warning|error|critical
     * @param  array        $context    Propriétés libres supplémentaires
     * @param  string|null  $userType   'admin'|'user'|null
     * @param  int|null     $userId
     * @param  string|null  $userLabel  Email ou pseudo
     * @param  string|null  $subjectType Classe du modèle concerné
     * @param  int|null     $subjectId
     * @param  Request|null $request    Pour récupérer IP & User-Agent
     */
    public static function log(
        string  $action,
        string  $level      = 'info',
        array   $context    = [],
        ?string $userType   = null,
        ?int    $userId     = null,
        ?string $userLabel  = null,
        ?string $subjectType = null,
        ?int    $subjectId  = null,
        ?Request $request   = null,
    ): void {
        try {
            $req = $request ?? request();

            ActivityLog::create([
                'level'        => $level,
                'action'       => $action,
                'subject_type' => $subjectType,
                'subject_id'   => $subjectId,
                'user_type'    => $userType,
                'user_id'      => $userId,
                'user_label'   => $userLabel ? mb_substr($userLabel, 0, 100) : null,
                'properties'   => $context ?: null,
                'ip_address'   => $req?->ip(),
                'user_agent'   => $req ? mb_substr($req->userAgent() ?? '', 0, 500) : null,
                'created_at'   => now(),
            ]);
        } catch (\Throwable $e) {
            // Ne jamais crasher l'app si le logging échoue
            Log::error('ActivityLogService::log failed: ' . $e->getMessage());
        }
    }

    /** Raccourci pour un admin connecté via session. */
    public static function adminLog(
        string   $action,
        string   $level     = 'info',
        array    $context   = [],
        ?string  $subjectType = null,
        ?int     $subjectId   = null,
        ?Request $request     = null,
    ): void {
        $req      = $request ?? request();
        $adminId  = session('admin_id');
        $label    = session('admin_pseudo') ?? session('admin_email');

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
     * Détecter si un seuil de tentatives échouées est atteint.
     * Retourne true si suspicion (>= $threshold tentatives dans $windowMinutes minutes depuis $ip).
     */
    public static function isSuspiciousLogin(string $ip, int $threshold = 5, int $windowMinutes = 15): bool
    {
        $since = now()->subMinutes($windowMinutes);

        $count = ActivityLog::where('ip_address', $ip)
            ->whereIn('action', ['login_failed', 'admin_login_failed'])
            ->where('created_at', '>=', $since)
            ->count();

        return $count >= $threshold;
    }
}
