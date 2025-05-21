<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CrimeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'description', 
        'severity_level'
    ];

    public function crimes(): HasMany
    {
        return $this->hasMany(Crime::class);
    }

    public function getSeverityColor(): string
    {
        return match($this->severity_level) {
            'low' => '#FFA500',     // Orange
            'medium' => '#FF6B6B',  // Light Red
            'high' => '#FF0000',    // Red
            default => '#808080'    // Gray
        };
    }
}