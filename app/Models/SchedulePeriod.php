<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchedulePeriod extends Model
{
    protected $fillable = ['name', 'tipe', 'gelombang', 'capacity', 'start_date', 'end_date', 'slots', 'is_open'];

    protected $casts = ['slots' => 'array', 'is_open' => 'boolean', 'start_date' => 'date:Y-m-d', 'end_date' => 'date:Y-m-d'];
}
