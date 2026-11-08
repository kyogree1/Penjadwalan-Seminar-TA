<?php

namespace Database\Seeders;

use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use App\Models\SchedulePeriod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyTwentyStudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan Periode 2 (Gelombang 2) memiliki kapasitas dan slot yang memadai
        $semproSlotsWave2 = [
            // Hari 1: Senin, 19 Oktober 2026
            ['tanggal' => '2026-10-19', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-10-19', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-10-19', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-10-19', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],

            // Hari 2: Selasa, 20 Oktober 2026
            ['tanggal' => '2026-10-20', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-10-20', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-10-20', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-10-20', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],

            // Hari 3: Rabu, 21 Oktober 2026
            ['tanggal' => '2026-10-21', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-10-21', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-10-21', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-10-21', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],

            // Hari 4: Kamis, 22 Oktober 2026
            ['tanggal' => '2026-10-22', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-10-22', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-10-22', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-10-22', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],

            // Hari 5: Jumat, 23 Oktober 2026
            ['tanggal' => '2026-10-23', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-10-23', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-10-23', 'jam_mulai' => '13:30:00', 'jam_selesai' => '15:00:00'],
            ['tanggal' => '2026-10-23', 'jam_mulai' => '15:15:00', 'jam_selesai' => '16:45:00'],
        ];

        SchedulePeriod::updateOrCreate(
            ['tipe' => 'sempro', 'gelombang' => 2],
            [
                'name' => '2026/2027',
                'semester' => 'Gasal',
                'prodi' => 'Informatika',
                'capacity' => 50,
                'start_date' => '2026-10-01',
                'end_date' => '2026-10-31',
                'slots' => $semproSlotsWave2,
                'is_open' => false,
                'is_published' => false,
            ]
        );

        $kaprodi = User::where('role', 'kaprodi')->first();

        // Ambil daftar dosen Informatika untuk pembimbing
        $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])
            ->where('prodi', 'Informatika')
            ->orderBy('id')
            ->get();

        if ($lecturers->count() < 2) {
            $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('id')->get();
        }

        $lecturerCount = $lecturers->count();

        // 2. Data 20 Mahasiswa Dummy
        $students = [
            [
                'name' => 'Aditiya Pratama',
                'nim' => '11221001',
                'judul' => 'Rancang Bangun Sistem Informasi Manajemen Laboratorium Berbasis Web Menggunakan Laravel',
                'bidang' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'name' => 'Anisa Rahmawati',
                'nim' => '11221002',
                'judul' => 'Klasifikasi Sentimen Ulasan Pengguna Aplikasi Mobile Menggunakan Algoritma Naive Bayes',
                'bidang' => 'Data Science',
            ],
            [
                'name' => 'Bayu Nugroho',
                'nim' => '11221003',
                'judul' => 'Deteksi Dini Penyakit Daun Padi Menggunakan Convolutional Neural Network',
                'bidang' => 'Computer Vision',
            ],
            [
                'name' => 'Cantika Dewi',
                'nim' => '11221004',
                'judul' => 'Pengembangan Aplikasi E-Commerce Produk UMKM Balikpapan Berbasis Flutter',
                'bidang' => 'Mobile Application',
            ],
            [
                'name' => 'Dimas Aryasatya',
                'nim' => '11221005',
                'judul' => 'Analisis Performa Algoritma K-Means dan DBSCAN untuk Segmentasi Pelanggan',
                'bidang' => 'Data Science',
            ],
            [
                'name' => 'Erina Safitri',
                'nim' => '11221006',
                'judul' => 'Implementasi Sistem Deteksi Intrusi Snort Berbasis Cloud Security pada Server Kampus',
                'bidang' => 'Keamanan Siber & Jaringan',
            ],
            [
                'name' => 'Fajar Hidayat',
                'nim' => '11221007',
                'judul' => 'Sistem Rekomendasi Destinasi Wisata di Kalimantan Timur Berbasis Collaborative Filtering',
                'bidang' => 'Sistem Cerdas & Rekomendasi',
            ],
            [
                'name' => 'Gita Permata',
                'nim' => '11221008',
                'judul' => 'Optimasi Rute Distribusi Logistik Menggunakan Algoritma Genetika di Wilayah IKN',
                'bidang' => 'Computational Intelligence & Optimasi',
            ],
            [
                'name' => 'Hendra Kurniawan',
                'nim' => '11221009',
                'judul' => 'Rancang Bangun RESTful API Layanan Akademik dengan Arsitektur Microservices',
                'bidang' => 'Rekayasa Perangkat Lunak',
            ],
            [
                'name' => 'Indah Cahyani',
                'nim' => '11221010',
                'judul' => 'Analisis Kerentanan Website Institusi Menggunakan OWASP Top 10 Framework',
                'bidang' => 'Keamanan Siber & Jaringan',
            ],
            [
                'name' => 'Joko Prasetyo',
                'nim' => '11221011',
                'judul' => 'Penerapan Optical Character Recognition pada Ekstraksi Data Kartu Identitas Otomatis',
                'bidang' => 'Computer Vision',
            ],
            [
                'name' => 'Karina Anggraini',
                'nim' => '11221012',
                'judul' => 'Prediksi Curah Hujan Harian Kota Balikpapan Menggunakan Model Long Short-Term Memory',
                'bidang' => 'Deep Learning',
            ],
            [
                'name' => 'Luthfi Maulana',
                'nim' => '11221013',
                'judul' => 'Pengembangan Chatbot Layanan Akademik Berbasis Retrieval-Augmented Generation',
                'bidang' => 'Natural Language Processing',
            ],
            [
                'name' => 'Maya Salsabila',
                'nim' => '11221014',
                'judul' => 'Sistem Monitoring Kualitas Air Tambak Menggunakan Sensor IoT dan Web Dashboard',
                'bidang' => 'Internet of Things',
            ],
            [
                'name' => 'Naufal Rizqullah',
                'nim' => '11221015',
                'judul' => 'Analisis Trafik Jaringan Kampus Terhadap Serangan DDoS Menggunakan Random Forest',
                'bidang' => 'Keamanan Siber & Jaringan',
            ],
            [
                'name' => 'Olivia Maharani',
                'nim' => '11221016',
                'judul' => 'Aplikasi Pengenalan Bahasa Isyarat Indonesia (SIBI) Real-Time Menggunakan MediaPipe',
                'bidang' => 'Computer Vision',
            ],
            [
                'name' => 'Pandu Wicaksono',
                'nim' => '11221017',
                'judul' => 'Optimasi Alokasi Truk Pengangkut Sampah Menggunakan Hybrid Genetic Algorithm',
                'bidang' => 'Computational Intelligence & Optimasi',
            ],
            [
                'name' => 'Qori Nurhaliza',
                'nim' => '11221018',
                'judul' => 'Sistem Pendukung Keputusan Penerima Beasiswa Menggunakan Metode TOPSIS Terbobot',
                'bidang' => 'Decision Support Systems',
            ],
            [
                'name' => 'Raditya Putra',
                'nim' => '11221019',
                'judul' => 'Pengembangan Game Edukasi Pencegahan Stunting Berbasis WebGL dan Unity 3D',
                'bidang' => 'Game Technology',
            ],
            [
                'name' => 'Salsabila Firdaus',
                'nim' => '11221020',
                'judul' => 'Klasifikasi Suara Paru-Paru Menggunakan Wavelet Transform dan Support Vector Machine',
                'bidang' => 'Biomedical Computing',
            ],
        ];

        $defaultPassword = Hash::make('password');

        foreach ($students as $index => $stu) {
            $email = $stu['nim'].'@student.itk.ac.id';

            // Buat atau update User
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $stu['name'],
                    'username' => $stu['nim'],
                    'role' => 'mahasiswa',
                    'nim_nip' => $stu['nim'],
                    'no_telpon' => '0812'.str_pad((string) ($index + 1000), 8, '0', STR_PAD_LEFT),
                    'angkatan' => '2022',
                    'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                    'prodi' => 'Informatika',
                    'status_ta' => 'Sempro',
                    'jabatan' => 'Mahasiswa Informatika',
                    'password' => $defaultPassword,
                ]
            );

            // Tentukan Pembimbing 1 dan 2 secara merata
            $p1 = $lecturers[$index % $lecturerCount];
            $p2 = $lecturers[($index + 3) % $lecturerCount];

            // Buat atau update Pengajuan Judul (status: disetujui)
            PengajuanJudul::updateOrCreate(
                ['mahasiswa_id' => $user->id],
                [
                    'judul_ta' => $stu['judul'],
                    'bidang_penelitian' => $stu['bidang'],
                    'pembimbing_1_id' => $p1->id,
                    'pembimbing_2_id' => $p2->id,
                    'telah_konsultasi' => true,
                    'status' => 'disetujui',
                    'catatan_kaprodi' => 'Judul dan usulan penelitian disetujui maju seminar proposal.',
                    'disetujui_oleh' => $kaprodi?->id,
                    'disetujui_at' => now(),
                ]
            );

            // Buat atau update Pendaftaran Sempro Gelombang 2 (status: verifikasi_tendik, status_akademik: disetujui)
            PendaftaranSempro::updateOrCreate(
                ['mahasiswa_id' => $user->id],
                [
                    'judul_ta' => $stu['judul'],
                    'bentuk_ta' => 'Skripsi Reguler',
                    'lokasi_mitra' => 'Kampus ITK Balikpapan',
                    'gelombang' => 2,
                    'status' => 'verifikasi_tendik',
                    'status_akademik' => 'disetujui',
                    'catatan' => 'Berkas pendaftaran sempro lengkap dan terverifikasi.',
                    'catatan_kaprodi' => 'Disetujui untuk penjadwalan sempro gelombang 2.',
                ]
            );
        }
    }
}
