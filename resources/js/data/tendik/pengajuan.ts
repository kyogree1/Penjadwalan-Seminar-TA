import type { Pengajuan } from '@/types/models';

/**
 * Mock pengajuan data for the tendik verification queue.
 *
 * Delete this file when the backend sends real props; the pages default to it
 * and nothing in the template knows the difference.
 */
export const mockPengajuan: Pengajuan[] = [
    {
        id: 1,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        prodi: 'S1 Informatika',
        tipe: 'Sidang Akhir TA',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto',
        pembimbing2: 'Gusti Ahmad Fanshuri, M.Cs.',
        tanggalDaftar: '18 September 2026',
        status: 'diajukan',
        fieldTerverifikasi: ['judul', 'berkas'],
        syarat: [
            {
                label: 'Bukti Pembayaran UKT Semester Ganjil 2026/2027',
                file: 'Bukti_UKT_11231006.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Akademik (140 SKS, IPK 3.82, Bebas D/E)',
                file: 'Transkrip_11231006.pdf',
                valid: true,
            },
            {
                label: 'Formulir TA-04 (ACC Pembimbing 1 & 2)',
                file: 'Form_TA04_Signed.pdf',
                valid: true,
            },
            {
                label: 'Sertifikat TOEFL ITK (Skor 510)',
                file: 'TOEFL_Certificate.pdf',
                valid: true,
            },
            {
                label: 'Laporan Turnitin Similarity Index (14%)',
                file: 'Turnitin_Report_14pct.pdf',
                valid: true,
            },
        ],
        riwayat: [
            {
                aktor: 'Akmal Falah Maulana',
                peran: 'mahasiswa',
                aksi: 'mengajukan',
                waktu: '18 September 2026, 10:15 WITA',
                catatan: 'Pengajuan sidang akhir diunggah lengkap.',
            },
        ],
    },
    {
        id: 2,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        prodi: 'S1 Informatika',
        tipe: 'Sidang Akhir TA',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto',
        pembimbing2: 'Dewi Ratnasari, S.T., M.T.',
        tanggalDaftar: '18 September 2026',
        status: 'diajukan',
        fieldTerverifikasi: ['judul', 'berkas'],
        syarat: [
            {
                label: 'Bukti Pembayaran UKT Semester Ganjil 2026/2027',
                file: 'UKT_11211045.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Akademik (142 SKS, IPK 3.75, Bebas D/E)',
                file: 'Transkrip_11211045.pdf',
                valid: true,
            },
            {
                label: 'Formulir TA-04 (ACC Pembimbing 1 & 2)',
                file: 'ACC_TA04_11211045.pdf',
                valid: true,
            },
            {
                label: 'Sertifikat TOEFL ITK (Skor 485)',
                file: 'TOEFL_11211045.pdf',
                valid: true,
            },
            {
                label: 'Laporan Turnitin Similarity Index (11%)',
                file: 'Turnitin_11pct.pdf',
                valid: true,
            },
        ],
        riwayat: [
            {
                aktor: 'Siti Nurhaliza Putri',
                peran: 'mahasiswa',
                aksi: 'mengajukan',
                waktu: '18 September 2026, 08:40 WITA',
                catatan: 'Pengajuan sidang akhir diunggah lengkap.',
            },
        ],
    },
    {
        id: 3,
        nama: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        tipe: 'Seminar Proposal',
        judul: 'Penerapan Internet of Things untuk Monitoring Kualitas Air Tambak Udang Berbasis LoRaWAN di Balikpapan',
        pembimbing1: 'Prof. Dr. Agus Tri Haryanto',
        pembimbing2: 'Dr. Ir. Tejo Wahyu Susanto',
        tanggalDaftar: '17 September 2026',
        status: 'diajukan',
        fieldTerverifikasi: ['judul', 'berkas'],
        syarat: [
            {
                label: 'KRS Aktif Mata Kuliah Seminar Proposal',
                file: 'KRS_11221089.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Sementara (Min 110 SKS)',
                file: 'Transkrip_11221089.pdf',
                valid: true,
            },
            {
                label: 'Formulir Persetujuan Sempro (TA-02)',
                file: 'ACC_Sempro_Signed.pdf',
                valid: true,
            },
            {
                label: 'Draft Proposal Naskah Bab 1-3',
                file: 'Proposal_Bagus_Fix.pdf',
                valid: true,
            },
        ],
        riwayat: [
            {
                aktor: 'Bagus Pratama Hendrawan',
                peran: 'mahasiswa',
                aksi: 'mengajukan',
                waktu: '17 September 2026, 16:20 WITA',
                catatan: 'Pendaftaran seminar proposal diunggah.',
            },
        ],
    },
];
