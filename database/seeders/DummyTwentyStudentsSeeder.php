<?php

namespace Database\Seeders;

use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
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

        // =========================================================================
        // 1. SETUP DOSEN / LECTURERS (22 Dosen Lengkap)
        // =========================================================================
        $dosenList = [
            ['name' => "Dr.Eng. Devy Setiorini Sa'adiyah, S.T., M.S.", 'email' => 'devysetiorini@lecturer.itk.ac.id', 'nip' => '198501152014042001', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'AI & Data Science'],
            ['name' => "Dr.Eng., Samsu Dlukha Nurcholik, S.T., M.Sc.", 'email' => 'dlukha@lecturer.itk.ac.id', 'nip' => '198603202015041002', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Computer Vision'],
            ['name' => "Rosa Eliviani, S.Kom., M.T.", 'email' => 'rosa.eliviani@lecturer.itk.ac.id', 'nip' => '198807122018032001', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => "Nisa Rizqiya Fadhliana, S.Kom., M.T.", 'email' => 'nisafadhliana@lecturer.itk.ac.id', 'nip' => '199009182019032002', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Data Science'],
            ['name' => "Nur Fajri Azhar, S.Kom., M.Kom.", 'email' => 'fajri@lecturer.itk.ac.id', 'nip' => '198904222017041001', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => "Ramadhan Paninggalih, S.Si., M.Si., M.Sc.", 'email' => 'ramadhanpaninggalih@lecturer.itk.ac.id', 'nip' => '198711052016041003', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Computational Intelligence & Optimasi'],
            ['name' => "Riska Kurniyanto Abdullah, S.T., M.Kom.", 'email' => 'riska.abdullah@lecturer.itk.ac.id', 'nip' => '198402102013041001', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Internet of Things'],
            ['name' => "Rizal Kusuma Putra, M.T.", 'email' => 'rizal.putra@lecturer.itk.ac.id', 'nip' => '199105142020031001', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Mobile Application'],
            ['name' => "Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.", 'email' => 'faisal.ridwan@lecturer.itk.ac.id', 'nip' => '198308252012041002', 'role' => 'dosen', 'jabatan' => 'Lektor Kepala', 'bidang' => 'AI & Data Science'],
            ['name' => "Dian Permata Sari, S.Kom., M.Kom.", 'email' => 'dian.permata@lecturer.itk.ac.id', 'nip' => '198712032016042001', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => "Dr. Budi Wicaksono, M.Kom.", 'email' => 'budi.wicaksono@lecturer.itk.ac.id', 'nip' => '198106192010041001', 'role' => 'dosen', 'jabatan' => 'Lektor Kepala', 'bidang' => 'Natural Language Processing'],
            ['name' => "Ir. Hendra Gunawan, S.T., M.T.", 'email' => 'hendra.gunawan@pertamina.com', 'nip' => '197903142008041001', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => "Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.", 'email' => 'tejo.wahyu@lecturer.itk.ac.id', 'nip' => '198504122010121003', 'role' => 'dosen', 'jabatan' => 'Lektor Kepala', 'bidang' => 'AI & Data Science'],
            ['name' => "Rian Kurniawan, S.Kom., M.Eng.", 'email' => 'rian.kurniawan@lecturer.itk.ac.id', 'nip' => '199208152021031002', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Computer Vision'],
            ['name' => "Arif Wicaksana, S.Kom., M.Kom.", 'email' => 'arif.wicaksana@lecturer.itk.ac.id', 'nip' => '199011112019031005', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Data Science'],
            ['name' => "Dewi Anggraini, S.T., M.T.", 'email' => 'dewi.anggraini@lecturer.itk.ac.id', 'nip' => '198810202018032004', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Rekayasa Perangkat Lunak'],
            ['name' => "Fitriani Siregar, S.Kom., M.Sc.", 'email' => 'fitriani.siregar@lecturer.itk.ac.id', 'nip' => '199103252020032003', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Natural Language Processing'],
            ['name' => "Galih Pratama, S.T., M.Eng.", 'email' => 'galih.pratama@lecturer.itk.ac.id', 'nip' => '198906142017041002', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Internet of Things'],
            ['name' => "Haryo Tedjo, S.Kom., M.T.", 'email' => 'haryo.tedjo@lecturer.itk.ac.id', 'nip' => '198612082015041003', 'role' => 'dosen', 'jabatan' => 'Lektor', 'bidang' => 'Keamanan Siber & Jaringan'],
            ['name' => "Indah Wahyuni, S.Si., M.Kom.", 'email' => 'indah.wahyuni@lecturer.itk.ac.id', 'nip' => '199304192022032001', 'role' => 'dosen', 'jabatan' => 'Asisten Ahli', 'bidang' => 'Computational Intelligence & Optimasi'],
            ['name' => "Prof. Dr. Eng. Muhammad Ridwan, S.T., M.T.", 'email' => 'kaprodi.if@itk.ac.id', 'nip' => '198003152005011002', 'role' => 'kaprodi', 'jabatan' => 'Guru Besar', 'bidang' => 'AI & Data Science'],
            ['name' => "Dra. Nurul Rahmania, M.T.", 'email' => 'koorprodi.if@itk.ac.id', 'nip' => '197708222003042002', 'role' => 'koordinator', 'jabatan' => 'Lektor Kepala', 'bidang' => 'Rekayasa Perangkat Lunak'],
        ];

        foreach ($dosenList as $d) {
            User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'username' => $d['nip'],
                    'role' => $d['role'],
                    'nim_nip' => $d['nip'],
                    'prodi' => 'Informatika',
                    'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                    'jabatan' => $d['jabatan'],
                    'password' => $defaultPassword,
                ]
            );
        }

        $kaprodi = User::where('role', 'kaprodi')->first();
        $lecturers = User::whereIn('role', ['dosen', 'kaprodi', 'koordinator'])
            ->where('prodi', 'Informatika')
            ->orderBy('id')
            ->get();
        $lecturerCount = $lecturers->count();

        // Helper pembuatan 30 slot waktu seminar per gelombang
        $createSlots = function (string $startDate) {
            $slots = [];
            $times = [
                ['08:30:00', '10:00:00'],
                ['10:15:00', '11:45:00'],
                ['13:00:00', '14:30:00'],
                ['14:45:00', '16:15:00'],
                ['16:30:00', '18:00:00'],
                ['19:00:00', '20:30:00'],
            ];
            for ($day = 0; $day < 5; $day++) {
                $date = date('Y-m-d', strtotime("{$startDate} +{$day} days"));
                foreach ($times as $t) {
                    $slots[] = [
                        'tanggal' => $date,
                        'jam_mulai' => $t[0],
                        'jam_selesai' => $t[1],
                    ];
                }
            }
            return $slots;
        };

        // =========================================================================
        // 2. SETUP SCHEDULE PERIODS (Sempro & Sidang: Gelombang 1, 2, 3)
        // =========================================================================
        $periodConfigs = [
            ['tipe' => 'sempro', 'gelombang' => 1, 'start' => '2026-08-17', 'end' => '2026-08-31', 'sem' => 'Gasal'],
            ['tipe' => 'sempro', 'gelombang' => 2, 'start' => '2026-10-19', 'end' => '2026-10-31', 'sem' => 'Gasal'],
            ['tipe' => 'sempro', 'gelombang' => 3, 'start' => '2026-12-14', 'end' => '2026-12-31', 'sem' => 'Gasal'],
            ['tipe' => 'sidang', 'gelombang' => 1, 'start' => '2026-09-14', 'end' => '2026-09-30', 'sem' => 'Gasal'],
            ['tipe' => 'sidang', 'gelombang' => 2, 'start' => '2026-11-16', 'end' => '2026-11-30', 'sem' => 'Gasal'],
            ['tipe' => 'sidang', 'gelombang' => 3, 'start' => '2027-01-18', 'end' => '2027-01-31', 'sem' => 'Genap'],
        ];

        foreach ($periodConfigs as $cfg) {
            SchedulePeriod::updateOrCreate(
                ['tipe' => $cfg['tipe'], 'gelombang' => $cfg['gelombang']],
                [
                    'name' => '2026/2027',
                    'semester' => $cfg['sem'],
                    'prodi' => 'Informatika',
                    'capacity' => 50,
                    'start_date' => $cfg['start'],
                    'end_date' => $cfg['end'],
                    'slots' => $createSlots($cfg['start']),
                    'is_open' => false,
                    'is_published' => false,
                ]
            );
        }

        // =========================================================================
        // 3. SEED MAHASISWA SEMPRO (Gelombang 1, 2, 3 - Masing-masing 20 Peserta)
        // =========================================================================
        $semproStudents = [
            // GELOMBANG 1 (20 Mahasiswa)
            1 => [
                ['name' => 'Bima Satria', 'nim' => '11211001', 'judul' => 'Sistem Rekomendasi Buku Perpustakaan Kampus Menggunakan TF-IDF', 'bidang' => 'AI & Data Science'],
                ['name' => 'Chandra Wijaya', 'nim' => '11211002', 'judul' => 'Klasifikasi Citra X-Ray Paru-Paru Menggunakan MobileNetV2', 'bidang' => 'Computer Vision'],
                ['name' => 'Dwi Lestari', 'nim' => '11211003', 'judul' => 'Aplikasi Pendeteksi Hoaks Bahasa Indonesia Menggunakan IndoBERT', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Eko Prasetyo', 'nim' => '11211004', 'judul' => 'Optimasi Penugasan Shift Kerja Perawat Menggunakan Algoritma Genetika', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Farhan Alamsyah', 'nim' => '11211005', 'judul' => 'Pengembangan Sistem Presensi Mahasiswa Berbasis Geolocation dan QR-Code', 'bidang' => 'Mobile Application'],
                ['name' => 'Grace Natalia', 'nim' => '11211006', 'judul' => 'Analisis Pola Belanja Pelanggan Minimarket Menggunakan Algoritma Apriori', 'bidang' => 'Data Science'],
                ['name' => 'Hafizh Syahputra', 'nim' => '11211007', 'judul' => 'Implementasi Honeypot Cowrie untuk Deteksi Serangan SSH pada Server Cloud', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Irma Suryani', 'nim' => '11211008', 'judul' => 'Prediksi Harga Bahan Pokok Kota Balikpapan Menggunakan Model ARIMA', 'bidang' => 'Data Science'],
                ['name' => 'Jerry Christian', 'nim' => '11211009', 'judul' => 'Sistem Informasi Manajemen Aset Laboratorium Komputer Berbasis Web', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Kurnia Ramadhan', 'nim' => '11211010', 'judul' => 'Pendeteksian Masker Wajah Secara Real-Time Menggunakan YOLOv8', 'bidang' => 'Computer Vision'],
                ['name' => 'Latifah Hanum', 'nim' => '11211011', 'judul' => 'Klasifikasi Jenis Kopi Berdasarkan Citra Biji Menggunakan SVM', 'bidang' => 'Computer Vision'],
                ['name' => 'Muhammad Fikri', 'nim' => '11211012', 'judul' => 'Pengembangan Sistem Kasir Berbasis POS dengan Sinkronisasi Offline-First', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Nadia Utami', 'nim' => '11211013', 'judul' => 'Analisis Kepuasan Pasien BPJS Berbasis Twitter Menggunakan Naive Bayes', 'bidang' => 'Data Science'],
                ['name' => 'Oki Setiawan', 'nim' => '11211014', 'judul' => 'Rancang Bangun Sistem Smart Greenhouse Berbasis ESP32 dan MQTT', 'bidang' => 'Internet of Things'],
                ['name' => 'Putri Ayu Wandira', 'nim' => '11211015', 'judul' => 'Sistem Pakar Diagnosis Kerusakan Mesin Pompa Air Menggunakan Forward Chaining', 'bidang' => 'AI & Data Science'],
                ['name' => 'Qomaruzzaman', 'nim' => '11211016', 'judul' => 'Deteksi Serangan SQL Injection Menggunakan Ensemble Random Forest', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Rendi Syahrial', 'nim' => '11211017', 'judul' => 'Implementasi Algoritma Dijkstra untuk Jalur Evakuasi Kebakaran Gedung Bertingkat', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Silvia Anggraeni', 'nim' => '11211018', 'judul' => 'Aplikasi Pembelajaran Matematika Interaktif untuk Siswa Sekolah Dasar', 'bidang' => 'Mobile Application'],
                ['name' => 'Teguh Prakoso', 'nim' => '11211019', 'judul' => 'Segmentasi Tumor Otak dari Citra MRI Menggunakan U-Net Architecture', 'bidang' => 'Computer Vision'],
                ['name' => 'Ulfa Nabila', 'nim' => '11211020', 'judul' => 'Pengenalan Emosi Percakapan Teks Menggunakan BiLSTM dan Attention Mechanism', 'bidang' => 'Natural Language Processing'],
            ],

            // GELOMBANG 2 (20 Mahasiswa)
            2 => [
                ['name' => 'Aditiya Pratama', 'nim' => '11221001', 'judul' => 'Rancang Bangun Sistem Informasi Manajemen Laboratorium Berbasis Web Menggunakan Laravel', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Anisa Rahmawati', 'nim' => '11221002', 'judul' => 'Klasifikasi Sentimen Ulasan Pengguna Aplikasi Mobile Menggunakan Algoritma Naive Bayes', 'bidang' => 'Data Science'],
                ['name' => 'Bayu Nugroho', 'nim' => '11221003', 'judul' => 'Deteksi Dini Penyakit Daun Padi Menggunakan Convolutional Neural Network', 'bidang' => 'Computer Vision'],
                ['name' => 'Cantika Dewi', 'nim' => '11221004', 'judul' => 'Pengembangan Aplikasi E-Commerce Produk UMKM Balikpapan Berbasis Flutter', 'bidang' => 'Mobile Application'],
                ['name' => 'Dimas Aryasatya', 'nim' => '11221005', 'judul' => 'Analisis Performa Algoritma K-Means dan DBSCAN untuk Segmentasi Pelanggan', 'bidang' => 'Data Science'],
                ['name' => 'Erina Safitri', 'nim' => '11221006', 'judul' => 'Implementasi Sistem Deteksi Intrusi Snort Berbasis Cloud Security pada Server Kampus', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Fajar Hidayat', 'nim' => '11221007', 'judul' => 'Sistem Rekomendasi Destinasi Wisata di Kalimantan Timur Berbasis Collaborative Filtering', 'bidang' => 'AI & Data Science'],
                ['name' => 'Gita Permata', 'nim' => '11221008', 'judul' => 'Optimasi Rute Distribusi Logistik Menggunakan Algoritma Genetika di Wilayah IKN', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Hendra Kurniawan', 'nim' => '11221009', 'judul' => 'Rancang Bangun RESTful API Layanan Akademik dengan Arsitektur Microservices', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Indah Cahyani', 'nim' => '11221010', 'judul' => 'Analisis Kerentanan Website Institusi Menggunakan OWASP Top 10 Framework', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Joko Prasetyo', 'nim' => '11221011', 'judul' => 'Penerapan Optical Character Recognition pada Ekstraksi Data Kartu Identitas Otomatis', 'bidang' => 'Computer Vision'],
                ['name' => 'Karina Anggraini', 'nim' => '11221012', 'judul' => 'Prediksi Curah Hujan Harian Kota Balikpapan Menggunakan Model Long Short-Term Memory', 'bidang' => 'AI & Data Science'],
                ['name' => 'Luthfi Maulana', 'nim' => '11221013', 'judul' => 'Pengembangan Chatbot Layanan Akademik Berbasis Retrieval-Augmented Generation', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Maya Salsabila', 'nim' => '11221014', 'judul' => 'Sistem Monitoring Kualitas Air Tambak Menggunakan Sensor IoT dan Web Dashboard', 'bidang' => 'Internet of Things'],
                ['name' => 'Naufal Rizqullah', 'nim' => '11221015', 'judul' => 'Analisis Trafik Jaringan Kampus Terhadap Serangan DDoS Menggunakan Random Forest', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Olivia Maharani', 'nim' => '11221016', 'judul' => 'Aplikasi Pengenalan Bahasa Isyarat Indonesia (SIBI) Real-Time Menggunakan MediaPipe', 'bidang' => 'Computer Vision'],
                ['name' => 'Pandu Wicaksono', 'nim' => '11221017', 'judul' => 'Optimasi Alokasi Truk Pengangkut Sampah Menggunakan Hybrid Genetic Algorithm', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Qori Nurhaliza', 'nim' => '11221018', 'judul' => 'Sistem Pendukung Keputusan Penerima Beasiswa Menggunakan Metode TOPSIS Terbobot', 'bidang' => 'AI & Data Science'],
                ['name' => 'Raditya Putra', 'nim' => '11221019', 'judul' => 'Pengembangan Platform Crowdfunding Kegiatan Mahasiswa Berbasis Web', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Salsabila Firdaus', 'nim' => '11221020', 'judul' => 'Klasifikasi Suara Paru-Paru Menggunakan Wavelet Transform dan Support Vector Machine', 'bidang' => 'Computer Vision'],
            ],

            // GELOMBANG 3 (20 Mahasiswa)
            3 => [
                ['name' => 'Ahmad Dahlan', 'nim' => '11231051', 'judul' => 'Sistem Peringatan Dini Kebakaran Hutan Berbasis Citra Satelit MODIS dan CNN', 'bidang' => 'Computer Vision'],
                ['name' => 'Bella Safira', 'nim' => '11231052', 'judul' => 'Analisis Sentimen Opini Publik Pemindahan Ibu Kota Negara pada Media Sosial', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Cahyo Wibowo', 'nim' => '11231053', 'judul' => 'Pengembangan Smart Lock Pintu Laboratorium Menggunakan Pengenalan Wajah OpenCV', 'bidang' => 'Internet of Things'],
                ['name' => 'Denny Setiawan', 'nim' => '11231054', 'judul' => 'Sistem Monitoring Suhu dan Kelembapan Ruang Server Berbasis LoRaWAN', 'bidang' => 'Internet of Things'],
                ['name' => 'Elsa Rahmadani', 'nim' => '11231055', 'judul' => 'Klasifikasi Kematangan Buah Kelapa Sawit Menggunakan Algoritma ResNet-50', 'bidang' => 'Computer Vision'],
                ['name' => 'Ferry Gunawan', 'nim' => '11231056', 'judul' => 'Pendeteksian Botnet Mirai pada Perangkat IoT Menggunakan Algoritma XGBoost', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Gading Pamungkas', 'nim' => '11231057', 'judul' => 'Sistem Pendukung Keputusan Pemilihan Dosen Pembimbing Menggunakan AHP', 'bidang' => 'AI & Data Science'],
                ['name' => 'Hana Fauziah', 'nim' => '11231058', 'judul' => 'Pengembangan Aplikasi Pelaporan Kerusakan Fasilitas Kampus Berbasis Android', 'bidang' => 'Mobile Application'],
                ['name' => 'Irfan Hakim', 'nim' => '11231059', 'judul' => 'Penerapan Algoritma Ant Colony Optimization untuk Rute Wisata Balikpapan', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Julia Pratiwi', 'nim' => '11231060', 'judul' => 'Sistem Temu Kembali Informasi Artikel Ilmiah Menggunakan Okapi BM25', 'bidang' => 'Data Science'],
                ['name' => 'Kiki Fatmawati', 'nim' => '11231061', 'judul' => 'Klasifikasi Penyakit Tanaman Jagung Menggunakan Arsitektur Vision Transformer', 'bidang' => 'Computer Vision'],
                ['name' => 'Lukman Hakim', 'nim' => '11231062', 'judul' => 'Sistem Keamanan Jaringan Wireless Kampus Menggunakan WPA3 Enterprise dan RADIUS', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Mega Puspita', 'nim' => '11231063', 'judul' => 'Prediksi Kebutuhan Listrik Gedung Kampus ITK Menggunakan Prophet Model', 'bidang' => 'Data Science'],
                ['name' => 'Nugroho Adiputra', 'nim' => '11231064', 'judul' => 'Sistem Otomasi Ujian Online Anti-Plagiasi Menggunakan Cosine Similarity', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Okky Chandra', 'nim' => '11231065', 'judul' => 'Rancang Bangun Aplikasi Telemedicine Konsultasi Gizi Menggunakan WebRTC', 'bidang' => 'Mobile Application'],
                ['name' => 'Paramitha Indah', 'nim' => '11231066', 'judul' => 'Deteksi Retak Dinding Bangunan Beton Menggunakan Mask R-CNN', 'bidang' => 'Computer Vision'],
                ['name' => 'Rendy Oktavian', 'nim' => '11231067', 'judul' => 'Penerapan Continuous Integration & Delivery (CI/CD) pada Deployment Layanan SI', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Syahrul Ramadhan', 'nim' => '11231068', 'judul' => 'Sistem Identifikasi Plat Nomor Kendaraan Otomatis (ALPR) di Gerbang ITK', 'bidang' => 'Computer Vision'],
                ['name' => 'Tania Amelia', 'nim' => '11231069', 'judul' => 'Klasifikasi Status Gizi Balita Menggunakan Metode K-Nearest Neighbor (KNN)', 'bidang' => 'Data Science'],
                ['name' => 'Vicky Prasetya', 'nim' => '11231070', 'judul' => 'Analisis Forensik Digital Rekaman Log Web Server Akibat Serangan Cross-Site Scripting', 'bidang' => 'Keamanan Siber & Jaringan'],
            ],
        ];

        foreach ($semproStudents as $wave => $students) {
            foreach ($students as $index => $stu) {
                $email = $stu['nim'].'@student.itk.ac.id';
                $user = User::updateOrCreate(
                    ['username' => $stu['nim']],
                    [
                        'name' => $stu['name'],
                        'email' => $email,
                        'username' => $stu['nim'],
                        'role' => 'mahasiswa',
                        'nim_nip' => $stu['nim'],
                        'no_telpon' => '0812'.str_pad((string) ($wave * 1000 + $index), 8, '0', STR_PAD_LEFT),
                        'angkatan' => '20'.substr($stu['nim'], 2, 2),
                        'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                        'prodi' => 'Informatika',
                        'status_ta' => 'Sempro',
                        'jabatan' => 'Mahasiswa Informatika',
                        'password' => $defaultPassword,
                    ]
                );

                $p1 = $lecturers[($index * 2) % $lecturerCount];
                $p2 = $lecturers[($index * 2 + 1) % $lecturerCount];

                PengajuanJudul::updateOrCreate(
                    ['mahasiswa_id' => $user->id],
                    [
                        'judul_ta' => $stu['judul'],
                        'bidang_penelitian' => $stu['bidang'],
                        'pembimbing_1_id' => $p1->id,
                        'pembimbing_2_id' => $p2->id,
                        'telah_konsultasi' => true,
                        'status' => 'disetujui',
                        'catatan_kaprodi' => "Judul dan proposal seminar proposal gelombang {$wave} disetujui.",
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
                        'gelombang' => $wave,
                        'status' => 'verifikasi_tendik',
                        'status_akademik' => 'disetujui',
                        'catatan' => "Berkas pendaftaran sempro gelombang {$wave} lengkap & tervalidasi.",
                        'catatan_kaprodi' => "Disetujui untuk penjadwalan sempro gelombang {$wave}.",
                    ]
                );
            }
        }

        // =========================================================================
        // 4. SEED MAHASISWA SIDANG TA (Gelombang 1, 2, 3 - Masing-masing 15 Peserta)
        // =========================================================================
        $sidangStudents = [
            // GELOMBANG 1 (15 Mahasiswa)
            1 => [
                ['name' => 'Agus Priyono', 'nim' => '11201001', 'judul' => 'Sistem Rekomendasi Karir Lulusan IT Menggunakan Hybrid Filtering dan Deep Learning', 'bidang' => 'AI & Data Science'],
                ['name' => 'Bagas Pamungkas', 'nim' => '11201002', 'judul' => 'Optimasi Hyperparameter XGBoost untuk Deteksi Dini Penyakit Diabetes Melitus', 'bidang' => 'Data Science'],
                ['name' => 'Citra Kirana', 'nim' => '11201003', 'judul' => 'Pengembangan Sistem Klasifikasi Dokumen Peraturan Daerah Menggunakan IndoBERT', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Danang Kusuma', 'nim' => '11201004', 'judul' => 'Pendeteksian Objek Maritim pada Citra Satelit Sentinel-2 Menggunakan YOLOv8x', 'bidang' => 'Computer Vision'],
                ['name' => 'Evi Marlina', 'nim' => '11201005', 'judul' => 'Analisis Forensik Malware Ransomware pada Lingkungan Sandbox Virtual Berbasis Cuckoo', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Faisal Reza', 'nim' => '11201006', 'judul' => 'Implementasi Algoritma Genetika Multiobjektif untuk Penjadwalan Sidang Skripsi', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Gita Gutawa', 'nim' => '11201007', 'judul' => 'Sistem Monitoring Kekeruhan Air PDAM Balikpapan Berbasis Sensor Turbidity dan IoT', 'bidang' => 'Internet of Things'],
                ['name' => 'Haris Maulana', 'nim' => '11201008', 'judul' => 'Rancang Bangun Aplikasi Mobile Pelaporan Insiden Keselamatan Tambang Batubara', 'bidang' => 'Mobile Application'],
                ['name' => 'Intan Permatasari', 'nim' => '11201009', 'judul' => 'Sistem Pendukung Keputusan Pemilihan Penerima KIP Kuliah Menggunakan Metode VIKOR', 'bidang' => 'AI & Data Science'],
                ['name' => 'Jamaludin Malik', 'nim' => '11201010', 'judul' => 'Pengembangan Sistem Informasi Manajemen Skripsi dengan Arsitektur Clean Architecture', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Kartika Sari', 'nim' => '11201011', 'judul' => 'Prediksi Beban Puncak Listrik Wilayah Kaltim Menggunakan Gated Recurrent Unit', 'bidang' => 'AI & Data Science'],
                ['name' => 'Lukman Hakim TA', 'nim' => '11201012', 'judul' => 'Sistem Deteksi Anomali Trafik Jaringan Kampus Menggunakan Isolation Forest', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Mega Utami', 'nim' => '11201013', 'judul' => 'Klasifikasi Jenis Kerusakan Jalan Aspal Menggunakan Convolutional Neural Network', 'bidang' => 'Computer Vision'],
                ['name' => 'Nanda Pratama', 'nim' => '11201014', 'judul' => 'Sistem Temu Kembali Citra Medis Berbasis Content-Based Image Retrieval (CBIR)', 'bidang' => 'Computer Vision'],
                ['name' => 'Okta Wulandari', 'nim' => '11201015', 'judul' => 'Penerapan Named Entity Recognition (NER) pada Abstrak Jurnal Ilmiah Indonesia', 'bidang' => 'Natural Language Processing'],
            ],

            // GELOMBANG 2 (15 Mahasiswa)
            2 => [
                ['name' => 'Prasetyo Wibowo', 'nim' => '11201021', 'judul' => 'Sistem Pendeteksi Phishing URL Menggunakan Pembelajaran Mesin dan Ekstraksi Fitur Lexical', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Qonita Rahmah', 'nim' => '11201022', 'judul' => 'Pengembangan Aplikasi Kasir Pintar Berbasis Barcode Scanner dan Machine Vision', 'bidang' => 'Computer Vision'],
                ['name' => 'Reza Pahlevi', 'nim' => '11201023', 'judul' => 'Optimasi Biaya Rute Pengiriman Barang di Kawasan IKN Menggunakan Tabu Search', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Sinta Nuriyah', 'nim' => '11201024', 'judul' => 'Sistem Monitoring Getaran Jembatan Menggunakan Sensor Akselerometer dan NodeMCU', 'bidang' => 'Internet of Things'],
                ['name' => 'Taufiq Hidayat', 'nim' => '11201025', 'judul' => 'Pengembangan Aplikasi Mobile Manajemen Donasi Bencana Berbasis Flutter dan Firebase', 'bidang' => 'Mobile Application'],
                ['name' => 'Utami Dewi', 'nim' => '11201026', 'judul' => 'Klasifikasi Suara Detak Jantung untuk Skrining Murmur Menggunakan Mel-Spectrogram', 'bidang' => 'AI & Data Science'],
                ['name' => 'Vino Bastian', 'nim' => '11201027', 'judul' => 'Analisis Performa Basis Data SQL vs NoSQL pada Beban Transaksi E-Commerce Tinggi', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Winda Novita', 'nim' => '11201028', 'judul' => 'Pengenalan Bahasa Isyarat BISINDO Menggunakan Deep Learning Berbasis Video Sekuensial', 'bidang' => 'Computer Vision'],
                ['name' => 'Yoga Pratama', 'nim' => '11201029', 'judul' => 'Sistem Deteksi Akun Palsu di Media Sosial Instagram Menggunakan Graph Neural Network', 'bidang' => 'Data Science'],
                ['name' => 'Zahra Annisa', 'nim' => '11201030', 'judul' => 'Penerapan Model Topic Modeling Berbasis BERTopic pada Review Aplikasi Pelayanan Publik', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Ardiansyah Putra', 'nim' => '11201031', 'judul' => 'Sistem Informasi Presensi Pegawai Berbasis Lokasi GPS dan Deteksi Liveness Wajah', 'bidang' => 'Computer Vision'],
                ['name' => 'Bella Citra', 'nim' => '11201032', 'judul' => 'Implementasi Zero Trust Network Access (ZTNA) pada Infrastruktur Cloud Hybrid', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Cipto Mangunkusumo', 'nim' => '11201033', 'judul' => 'Prediksi Konsumsi Bahan Bakar Kapal Feri Kariangau Menggunakan Regresi Nonlinier', 'bidang' => 'Data Science'],
                ['name' => 'Dinda Amelia', 'nim' => '11201034', 'judul' => 'Pengembangan Sistem E-Learning Adaptif Berdasarkan Gaya Belajar Mahasiswa', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Evan Sanders', 'nim' => '11201035', 'judul' => 'Klasifikasi Tingkat Kematangan Buah Pisang Berdasarkan Fitur Warna HSV dan Tekstur GLCM', 'bidang' => 'Computer Vision'],
            ],

            // GELOMBANG 3 (15 Mahasiswa)
            3 => [
                ['name' => 'Fikri Haikal TA', 'nim' => '11201041', 'judul' => 'Sistem Pengendalian Drone Otonom untuk Pemetaan Lahan Terbuka Berbasis ROS 2', 'bidang' => 'Internet of Things'],
                ['name' => 'Gina Sonia', 'nim' => '11201042', 'judul' => 'Pengembangan Dashboard Visualisasi Data Kemiskinan Kaltim dengan Apache Superset', 'bidang' => 'Data Science'],
                ['name' => 'Harun Al Rasyid', 'nim' => '11201043', 'judul' => 'Pendeteksian Serangan Man-in-the-Middle Menggunakan Pembelajaran Mesin Berbasis Paket', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Ika Nurjanah', 'nim' => '11201044', 'judul' => 'Sistem Rekomendasi Pakaian Berdasarkan Bentuk Tubuh dan Cuaca Menggunakan CNN', 'bidang' => 'AI & Data Science'],
                ['name' => 'Jefri Nichol', 'nim' => '11201045', 'judul' => 'Optimasi Pembagian Muatan Kapal Kontainer Menggunakan Algoritma Genetika 3D', 'bidang' => 'Computational Intelligence & Optimasi'],
                ['name' => 'Kamelia Putri', 'nim' => '11201046', 'judul' => 'Pengembangan Asisten Virtual Akademik Menggunakan Large Language Model Llama 3', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Lukman Sardi', 'nim' => '11201047', 'judul' => 'Sistem Verifikasi Tanda Tangan Digital Menggunakan Algoritma Siamese Neural Network', 'bidang' => 'Computer Vision'],
                ['name' => 'Mawar Melati', 'nim' => '11201048', 'judul' => 'Analisis Kinerja Jaringan 5G untuk Pengiriman Video Resolusi 4K pada Drone Patroli', 'bidang' => 'Keamanan Siber & Jaringan'],
                ['name' => 'Naufal Samudra', 'nim' => '11201049', 'judul' => 'Sistem Pendukung Keputusan Kelayakan Penerima Kredit Usaha Rakyat dengan C4.5', 'bidang' => 'AI & Data Science'],
                ['name' => 'Olla Ramlan', 'nim' => '11201050', 'judul' => 'Aplikasi Pembelajaran Anatomi Tubuh Manusia Menggunakan Augmented Reality Unity', 'bidang' => 'Mobile Application'],
                ['name' => 'Pasha Ungu', 'nim' => '11201051', 'judul' => 'Sistem Pemantau Kebocoran Pipa Minyak Menggunakan Sensor Tekanan dan IoT', 'bidang' => 'Internet of Things'],
                ['name' => 'Qory Sandioriva', 'nim' => '11201052', 'judul' => 'Klasifikasi Kualitas Biji Kakao Menggunakan Metode Gray Level Co-occurrence Matrix', 'bidang' => 'Computer Vision'],
                ['name' => 'Raffi Ahmad TA', 'nim' => '11201053', 'judul' => 'Pengembangan Sistem Marketplace Lelang Ikan Balikpapan Berbasis Smart Contract Web', 'bidang' => 'Rekayasa Perangkat Lunak'],
                ['name' => 'Siti Badriah', 'nim' => '11201054', 'judul' => 'Analisis Sentimen Kebijakan Transportasi Publik Balikpapan Menggunakan Support Vector Machine', 'bidang' => 'Natural Language Processing'],
                ['name' => 'Titi Kamal', 'nim' => '11201055', 'judul' => 'Pendeteksian Retak Struktur Sayap Pesawat Menggunakan Deep Learning Ultrasonik', 'bidang' => 'Computer Vision'],
            ],
        ];

        foreach ($sidangStudents as $wave => $students) {
            foreach ($students as $index => $stu) {
                $email = $stu['nim'].'@student.itk.ac.id';
                $user = User::updateOrCreate(
                    ['username' => $stu['nim']],
                    [
                        'name' => $stu['name'],
                        'email' => $email,
                        'username' => $stu['nim'],
                        'role' => 'mahasiswa',
                        'nim_nip' => $stu['nim'],
                        'no_telpon' => '0813'.str_pad((string) ($wave * 1000 + $index), 8, '0', STR_PAD_LEFT),
                        'angkatan' => '2020',
                        'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                        'prodi' => 'Informatika',
                        'status_ta' => 'Sidang',
                        'jabatan' => 'Mahasiswa Informatika Tingkat Akhir',
                        'password' => $defaultPassword,
                    ]
                );

                $p1 = $lecturers[($index * 2) % $lecturerCount];
                $p2 = $lecturers[($index * 2 + 1) % $lecturerCount];

                PengajuanJudul::updateOrCreate(
                    ['mahasiswa_id' => $user->id],
                    [
                        'judul_ta' => $stu['judul'],
                        'bidang_penelitian' => $stu['bidang'],
                        'pembimbing_1_id' => $p1->id,
                        'pembimbing_2_id' => $p2->id,
                        'telah_konsultasi' => true,
                        'status' => 'disetujui',
                        'catatan_kaprodi' => "Judul tugas akhir dan naskah skripsi sidang gelombang {$wave} disetujui.",
                        'disetujui_oleh' => $kaprodi?->id,
                        'disetujui_at' => now(),
                    ]
                );

                PendaftaranSidang::updateOrCreate(
                    ['mahasiswa_id' => $user->id],
                    [
                        'judul_ta' => $stu['judul'],
                        'lokasi_mitra' => 'Kampus ITK Balikpapan',
                        'gelombang' => $wave,
                        'status' => 'verifikasi_tendik',
                        'status_akademik' => 'disetujui',
                        'catatan' => "Berkas pendaftaran sidang gelombang {$wave} lengkap & tervalidasi.",
                        'catatan_kaprodi' => "Disetujui untuk penjadwalan sidang gelombang {$wave}.",
                    ]
                );
            }
        }

        // =========================================================================
        // 5. SEED DATA RESCHEDULE REQUESTS UNTUK DEMO
        // =========================================================================
        $mhsDemo = User::where('email', '11211001@student.itk.ac.id')->first();
        $mhsDemo2 = User::where('email', '11211002@student.itk.ac.id')->first();
        $mhsDemo3 = User::where('email', '11211003@student.itk.ac.id')->first();
        $mhsDemo4 = User::where('email', '11211004@student.itk.ac.id')->first();
        $mhsWave2A = User::where('email', '11221001@student.itk.ac.id')->first();
        $mhsWave2B = User::where('email', '11221002@student.itk.ac.id')->first();

        $dosenTejo = User::where('email', 'tejo.wahyu@lecturer.itk.ac.id')->first() ?? $lecturers[0];
        $dosenFaisal = User::where('email', 'faisal.ridwan@lecturer.itk.ac.id')->first() ?? $lecturers[1];
        $dosenDian = User::where('email', 'dian.permata@lecturer.itk.ac.id')->first() ?? $lecturers[2];
        $dosenRian = User::where('email', 'rian.kurniawan@lecturer.itk.ac.id')->first() ?? $lecturers[3];

        $periodWave1 = SchedulePeriod::where('tipe', 'sempro')->where('gelombang', 1)->first();
        $periodWave2 = SchedulePeriod::where('tipe', 'sempro')->where('gelombang', 2)->first();

        $createReschedule = function ($mhs, $periodId, $dosenPemohon, $alasan, $catatan, $tglUsul, $jamMulai, $jamSelesai, $ruang, $status, $catatanKaprodi = null) use ($dosenFaisal, $dosenTejo, $dosenDian, $dosenRian, $kaprodi, $lecturers) {
            if (! $mhs || ! $periodId) return;
            $reg = PendaftaranSempro::where('mahasiswa_id', $mhs->id)->first();
            $jadwal = Penjadwalan::updateOrCreate(
                ['mahasiswa_id' => $mhs->id, 'tipe' => 'sempro'],
                [
                    'period_id' => $periodId,
                    'pendaftaran_id' => $reg?->id,
                    'pembimbing_1_id' => $dosenFaisal->id,
                    'pembimbing_2_id' => $kaprodi?->id ?? $lecturers[0]->id,
                    'penguji_1_id' => $dosenTejo->id,
                    'penguji_2_id' => $dosenDian->id,
                    'tanggal' => '2026-08-18',
                    'jam_mulai' => '08:30:00',
                    'jam_selesai' => '10:00:00',
                    'mode' => 'offline',
                    'link_online' => 'Ruang Sidang 1 (Gedung Lab A)',
                    'status' => 'terpublikasi',
                    'catatan' => $mhs->name . ' - Reschedule request',
                ]
            );

            RescheduleRequest::updateOrCreate(
                ['penjadwalan_id' => $jadwal->id],
                [
                    'diminta_oleh' => $dosenPemohon->id,
                    'alasan' => $alasan,
                    'catatan' => $catatan,
                    'tanggal_usulan' => $tglUsul,
                    'jam_mulai_usulan' => $jamMulai,
                    'jam_selesai_usulan' => $jamSelesai,
                    'ruangan_usulan' => $ruang,
                    'status' => $status,
                    'catatan_kaprodi' => $catatanKaprodi,
                    'diputuskan_oleh' => $status !== 'menunggu' ? $kaprodi?->id : null,
                    'diputuskan_at' => $status !== 'menunggu' ? now()->subHours(2) : null,
                ]
            );
        };

        // Permohonan Gelombang 1
        $createReschedule($mhsDemo, $periodWave1?->id, $dosenTejo, 'Tugas dinas luar kota (asesmen akreditasi LAM-INFOKOM)', 'Mohon dimundurkan 2 hari bila slot memungkinkan.', '2026-08-20', '13:00:00', '14:30:00', 'Ruang Sidang 2 (Gedung Lab B)', 'menunggu');
        $createReschedule($mhsDemo2, $periodWave1?->id, $dosenFaisal, 'Mahasiswa sakit demam berdarah (surat dokter terlampir)', 'Mohon dijadwalkan ulang ke hari Jumat.', '2026-08-21', '09:00:00', '10:30:00', 'Ruang Sidang 1 (Gedung Lab A)', 'menunggu');
        $createReschedule($mhsDemo3, $periodWave1?->id, $dosenDian, 'Bentrok jadwal mengajar kelas praktikum reguler', 'Sudah konfirmasi dengan mahasiswa bersangkutan.', '2026-08-22', '10:15:00', '11:45:00', 'Ruang Sidang 1 (Gedung Lab A)', 'disetujui', 'Disetujui. Jadwal dipindahkan.');
        $createReschedule($mhsDemo4, $periodWave1?->id, $dosenRian, 'Menghadiri konferensi internasional di Bali', 'Bisa digantikan penguji lain jika ruangan penuh.', '2026-08-25', '14:45:00', '16:15:00', 'Ruang Sidang 2 (Gedung Lab B)', 'ditolak', 'Ruangan lab penuh pada sesi sore.');

        // Permohonan Gelombang 2 (Aman dari reset gelombang 1)
        $createReschedule($mhsWave2A, $periodWave2?->id, $dosenTejo, 'Ujian bentrok dengan jadwal sidang skripsi departemen lain', 'Mohon dialihkan ke slot siang.', '2026-10-22', '13:00:00', '14:30:00', 'Ruang Sidang 2 (Gedung Lab B)', 'menunggu');
        $createReschedule($mhsWave2B, $periodWave2?->id, $dosenDian, 'Dosen pembimbing dinas luar daerah', 'Surat tugas terlampir.', '2026-10-23', '10:15:00', '11:45:00', 'Ruang Sidang 1 (Gedung Lab A)', 'disetujui', 'Disetujui untuk perubahan hari.');
    }
}
