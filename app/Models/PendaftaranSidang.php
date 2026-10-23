<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PendaftaranSidang extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_sidang';

    protected $fillable = [
        'mahasiswa_id',
        'judul_ta',
        'lokasi_mitra',
        'skor_iaet',
        'skor_iaet_file',
        'skor_iaet',
        'draft_laporan_file',
        'turnitin_file',
        'gelombang',
        'status',
        'catatan',
        'status_akademik',
        'catatan_kaprodi',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function penjadwalan(): HasOne
    {
        return $this->hasOne(Penjadwalan::class, 'pendaftaran_id')->where('tipe', 'sidang');
    }
}
