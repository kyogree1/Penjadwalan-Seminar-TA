<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicSchedulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_publication_is_an_empty_array_for_both_guest_routes(): void
    {
        foreach (['/', '/login'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')->where('publicSchedules', []));
        }
    }

    public function test_authenticated_users_keep_their_role_redirect_on_both_routes(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'dosen']));
        foreach (['/', '/login'] as $url) {
            $this->get($url)->assertRedirect(route('dosen.dashboard'));
        }
    }

    public function test_missing_or_mismatched_registration_never_exposes_another_students_title(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $other = User::factory()->create(['role' => 'mahasiswa']);
        $registration = PendaftaranSidang::create(['mahasiswa_id' => $other->id, 'judul_ta' => 'PRIVATE OTHER TITLE']);
        foreach ([$registration->id, 99999] as $registrationId) {
            Penjadwalan::create([
                'tipe' => 'sidang', 'pendaftaran_id' => $registrationId,
                'mahasiswa_id' => $student->id, 'tanggal' => '2026-11-01',
                'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00',
                'status' => 'terpublikasi', 'mode' => 'online',
            ]);
        }
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('publicSchedules', 2)
            ->where('publicSchedules.0.title', null)
            ->where('publicSchedules.1.title', null)
            ->where('publicSchedules.0.room', null)
            ->where('publicSchedules.0.supervisors', [])
            ->where('publicSchedules.0.examiners', []));
    }

    public function test_guests_see_only_published_schedules_with_explicit_public_fields_on_home_and_login(): void
    {
        $student = User::factory()->create(['name' => 'Student Public', 'role' => 'mahasiswa', 'email' => 'private@example.test']);
        $lecturer = User::factory()->create(['name' => 'Lecturer Public', 'role' => 'dosen']);
        $room = Ruangan::create(['nama_ruangan' => 'Room A', 'gedung' => 'Building', 'lantai' => 1, 'kapasitas' => 20, 'keterangan' => 'PRIVATE ROOM NOTE']);
        $sempro = PendaftaranSempro::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Proposal title', 'bentuk_ta' => 'Skripsi', 'proposal_file' => 'PRIVATE DOCUMENT', 'catatan' => 'PRIVATE REGISTRATION NOTE']);
        $sidang = PendaftaranSidang::create(['mahasiswa_id' => $student->id, 'judul_ta' => 'Defense title', 'draft_laporan_file' => 'PRIVATE DRAFT']);
        foreach (['sidang' => $sidang, 'sempro' => $sempro] as $type => $registration) {
            foreach (['terpublikasi', 'draft', 'dibatalkan', 'selesai'] as $status) {
                Penjadwalan::create([
                    'tipe' => $type, 'pendaftaran_id' => $registration->id,
                    'mahasiswa_id' => $student->id, 'ruangan_id' => $room->id,
                    'pembimbing_1_id' => $lecturer->id, 'penguji_1_id' => $lecturer->id,
                    'tanggal' => $type === 'sempro' ? '2026-11-01' : '2026-11-02',
                    'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00',
                    'mode' => 'hybrid', 'status' => $status,
                    'link_online' => 'https://private-meeting.example.test', 'catatan' => 'PRIVATE SCHEDULE NOTE',
                ]);
            }
        }
        $expected = array_map(fn ($type) => [
            'student_name' => 'Student Public',
            'title' => $type === 'sempro' ? 'Proposal title' : 'Defense title',
            'type' => $type, 'date' => $type === 'sempro' ? '2026-11-01' : '2026-11-02',
            'start_time' => '09:00:00', 'end_time' => '10:30:00',
            'room' => 'Room A', 'mode' => 'hybrid',
            'supervisors' => ['Lecturer Public'], 'examiners' => ['Lecturer Public'],
        ], ['sempro', 'sidang']);
        foreach (['/', '/login'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login')->where('publicSchedules', $expected));
        }
    }
}
