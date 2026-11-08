<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RescheduleRequest extends Model
{
    use HasFactory;

    protected $table = 'reschedule_requests';

    protected $fillable = [
        'penjadwalan_id',
        'diminta_oleh',
        'alasan',
        'catatan',
        'tanggal_usulan',
        'jam_mulai_usulan',
        'jam_selesai_usulan',
        'ruangan_usulan',
        'status',
        'catatan_kaprodi',
        'diputuskan_oleh',
        'diputuskan_at',
    ];

    protected $casts = [
        'tanggal_usulan' => 'date',
        'diputuskan_at' => 'datetime',
    ];

    public function penjadwalan(): BelongsTo
    {
        return $this->belongsTo(Penjadwalan::class, 'penjadwalan_id');
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diminta_oleh');
    }

    public function pemutus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }
}
