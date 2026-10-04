<?php

namespace Database\Seeders;

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
        User::updateOrCreate(
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
        User::updateOrCreate(
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

        // 3. Kaprodi (Koordinator Prodi)
        User::updateOrCreate(
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

        // 5. Data Master Ruangan
        \App\Models\Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Lab Riset Multimedia'],
            ['gedung' => 'Gedung A', 'lantai' => 'Lantai 2', 'kapasitas' => 30, 'status' => 'tersedia']
        );
        \App\Models\Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Lab Jaringan & Keamanan Siber'],
            ['gedung' => 'Gedung B', 'lantai' => 'Lantai 3', 'kapasitas' => 25, 'status' => 'tersedia']
        );
        \App\Models\Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Sidang Utama Informatika'],
            ['gedung' => 'Gedung A', 'lantai' => 'Lantai 3', 'kapasitas' => 40, 'status' => 'tersedia']
        );
    }
}
