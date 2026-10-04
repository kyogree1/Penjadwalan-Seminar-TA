<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MahasiswaFlowComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_mahasiswa_can_visit_all_pages_without_errors(): void
    {
        $mahasiswa = User::factory()->create([
            'role' => 'mahasiswa',
            'username' => '11231006',
            'name' => 'Akmal Falah Maulana',
            'prodi' => 'Informatika',
        ]);

        // 1. Dashboard
        $resp = $this->actingAs($mahasiswa)->get('/dashboard');
        $resp->assertOk();

        // 2. Pengajuan Judul
        $resp = $this->actingAs($mahasiswa)->get('/pendaftaran/judul');
        $resp->assertOk();

        // 3. Logbook Bimbingan
        $resp = $this->actingAs($mahasiswa)->get('/pendaftaran/bimbingan');
        $resp->assertOk();

        // 4. Pendaftaran Jadwal
        $resp = $this->actingAs($mahasiswa)->get('/pendaftaran/jadwal');
        $resp->assertOk();

        // 5. Sempro
        $resp = $this->actingAs($mahasiswa)->get('/pendaftaran/sempro');
        $resp->assertOk();

        // 6. Sidang
        $resp = $this->actingAs($mahasiswa)->get('/pendaftaran/sidang');
        $resp->assertOk();

        // 7. Static/Public Pages
        $resp = $this->actingAs($mahasiswa)->get('/panduan');
        $resp->assertOk();

        $resp = $this->actingAs($mahasiswa)->get('/prosedur');
        $resp->assertOk();

        $resp = $this->actingAs($mahasiswa)->get('/katalog');
        $resp->assertOk();
    }

    public function test_mahasiswa_sempro_submission_flow(): void
    {
        Storage::fake('public');
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $file = UploadedFile::fake()->create('proposal.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($mahasiswa)->post('/pendaftaran/sempro', [
            'judul_ta' => 'Sistem Informasi Penjadwalan Sidang',
            'bentuk_ta' => 'Skripsi Reguler',
            'lokasi_mitra' => 'Balikpapan',
            'proposal_file' => $file,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pendaftaran_sempro', [
            'mahasiswa_id' => $mahasiswa->id,
            'judul_ta' => 'Sistem Informasi Penjadwalan Sidang',
            'status' => 'menunggu',
        ]);
    }

    public function test_mahasiswa_sidang_submission_flow(): void
    {
        Storage::fake('public');
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);

        $file = UploadedFile::fake()->create('draft.pdf', 1000, 'application/pdf');

        $response = $this->actingAs($mahasiswa)->post('/pendaftaran/sidang', [
            'judul_ta' => 'Sistem Informasi Penjadwalan Sidang Final',
            'lokasi_mitra' => 'Balikpapan',
            'draft_laporan_file' => $file,
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('pendaftaran_sidang', [
            'mahasiswa_id' => $mahasiswa->id,
            'judul_ta' => 'Sistem Informasi Penjadwalan Sidang Final',
            'status' => 'menunggu',
        ]);
    }
}
