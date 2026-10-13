<?php

namespace Database\Seeders;

use App\Models\Bimbingan;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Mahasiswa
        $mahasiswa = User::updateOrCreate(
            ['email' => 'akmal.falah@student.itk.ac.id'],
            [
                'name' => 'Akmal Falah Maulana',
                'username' => '11231006',
                'role' => 'mahasiswa',
                'nim_nip' => 'NIM: 11231006',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Mahasiswa S1 Informatika',
                'password' => bcrypt('password'),
            ]
        );

        // 2. Dosen (Pembimbing & Penguji)
        $dosen1 = User::updateOrCreate(
            ['email' => 'tejo.wahyu@lecturer.itk.ac.id'],
            [
                'name' => 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
                'username' => '198504122010121003',
                'role' => 'dosen',
                'nim_nip' => 'NIP: 198504122010121003',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Dosen Pembimbing & Penguji',
                'password' => bcrypt('password'),
            ]
        );

        $dosen2 = User::updateOrCreate(
            ['email' => 'rina.agustina@lecturer.itk.ac.id'],
            [
                'name' => 'Rina Agustina, S.T., M.Kom.',
                'username' => '198803152018012001',
                'role' => 'dosen',
                'nim_nip' => 'NIP: 198803152018012001',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Dosen Penguji',
                'password' => bcrypt('password'),
            ]
        );

        // 3. Kaprodi (Koordinator Prodi)
        $kaprodi = User::updateOrCreate(
            ['email' => 'kaprodi.if@itk.ac.id'],
            [
                'name' => 'Prof. Dr. Eng. Muhammad Ridwan, S.T., M.T.',
                'username' => '198003152005011002',
                'role' => 'kaprodi',
                'nim_nip' => 'NIP: 198003152005011002',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Ketua Program Studi Informatika',
                'password' => bcrypt('password'),
            ]
        );

        // 4. Tendik (Tenaga Kependidikan / Admin Akademik)
        User::updateOrCreate(
            ['email' => 'tendik.fsti@itk.ac.id'],
            [
                'name' => 'Siti Nurhaliza, S.Kom.',
                'username' => '199208192018032001',
                'role' => 'tendik',
                'nim_nip' => 'NIP: 199208192018032001',
                'prodi' => 'Fakultas Sains & Teknologi Informasi',
                'jabatan' => 'Tenaga Kependidikan / Staf Akademik',
                'password' => bcrypt('password'),
            ]
        );

        // 5. Paket demo alur penuh: judul disetujui -> bimbingan -> sempro
        // terverifikasi -> periode terbuka -> jadwal terpublikasi.
        PengajuanJudul::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'judul_ta' => 'Implementasi Machine Learning untuk Prediksi Cuaca',
                'bidang_penelitian' => 'Kecerdasan Buatan',
                'pembimbing_1_id' => $dosen1->id,
                'pembimbing_2_id' => $dosen2->id,
                'telah_konsultasi' => true,
                'status' => 'disetujui',
                'disetujui_oleh' => $kaprodi->id,
                'disetujui_at' => now(),
            ]
        );

        Bimbingan::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id, 'dosen_id' => $dosen1->id, 'tanggal' => '2026-09-01'],
            [
                'rangkuman' => 'Membahas bab 1 dan tinjauan pustaka',
                'keterangan' => 'Tatap Muka di Lab',
                'status' => 'delivered',
                'catatan_dosen' => 'Lanjut ke bab 2',
            ]
        );

        $sempro = PendaftaranSempro::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'judul_ta' => 'Implementasi Machine Learning untuk Prediksi Cuaca',
                'bentuk_ta' => 'Skripsi Reguler',
                'gelombang' => 1,
                'status' => 'terjadwal',
                'status_akademik' => 'disetujui',
            ]
        );

        PendaftaranSidang::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id],
            [
                'judul_ta' => 'Implementasi Machine Learning untuk Prediksi Cuaca',
                'gelombang' => 1,
                'status' => 'menunggu',
                'status_akademik' => 'menunggu',
            ]
        );

        $period = SchedulePeriod::updateOrCreate(
            ['tipe' => 'sempro', 'gelombang' => 1],
            [
                'name' => 'Sempro Gelombang 1',
                'capacity' => 20,
                'start_date' => '2026-11-01',
                'end_date' => '2026-11-30',
                'slots' => [['tanggal' => '2026-11-10', 'jam_mulai' => '09:00:00', 'jam_selesai' => '10:30:00']],
                'is_open' => true,
            ]
        );

        Penjadwalan::updateOrCreate(
            ['tipe' => 'sempro', 'pendaftaran_id' => $sempro->id],
            [
                'period_id' => $period->id,
                'mahasiswa_id' => $mahasiswa->id,
                'nama_ruangan' => 'Ruang Sidang Utama Informatika',
                'pembimbing_1_id' => $dosen1->id,
                'pembimbing_2_id' => $dosen2->id,
                'penguji_1_id' => $dosen1->id,
                'penguji_2_id' => $dosen2->id,
                'tanggal' => '2026-11-10',
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '10:30:00',
                'mode' => 'offline',
                'gelombang' => 1,
                'status' => 'terpublikasi',
                'dibuat_oleh' => $kaprodi->id,
            ]
        );
    }
}
