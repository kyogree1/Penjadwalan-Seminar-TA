<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PortalsBackendTest extends TestCase
{
    use RefreshDatabase;

    /* -------------------------------------------------------------
     * KAPRODI PORTAL TESTS
     * -----------------------------------------------------------*/

    public function test_kaprodi_can_crud_dosen(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);

        // 1. Create Dosen
        $response = $this->actingAs($kaprodi)->post('/kaprodi/dosen', [
            'name' => 'Dr. Budi Santoso, M.Kom.',
            'email' => 'budi.santoso@lecturer.itk.ac.id',
            'nip' => '198506152015041009',
            'prodi' => 'Informatika',
            'jabatan' => 'Lektor Kepala',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'email' => 'budi.santoso@lecturer.itk.ac.id',
            'role' => 'dosen',
        ]);

        $dosen = User::where('email', 'budi.santoso@lecturer.itk.ac.id')->first();

        // 2. Update Dosen
        $updateResp = $this->actingAs($kaprodi)->put("/kaprodi/dosen/{$dosen->id}", [
            'name' => 'Prof. Dr. Budi Santoso, M.Kom.',
            'email' => 'budi.santoso@lecturer.itk.ac.id',
            'prodi' => 'Informatika',
            'jabatan' => 'Guru Besar',
        ]);

        $updateResp->assertStatus(302);
        $this->assertDatabaseHas('users', [
            'id' => $dosen->id,
            'name' => 'Prof. Dr. Budi Santoso, M.Kom.',
            'jabatan' => 'Guru Besar',
        ]);

        // 3. Delete Dosen
        $delResp = $this->actingAs($kaprodi)->delete("/kaprodi/dosen/{$dosen->id}");
        $delResp->assertStatus(302);
        $this->assertDatabaseMissing('users', ['id' => $dosen->id]);
    }

    public function test_kaprodi_can_approve_judul_with_notes(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $judul = PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul_ta' => 'Optimasi Algoritma Genetika pada Penjadwalan Sidang',
            'bidang_penelitian' => 'Sistem Cerdas',
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($kaprodi)->patch("/kaprodi/persetujuan/judul/{$judul->id}", [
            'status' => 'disetujui',
            'catatan_kaprodi' => 'Judul sangat relevan dengan keilmuan prodi. Disetujui.',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pengajuan_judul', [
            'id' => $judul->id,
            'status' => 'disetujui',
            'catatan_kaprodi' => 'Judul sangat relevan dengan keilmuan prodi. Disetujui.',
            'disetujui_oleh' => $kaprodi->id,
        ]);
    }

    /* -------------------------------------------------------------
     * DOSEN PORTAL TESTS
     * -----------------------------------------------------------*/

    public function test_dosen_can_create_sesi_and_paraf_bimbingan(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen']);
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        // 1. Dosen creates session
        $storeResp = $this->actingAs($dosen)->post('/dosen/bimbingan/sesi', [
            'mahasiswa_id' => $mahasiswa->id,
            'tanggal' => '2026-09-28',
            'rangkuman' => 'Diskusi Bab 3 Metodologi Penelitian dan Desain Kromosom GA',
            'catatan_dosen' => 'Bagus, lanjutkan penulisan Bab 4.',
        ]);

        $storeResp->assertStatus(302);
        $this->assertDatabaseHas('bimbingan', [
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_id' => $dosen->id,
            'status' => 'delivered',
        ]);

        $bimbingan = Bimbingan::where('mahasiswa_id', $mahasiswa->id)->first();

        // 2. Dosen updates paraf / review
        $parafResp = $this->actingAs($dosen)->patch("/dosen/bimbingan/{$bimbingan->id}/paraf", [
            'status' => 'disetujui',
            'catatan_dosen' => 'ACC Bab 3, siap lanjut bab berikutnya.',
        ]);

        $parafResp->assertStatus(302);
        $this->assertDatabaseHas('bimbingan', [
            'id' => $bimbingan->id,
            'catatan_dosen' => 'ACC Bab 3, siap lanjut bab berikutnya.',
        ]);
    }

    /* -------------------------------------------------------------
     * TENDIK PORTAL TESTS
     * -----------------------------------------------------------*/

    public function test_tendik_can_manage_ruangan(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);

        // Create room
        $createResp = $this->actingAs($tendik)->post('/tendik/ruangan', [
            'nama_ruangan' => 'Ruang Sidang Baru FSTI',
            'gedung' => 'GKT 3',
            'lantai' => 'Lantai 3',
            'kapasitas' => 25,
            'status' => 'tersedia',
            'keterangan' => 'Dilengkapi proyektor 4K',
        ]);

        $createResp->assertStatus(302);
        $this->assertDatabaseHas('ruangan', [
            'nama_ruangan' => 'Ruang Sidang Baru FSTI',
            'kapasitas' => 25,
        ]);
    }

    public function test_tendik_can_reset_student_password(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $mahasiswa = User::factory()->create([
            'role' => 'mahasiswa',
            'username' => '11231006',
            'password' => bcrypt('custom_secret_password'),
        ]);

        $response = $this->actingAs($tendik)->post("/tendik/mahasiswa/{$mahasiswa->id}/reset-password");

        $response->assertStatus(302);

        $mahasiswa->refresh();
        $this->assertTrue(Hash::check('11231006', $mahasiswa->password));
    }
}
