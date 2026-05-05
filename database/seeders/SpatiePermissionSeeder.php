<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Initialise les rôles et permissions Spatie pour les admins.
 *
 * Rôles :
 *   - super_admin  : tous les droits, y compris manage_logs (non assignable aux autres)
 *   - moderateur   : articles, utilisateurs
 *
 * Permission manage_logs :
 *   - Assignée UNIQUEMENT au rôle super_admin via Spatie.
 *   - Absente de Admin::ALL_PERMISSIONS → invisible dans l'UI de gestion des admins.
 *   - Le middleware admin.can:manage_logs s'appuie sur hasPermissionTo() de Spatie.
 */
class SpatiePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Vider le cache Spatie avant de tout recréer
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ── Permissions publiques (assignables aux admins via UI) ─────────
        $publicPermissions = [
            'encyclopedie',
            'articles',
            'evenements',
            'utilisateurs',
            'admins',
            'import',
        ];

        foreach ($publicPermissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // ── Permission réservée super_admin (non assignable via UI) ───────
        $manageLogsPermission = Permission::firstOrCreate(
            ['name' => 'manage_logs', 'guard_name' => 'web']
        );

        // ── Rôle super_admin — toutes les permissions ─────────────────────
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(
            array_merge($publicPermissions, ['manage_logs'])
        );

        // ── Rôle moderateur — sous-ensemble ───────────────────────────────
        $moderateur = Role::firstOrCreate(['name' => 'moderateur', 'guard_name' => 'web']);
        $moderateur->syncPermissions(['articles', 'utilisateurs']);

        $this->command->info('Spatie permissions seeded : ' . implode(', ', $publicPermissions) . ', manage_logs (super_admin only)');
    }
}
