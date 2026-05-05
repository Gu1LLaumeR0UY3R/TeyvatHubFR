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

        $superAdminRole->syncPermissions(Permission::where('guard_name', $guardName)->get());

        Admin::cursor()->each(function (Admin $admin) use ($guardName, $superAdminRole) {
            $adminRole = $admin->role ?: 'moderateur';
            $role = Role::findOrCreate($adminRole, $guardName);
            $admin->syncRoles([$role]);

            if (in_array($adminRole, ['super_admin', 'superadmin'], true)) {
                $admin->syncPermissions(Permission::where('guard_name', $guardName)->get());
                return;
            }

            $permissions = (array) ($admin->legacy_permissions ?? []);
            $permissionModels = collect($permissions)
                ->filter()
                ->map(fn(string $permissionName) => Permission::findOrCreate($permissionName, $guardName))
                ->all();

            $admin->syncPermissions($permissionModels);
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
