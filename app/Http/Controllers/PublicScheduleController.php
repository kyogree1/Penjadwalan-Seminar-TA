<?php

namespace App\Http\Controllers;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PublicScheduleController extends Controller
{
    public static function getPublicSchedules(): array
    {
        $schedules = Penjadwalan::query()
            ->where('penjadwalan.status', 'terpublikasi')
            ->with(['mahasiswa:id,name', 'pembimbing1:id,name', 'pembimbing2:id,name', 'penguji1:id,name', 'penguji2:id,name'])
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->orderBy('penjadwalan.id')
            ->get();

        $titles = [];
        foreach (['sempro' => PendaftaranSempro::class, 'sidang' => PendaftaranSidang::class] as $type => $model) {
            $titles[$type] = $model::whereIn('id', $schedules->where('tipe', $type)->pluck('pendaftaran_id'))
                ->get(['id', 'mahasiswa_id', 'judul_ta'])
                ->keyBy('id');
        }

        return $schedules->map(function ($schedule) use ($titles) {
            $registration = $titles[$schedule->tipe]->get($schedule->pendaftaran_id);

            return [
                'student_name' => $schedule->mahasiswa?->name,
                'title' => $registration?->mahasiswa_id === $schedule->mahasiswa_id ? $registration->judul_ta : null,
                'type' => $schedule->tipe,
                'date' => $schedule->tanggal->format('Y-m-d'),
                'start_time' => $schedule->jam_mulai,
                'end_time' => $schedule->jam_selesai,
                'mode' => $schedule->mode,
                'supervisors' => array_values(array_filter([$schedule->pembimbing1?->name, $schedule->pembimbing2?->name])),
                'examiners' => array_values(array_filter([$schedule->penguji1?->name, $schedule->penguji2?->name])),
            ];
        })->values()->all();
    }

    public static function getPublicPeriods(): array
    {
        return DB::table('schedule_periods')
            ->where('is_open', true)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'type' => $p->tipe,
                    'gelombang' => $p->gelombang,
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                    'is_open' => (bool) $p->is_open,
                ];
            })->all();
    }

    public function index(): Response
    {
        return Inertia::render('Public/Jadwal', [
            'publicSchedules' => self::getPublicSchedules(),
            'publicPeriods' => self::getPublicPeriods(),
        ]);
    }
}
