<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PengajuanJudul;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaBackendTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_dapat_login_menggunakan_nim(): void
    {
        $user = User::factory()->create([
            'username' => '11231006',
            'email' => 'mahasiswa@student.itk.ac.id',
            'password' => bcrypt('password123'),
            'role' => 'mahasiswa',
        ]);

        $response = $this->post('/login', [
            'email' => '11231006',
            'password' => 'password123',
            'remember' => true,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_mahasiswa_dapat_mengajukan_judul_ta(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen', 'name' => 'Dr. Hendra Wijaya']);

        $response = $this->actingAs($mahasiswa)->post('/pendaftaran/judul', [
            'judul_ta' => 'Implementasi Machine Learning untuk Prediksi Cuaca',
            'bidang_penelitian' => 'Kecerdasan Buatan',
            'pembimbing_1' => $dosen->id,
            'telah_konsultasi' => true,
        ]);

        $response->assertStatus(302); // Redirect back with flash
        $this->assertDatabaseHas('pengajuan_judul', [
            'mahasiswa_id' => $mahasiswa->id,
            'judul_ta' => 'Implementasi Machine Learning untuk Prediksi Cuaca',
            'bidang_penelitian' => 'Kecerdasan Buatan',
            'status' => 'menunggu',
        ]);
    }

    public function test_mahasiswa_dapat_menginput_logbook_bimbingan(): void
    {
        $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
        $dosen = User::factory()->create(['role' => 'dosen']);

        $response = $this->actingAs($mahasiswa)->post('/pendaftaran/bimbingan', [
            'tanggal' => now()->format('Y-m-d'),
            'dosen' => $dosen->id,
            'rangkuman' => 'Membahas bab 1 dan tinjauan pustaka',
            'keterangan' => 'Tatap Muka di Lab',
        ]);

        $response->assertStatus(302);
        $this->assertDatabaseHas('bimbingan', [
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_id' => $dosen->id,
            'rangkuman' => 'Membahas bab 1 dan tinjauan pustaka',
            'status' => 'process',
        ]);
    }
}
