<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use Illuminate\Http\Request;

class ScheduleExportController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate(['period_id' => ['nullable', 'integer', 'min:1']]);
        $periodId = $validated['period_id'] ?? null;
        if ($periodId !== null) {
            SchedulePeriod::findOrFail($periodId);
        }
        $rows = Penjadwalan::with(['mahasiswa', 'penguji1', 'penguji2', 'pembimbing1', 'pembimbing2'])
            ->when($periodId !== null, fn ($query) => $query->where('period_id', $periodId))
            ->orderBy('tanggal')->orderBy('jam_mulai')->orderBy('id')->get();

        return response()->streamDownload(function () use ($rows) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, ['id', 'period_id', 'tipe', 'pendaftaran_id', 'mahasiswa_id', 'mahasiswa', 'nim', 'tanggal', 'jam_mulai', 'jam_selesai', 'pembimbing_1', 'pembimbing_2', 'penguji_1', 'penguji_2', 'ruangan', 'mode', 'link_online', 'status'], ',', '"', '', "\r\n");
            foreach ($rows as $row) {
                $values = [$row->id, $row->period_id, $row->tipe, $row->pendaftaran_id, $row->mahasiswa_id, $row->mahasiswa?->name, $row->mahasiswa?->nim_nip, $row->tanggal?->format('Y-m-d'), $row->jam_mulai, $row->jam_selesai, $row->pembimbing1?->name, $row->pembimbing2?->name, $row->penguji1?->name, $row->penguji2?->name, $row->link_online, $row->mode, $row->link_online, $row->status];
                // CSV opened in spreadsheet applications must not execute user-supplied formulas.
                $values = array_map(fn ($value) => is_string($value) && preg_match('/^[\s]*[=+@-]/u', $value) ? "'".$value : $value, $values);
                fputcsv($stream, $values, ',', '"', '', "\r\n");
            }
            fclose($stream);
        }, 'jadwal.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
