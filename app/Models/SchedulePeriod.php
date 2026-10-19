<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulePeriod extends Model
{
    protected $fillable = [
        'name',
        'tipe',
        'semester',
        'prodi',
        'gelombang',
        'capacity',
        'start_date',
        'end_date',
        'slots',
        'is_open',
        'is_published',
        'google_calendar_synced',
        'google_calendar_synced_at',
    ];

    protected $casts = [
        'slots' => 'array',
        'is_open' => 'boolean',
        'is_published' => 'boolean',
        'google_calendar_synced' => 'boolean',
        'google_calendar_synced_at' => 'datetime',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    public function penjadwalan()
    {
        return $this->hasMany(Penjadwalan::class, 'period_id');
    }
}
