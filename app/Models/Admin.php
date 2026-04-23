<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id_admin
 * @property string $pseudo_admin
 * @property string $email_admin
 * @property string $mot_de_passe_admin
 * @property string $role
 * @property array|null $permissions
 */
class Admin extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    /** All available permission keys. */
    public const ALL_PERMISSIONS = [
        'encyclopedie',
        'blog',
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
        'permissions',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'photo_profil',
        'banniere_admin',
    ];

    protected $hidden = ['mot_de_passe_admin', 'two_factor_secret'];

    protected function casts(): array
    {
        return [
            'mot_de_passe_admin' => 'hashed',
            'permissions'        => 'array',
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

        $permissions = (array) ($this->permissions ?? []);

        if ($permissions === []) {
            return true;
        }

        return in_array($permission, $permissions, true);
    }

    /** True if the admin holds every one of the given permissions. */
    public function canAll(array $permissions): bool
    {
        foreach ($permissions as $perm) {
            if (!$this->can($perm)) {
                return false;
            }
        }

        return true;
    }
}
