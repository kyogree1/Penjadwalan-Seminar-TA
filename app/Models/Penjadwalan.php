<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penjadwalan extends Model
{
    use HasFactory;

    protected $table = 'penjadwalan';

    protected $fillable = [
        'tipe',
        'period_id',
        'pendaftaran_id',
        'mahasiswa_id',
        'ruangan_id',
        'pembimbing_1_id',
        'pembimbing_2_id',
        'penguji_1_id',
        'penguji_2_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'mode',
        'link_online',
        'gelombang',
        'status',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'penjadwalan_id');
    }

    public function examinerIds(): array
    {
        return array_values(array_unique(array_filter([$this->penguji_1_id, $this->penguji_2_id])));
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function ruangan(): BelongsTo
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function pembimbing1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembimbing_1_id');
    }

    public function pembimbing2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembimbing_2_id');
    }

    public function penguji1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penguji_1_id');
    }

    public function penguji2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penguji_2_id');
    }
}
