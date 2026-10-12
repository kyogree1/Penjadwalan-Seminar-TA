<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = ['penjadwalan_id', 'dosen_id', 'nilai', 'catatan'];

    protected $casts = ['nilai' => 'float'];
}
