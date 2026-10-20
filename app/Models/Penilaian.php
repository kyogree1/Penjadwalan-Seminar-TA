<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    protected $table = 'penilaian';

    protected $fillable = ['penjadwalan_id', 'dosen_id', 'nilai', 'catatan'];

    protected $casts = ['nilai' => 'float'];

    /**
     * Jadwal ujian yang dinilai (Sempro / Sidang).
     */
    public function penjadwalan(): BelongsTo
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id');
    }

    /**
     * Dosen penguji yang memberi nilai.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }
}
