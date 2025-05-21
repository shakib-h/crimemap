<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
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
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (!$user->role_id) {
                $defaultRole = Role::where('slug', 'user')->first();
                if ($defaultRole) {
                    $user->role_id = $defaultRole->id;
                }
            }
        });
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function crimes()
    {
        return $this->hasMany(Crime::class);
    }

    public function isAdmin()
    {
        return $this->role->slug === 'admin';
    }

    public function isModerator()
    {
        return $this->role->slug === 'moderator';
    }
}
