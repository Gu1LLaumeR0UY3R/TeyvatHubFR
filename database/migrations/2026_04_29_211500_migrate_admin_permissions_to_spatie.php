<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $guardName = 'web';

        foreach (Admin::ALL_PERMISSIONS as $permissionName) {
            Permission::findOrCreate($permissionName, $guardName);
        }

        $superAdminRole = Role::findOrCreate('super_admin', $guardName);
        $moderateurRole = Role::findOrCreate('moderateur', $guardName);

        $allPermissionIds = Permission::where('guard_name', $guardName)->pluck('id');

        // Rôle super_admin : toutes les permissions, via insertion directe
        // dans la table pivot (on évite syncPermissions() qui provoque un
        // "Call to a member function map() on null" dans certains environnements
        // avec spatie/laravel-permission).
        $rolePermissionRows = $allPermissionIds->map(fn ($permissionId) => [
            'permission_id' => $permissionId,
            'role_id' => $superAdminRole->id,
        ])->all();

        if (!empty($rolePermissionRows)) {
            DB::table(config('permission.table_names.role_has_permissions'))
                ->insertOrIgnore($rolePermissionRows);
        }

        Admin::all()->each(function (Admin $admin) use ($guardName, $allPermissionIds) {
            $adminRole = $admin->role ?: 'moderateur';
            $role = Role::findOrCreate($adminRole, $guardName);

            DB::table(config('permission.table_names.model_has_roles'))->insertOrIgnore([
                'role_id' => $role->id,
                'model_type' => Admin::class,
                config('permission.column_names.model_morph_key') => $admin->id_admin,
            ]);

            if (in_array($adminRole, ['super_admin', 'superadmin'], true)) {
                $permissionIds = $allPermissionIds;
            } else {
                $permissionNames = collect((array) ($admin->legacy_permissions ?? []))->filter();
                $permissionIds = $permissionNames->map(
                    fn (string $permissionName) => Permission::findOrCreate($permissionName, $guardName)->id
                );
            }

            $modelPermissionRows = $permissionIds->map(fn ($permissionId) => [
                'permission_id' => $permissionId,
                'model_type' => Admin::class,
                config('permission.column_names.model_morph_key') => $admin->id_admin,
            ])->all();

            if (!empty($modelPermissionRows)) {
                DB::table(config('permission.table_names.model_has_permissions'))
                    ->insertOrIgnore($modelPermissionRows);
            }
        });
    }

    public function down(): void
    {
        $guardName = 'web';

        DB::table(config('permission.table_names.model_has_roles'))
            ->where('model_type', Admin::class)
            ->delete();

        DB::table(config('permission.table_names.model_has_permissions'))
            ->where('model_type', Admin::class)
            ->delete();

        $roles = Role::where('guard_name', $guardName)
            ->whereIn('name', ['super_admin', 'superadmin', 'moderateur'])
            ->get();

        /** @var Role $role */
        foreach ($roles as $role) {
            $role->delete();
        }

        Permission::where('guard_name', $guardName)
            ->whereIn('name', Admin::ALL_PERMISSIONS)
            ->delete();
    }
};
