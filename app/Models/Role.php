<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug'
    ];

    /**
     * Get the users associated with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if role is admin
     */
    public function isAdmin(): bool
    {
        return $this->slug === 'admin';
    }

    /**
     * Check if role is moderator
     */
    public function isModerator(): bool
    {
        return $this->slug === 'moderator';
    }
}
