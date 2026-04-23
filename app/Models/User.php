<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $pseudo
 * @property string|null $avatar
 * @property string|null $banniere
 * @property string|null $bio_joueur
 * @property string|null $uid_genshin
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'two_factor_secret',
        'two_factor_enabled',
        'two_factor_confirmed_at',
        'pseudo',
        'avatar',
        'banniere',
        'bio_joueur',
        'uid_genshin',
        'date_inscription',
        'derniere_connexion',
        'banni_le',
        'motif_ban',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function personnages(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Personnage::class,
            'joueur_personnage',
            'fid_joueur',
            'fid_perso'
        )->withPivot(['niveau', 'affinite', 'perso_amelioration']);
    }

    public function armes(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\Arme::class,
            'joueur_arme',
            'fid_joueur',
            'fid_arme'
        )->withPivot(['niveau', 'rang']);
    }

    public function amis(): HasMany
    {
        return $this->hasMany(\App\Models\Amitie::class, 'fid_demandeur');
    }
}
