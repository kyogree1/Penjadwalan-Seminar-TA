<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KatalogController extends Controller
{
    public function index(): Response
    {
        $bidangList = [
            'UI/UX & Human-Computer Interaction',
            'Kecerdasan Buatan (AI) & NLP',
            'Sistem Informasi & Optimasi',
            'Rekayasa Perangkat Lunak',
            'Computer Vision & Citra Digital',
            'Keamanan Siber & Jaringan',
            'Internet of Things (IoT) & Embedded',
            'Data Science & Analitika Bisnis',
        ];

        $metodeList = [
            'Prototyping & Field Testing',
            'Experimental Research & Evaluation',
            'Design Science Research (DSR)',
            'Action Research & Scrum',
            'Convolutional Neural Network (CNN)',
            'Transfer Learning & Fine-Tuning',
            'Support Vector Machine & Random Forest',
            'System Usability Scale (SUS) & UEQ',
        ];

        $dosenList = [
            ['name' => 'Dr. Ir. Tejo Wahyu Susanto, M.T.', 'nip' => '198504122010121003'],
            ['name' => 'Prof. Dr. Eng. Muhammad Ridwan, S.T., M.T.', 'nip' => '198003152005011002'],
            ['name' => 'Fadlan Rahmat, S.Kom., M.Cs.', 'nip' => '198901232015041002'],
            ['name' => 'Dr. Dian Indah Permatasari, M.Kom.', 'nip' => '198402122010122004'],
            ['name' => 'Rina Agustina, S.T., M.Kom.', 'nip' => '198607142012122001'],
            ['name' => 'Ahmad Fauzi, S.Kom., M.T.', 'nip' => '198811052015041003'],
            ['name' => 'Hendra Kusuma, S.T., M.Eng.', 'nip' => '198205102008121001'],
            ['name' => 'Bambang Sudarsono, M.Sc.', 'nip' => '197608152003121002'],
        ];

        $sampleTitles = [
            'Implementasi Augmented Reality Berbasis Web untuk Pembelajaran Anatomi Interaktif',
            'Rancang Bangun Chatbot Akademik Terintegrasi Menggunakan Model Bahasa LLaMA dan RAG',
            'Optimasi Penjadwalan Sidang Tugas Akhir Menggunakan Algoritma Genetika Terdistribusi',
            'Deteksi Penyakit Daun Kelapa Sawit Menggunakan Convolutional Neural Network (CNN)',
            'Pengembangan Sistem Informasi Manajemen Inventaris Laboratorium Berbasis Microservices',
            'Analisis Sentimen Opini Publik Terhadap Kebijakan IKN Nusantara pada Media Sosial X',
            'Sistem Monitoring Kualitas Udara dan Suhu Ruang Server Berbasis IoT dan MQTT',
            'Rancang Bangun E-Commerce Berkelanjutan dengan Arsitektur JAMstack dan Serverless',
            'Implementasi Blockchain untuk Verifikasi Ijazah Digital dan Transkrip Nilai Akademik',
            'Klasifikasi Citra Rontgen Paru-Paru Menggunakan Transfer Learning Vision Transformer',
            'Pengembangan Aplikasi Mobile Deteksi Dini Postur Tubuh Menggunakan MediaPipe Pose',
            'Sistem Pendukung Keputusan Pemilihan Dosen Pembimbing Menggunakan Metode TOPSIS Terbobot',
            'Analisis Keamanan Jaringan Wireless Kampus Berdasarkan Framework NIST Cybersecurity',
            'Prediksi Harga Komoditas Pangan di Kalimantan Timur Menggunakan Metode LSTM',
            'Rancang Bangun Game Edukasi Budaya Dayak Berbasis Unity Engine dan Gamifikasi',
            'Sistem Presensi Mahasiswa Berbasis Pengenalan Wajah Real-Time dengan FaceNet',
            'Otomasi Pengujian Fungsional Web Aplikasi Menggunakan Playwright dan CI/CD Pipeline',
            'Segmentasi Pembuluh Darah Retina Mata Menggunakan U-Net Architecture',
            'Implementasi Progressive Web App (PWA) pada Sistem Logbook Bimbingan Mahasiswa',
            'Analisis Kinerja Jaringan Software-Defined Networking (SDN) Menggunakan Ryu Controller',
            'Sistem Deteksi Intrusi Jaringan Menggunakan Random Forest dan Ekstraksi Fitur CICIDS',
            'Pengembangan Platform Virtual Tour Kampus 360 Derajat Berbasis WebGL dan Pannellum',
            'Optimasi Rute Distribusi Logistik Menggunakan Particle Swarm Optimization (PSO)',
            'Sistem Rekomendasi Tempat Wisata Kalimantan Timur Berbasis Collaborative Filtering',
            'Penerapan UI/UX Redesign pada Aplikasi Layanan Publik Berdasarkan Design Thinking',
            'Pengembangan Dashboard Monitoring Energi Listrik Gedung Kampus Berbasis IoT',
            'Analisis Pola Belanja Pelanggan Menggunakan Algoritma FP-Growth dan Market Basket Analysis',
            'Sistem Informasi Pelacakan Alumni (Tracer Study) Terintegrasi dengan Webhook Telegram',
            'Deteksi Anomali Transaksi Keuangan Digital Menggunakan Autoencoder Neural Network',
            'Sistem Informasi Geografis (GIS) Pemetaan Fasilitas Kesehatan Berbasis OpenStreetMap',
            'Implementasi Single Sign-On (SSO) Menggunakan OAuth 2.0 dan JSON Web Token (JWT)',
            'Rancang Bangun Asisten Virtual Laboratorium Menggunakan Speech-to-Text Whisper',
            'Klasifikasi Mutu Beras Menggunakan Pengolahan Citra Digital dan K-Nearest Neighbor',
            'Pengembangan Sistem Antrean Rumah Sakit Online Berbasis WebSocket Real-Time',
            'Sistem Prediksi Cuaca Ekstrem Lokal Berbasis Long Short-Term Memory (LSTM)',
            'Analisis User Experience Aplikasi SuperApp Perbankan Menggunakan Metode HEART Framework',
            'Pengembangan Modul Pembelajaran Interaktif Pemrograman Web Berbasis WebAssembly',
            'Sistem Pendeteksi Kantuk Pengemudi Berbasis Eye-Aspect Ratio dan Webcam',
            'Rancang Bangun Smart Green House Berbasis Sensor ESP32 dan Dashboard Monitoring',
            'Penerapan Natural Language Processing untuk Pengelompokan Otomatis Dokumen Skripsi',
        ];

        $studentNames = [
            'Claudia Stephanie Tan', 'Muhammad Fadhil Ramadhan', 'Akmal Falah Maulana',
            'Zaskia Nabila Putri', 'Bagus Prasetyo Nugroho', 'Dinda Permata Sari',
            'Rizky Aditya Pratama', 'Nurul Annisa Fitri', 'Farhan Maulana Hakim',
            'Siti Hajar Rahmawati', 'Dimas Arya Pamungkas', 'Anggita Sekar Arum',
            'Fikri Haikal Rahman', 'Meisya Aurelia Putri', 'Reza Kurnia Ramadhani',
            'Naufal Ilham Saputra', 'Syifa Ayu Lestari', 'Alifian Bagas Wicaksono',
            'Taufik Hidayatullah', 'Kharisma Dewi Fortuna', 'Fajar Nur Cahyono',
            'Aulia Rahmah Azzahra', 'Bayu Tri Pamungkas', 'Devina Alisya Putri',
            'Gilang Ramadhan', 'Intan Permatasari', 'Kevin Ardiansyah',
            'Lestari Wulandari', 'Michael Christian', 'Nadya Putri Utami',
            'Oscar Febrian', 'Putri Ayu Wandira', 'Qori Akbar Mahendra',
            'Rian Hidayatulloh', 'Salma Zahirah', 'Tommy Wijaya Kusuma',
            'Ulfah Nur Faizah', 'Vicky Hendrawan', 'Wahyu Setiawan', 'Yohanes Aditya'
        ];

        $items = [];
        $total = count($sampleTitles);

        for ($i = 0; $i < $total; $i++) {
            $year = 2024 - ($i % 3);
            $bidang = $bidangList[$i % count($bidangList)];
            $metode = $metodeList[$i % count($metodeList)];
            $p1 = $dosenList[$i % count($dosenList)];
            $p2 = $dosenList[($i + 3) % count($dosenList)];
            $author = $studentNames[$i % count($studentNames)];
            $nim = '11' . (20 + ($i % 4)) . '10' . str_pad($i + 1, 2, '0', STR_PAD_LEFT);
            $day = ($i % 25) + 1;
            $month = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'][$i % 12];

            $items[] = [
                'id' => $i + 1,
                'tahun' => (string) $year,
                'prodi' => 'S1 Informatika',
                'bidang' => $bidang,
                'judul' => $sampleTitles[$i],
                'penulis' => $author,
                'nim' => $nim,
                'pembimbing_1' => $p1['name'],
                'nip_pembimbing_1' => $p1['nip'],
                'pembimbing_2' => $p2['name'],
                'nip_pembimbing_2' => $p2['nip'],
                'abstrak_id' => "Penelitian ini berfokus pada {$sampleTitles[$i]} dengan menerapkan metode {$metode}. Hasil pengujian sistem menunjukkan tingkat akurasi dan efisiensi yang optimal serta mampu memberikan kontribusi nyata dalam bidang {$bidang}.",
                'abstrak_en' => "This study investigates {$sampleTitles[$i]} by applying the {$metode} approach. Evaluation results demonstrate significant performance improvements, achieving reliable outcomes in the field of {$bidang}.",
                'tags' => ['#' . str_replace([' ', '/', '&', '-'], '', $bidang), '#' . str_replace([' ', '/', '&', '-'], '', $metode), '#Informatika', '#ITK'],
                'metode' => $metode,
                'tanggal_sidang' => "{$day} {$month} {$year}",
                'doi' => "https://doi.org/10.14710/sipta.{$year}." . (300 + $i),
            ];
        }

        return Inertia::render('Katalog/Index', [
            'items' => $items,
        ]);
    }
}
