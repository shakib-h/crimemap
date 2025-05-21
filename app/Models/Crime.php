<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crime extends Model
{
    protected $fillable = [
        'user_id',
        'crime_type_id',
        'title',
        'description',
        'latitude',
        'longitude',
        'incident_date',
        'status'
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function crimeType()
    {
        return $this->belongsTo(CrimeType::class);
    }
}