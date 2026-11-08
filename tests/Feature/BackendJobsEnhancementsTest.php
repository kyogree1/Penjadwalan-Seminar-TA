<?php

namespace Tests\Feature;

use App\Models\Penilaian;
use App\Models\Penjadwalan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class BackendJobsEnhancementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_katalog_ta_returns_completed_sidang_from_db(): void
    {
        $student = User::factory()->create(['name' => 'Budi Mahasiswa', 'nim_nip' => '11201001', 'role' => 'mahasiswa']);
        $dosen = User::factory()->create(['name' => 'Dr. Bambang', 'nim_nip' => '19800101', 'role' => 'dosen']);

        PengajuanJudul::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Sistem Cerdas Klasifikasi Citra Medis',
            'bidang_penelitian' => 'Visi Komputer',
            'pembimbing_1_id' => $dosen->id,
            'status' => 'disetujui',
        ]);

        PendaftaranSidang::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Sistem Cerdas Klasifikasi Citra Medis',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($student)->get('/katalog');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Katalog/Index')
            ->has('items', 1)
            ->where('items.0.judul', 'Sistem Cerdas Klasifikasi Citra Medis')
            ->where('items.0.penulis', 'Budi Mahasiswa')
            ->where('items.0.pembimbing_1', 'Dr. Bambang')
            ->has('dosenOptions')
        );
    }

    public function test_dosen_dashboard_returns_real_supervised_students_and_stages(): void
    {
        $dosen = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Utama']);
        $student1 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mhs Bimbingan 1']);
        $student2 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Mhs Sempro']);

        PengajuanJudul::create([
            'mahasiswa_id' => $student1->id,
            'judul_ta' => 'Judul Mhs 1',
            'bidang_penelitian' => 'AI',
            'pembimbing_1_id' => $dosen->id,
            'status' => 'disetujui',
        ]);

        PengajuanJudul::create([
            'mahasiswa_id' => $student2->id,
            'judul_ta' => 'Judul Mhs 2',
            'bidang_penelitian' => 'IoT',
            'pembimbing_1_id' => $dosen->id,
            'status' => 'disetujui',
        ]);

        PendaftaranSempro::create([
            'mahasiswa_id' => $student2->id,
            'judul_ta' => 'Judul Mhs 2',
            'bentuk_ta' => 'Skripsi',
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($dosen)->get('/dosen/dashboard');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Dosen/Dashboard')
            ->has('mySupervisedStudents', 2)
            ->has('adviseeStages', 3)
            ->has('upcomingExams')
        );
    }

    public function test_kaprodi_persetujuan_loads_grades_from_penilaian(): void
    {
        $kaprodi = User::factory()->create(['role' => 'kaprodi']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Siti']);
        $examiner = User::factory()->create(['role' => 'dosen']);

        $sidang = PendaftaranSidang::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Analisis Big Data',
            'status' => 'selesai',
        ]);

        $jadwal = Penjadwalan::create([
            'tipe' => 'sidang',
            'pendaftaran_id' => $sidang->id,
            'mahasiswa_id' => $student->id,
            'penguji_1_id' => $examiner->id,
            'tanggal' => now()->toDateString(),
            'jam_mulai' => '09:00',
            'jam_selesai' => '10:00',
            'status' => 'selesai',
        ]);

        Penilaian::create([
            'penjadwalan_id' => $jadwal->id,
            'dosen_id' => $examiner->id,
            'nilai' => 88.0,
            'catatan' => 'Sangat bagus',
        ]);

        $response = $this->actingAs($kaprodi)->get('/kaprodi/persetujuan');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Kaprodi/Persetujuan')
            ->has('dbSidang', 1)
            ->where('dbSidang.0.nilai', 'A')
            ->where('dbSidang.0.nilai_angka', fn ($v) => (float) $v === 88.0)
            ->where('dbSidang.0.jumlah_penilai', 1)
        );
    }

    public function test_tendik_arsip_loads_finished_thesis_with_persistence(): void
    {
        $tendik = User::factory()->create(['role' => 'tendik']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Alumni Hebat']);

        PendaftaranSidang::create([
            'mahasiswa_id' => $student->id,
            'judul_ta' => 'Arsip Berita Acara Skripsi',
            'status' => 'selesai',
        ]);

        $response = $this->actingAs($tendik)->get('/tendik/arsip');
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => $page
            ->component('Tendik/Arsip')
            ->where('archiveCapabilities.persistence', true)
            ->has('arsipList', 1)
            ->where('arsipList.0.judul', 'Arsip Berita Acara Skripsi')
        );
    }
}
