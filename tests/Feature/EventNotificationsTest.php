<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_tendik_verification_triggers_notification(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $sempro = PendaftaranSempro::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Judul Sempro',
            'bentuk_ta' => 'Skripsi',
            'status' => 'menunggu',
        ]);

        $this->actingAs($tendik)
            ->patch("/tendik/verifikasi/sempro/{$sempro->id}", [
                'status' => 'verifikasi_tendik',
                'catatan' => 'Berkas lengkap',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Verifikasi Berkas Seminar Proposal',
            'type' => 'info',
        ]);

        $sidang = PendaftaranSidang::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Judul Sidang',
            'bentuk_ta' => 'Skripsi',
            'status' => 'menunggu',
        ]);

        $this->actingAs($tendik)
            ->patch("/tendik/verifikasi/sidang/{$sidang->id}", [
                'status' => 'ditolak',
                'catatan' => 'Turnitin melebihi batas',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Verifikasi Berkas Sidang TA',
            'type' => 'warning',
        ]);
    }

    public function test_kaprodi_title_approval_triggers_notification_for_student_and_supervisors(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Budi Santoso']);
        $dosen1 = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Satu']);
        $dosen2 = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Dua']);

        $judul = PengajuanJudul::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Sistem Rekomendasi Pintar',
            'bidang_penelitian' => 'AI',
            'status' => 'menunggu',
        ]);

        $this->actingAs($kaprodi)
            ->patch("/persetujuan/judul/{$judul->id}", [
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Lanjutkan ke proposal',
                'pembimbing_1_id' => $dosen1->id,
                'pembimbing_2_id' => $dosen2->id,
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Status Pengajuan Judul TA',
            'type' => 'success',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dosen1->id,
            'title' => 'Penugasan Pembimbing 1',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $dosen2->id,
            'title' => 'Penugasan Pembimbing 2',
        ]);
    }

    public function test_dosen_bimbingan_paraf_triggers_notification(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $student = User::factory()->create(['role' => 'mahasiswa']);

        $bimbingan = Bimbingan::create([
            'mahasiswa_id' => $student->id,
            'dosen_id' => $dosen->id,
            'tanggal' => now(),
            'rangkuman' => 'Diskusi Bab 2',
            'keterangan' => 'Bimbingan berkala Bab 2',
            'status' => 'process',
        ]);

        $this->actingAs($dosen)
            ->patch("/dosen/bimbingan/{$bimbingan->id}/paraf", [
                'status' => 'delivered',
                'catatan_dosen' => 'Bagus, lanjut Bab 3',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'title' => 'Logbook Bimbingan Diparaf',
            'type' => 'success',
        ]);
    }
}
