<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PendaftaranSempro extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_sempro';

    protected $fillable = [
        'mahasiswa_id',
        'judul_ta',
        'bentuk_ta',
        'lokasi_mitra',
        'skor_iaet',
        'lembar_kehadiran_file',
        'proposal_file',
        'turnitin_file',
        'iaet_file',
        'skor_iaet',
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
}
