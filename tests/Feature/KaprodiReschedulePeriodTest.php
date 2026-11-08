<?php

namespace Tests\Feature;

use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\Penilaian;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaprodiReschedulePeriodTest extends TestCase
{
    use RefreshDatabase;

    private User $kaprodi;
    private User $dosen1;
    private User $dosen2;
    private User $dosen3;
    private User $dosen4;
    private User $dosen5;
    private User $student1;
    private User $student2;
    private SchedulePeriod $period;
    private PendaftaranSempro $reg1;
    private PendaftaranSempro $reg2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kaprodi = User::factory()->create([
            'role' => 'kaprodi',
            'prodi' => 'Informatika',
        ]);

        $this->dosen1 = User::factory()->create([
            'role' => 'dosen',
            'prodi' => 'Informatika',
        ]);
        $this->dosen2 = User::factory()->create([
            'role' => 'dosen',
            'prodi' => 'Informatika',
        ]);
        $this->dosen3 = User::factory()->create([
            'role' => 'dosen',
            'prodi' => 'Informatika',
        ]);
        $this->dosen4 = User::factory()->create([
            'role' => 'dosen',
            'prodi' => 'Informatika',
        ]);
        $this->dosen5 = User::factory()->create([
            'role' => 'dosen',
            'prodi' => 'Informatika',
        ]);

        $this->student1 = User::factory()->create([
            'role' => 'mahasiswa',
            'prodi' => 'Informatika',
            'nim_nip' => '11231001',
        ]);
        $this->student2 = User::factory()->create([
            'role' => 'mahasiswa',
            'prodi' => 'Informatika',
            'nim_nip' => '11231002',
        ]);

        PengajuanJudul::create([
            'mahasiswa_id' => $this->student1->id,
            'judul_ta' => 'Topik Skripsi Mahasiswa 1',
            'bidang_penelitian' => 'AI & Data Science',
            'pembimbing_1_id' => $this->dosen1->id,
            'pembimbing_2_id' => $this->dosen2->id,
            'status' => 'disetujui',
        ]);

        PengajuanJudul::create([
            'mahasiswa_id' => $this->student2->id,
            'judul_ta' => 'Topik Skripsi Mahasiswa 2',
            'bidang_penelitian' => 'Software Engineering',
            'pembimbing_1_id' => $this->dosen2->id,
            'pembimbing_2_id' => $this->dosen3->id,
            'status' => 'disetujui',
        ]);

        $this->period = SchedulePeriod::create([
            'name' => '2026/2027',
            'tipe' => 'sempro',
            'semester' => 'Gasal',
            'prodi' => 'Informatika',
            'gelombang' => 2,
            'capacity' => 10,
            'start_date' => '2026-10-01',
            'end_date' => '2026-10-31',
            'is_open' => false,
            'is_published' => false,
            'slots' => [
                ['tanggal' => '2026-10-20', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
                ['tanggal' => '2026-10-20', 'jam_mulai' => '10:30:00', 'jam_selesai' => '12:00:00'],
                ['tanggal' => '2026-10-21', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00'],
                ['tanggal' => '2026-10-21', 'jam_mulai' => '10:30:00', 'jam_selesai' => '12:00:00'],
            ],
        ]);

        $this->reg1 = PendaftaranSempro::create([
            'mahasiswa_id' => $this->student1->id,
            'judul_ta' => 'Topik Skripsi Mahasiswa 1',
            'bentuk_ta' => 'Skripsi Reguler',
            'gelombang' => 2,
            'status' => 'verifikasi_tendik',
            'status_akademik' => 'disetujui',
        ]);

        $this->reg2 = PendaftaranSempro::create([
            'mahasiswa_id' => $this->student2->id,
            'judul_ta' => 'Topik Skripsi Mahasiswa 2',
            'bentuk_ta' => 'Skripsi Reguler',
            'gelombang' => 2,
            'status' => 'verifikasi_tendik',
            'status_akademik' => 'disetujui',
        ]);
    }

    public function test_kaprodi_can_reset_period_schedules(): void
    {
        // Simulasikan jadwal sudah pernah disimpan
        $jadwal1 = Penjadwalan::create([
            'period_id' => $this->period->id,
            'pendaftaran_id' => $this->reg1->id,
            'mahasiswa_id' => $this->student1->id,
            'tipe' => 'sempro',
            'pembimbing_1_id' => $this->dosen1->id,
            'pembimbing_2_id' => $this->dosen2->id,
            'penguji_1_id' => $this->dosen3->id,
            'penguji_2_id' => $this->dosen1->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
            'gelombang' => 2,
            'dibuat_oleh' => $this->kaprodi->id,
        ]);
        $this->reg1->update(['status' => 'terjadwal']);
        $this->period->update(['is_published' => true]);

        $this->assertDatabaseHas('penjadwalan', ['id' => $jadwal1->id]);
        $this->assertEquals('terjadwal', $this->reg1->fresh()->status);

        // Kaprodi memanggil reset / reschedule periode
        $response = $this->actingAs($this->kaprodi)
            ->postJson("/kaprodi/penjadwalan/periods/{$this->period->id}/reset");

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Jadwal terhapus
        $this->assertDatabaseMissing('penjadwalan', ['id' => $jadwal1->id]);

        // Status pendaftaran kembali menjadi 'verifikasi_tendik'
        $this->assertEquals('verifikasi_tendik', $this->reg1->fresh()->status);

        // Periode is_published kembali false
        $this->assertFalse((bool) $this->period->fresh()->is_published);
    }

    public function test_kaprodi_can_reschedule_via_reschedule_route(): void
    {
        $jadwal = Penjadwalan::create([
            'period_id' => $this->period->id,
            'pendaftaran_id' => $this->reg1->id,
            'mahasiswa_id' => $this->student1->id,
            'tipe' => 'sempro',
            'pembimbing_1_id' => $this->dosen1->id,
            'penguji_1_id' => $this->dosen2->id,
            'penguji_2_id' => $this->dosen3->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
            'gelombang' => 2,
            'dibuat_oleh' => $this->kaprodi->id,
        ]);
        $this->reg1->update(['status' => 'terjadwal']);

        $response = $this->actingAs($this->kaprodi)
            ->postJson("/kaprodi/penjadwalan/periods/{$this->period->id}/reschedule");

        $response->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('penjadwalan', ['id' => $jadwal->id]);
        $this->assertEquals('verifikasi_tendik', $this->reg1->fresh()->status);
    }

    public function test_generate_ga_with_reschedule_flag_clears_old_schedules_and_runs(): void
    {
        Penjadwalan::create([
            'period_id' => $this->period->id,
            'pendaftaran_id' => $this->reg1->id,
            'mahasiswa_id' => $this->student1->id,
            'tipe' => 'sempro',
            'pembimbing_1_id' => $this->dosen1->id,
            'penguji_1_id' => $this->dosen2->id,
            'penguji_2_id' => $this->dosen3->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'terpublikasi',
            'gelombang' => 2,
            'dibuat_oleh' => $this->kaprodi->id,
        ]);
        $this->reg1->update(['status' => 'terjadwal']);
        $this->period->update(['is_published' => true]);

        // Panggil GA dengan reschedule = true
        $response = $this->actingAs($this->kaprodi)
            ->postJson('/kaprodi/penjadwalan/generate-ga', [
                'period_id' => $this->period->id,
                'reschedule' => true,
                'popSize' => 10,
                'maxGenerations' => 10,
            ]);

        $response->assertOk();
        $json = $response->json();

        // GA harus berhasil menemukan jadwal
        $this->assertNotEmpty($json['schedule']);
    }

    public function test_plotting_sempro_page_shows_all_registered_students_even_before_scheduled(): void
    {
        $response = $this->actingAs($this->kaprodi)
            ->get("/kaprodi/penjadwalan/plotting-sempro?period_id={$this->period->id}");

        $response->assertOk();
        $schedules = $response->viewData('page')['props']['schedules'];

        // Ada 2 pendaftar (student1 dan student2) yang muncul di tabel
        $this->assertCount(2, $schedules);
        $this->assertEquals('-', $schedules[0]['penguji1']);
        $this->assertEquals('-', $schedules[0]['hari']);
        $this->assertFalse($schedules[0]['is_scheduled']);
    }

    public function test_cannot_reset_period_if_any_schedule_has_grades_or_is_finished(): void
    {
        $jadwal = Penjadwalan::create([
            'period_id' => $this->period->id,
            'pendaftaran_id' => $this->reg1->id,
            'mahasiswa_id' => $this->student1->id,
            'tipe' => 'sempro',
            'pembimbing_1_id' => $this->dosen1->id,
            'penguji_1_id' => $this->dosen2->id,
            'penguji_2_id' => $this->dosen3->id,
            'tanggal' => '2026-10-20',
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'status' => 'selesai',
            'gelombang' => 2,
            'dibuat_oleh' => $this->kaprodi->id,
        ]);

        Penilaian::create([
            'penjadwalan_id' => $jadwal->id,
            'dosen_id' => $this->dosen2->id,
            'nilai' => 85.0,
            'catatan' => 'Baik',
        ]);

        $response = $this->actingAs($this->kaprodi)
            ->postJson("/kaprodi/penjadwalan/periods/{$this->period->id}/reset");

        $response->assertStatus(422)
            ->assertJsonValidationErrors('period');

        // Jadwal tetap ada
        $this->assertDatabaseHas('penjadwalan', ['id' => $jadwal->id]);
    }
}
