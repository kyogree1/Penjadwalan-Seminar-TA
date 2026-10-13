import type { AcademicSubmission, JudulSubmission } from '@/types/models';

export const mockJudulSubmissions: JudulSubmission[] = [
    {
        id: 1,
        nama: 'Muhammad Azka Yunastio',
        nim: '11231036',
        angkatan: '2023',
        bidang: 'Artificial Intelligence & NLP',
        judul: 'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Penjadwalan Seminar Tugas Akhir',
        deskripsi:
            'Penelitian ini mengembangkan sistem otomasi penjadwalan seminar proposal dan hasil berbasis Web dengan optimasi Algoritma Genetika untuk menghindari konflik jadwal dosen dan mahasiswa, serta dilengkapi asisten virtual interaktif berbasis LLM.',
        pembimbing1Usulan: 'Dra. Nurul Rahmania, M.T.',
        pembimbing2Usulan: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        pembimbing1Final: 'Dra. Nurul Rahmania, M.T.',
        pembimbing2Final: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        tanggal: '28 September 2026',
        status: 'diajukan',
        catatanKaprodi:
            'Menunggu telaah kesesuaian topik dengan roadmap riset program studi.',
    },
    {
        id: 2,
        nama: 'Hari Yatun Nasifa',
        nim: '11231030',
        angkatan: '2023',
        bidang: 'Human-Computer Interaction & Software Eng.',
        judul: 'Pengembangan Antarmuka Sistem Informasi Akademik Tugas Akhir Berbasis Desain Komponen Reusable dan Responsif',
        deskripsi:
            'Fokus pada rancang bangun UI/UX web modern berbasis Vue 3 dan Tailwind CSS untuk mempermudah pemantauan alur sidang TA mahasiswa secara real-time dan ramah pengguna.',
        pembimbing1Usulan: 'Dian Permata Sari, S.Kom., M.Kom.',
        pembimbing2Usulan: 'Rian Kurniawan, S.Kom., M.Eng.',
        pembimbing1Final: 'Dian Permata Sari, S.Kom., M.Kom.',
        pembimbing2Final: 'Rian Kurniawan, S.Kom., M.Eng.',
        tanggal: '25 September 2026',
        status: 'disetujui',
        catatanKaprodi:
            'Topik disetujui. Lanjutkan persiapan Bab 1-3 untuk Seminar Proposal.',
    },
    {
        id: 3,
        nama: 'Junnior Marcellino Polla',
        nim: '11231034',
        angkatan: '2023',
        bidang: 'Cloud Computing & Cyber Security',
        judul: 'Rancang Bangun Arsitektur Microservices dan Keamanan Data pada Portal Layanan Ujian Tugas Akhir',
        deskripsi:
            'Membahas perancangan backend REST API dengan autentikasi JWT dan enkripsi data sensitif dokumen tugas akhir mahasiswa.',
        pembimbing1Usulan: 'Dr. Budi Wicaksono, M.Kom.',
        pembimbing2Usulan: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        pembimbing1Final: 'Dr. Budi Wicaksono, M.Kom.',
        pembimbing2Final: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        tanggal: '22 September 2026',
        status: 'revisi',
        catatanKaprodi:
            'Perjelas studi kasus pembanding dan batasan pengujian keamanan sistem pada bagian latar belakang.',
    },
    {
        id: 4,
        nama: 'Mochammad Reezqi Pratama',
        nim: '11231041',
        angkatan: '2023',
        bidang: 'Computational Intelligence & Optimasi',
        judul: 'Optimasi Alokasi Dosen Penguji Ujian Skripsi dengan Pendekatan Constraint Satisfaction Problem dan Algoritma Genetika',
        deskripsi:
            'Eksperimen tuning hyperparameter mutation rate dan crossover rate untuk mencari konvergensi fitness tercepat pada penjadwalan sidang.',
        pembimbing1Usulan: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        pembimbing2Usulan: 'Dra. Nurul Rahmania, M.T.',
        pembimbing1Final: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        pembimbing2Final: 'Dra. Nurul Rahmania, M.T.',
        tanggal: '20 September 2026',
        status: 'disetujui',
        catatanKaprodi:
            'Judul dan metode sangat relevan dengan kebutuhan prodi. Disetujui.',
    },
    {
        id: 5,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        bidang: 'Human-Computer Interaction & Software Eng.',
        judul: 'Rancang Bangun Frontend Dashboard Monitoring Progres Bimbingan Tugas Akhir Menggunakan Progressive Web Apps',
        deskripsi:
            'Penerapan PWA dan caching cerdas pada frontend dashboard monitoring tugas akhir mahasiswa untuk aksesibilitas tinggi di piranti bergerak.',
        pembimbing1Usulan: 'Rian Kurniawan, S.Kom., M.Eng.',
        pembimbing2Usulan: 'Dian Permata Sari, S.Kom., M.Kom.',
        pembimbing1Final: 'Rian Kurniawan, S.Kom., M.Eng.',
        pembimbing2Final: 'Dian Permata Sari, S.Kom., M.Kom.',
        tanggal: '18 September 2026',
        status: 'disetujui',
        catatanKaprodi: 'Topik disetujui. Lanjutkan penyusunan proposal.',
    },
];

export const mockSemproSubmissions: AcademicSubmission[] = [
    {
        id: 1,
        nama: 'Anisa Rahmadani',
        nim: '11231010',
        angkatan: '2023',
        judul: 'Analisis Perbandingan Kinerja dan Generalisasi Model Computer Vision pada Edge Device',
        status: 'diajukan',
        pembimbing: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        penguji:
            'Sri Wahyuni, S.Kom., M.T. · Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        jadwal: 'Selasa, 29 Sep 2026 · 09.00 WITA',
        files: [
            'Proposal Bab 1–3.pdf',
            'Lembar Persetujuan.pdf',
            'Turnitin 14%.pdf',
            'Logbook TA-04.pdf',
        ],
    },
    {
        id: 2,
        nama: 'Bayu Aditya Saputra',
        nim: '11231089',
        angkatan: '2023',
        judul: 'Rancang Bangun Sistem Lokalisasi Indoor Menggunakan WiFi Fingerprinting',
        status: 'revisi',
        pembimbing: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        penguji:
            'Sri Wahyuni, S.Kom., M.T. · Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        jadwal: 'Menunggu verifikasi berkas',
        files: [
            'Proposal WiFi Fingerprint.pdf',
            'Lembar Persetujuan.pdf',
            'Turnitin 19%.pdf',
        ],
        catatan:
            'Perbaiki metodologi pengujian sinyal RSSI pada kondisi lingkungan dinamis.',
    },
    {
        id: 3,
        nama: 'Ahmad Fauzan Pratama',
        nim: '11231065',
        angkatan: '2023',
        judul: 'Analisis Ketahanan Model Countermeasure Terhadap Serangan Adversarial pada Citra Medis',
        status: 'disetujui',
        pembimbing: 'Sri Wahyuni, S.Kom., M.T.',
        penguji:
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom. · Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        jadwal: 'Rabu, 30 Sep 2026 · 13.30 WITA',
        files: [
            'Proposal Citra Medis.pdf',
            'Turnitin 12%.pdf',
            'Logbook TA-04.pdf',
        ],
        catatan: 'Dokumen lengkap dan memenuhi persyaratan akademik sempro.',
    },
];

export const mockSidangSubmissions: AcademicSubmission[] = [
    {
        id: 1,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Vision Transformer pada Citra Fundus',
        status: 'diajukan',
        pembimbing: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        penguji: 'Dr. Muhammad Rizal, M.Kom. · Hendra Pratama, S.Kom., M.Sc.',
        jadwal: 'Menunggu hasil penjadwalan',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: true },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: [
            'Naskah TA Final.pdf',
            'Lembar Persetujuan Sidang.pdf',
            'Turnitin 11%.pdf',
        ],
    },
    {
        id: 2,
        nama: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        judul: 'Analisis Sentimen Kebijakan IKN Menggunakan IndoBERT dan Topic Modeling',
        status: 'revisi',
        pembimbing: 'Sri Wahyuni, S.Kom., M.T.',
        penguji: 'Belum ditetapkan',
        jadwal: 'Belum tersedia',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: false },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: ['Naskah TA.pdf', 'Turnitin 16%.pdf'],
        catatan:
            'Lengkapi bukti minimal 12 kali bimbingan sebelum diverifikasi.',
    },
    {
        id: 3,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        angkatan: '2022',
        judul: 'Deteksi Kerusakan Aspal Jalan Raya pada Citra Drone Menggunakan YOLOv8',
        status: 'disetujui',
        pembimbing: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        penguji:
            'Prof. Dr. Agus Tri Haryanto, M.T. · Sri Wahyuni, S.Kom., M.T.',
        jadwal: 'Kamis, 1 Okt 2026 · 09.00 WITA',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: true },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: [
            'Naskah TA Final.pdf',
            'Lembar Persetujuan Sidang.pdf',
            'Turnitin 9%.pdf',
        ],
        catatan: 'Naskah final dan persyaratan sidang lengkap memenuhi syarat.',
    },
];
