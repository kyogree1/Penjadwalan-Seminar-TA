<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamDocumentController extends Controller
{
    public static function fields(string $type): array
    {
        return match ($type) {
            'sempro' => ['proposal_file', 'lembar_kehadiran_file', 'turnitin_file', 'iaet_file'],
            'sidang' => ['draft_laporan_file', 'turnitin_file', 'skor_iaet_file'],
            default => [],
        };
    }

    public static function privatePath(?string $path, Penjadwalan $exam): bool
    {
        return is_string($path) && str_starts_with($path, "dokumen/{$exam->tipe}/{$exam->mahasiswa_id}/") && ! str_contains($path, '..') && ! str_contains($path, '\\') && Storage::disk('local')->exists($path);
    }

    public static function links(Penjadwalan $exam, int $userId): array
    {
        if (! in_array($userId, $exam->examinerIds()) || ! in_array($exam->status, ['terpublikasi', 'selesai'])) {
            return [];
        }
        $fields = self::fields($exam->tipe);
        if (! $fields) {
            return [];
        }
        $model = $exam->tipe === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
        $registration = $model::where('mahasiswa_id', $exam->mahasiswa_id)->find($exam->pendaftaran_id);
        if (! $registration) {
            return [];
        }

        return collect($fields)->filter(fn ($field) => self::privatePath($registration->{$field}, $exam))
            ->map(fn ($field) => ['label' => str_replace('_', ' ', $field), 'url' => "/dosen/penilaian/{$exam->id}/dokumen/{$field}"])->values()->all();
    }

    public function __invoke(Request $request, int $id, string $field)
    {
        $exam = Penjadwalan::whereIn('status', ['terpublikasi', 'selesai'])
            ->where(fn ($q) => $q->where('penguji_1_id', $request->user()->id)->orWhere('penguji_2_id', $request->user()->id))
            ->findOrFail($id);
        $fields = self::fields($exam->tipe);
        abort_unless(in_array($field, $fields, true), 404);
        $model = $exam->tipe === 'sempro' ? PendaftaranSempro::class : PendaftaranSidang::class;
        $registration = $model::where('mahasiswa_id', $exam->mahasiswa_id)->findOrFail($exam->pendaftaran_id);
        $path = $registration->{$field};
        // Never accept caller paths or serve legacy public files as if access-controlled.
        abort_unless(is_string($path) && str_starts_with($path, "dokumen/{$exam->tipe}/{$exam->mahasiswa_id}/") && ! str_contains($path, '..') && ! str_contains($path, '\\'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, $field.'.pdf', [
            'Content-Type' => 'application/pdf',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "sandbox; default-src 'none'",
        ]);
    }
}
