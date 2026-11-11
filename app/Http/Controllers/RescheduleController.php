<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Penjadwalan;
use App\Models\RescheduleRequest;
use App\Models\User;
use App\Services\GeneticAlgorithm\ScheduleOptimizer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RescheduleController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'penjadwalan_id' => ['required', 'integer', 'exists:penjadwalan,id'],
            'alasan' => ['required', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
            'tanggal_usulan' => ['nullable', 'date'],
            'jam_mulai_usulan' => ['nullable', 'string'],
            'jam_selesai_usulan' => ['nullable', 'string'],
            'ruangan_usulan' => ['nullable', 'string', 'max:100'],
        ]);

        $exam = Penjadwalan::with('mahasiswa')->findOrFail($validated['penjadwalan_id']);
        $user = Auth::user();

        // Check if requester is related to this schedule
        $allowedIds = array_filter([
            $exam->mahasiswa_id,
            $exam->penguji_1_id,
            $exam->penguji_2_id,
            $exam->pembimbing_1_id,
            $exam->pembimbing_2_id,
        ]);

        if (! in_array($user->id, $allowedIds) && ! in_array($user->role, ['kaprodi', 'koordinator'])) {
            throw ValidationException::withMessages(['penjadwalan_id' => 'Anda tidak memiliki akses terhadap jadwal ujian ini.']);
        }

        if (in_array($exam->status, ['selesai', 'dibatalkan'], true)) {
            throw ValidationException::withMessages(['penjadwalan_id' => 'Jadwal yang sudah selesai atau dibatalkan tidak dapat dijadwalkan ulang.']);
        }

        $jamMulai = ! empty($validated['jam_mulai_usulan']) ? $this->normalizeTime($validated['jam_mulai_usulan']) : null;
        $jamSelesai = ! empty($validated['jam_selesai_usulan']) ? $this->normalizeTime($validated['jam_selesai_usulan']) : null;
        if ($jamMulai && $jamSelesai && $jamMulai >= $jamSelesai) {
            throw ValidationException::withMessages(['jam_selesai_usulan' => 'Jam selesai usulan harus lebih besar dari jam mulai.']);
        }

        $reschedule = RescheduleRequest::create([
            'penjadwalan_id' => $exam->id,
            'diminta_oleh' => $user->id,
            'alasan' => $validated['alasan'],
            'catatan' => $validated['catatan'] ?? null,
            'tanggal_usulan' => $validated['tanggal_usulan'] ?? null,
            'jam_mulai_usulan' => $jamMulai,
            'jam_selesai_usulan' => $jamSelesai,
            'ruangan_usulan' => $validated['ruangan_usulan'] ?? null,
            'status' => 'menunggu',
        ]);

        // Notify Kaprodi / Koordinator
        $kaprodiUsers = User::whereIn('role', ['kaprodi', 'koordinator'])->get();
        foreach ($kaprodiUsers as $kaprodi) {
            Notification::create([
                'user_id' => $kaprodi->id,
                'title' => 'Permohonan Reschedule Jadwal',
                'message' => "{$user->name} mengajukan reschedule ujian {$exam->tipe} untuk mahasiswa {$exam->mahasiswa?->name}.",
                'type' => 'warning',
                'link' => '/kaprodi/persetujuan?section=reschedule',
            ]);
        }

        return back()->with('success', 'Permohonan reschedule jadwal berhasil dikirimkan ke Koordinator TA.');
    }

    public function update(Request $request, int $id)
    {
        $user = Auth::user();
        abort_unless($user && in_array($user->role, ['kaprodi', 'koordinator'], true), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:menunggu,disetujui,ditolak'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable', 'string'],
            'jam_selesai' => ['nullable', 'string'],
        ]);

        $reschedule = RescheduleRequest::with(['penjadwalan.mahasiswa', 'pemohon'])->findOrFail($id);

        DB::transaction(function () use ($reschedule, $validated, $user) {
            $exam = Penjadwalan::lockForUpdate()->findOrFail($reschedule->penjadwalan_id);

            if ($validated['status'] === 'disetujui') {
                if (in_array($exam->status, ['selesai', 'dibatalkan'], true)) {
                    throw ValidationException::withMessages(['status' => 'Jadwal yang sudah selesai atau dibatalkan tidak dapat diubah.']);
                }

                $newDateRaw = $validated['tanggal'] ?? $reschedule->tanggal_usulan;
                $newDate = $newDateRaw instanceof \DateTimeInterface
                    ? $newDateRaw->format('Y-m-d')
                    : ($newDateRaw ? Carbon::parse($newDateRaw)->format('Y-m-d') : $exam->tanggal?->format('Y-m-d'));

                $newStartRaw = $validated['jam_mulai'] ?? $reschedule->jam_mulai_usulan;
                $newStart = $newStartRaw ? $this->normalizeTime($newStartRaw) : $exam->jam_mulai;

                $newEndRaw = $validated['jam_selesai'] ?? $reschedule->jam_selesai_usulan;
                $newEnd = $newEndRaw ? $this->normalizeTime($newEndRaw) : $exam->jam_selesai;

                $newRoom = $validated['ruangan'] ?? $reschedule->ruangan_usulan;

                if (! $newDate || ! $newStart || ! $newEnd || $newStart >= $newEnd) {
                    throw ValidationException::withMessages(['jam_selesai' => 'Tanggal serta rentang waktu jadwal baru yang valid wajib tersedia.']);
                }

                // Serialize and verify there are no lecturer/student schedule conflicts on the new slot
                $otherBookings = Penjadwalan::whereIn('status', ['draft', 'terpublikasi', 'selesai'])
                    ->where('id', '!=', $exam->id)
                    ->lockForUpdate()
                    ->get()
                    ->map(fn ($row) => array_merge($row->toArray(), ['tanggal' => $row->tanggal->format('Y-m-d')]))
                    ->all();

                $candidateRow = [
                    'mahasiswa_id' => $exam->mahasiswa_id,
                    'pembimbing_1_id' => $exam->pembimbing_1_id,
                    'pembimbing_2_id' => $exam->pembimbing_2_id,
                    'penguji_1_id' => $exam->penguji_1_id,
                    'penguji_2_id' => $exam->penguji_2_id,
                    'tanggal' => $newDate,
                    'jam_mulai' => $newStart,
                    'jam_selesai' => $newEnd,
                ];

                if ((new ScheduleOptimizer)->conflicts($candidateRow, $otherBookings)) {
                    throw ValidationException::withMessages(['tanggal' => 'Konflik jadwal: penguji, pembimbing, atau mahasiswa sudah terjadwal pada slot baru tersebut.']);
                }

                $exam->tanggal = $newDate;
                $exam->jam_mulai = $newStart;
                $exam->jam_selesai = $newEnd;
                if ($newRoom) {
                    $exam->link_online = $newRoom;
                }
                $exam->save();

                $reschedule->status = 'disetujui';
                $reschedule->catatan_kaprodi = $validated['catatan_kaprodi'] ?? null;
                $reschedule->diputuskan_oleh = $user->id;
                $reschedule->diputuskan_at = now();
                $reschedule->save();

                // Notify requester with role-appropriate link
                $requesterLink = $this->notificationLinkFor($reschedule->pemohon, $exam->tipe);
                Notification::create([
                    'user_id' => $reschedule->diminta_oleh,
                    'title' => 'Reschedule Jadwal Disetujui',
                    'message' => "Permohonan reschedule untuk mahasiswa {$exam->mahasiswa?->name} telah disetujui.",
                    'type' => 'success',
                    'link' => $requesterLink,
                ]);

                // Also notify the student if the requester was a lecturer/kaprodi
                if ($exam->mahasiswa_id && (int) $exam->mahasiswa_id !== (int) $reschedule->diminta_oleh) {
                    Notification::create([
                        'user_id' => $exam->mahasiswa_id,
                        'title' => 'Perubahan Jadwal Ujian',
                        'message' => "Jadwal ujian {$exam->tipe} Anda diperbarui menjadi tanggal {$newDate} pukul ".substr($newStart, 0, 5).'–'.substr($newEnd, 0, 5).'.',
                        'type' => 'info',
                        'link' => '/pendaftaran/'.($exam->tipe === 'sempro' ? 'sempro' : 'sidang'),
                    ]);
                }
            } elseif ($validated['status'] === 'ditolak') {
                $reschedule->status = 'ditolak';
                $reschedule->catatan_kaprodi = $validated['catatan_kaprodi'] ?? null;
                $reschedule->diputuskan_oleh = $user->id;
                $reschedule->diputuskan_at = now();
                $reschedule->save();

                // Notify requester with role-appropriate link
                $requesterLink = $this->notificationLinkFor($reschedule->pemohon, $exam->tipe);
                Notification::create([
                    'user_id' => $reschedule->diminta_oleh,
                    'title' => 'Reschedule Jadwal Ditolak',
                    'message' => "Permohonan reschedule untuk mahasiswa {$exam->mahasiswa?->name} ditolak: ".($validated['catatan_kaprodi'] ?? 'Tidak ada catatan.'),
                    'type' => 'warning',
                    'link' => $requesterLink,
                ]);
            } else {
                $reschedule->status = 'menunggu';
                $reschedule->catatan_kaprodi = null;
                $reschedule->diputuskan_oleh = null;
                $reschedule->diputuskan_at = null;
                $reschedule->save();
            }
        });

        return back()->with('success', 'Keputusan reschedule jadwal berhasil diperbarui.');
    }

    private function normalizeTime(string $time): string
    {
        $trimmed = trim($time);
        if (preg_match('/^\d{2}:\d{2}$/', $trimmed)) {
            return $trimmed.':00';
        }

        return substr($trimmed, 0, 8);
    }

    private function notificationLinkFor(?User $user, string $tipe): string
    {
        if (! $user) {
            return '/kaprodi/persetujuan?section=reschedule';
        }

        return match ($user->role) {
            'mahasiswa' => '/pendaftaran/'.($tipe === 'sempro' ? 'sempro' : 'sidang'),
            'dosen' => '/dosen/penilaian',
            default => '/kaprodi/persetujuan?section=reschedule',
        };
    }
}
