<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id_admin
 * @property string $pseudo_admin
 * @property string $email_admin
 * @property string $mot_de_passe_admin
 * @property string $role
 * @property array|null $legacy_permissions
 */
class Admin extends Model
{
    use HasFactory;
    use HasRoles;

    public $timestamps = false;
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    /** All available permission keys. */
    public const ALL_PERMISSIONS = [
        'encyclopedie',
        'articles',
        'evenements',
        'utilisateurs',
        'admins',
        'import',
    ];

    protected $fillable = [
        'pseudo_admin',
        'email_admin',
        'mot_de_passe_admin',
        'role',
        'legacy_permissions',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'photo_profil',
        'banniere_admin',
    ];

    protected $hidden = ['mot_de_passe_admin', 'two_factor_secret'];

    protected $guard_name = 'web';

    protected function casts(): array
    {
        return [
            'mot_de_passe_admin' => 'hashed',
            'legacy_permissions' => 'array',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    /** Preserve legacy admin access when no explicit permission set exists. */
    public function can(string $permission): bool
    {
        if (in_array($this->role, ['super_admin', 'superadmin'], true)) {
            return true;
        }

        if (method_exists($this, 'hasPermissionTo')) {
            try {
                return $this->hasPermissionTo($permission);
            } catch (\Throwable) {
                // Fallback to legacy permissions when the permission record does not exist or the package is not ready.
            }
        }

        $permissions = (array) ($this->legacy_permissions ?? []);

        if ($permissions === []) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /** True if the admin holds every one of the given permissions. */
    public function canAll(array $permissions): bool
    {
        if (in_array($this->role, ['super_admin', 'superadmin'], true)) {
            return true;
        }

        if (method_exists($this, 'hasAllPermissions')) {
            try {
                return $this->hasAllPermissions($permissions);
            } catch (\Throwable) {
                // Fallback to legacy permissions if needed.
            }
        }

        foreach ($permissions as $perm) {
            if (!$this->can($perm)) {
                return false;
            }
        }

        return true;
    }
}

