<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'telephone',
        'photo',
        'actif',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'actif' => 'boolean',
        ];
    }

    // --- Relations ---

    /** Pannes déclarées par cet utilisateur (chef d'équipe). */
    public function pannesDeclarees(): HasMany
    {
        return $this->hasMany(Panne::class, 'declaree_par_id');
    }

    /** Pannes sur lesquelles cet utilisateur intervient. */
    public function pannesAssignees(): BelongsToMany
    {
        return $this->belongsToMany(Panne::class, 'panne_intervenants')->withTimestamps();
    }

    /** Interventions menées par cet utilisateur (maintenancier). */
    public function interventions(): HasMany
    {
        return $this->hasMany(Intervention::class, 'maintenancier_id');
    }
}
