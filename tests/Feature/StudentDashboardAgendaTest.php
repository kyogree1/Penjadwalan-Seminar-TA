<?php

namespace Tests\Feature;

use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentDashboardAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_supervisors_and_all_schedule_positions_are_loaded_without_private_lecturer_fields(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $lecturers = User::factory()->count(4)->create(['role' => 'dosen']);
        $title = ['mahasiswa_id' => $student->id, 'judul_ta' => 'Approved research', 'bidang_penelitian' => 'Informatika', 'pembimbing_1_id' => $lecturers[0]->id, 'pembimbing_2_id' => $lecturers[1]->id];
        $approved = PengajuanJudul::create($title + ['status' => 'disetujui']);
        PengajuanJudul::create(array_replace($title, ['status' => 'menunggu', 'created_at' => now()->addDay()]));
        Penjadwalan::create(['mahasiswa_id' => $student->id, 'tipe' => 'sidang', 'pendaftaran_id' => 1, 'tanggal' => '2026-10-01', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:00:00', 'status' => 'terpublikasi', 'pembimbing_1_id' => $lecturers[0]->id, 'pembimbing_2_id' => $lecturers[1]->id, 'penguji_1_id' => $lecturers[2]->id, 'penguji_2_id' => $lecturers[3]->id]);
        $this->actingAs($student)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('dbApprovedTitle.id', $approved->id)
            ->where('dbApprovedTitle.pembimbing1.name', $lecturers[0]->name)
            ->where('dbApprovedTitle.pembimbing2.name', $lecturers[1]->name)
            ->where('dbJadwalList.0.pembimbing1.name', $lecturers[0]->name)
            ->where('dbJadwalList.0.pembimbing2.name', $lecturers[1]->name)
            ->where('dbJadwalList.0.penguji1.name', $lecturers[2]->name)
            ->where('dbJadwalList.0.penguji2.name', $lecturers[3]->name)
            ->missing('dbJadwalList.0.penguji2.email'));
    }

    public function test_empty_student_has_no_agenda_or_approved_team(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $other = User::factory()->create(['role' => 'mahasiswa']);
        PengajuanJudul::create(['mahasiswa_id' => $other->id, 'judul_ta' => 'Other research', 'bidang_penelitian' => 'Informatika', 'status' => 'disetujui']);
        PengajuanJudul::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Pending research', 'bidang_penelitian' => 'Informatika', 'status' => 'menunggu']);
        $this->actingAs($student)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->where('dbJadwalList', [])->where('dbJadwal', null)->where('dbApprovedTitle', null));
    }

    public function test_personal_agenda_contains_only_owned_published_schedules_in_date_and_time_order(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $other = User::factory()->create(['role' => 'mahasiswa']);
        $base = ['mahasiswa_id' => $student->id, 'tipe' => 'sempro', 'pendaftaran_id' => 1, 'tanggal' => '2026-12-31', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:00:00', 'status' => 'terpublikasi'];
        $late = Penjadwalan::create($base);
        $early = Penjadwalan::create(array_replace($base, ['jam_mulai' => '09:00:00']));
        $first = Penjadwalan::create(array_replace($base, ['tanggal' => '2026-10-01', 'tipe' => 'sidang']));
        Penjadwalan::create(array_replace($base, ['mahasiswa_id' => $other->id]));
        foreach (['draft', 'dibatalkan'] as $status) {
            Penjadwalan::create(array_replace($base, ['status' => $status]));
        }
        $this->actingAs($student)->get('/dashboard')->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')->has('dbJadwalList', 3)
            ->where('dbJadwalList.0.id', $first->id)
            ->where('dbJadwalList.1.id', $early->id)
            ->where('dbJadwalList.2.id', $late->id)
            ->where('dbJadwal.id', $late->id));
    }
}
