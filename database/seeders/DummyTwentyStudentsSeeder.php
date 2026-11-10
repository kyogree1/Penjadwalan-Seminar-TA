<?php

namespace Database\Seeders;

use App\Models\PendaftaranSempro;
use App\Models\PengajuanJudul;
use App\Models\Penjadwalan;
use App\Models\RescheduleRequest;
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
        $defaultPassword = Hash::make('password');
        $kaprodi = User::where('role', 'kaprodi')->first();

        // Ambil daftar dosen Informatika untuk pembimbing & penguji
        $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])
            ->where('prodi', 'Informatika')
            ->orderBy('id')
            ->get();

        if ($lecturers->count() < 2) {
            $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])->orderBy('id')->get();
        }

        $lecturerCount = $lecturers->count();

        // =========================================================================
        // 1. SETUP GELOMBANG 1 (Sempro - ID 1)
        // =========================================================================
        $semproSlotsWave1 = [
            ['tanggal' => '2026-08-18', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-08-18', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-08-18', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-08-18', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],
            ['tanggal' => '2026-08-19', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-08-19', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-08-19', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-08-19', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],
            ['tanggal' => '2026-08-20', 'jam_mulai' => '08:30:00', 'jam_selesai' => '10:00:00'],
            ['tanggal' => '2026-08-20', 'jam_mulai' => '10:15:00', 'jam_selesai' => '11:45:00'],
            ['tanggal' => '2026-08-20', 'jam_mulai' => '13:00:00', 'jam_selesai' => '14:30:00'],
            ['tanggal' => '2026-08-20', 'jam_mulai' => '14:45:00', 'jam_selesai' => '16:15:00'],
        ];

        SchedulePeriod::updateOrCreate(
            ['tipe' => 'sempro', 'gelombang' => 1],
            [
                'name' => '2026/2027',
                'semester' => 'Gasal',
                'prodi' => 'Informatika',
                'capacity' => 50,
                'start_date' => '2026-08-01',
                'end_date' => '2026-08-31',
                'slots' => $semproSlotsWave1,
                'is_open' => false,
                'is_published' => false,
            ]
        );

        $studentsWave1 = [
            ['name' => 'Bima Satria', 'nim' => '11211001', 'judul' => 'Sistem Rekomendasi Buku Perpustakaan Kampus Menggunakan TF-IDF', 'bidang' => 'Information Retrieval'],
            ['name' => 'Chandra Wijaya', 'nim' => '11211002', 'judul' => 'Klasifikasi Citra X-Ray Paru-Paru Menggunakan MobileNetV2', 'bidang' => 'Computer Vision'],
            ['name' => 'Dwi Lestari', 'nim' => '11211003', 'judul' => 'Aplikasi Pendeteksi Hoaks Bahasa Indonesia Menggunakan BERT', 'bidang' => 'Natural Language Processing'],
            ['name' => 'Eko Prasetyo', 'nim' => '11211004', 'judul' => 'Optimasi Penugasan Shift Kerja Perawat Menggunakan Algoritma Genetika', 'bidang' => 'Computational Intelligence & Optimasi'],
            ['name' => 'Farhan Alamsyah', 'nim' => '11211005', 'judul' => 'Pengembangan Sistem Presensi Mahasiswa Berbasis Geolocation dan QR-Code', 'bidang' => 'Mobile Application'],
            ['name' => 'Grace Natalia', 'nim' => '11211006', 'judul' => 'Analisis Pola Belanja Pelanggan Minimarket Menggunakan Algoritma Apriori', 'bidang' => 'Data Science'],
            ['name' => 'Hafizh Syahputra', 'nim' => '11211007', 'judul' => 'Implementasi Honeypot Cowrie untuk Deteksi Serangan SSH pada Server Cloud', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => 'Irma Suryani', 'nim' => '11211008', 'judul' => 'Prediksi Harga Bahan Pokok Kota Balikpapan Menggunakan Model ARIMA', 'bidang' => 'Data Science'],
            ['name' => 'Jerry Christian', 'nim' => '11211009', 'judul' => 'Sistem Informasi Manajemen Aset Laboratorium Komputer Berbasis Web', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => 'Kurnia Ramadhan', 'nim' => '11211010', 'judul' => 'Pendeteksian Masker Wajah Secara Real-Time Menggunakan YOLOv8', 'bidang' => 'Computer Vision'],
        ];

        foreach ($studentsWave1 as $index => $stu) {
            $email = $stu['nim'].'@student.itk.ac.id';
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $stu['name'],
                    'username' => $stu['nim'],
                    'role' => 'mahasiswa',
                    'nim_nip' => $stu['nim'],
                    'no_telpon' => '0811'.str_pad((string) ($index + 1000), 8, '0', STR_PAD_LEFT),
                    'angkatan' => '2021',
                    'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                    'prodi' => 'Informatika',
                    'status_ta' => 'Sempro',
                    'jabatan' => 'Mahasiswa Informatika',
                    'password' => $defaultPassword,
                ]
            );

            $p1 = $lecturers[$index % $lecturerCount];
            $p2 = $lecturers[($index + 2) % $lecturerCount];

            PengajuanJudul::updateOrCreate(
                ['mahasiswa_id' => $user->id],
                [
                    'judul_ta' => $stu['judul'],
                    'bidang_penelitian' => $stu['bidang'],
                    'pembimbing_1_id' => $p1->id,
                    'pembimbing_2_id' => $p2->id,
                    'telah_konsultasi' => true,
                    'status' => 'disetujui',
                    'catatan_kaprodi' => 'Judul dan usulan penelitian disetujui.',
                    'disetujui_oleh' => $kaprodi?->id,
                    'disetujui_at' => now(),
                ]
            );

            PendaftaranSempro::updateOrCreate(
                ['mahasiswa_id' => $user->id],
                [
                    'judul_ta' => $stu['judul'],
                    'bentuk_ta' => 'Skripsi Reguler',
                    'lokasi_mitra' => 'Kampus ITK Balikpapan',
                    'gelombang' => 1,
                    'status' => 'verifikasi_tendik',
                    'status_akademik' => 'disetujui',
                    'catatan' => 'Berkas pendaftaran sempro gelombang 1 lengkap dan terverifikasi.',
                    'catatan_kaprodi' => 'Disetujui untuk penjadwalan sempro gelombang 1.',
                ]
            );
        }

        // =========================================================================
        // 2. SETUP GELOMBANG 2 (Sempro - ID 2)
        // =========================================================================
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

        $studentsWave2 = [
            ['name' => 'Aditiya Pratama', 'nim' => '11221001', 'judul' => 'Rancang Bangun Sistem Informasi Manajemen Laboratorium Berbasis Web Menggunakan Laravel', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => 'Anisa Rahmawati', 'nim' => '11221002', 'judul' => 'Klasifikasi Sentimen Ulasan Pengguna Aplikasi Mobile Menggunakan Algoritma Naive Bayes', 'bidang' => 'Data Science'],
            ['name' => 'Bayu Nugroho', 'nim' => '11221003', 'judul' => 'Deteksi Dini Penyakit Daun Padi Menggunakan Convolutional Neural Network', 'bidang' => 'Computer Vision'],
            ['name' => 'Cantika Dewi', 'nim' => '11221004', 'judul' => 'Pengembangan Aplikasi E-Commerce Produk UMKM Balikpapan Berbasis Flutter', 'bidang' => 'Mobile Application'],
            ['name' => 'Dimas Aryasatya', 'nim' => '11221005', 'judul' => 'Analisis Performa Algoritma K-Means dan DBSCAN untuk Segmentasi Pelanggan', 'bidang' => 'Data Science'],
            ['name' => 'Erina Safitri', 'nim' => '11221006', 'judul' => 'Implementasi Sistem Deteksi Intrusi Snort Berbasis Cloud Security pada Server Kampus', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => 'Fajar Hidayat', 'nim' => '11221007', 'judul' => 'Sistem Rekomendasi Destinasi Wisata di Kalimantan Timur Berbasis Collaborative Filtering', 'bidang' => 'Sistem Cerdas & Rekomendasi'],
            ['name' => 'Gita Permata', 'nim' => '11221008', 'judul' => 'Optimasi Rute Distribusi Logistik Menggunakan Algoritma Genetika di Wilayah IKN', 'bidang' => 'Computational Intelligence & Optimasi'],
            ['name' => 'Hendra Kurniawan', 'nim' => '11221009', 'judul' => 'Rancang Bangun RESTful API Layanan Akademik dengan Arsitektur Microservices', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => 'Indah Cahyani', 'nim' => '11221010', 'judul' => 'Analisis Kerentanan Website Institusi Menggunakan OWASP Top 10 Framework', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => 'Joko Prasetyo', 'nim' => '11221011', 'judul' => 'Penerapan Optical Character Recognition pada Ekstraksi Data Kartu Identitas Otomatis', 'bidang' => 'Computer Vision'],
            ['name' => 'Karina Anggraini', 'nim' => '11221012', 'judul' => 'Prediksi Curah Hujan Harian Kota Balikpapan Menggunakan Model Long Short-Term Memory', 'bidang' => 'Deep Learning'],
            ['name' => 'Luthfi Maulana', 'nim' => '11221013', 'judul' => 'Pengembangan Chatbot Layanan Akademik Berbasis Retrieval-Augmented Generation', 'bidang' => 'Natural Language Processing'],
            ['name' => 'Maya Salsabila', 'nim' => '11221014', 'judul' => 'Sistem Monitoring Kualitas Air Tambak Menggunakan Sensor IoT dan Web Dashboard', 'bidang' => 'Internet of Things'],
            ['name' => 'Naufal Rizqullah', 'nim' => '11221015', 'judul' => 'Analisis Trafik Jaringan Kampus Terhadap Serangan DDoS Menggunakan Random Forest', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => 'Olivia Maharani', 'nim' => '11221016', 'judul' => 'Aplikasi Pengenalan Bahasa Isyarat Indonesia (SIBI) Real-Time Menggunakan MediaPipe', 'bidang' => 'Computer Vision'],
            ['name' => 'Pandu Wicaksono', 'nim' => '11221017', 'judul' => 'Optimasi Alokasi Truk Pengangkut Sampah Menggunakan Hybrid Genetic Algorithm', 'bidang' => 'Computational Intelligence & Optimasi'],
            ['name' => 'Qori Nurhaliza', 'nim' => '11221018', 'judul' => 'Sistem Pendukung Keputusan Penerima Beasiswa Menggunakan Metode TOPSIS Terbobot', 'bidang' => 'Decision Support Systems'],
            ['name' => 'Raditya Putra', 'nim' => '11221019', 'judul' => 'Pengembangan Game Edukasi Pencegahan Stunting Berbasis WebGL dan Unity 3D', 'bidang' => 'Game Technology'],
            ['name' => 'Salsabila Firdaus', 'nim' => '11221020', 'judul' => 'Klasifikasi Suara Paru-Paru Menggunakan Wavelet Transform dan Support Vector Machine', 'bidang' => 'Biomedical Computing'],
        ];

        foreach ($studentsWave2 as $index => $stu) {
            $email = $stu['nim'].'@student.itk.ac.id';
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

            $p1 = $lecturers[$index % $lecturerCount];
            $p2 = $lecturers[($index + 3) % $lecturerCount];

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

        // =========================================================================
        // 3. SEED PENJADWALAN & DATA RESCHEDULE REQUESTS
        // =========================================================================
        // Ambil mahasiswa demo & dosen untuk mengisi permohonan reschedule
        $mhsAkmal = User::where('email', 'akmal.falah@student.itk.ac.id')->first();
        $mhsBagus = User::where('email', '11211018@student.itk.ac.id')->first();
        $mhsKevin = User::where('email', '11221045@student.itk.ac.id')->first();
        $mhsPutri = User::where('email', '11211052@student.itk.ac.id')->first();

        $dosenTejo = User::where('email', 'tejo.wahyu@lecturer.itk.ac.id')->first() ?? $lecturers[0];
        $dosenFaisal = User::where('email', 'faisal.ridwan@lecturer.itk.ac.id')->first() ?? $lecturers[1];
        $dosenDian = User::where('email', 'dian.permata@lecturer.itk.ac.id')->first() ?? $lecturers[2];
        $dosenRian = User::where('email', 'rian@lecturer.itk.ac.id')->first() ?? $lecturers[3];

        if ($mhsAkmal) {
            $jadwalAkmal = Penjadwalan::updateOrCreate(
                ['mahasiswa_id' => $mhsAkmal->id, 'tipe' => 'sempro'],
                [
                    'period_id' => 1,
                    'pendaftaran_id' => 901,
                    'pembimbing_1_id' => $dosenFaisal->id,
                    'pembimbing_2_id' => $kaprodi?->id ?? $lecturers[0]->id,
                    'penguji_1_id' => $dosenTejo->id,
                    'penguji_2_id' => $dosenDian->id,
                    'tanggal' => '2026-11-23',
                    'jam_mulai' => '09:00:00',
                    'jam_selesai' => '10:30:00',
                    'mode' => 'offline',
                    'link_online' => 'Ruang Sidang 1 (Gedung Lab A)',
                    'status' => 'terpublikasi',
                    'catatan' => 'Sistem Rekomendasi Pemilihan Topik Skripsi Menggunakan Content-Based Filtering',
                ]
            );

            RescheduleRequest::updateOrCreate(
                ['penjadwalan_id' => $jadwalAkmal->id],
                [
                    'diminta_oleh' => $dosenTejo->id,
                    'alasan' => 'Tugas dinas luar kota (asesmen akreditasi LAM-INFOKOM)',
                    'catatan' => 'Mohon dimundurkan ke tanggal 25 November siang bila memungkinkan.',
                    'tanggal_usulan' => '2026-11-25',
                    'jam_mulai_usulan' => '13:00:00',
                    'jam_selesai_usulan' => '14:30:00',
                    'ruangan_usulan' => 'Ruang Sidang 2 (Gedung Lab B)',
                    'status' => 'menunggu',
                ]
            );
        }

        if ($mhsBagus) {
            $jadwalBagus = Penjadwalan::updateOrCreate(
                ['mahasiswa_id' => $mhsBagus->id, 'tipe' => 'sempro'],
                [
                    'period_id' => 1,
                    'pendaftaran_id' => 902,
                    'pembimbing_1_id' => $dosenDian->id,
                    'pembimbing_2_id' => $dosenFaisal->id,
                    'penguji_1_id' => $dosenRian->id,
                    'penguji_2_id' => $dosenTejo->id,
                    'tanggal' => '2026-11-24',
                    'jam_mulai' => '10:30:00',
                    'jam_selesai' => '12:00:00',
                    'mode' => 'offline',
                    'link_online' => 'Ruang Sidang 2 (Gedung Lab B)',
                    'status' => 'terpublikasi',
                    'catatan' => 'Analisis Performa Algoritma K-Means dan DBSCAN untuk Segmentasi Pelanggan',
                ]
            );

            RescheduleRequest::updateOrCreate(
                ['penjadwalan_id' => $jadwalBagus->id],
                [
                    'diminta_oleh' => $dosenFaisal->id,
                    'alasan' => 'Mahasiswa mengalami demam tinggi dan perlu istirahat dokter',
                    'catatan' => 'Surat keterangan sakit terlampir, mohon dijadwalkan ulang 2 hari kemudian.',
                    'tanggal_usulan' => '2026-11-26',
                    'jam_mulai_usulan' => '09:00:00',
                    'jam_selesai_usulan' => '10:30:00',
                    'ruangan_usulan' => 'Ruang Sidang 1 (Gedung Lab A)',
                    'status' => 'menunggu',
                ]
            );
        }

        if ($mhsKevin) {
            $jadwalKevin = Penjadwalan::updateOrCreate(
                ['mahasiswa_id' => $mhsKevin->id, 'tipe' => 'sempro'],
                [
                    'period_id' => 1,
                    'pendaftaran_id' => 903,
                    'pembimbing_1_id' => $kaprodi?->id ?? $lecturers[0]->id,
                    'pembimbing_2_id' => $dosenRian->id,
                    'penguji_1_id' => $dosenTejo->id,
                    'penguji_2_id' => $dosenFaisal->id,
                    'tanggal' => '2026-11-20',
                    'jam_mulai' => '13:30:00',
                    'jam_selesai' => '15:00:00',
                    'mode' => 'offline',
                    'link_online' => 'Ruang Sidang 1 (Gedung Lab A)',
                    'status' => 'terpublikasi',
                    'catatan' => 'Pengembangan Sistem Deteksi Intrusi Jaringan Berbasis Deep Learning',
                ]
            );

            RescheduleRequest::updateOrCreate(
                ['penjadwalan_id' => $jadwalKevin->id],
                [
                    'diminta_oleh' => $dosenTejo->id,
                    'alasan' => 'Menghadiri konferensi internasional IEEE di Bali',
                    'catatan' => 'Menjadi pemakalah utama sesi pleno.',
                    'tanggal_usulan' => '2026-11-22',
                    'jam_mulai_usulan' => '10:00:00',
                    'jam_selesai_usulan' => '11:30:00',
                    'ruangan_usulan' => 'Ruang Sidang 1 (Gedung Lab A)',
                    'status' => 'disetujui',
                    'catatan_kaprodi' => 'Disetujui. Jadwal diperbarui ke 22 November 2026 pukul 10:00 WITA.',
                    'diputuskan_oleh' => $kaprodi?->id,
                    'diputuskan_at' => now()->subDay(),
                ]
            );
        }

        if ($mhsPutri) {
            $jadwalPutri = Penjadwalan::updateOrCreate(
                ['mahasiswa_id' => $mhsPutri->id, 'tipe' => 'sidang'],
                [
                    'period_id' => 1,
                    'pendaftaran_id' => 904,
                    'pembimbing_1_id' => $dosenDian->id,
                    'pembimbing_2_id' => $kaprodi?->id ?? $lecturers[0]->id,
                    'penguji_1_id' => $dosenFaisal->id,
                    'penguji_2_id' => $dosenTejo->id,
                    'tanggal' => '2026-11-21',
                    'jam_mulai' => '09:00:00',
                    'jam_selesai' => '10:30:00',
                    'mode' => 'offline',
                    'link_online' => 'Ruang Sidang 2 (Gedung Lab B)',
                    'status' => 'terpublikasi',
                    'catatan' => 'Sistem Rekomendasi Pemilihan Topik Skripsi Menggunakan Content-Based Filtering',
                ]
            );

            RescheduleRequest::updateOrCreate(
                ['penjadwalan_id' => $jadwalPutri->id],
                [
                    'diminta_oleh' => $dosenDian->id,
                    'alasan' => 'Permintaan pergeseran jam karena tabrakan kuliah praktikum',
                    'catatan' => 'Mohon dipindah ke sore jam 15:00 WITA.',
                    'tanggal_usulan' => '2026-11-21',
                    'jam_mulai_usulan' => '15:00:00',
                    'jam_selesai_usulan' => '16:30:00',
                    'ruangan_usulan' => 'Ruang Sidang 2 (Gedung Lab B)',
                    'status' => 'ditolak',
                    'catatan_kaprodi' => 'Ruangan sore hari telah dibooking untuk rapat prodi.',
                    'diputuskan_oleh' => $kaprodi?->id,
                    'diputuskan_at' => now()->subHours(5),
                ]
            );
        }
    }
}
