<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Penjadwalan;
use App\Models\RescheduleRequest;
use App\Models\User;
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

        $reschedule = RescheduleRequest::create([
            'penjadwalan_id' => $exam->id,
            'diminta_oleh' => $user->id,
            'alasan' => $validated['alasan'],
            'catatan' => $validated['catatan'] ?? null,
            'tanggal_usulan' => $validated['tanggal_usulan'] ?? null,
            'jam_mulai_usulan' => $validated['jam_mulai_usulan'] ? substr($validated['jam_mulai_usulan'], 0, 8) : null,
            'jam_selesai_usulan' => $validated['jam_selesai_usulan'] ? substr($validated['jam_selesai_usulan'], 0, 8) : null,
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
                'link' => '/kaprodi/persetujuan',
            ]);
        }

        return back()->with('success', 'Permohonan reschedule jadwal berhasil dikirimkan ke Koordinator TA.');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:menunggu,disetujui,ditolak'],
            'catatan_kaprodi' => ['nullable', 'string'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable', 'string'],
            'jam_selesai' => ['nullable', 'string'],
        ]);

        $reschedule = RescheduleRequest::with(['penjadwalan.mahasiswa', 'pemohon'])->findOrFail($id);
        $user = Auth::user();

        DB::transaction(function () use ($reschedule, $validated, $user) {
            $exam = $reschedule->penjadwalan;

            if ($validated['status'] === 'disetujui') {
                $newDate = $validated['tanggal'] ?? $reschedule->tanggal_usulan;
                $newStart = $validated['jam_mulai'] ?? $reschedule->jam_mulai_usulan;
                $newEnd = $validated['jam_selesai'] ?? $reschedule->jam_selesai_usulan;
                $newRoom = $validated['ruangan'] ?? $reschedule->ruangan_usulan;

                if ($newDate) {
                    $exam->tanggal = $newDate;
                }
                if ($newStart) {
                    $exam->jam_mulai = substr($newStart, 0, 8);
                }
                if ($newEnd) {
                    $exam->jam_selesai = substr($newEnd, 0, 8);
                }
                if ($newRoom) {
                    $exam->link_online = $newRoom;
                }
                $exam->save();

                $reschedule->status = 'disetujui';
                $reschedule->catatan_kaprodi = $validated['catatan_kaprodi'] ?? null;
                $reschedule->diputuskan_oleh = $user->id;
                $reschedule->diputuskan_at = now();
                $reschedule->save();

                // Notify requester
                Notification::create([
                    'user_id' => $reschedule->diminta_oleh,
                    'title' => 'Reschedule Jadwal Disetujui',
                    'message' => "Permohonan reschedule untuk mahasiswa {$exam->mahasiswa?->name} telah disetujui.",
                    'type' => 'success',
                    'link' => '/kaprodi/persetujuan',
                ]);
            } elseif ($validated['status'] === 'ditolak') {
                $reschedule->status = 'ditolak';
                $reschedule->catatan_kaprodi = $validated['catatan_kaprodi'] ?? null;
                $reschedule->diputuskan_oleh = $user->id;
                $reschedule->diputuskan_at = now();
                $reschedule->save();

                // Notify requester
                Notification::create([
                    'user_id' => $reschedule->diminta_oleh,
                    'title' => 'Reschedule Jadwal Ditolak',
                    'message' => "Permohonan reschedule untuk mahasiswa {$exam->mahasiswa?->name} ditolak: ".($validated['catatan_kaprodi'] ?? 'Tidak ada catatan.'),
                    'type' => 'warning',
                    'link' => '/kaprodi/persetujuan',
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
}
