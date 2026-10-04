export interface SessionDetail {
    id: string | number;
    type: 'Sempro' | 'Sidang' | 'Admin';
    title: string;
    studentName?: string;
    studentNim?: string;
    prodi?: string;
    angkatan?: string;
    time: string;
    room?: string;
    building?: string;
    mode?: string;
    judul?: string;
    pembimbing?: { name: string; nip: string; role: string }[];
    penguji?: { name: string; nip: string; role: string }[];
    notes?: string;
}

export interface CalendarEvent {
    id: string | number;
    title: string;
    type: 'primary' | 'warning' | 'danger' | 'success' | 'info' | 'today';
    detail?: SessionDetail;
    sessions?: SessionDetail[];
}

export interface CalendarDayItem {
    day: number;
    dateString: string;
    currentMonth: boolean;
    isToday?: boolean;
    events: CalendarEvent[];
}

export const calendarDays: CalendarDayItem[] = [
    {
        day: 29,
        dateString: 'Selasa, 29 September 2026',
        currentMonth: false,
        events: [
            {
                id: 'e-29',
                title: 'Bimbingan Mandiri',
                type: 'info',
                detail: {
                    id: 'd-29',
                    type: 'Admin',
                    title: 'Konsultasi Bimbingan Mandiri',
                    time: '09:00 - 15:00 WITA',
                    notes: 'Konsultasi kelengkapan dokumen proposal bersama dosen pembimbing.',
                },
            },
        ],
    },
    {
        day: 30,
        dateString: 'Rabu, 30 September 2026',
        currentMonth: false,
        events: [],
    },
    {
        day: 31,
        dateString: 'Kamis, 31 September 2026',
        currentMonth: false,
        events: [],
    },
    {
        day: 1,
        dateString: 'Kamis, 1 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-1-1',
                title: 'Buka Pendaftaran Gel. 2',
                type: 'success',
                detail: {
                    id: 'd-1',
                    type: 'Admin',
                    title: 'Pembukaan Pendaftaran Gelombang 2',
                    time: '08:00 - 23:59 WITA',
                    notes: 'Pendaftaran Seminar Proposal dan Sidang Tugas Akhir Periode Gasal 2026/2027 resmi dibuka secara daring melalui portal SIPTA IF.',
                },
            },
            {
                id: 'e-1-2',
                title: 'Briefing Dosen Pembimbing',
                type: 'info',
                detail: {
                    id: 'd-1-2',
                    type: 'Admin',
                    title: 'Briefing Tim Dosen Pembimbing TA',
                    time: '13:30 - 15:00 WITA',
                    notes: 'Penyelarasan standar rubrik bimbingan dan verifikasi logbook digital mahasiswa.',
                },
            },
            {
                id: 'e-1-3',
                title: 'Verifikasi Usulan Topik TA',
                type: 'primary',
                detail: {
                    id: 'd-1-3',
                    type: 'Admin',
                    title: 'Verifikasi Usulan Topik oleh KBK',
                    time: '09:00 - 16:00 WITA',
                    notes: 'Pemeriksaan kesesuaian roadmap riset informatika ITK oleh Koordinator KBK.',
                },
            },
        ],
    },
    {
        day: 2,
        dateString: 'Jumat, 2 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 3,
        dateString: 'Sabtu, 3 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-3-1',
                title: 'Sempro: Akmal Falah (09:00)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-3-1',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Akmal Falah Maulana',
                        studentNim: '11231006',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2023',
                        time: '09:00 - 10:30 WITA',
                        room: 'Ruang Lab Riset Multimedia',
                        building: 'Gedung A, Lantai 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Sistem Penjadwalan Seminar dan Chatbot Layanan Akademik Menggunakan Algoritma Genetika dan Large Language Model',
                        pembimbing: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Co-Pembimbing',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 2',
                            },
                        ],
                        notes: 'Terbuka untuk mahasiswa Informatika sebagai peserta seminar pendengar. Mahasiswa wajib hadir 15 menit sebelum acara dimulai dengan jas almamater.',
                    },
                ],
            },
            {
                id: 'e-3-2',
                title: 'Sempro: Bagus Pratama (10:45)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-3-2',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Bagus Pratama Hendrawan',
                        studentNim: '11221089',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '10:45 - 12:15 WITA',
                        room: 'Ruang Lab Jaringan & Keamanan Siber',
                        building: 'Gedung B, Lantai 3',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Implementasi Protokol LoRaWAN untuk Pemantauan Kualitas Air Tambak Berbasis IoT di Kawasan Pesisir Balikpapan',
                        pembimbing: [
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Co-Pembimbing',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Ir. Budi Santoso, M.Eng.',
                                nip: '198003152005011002',
                                role: 'Penguji 2',
                            },
                        ],
                        notes: 'Pengujian prototipe modul transmisi frekuensi 915 MHz dan simulasi paket data.',
                    },
                ],
            },
            {
                id: 'e-3-3',
                title: 'Batas Input Nilai Sempro',
                type: 'info',
                detail: {
                    id: 'd-3-3',
                    type: 'Admin',
                    title: 'Batas Akhir Input Penilaian Sempro oleh Tim Penguji',
                    time: '23:59 WITA',
                    notes: 'Dosen penguji diharapkan menyelesaikan pengisian form penilaian di portal sebelum batas waktu.',
                },
            },
        ],
    },
    {
        day: 4,
        dateString: 'Minggu, 4 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 5,
        dateString: 'Senin, 5 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-5-1',
                title: 'Sempro: Dinda Rahmadani (13:30)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-5-1',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Dinda Rahmadani',
                        studentNim: '11221012',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '13:30 - 15:00 WITA',
                        room: 'Ruang Sidang Jurusan JSTI',
                        building: 'Gedung Lab Terpadu Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Fine-Tuning IndoBERT untuk Analisis Sentimen Kebijakan Transportasi Publik IKN pada Media Sosial Twitter/X',
                        pembimbing: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Pembimbing Utama',
                            },
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Co-Pembimbing',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 2',
                            },
                        ],
                        notes: 'Presentasi metode scraping API Twitter dan data preprocessing teks berbahasa Indonesia.',
                    },
                ],
            },
            {
                id: 'e-5-2',
                title: 'Klinik Penulisan Naskah TA',
                type: 'info',
                detail: {
                    id: 'd-5-2',
                    type: 'Admin',
                    title: 'Klinik Tata Tulis Format Tugas Akhir',
                    time: '10:00 - 11:30 WITA',
                    room: 'Lab Pemrograman Komputer',
                    notes: 'Bimbingan tata tulis sitasi Mendeley/Zotero dan template baku naskah skripsi ITK.',
                },
            },
            {
                id: 'e-5-3',
                title: 'Konsultasi Bebas Plagiasi',
                type: 'success',
                detail: {
                    id: 'd-5-3',
                    type: 'Admin',
                    title: 'Layanan Pra-Cek Turnitin Mahasiswa',
                    time: '15:30 - 17:00 WITA',
                    notes: 'Pengecekan mandiri bab 1 hingga bab 3 dengan batas maksimal similarity 20%.',
                },
            },
        ],
    },
    {
        day: 6,
        dateString: 'Selasa, 6 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 7,
        dateString: 'Rabu, 7 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-7-1',
                title: 'Bimbingan Mandiri Dosen',
                type: 'info',
                detail: {
                    id: 'd-7-1',
                    type: 'Admin',
                    title: 'Sesi Bimbingan Rutin Mingguan',
                    time: '09:00 - 12:00 WITA',
                    notes: 'Konsultasi kemajuan penelitian mahasiswa bersama dosen pembimbing.',
                },
            },
            {
                id: 'e-7-2',
                title: 'Rapat Tim Tugas Akhir',
                type: 'warning',
                detail: {
                    id: 'd-7-2',
                    type: 'Admin',
                    title: 'Rapat Evaluasi Penjadwalan Sidang',
                    time: '14:00 - 15:30 WITA',
                    notes: 'Koordinasi Kaprodi, Koordinator TA, dan staf Tendik terkait ketersediaan ruangan.',
                },
            },
        ],
    },
    {
        day: 8,
        dateString: 'Kamis, 8 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 9,
        dateString: 'Jumat, 9 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-9-1',
                title: 'Sidang TA: Dimas Wahyu (08:30)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-9-1',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Dimas Wahyu Pratama',
                        studentNim: '11211044',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '08:30 - 10:30 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A, Lantai 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Deteksi Kerusakan Permukaan Jalan Tol IKN Balikpapan Menggunakan Arsitektur YOLOv10 dan Edge Computing',
                        pembimbing: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Co-Pembimbing',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Ir. Budi Santoso, M.Eng.',
                                nip: '198003152005011002',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 2',
                            },
                        ],
                        notes: 'Ujian Sidang Tertutup Skripsi. Mahasiswa wajib berpakaian resmi kemeja putih, dasi, dan jas almamater.',
                    },
                ],
            },
            {
                id: 'e-9-2',
                title: 'Sidang TA: Farhan K. (13:30)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-9-2',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Farhan Kurniawan',
                        studentNim: '11211078',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '13:30 - 15:30 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A, Lantai 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Rancang Bangun Sistem Informasi Rekomendasi Karir Berbasis Graph Neural Network untuk Lulusan ITK',
                        pembimbing: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Pembimbing Utama',
                            },
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Co-Pembimbing',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Penguji 2',
                            },
                        ],
                        notes: 'Demonstrasi sistem berbasis web dan evaluasi akurasi metrik precision@k.',
                    },
                ],
            },
            {
                id: 'e-9-3',
                title: 'Bimbingan Skripsi Gel. 2',
                type: 'info',
                detail: {
                    id: 'd-9-3',
                    type: 'Admin',
                    title: 'Konsultasi Perbaikan Metodologi Penelitian',
                    time: '10:30 - 12:00 WITA',
                    notes: 'Konsultasi khusus mahasiswa bimbingan KBK Rekayasa Perangkat Lunak.',
                },
            },
            {
                id: 'e-9-4',
                title: 'Unggah Revisi Sempro Tahap 1',
                type: 'primary',
                detail: {
                    id: 'd-9-4',
                    type: 'Admin',
                    title: 'Batas Unggah Naskah Proposal Telah Direvisi',
                    time: '23:59 WITA',
                    notes: 'Unggah lembar persetujuan perbaikan sempro yang telah ditandatangani penguji.',
                },
            },
        ],
    },
    {
        day: 10,
        dateString: 'Sabtu, 10 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 11,
        dateString: 'Minggu, 11 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 12,
        dateString: 'Senin, 12 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 13,
        dateString: 'Selasa, 13 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-13-1',
                title: 'Sempro: Nurul Azizah (09:00)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-13-1',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Nurul Azizah',
                        studentNim: '11221033',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '09:00 - 10:30 WITA',
                        room: 'Ruang Seminar Informatika',
                        building: 'Gedung A Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Penerapan Algoritma K-Means dan Random Forest untuk Klasterisasi Wilayah Rentan Banjir di Balikpapan',
                        pembimbing: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Ir. Budi Santoso, M.Eng.',
                                nip: '198003152005011002',
                                role: 'Penguji 1',
                            },
                        ],
                        notes: 'Terbuka untuk mahasiswa pendengar.',
                    },
                ],
            },
            {
                id: 'e-13-2',
                title: 'Sempro: Rizky Maulana (10:45)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-13-2',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Rizky Maulana',
                        studentNim: '11221055',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '10:45 - 12:15 WITA',
                        room: 'Ruang Seminar Informatika',
                        building: 'Gedung A Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Pengembangan Aplikasi Mobile Edukasi Konservasi Terumbu Karang Teluk Balikpapan dengan Gamifikasi',
                        pembimbing: [
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 1',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-13-3',
                title: 'Penyerahan Lembar Revisi',
                type: 'info',
                detail: {
                    id: 'd-13-3',
                    type: 'Admin',
                    title: 'Batas Penyerahan Bukti Revisi Sempro',
                    time: '16:00 WITA',
                    notes: 'Penyerahan lembar pengesahan tanda tangan dosen penguji ke loket Tendik.',
                },
            },
        ],
    },
    {
        day: 14,
        dateString: 'Rabu, 14 Oktober 2026',
        currentMonth: true,
        isToday: true,
        events: [
            {
                id: 'e-14-1',
                title: 'Hari Ini',
                type: 'today',
            },
            {
                id: 'e-14-2',
                title: 'Sosialisasi Turnitin TA (10:00)',
                type: 'info',
                detail: {
                    id: 'd-14',
                    type: 'Admin',
                    title: 'Sosialisasi Pengecekan Turnitin & Unggah Bebas Plagiasi',
                    time: '10:00 - 11:30 WITA',
                    room: 'Ruang Aula Gedung D Lt. 3 & Zoom',
                    notes: 'Wajib dihadiri seluruh mahasiswa tingkat akhir yang memprogram mata kuliah Tugas Akhir periode Gasal 2026/2027.',
                },
            },
            {
                id: 'e-14-3',
                title: 'Buka Pengajuan Judul Susulan',
                type: 'success',
                detail: {
                    id: 'd-14-3',
                    type: 'Admin',
                    title: 'Pembukaan Pengajuan Judul Skripsi Gelombang Susulan',
                    time: '08:00 - 23:59 WITA',
                    notes: 'Khusus mahasiswa angkatan 2020 dan 2021 yang belum memiliki judul terverifikasi.',
                },
            },
        ],
    },
    {
        day: 15,
        dateString: 'Kamis, 15 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 16,
        dateString: 'Jumat, 16 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 17,
        dateString: 'Sabtu, 17 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-17-1',
                title: 'Sidang TA: Kevin Jonathan (09:00)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-17-1',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Kevin Jonathan',
                        studentNim: '11211029',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '09:00 - 11:00 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Optimasi Hyperparameter Arsitektur Vision Transformer (ViT) untuk Klasifikasi Penyakit Daun Kelapa Sawit',
                        pembimbing: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Penguji 1 (Ketua)',
                            },
                            {
                                name: 'Ir. Budi Santoso, M.Eng.',
                                nip: '198003152005011002',
                                role: 'Penguji 2',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-17-2',
                title: 'Sidang TA: Hendra Kusuma (13:00)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-17-2',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Hendra Kusuma Jaya',
                        studentNim: '11211050',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '13:00 - 15:00 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Sistem Deteksi Phishing URL Menggunakan Kombinasi Fitur Lexical dan Transformer BERT',
                        pembimbing: [
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-17-3',
                title: 'Batas Validasi Berkas',
                type: 'danger',
                detail: {
                    id: 'd-17-3',
                    type: 'Admin',
                    title: 'Batas Validasi Berkas Sidang Kelulusan',
                    time: '17:00 WITA',
                    notes: 'Batas verifikasi persetujuan draf laporan skripsi oleh koordinator KBK.',
                },
            },
        ],
    },
    {
        day: 18,
        dateString: 'Minggu, 18 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 19,
        dateString: 'Senin, 19 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-19-1',
                title: 'Bimbingan Akbar Pra-Sidang',
                type: 'info',
                detail: {
                    id: 'd-19',
                    type: 'Admin',
                    title: 'Sesi Bimbingan Akbar Menjelang Sidang',
                    time: '13:00 - 16:00 WITA',
                    room: 'Lab Rekayasa Perangkat Lunak',
                    notes: 'Konsultasi kelengkapan logbook minimal 8 kali dan validasi tanda tangan persetujuan dosen pembimbing.',
                },
            },
            {
                id: 'e-19-2',
                title: 'Verifikasi Bebas Pustaka ITK',
                type: 'success',
                detail: {
                    id: 'd-19-2',
                    type: 'Admin',
                    title: 'Layanan Pengurusan Surat Bebas Pustaka',
                    time: '09:00 - 15:00 WITA',
                    notes: 'Pengembalian buku pinjaman dan verifikasi sumbangan buku skripsi.',
                },
            },
        ],
    },
    {
        day: 20,
        dateString: 'Selasa, 20 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-20-1',
                title: 'Sempro: Aditya Pratama (09:00)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-20-1',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Aditya Pratama Ramadhan',
                        studentNim: '11221042',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '09:00 - 10:30 WITA',
                        room: 'Ruang Seminar Informatika',
                        building: 'Gedung A Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Rancang Bangun Sistem Pemantauan Emisi Karbon Berbasis IoT Menggunakan Blockchain Hyperledger Fabric',
                        pembimbing: [
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-20-2',
                title: 'Sidang TA: Anita Sari (10:30)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-20-2',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Anita Sari Dewi',
                        studentNim: '11211015',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '10:30 - 12:30 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Analisis User Experience Aplikasi Presensi Kampus Berbasis Pengenalan Wajah dengan Metode HEART Framework',
                        pembimbing: [
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-20-3',
                title: 'Yudisium Periode Gasal',
                type: 'success',
                detail: {
                    id: 'd-20-3',
                    type: 'Admin',
                    title: 'Rapat Pleno Yudisium Kelulusan Gasal',
                    time: '14:00 - 16:00 WITA',
                    room: 'Ruang Rapat Jurusan JSTI',
                    notes: 'Penetapan kelulusan yudisium bagi mahasiswa Informatika yang telah menyelesaikan sidang dan revisi.',
                },
            },
            {
                id: 'e-20-4',
                title: 'Penyerahan Hardcover Skripsi',
                type: 'info',
                detail: {
                    id: 'd-20-4',
                    type: 'Admin',
                    title: 'Batas Penyerahan Buku Skripsi Hardcover',
                    time: '16:30 WITA',
                    notes: 'Pengumpulan 2 eksemplar hardcover warna biru donker ke ruang administrasi Tendik.',
                },
            },
        ],
    },
    {
        day: 21,
        dateString: 'Rabu, 21 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 22,
        dateString: 'Kamis, 22 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 23,
        dateString: 'Jumat, 23 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-23-1',
                title: 'Sempro: Wahyu Hidayat (09:00)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-23-1',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Wahyu Hidayat',
                        studentNim: '11221070',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '09:00 - 10:30 WITA',
                        room: 'Ruang Lab Riset Multimedia',
                        building: 'Gedung A Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Analisis Performa Model Retrieval-Augmented Generation (RAG) pada Dokumen Regulasi Akademik Kampus',
                        pembimbing: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Penguji 1',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-23-2',
                title: 'Sempro: Salsabila Putri (10:45)',
                type: 'primary',
                sessions: [
                    {
                        id: 's-23-2',
                        type: 'Sempro',
                        title: 'Seminar Proposal TA',
                        studentName: 'Salsabila Putri',
                        studentNim: '11221081',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2022',
                        time: '10:45 - 12:15 WITA',
                        room: 'Ruang Lab Riset Multimedia',
                        building: 'Gedung A Lt. 2',
                        mode: 'Tatap Muka (Offline)',
                        judul: 'Sistem Pendukung Keputusan Penentuan Dosen Pembimbing TA Menggunakan Metode TOPSIS Berdasarkan Topik Riset',
                        pembimbing: [
                            {
                                name: 'Rina Agustina, S.T., M.Kom.',
                                nip: '198906242015042002',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Penguji 1',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-23-3',
                title: 'Review Logbook Bimbingan',
                type: 'info',
                detail: {
                    id: 'd-23-3',
                    type: 'Admin',
                    title: 'Pemeriksaan Berkala Logbook oleh Dosen Wali',
                    time: '14:00 - 16:00 WITA',
                    notes: 'Verifikasi keaktifan bimbingan minimal mahasiswa sebelum pengajuan ujian lanjutan.',
                },
            },
        ],
    },
    {
        day: 24,
        dateString: 'Sabtu, 24 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 25,
        dateString: 'Minggu, 25 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-25-1',
                title: 'Sidang TA: Aldi Firmansyah (08:30)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-25-1',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Aldi Firmansyah',
                        studentNim: '11211002',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '08:30 - 10:30 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Sistem Rekomendasi Tempat Magang Berbasis Content-Based Filtering & Natural Language Processing',
                        pembimbing: [
                            {
                                name: 'Siti Nurhaliza, S.Kom., M.Cs.',
                                nip: '199108152019032004',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Prof. Dr. Agus Susanto, M.T.',
                                nip: '197802112003121001',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-25-2',
                title: 'Sidang TA: Nabila Maharani (10:45)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-25-2',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'Nabila Maharani',
                        studentNim: '11211019',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '10:45 - 12:45 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Audit Keamanan Informasi Sistem Akademik Berdasarkan Kerangka Kerja ISO 27001:2022',
                        pembimbing: [
                            {
                                name: 'Tejo Wahyu Utomo, S.Kom., M.Cs.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-25-3',
                title: 'Sidang TA: M. Fakhri (13:30)',
                type: 'warning',
                sessions: [
                    {
                        id: 's-25-3',
                        type: 'Sidang',
                        title: 'Sidang Tugas Akhir',
                        studentName: 'M. Fakhri Al-Farizi',
                        studentNim: '11211062',
                        prodi: 'S1 Informatika',
                        angkatan: 'Angkatan 2021',
                        time: '13:30 - 15:30 WITA',
                        room: 'Ruang Sidang Utama Informatika',
                        building: 'Gedung A Lt. 3',
                        mode: 'Tatap Muka (Tertutup)',
                        judul: 'Segmentasi Citra MRI Tumor Otak Berbasis Modifikasi Unet++ dengan Attention Gate',
                        pembimbing: [
                            {
                                name: 'Dr. Ir. Hendra Wijaya, M.Kom.',
                                nip: '198504122010121003',
                                role: 'Pembimbing Utama',
                            },
                        ],
                        penguji: [
                            {
                                name: 'Ir. Budi Santoso, M.Eng.',
                                nip: '198003152005011002',
                                role: 'Penguji 1 (Ketua)',
                            },
                        ],
                    },
                ],
            },
            {
                id: 'e-25-4',
                title: 'Rapat Pleno Kelulusan TA',
                type: 'success',
                detail: {
                    id: 'd-25-4',
                    type: 'Admin',
                    title: 'Rapat Pleno Penetapan Kelulusan Ujian Skripsi',
                    time: '16:00 - 17:30 WITA',
                    notes: 'Rekapitulasi berita acara penilaian sidang oleh dewan penguji Informatika.',
                },
            },
        ],
    },
    {
        day: 26,
        dateString: 'Senin, 26 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 27,
        dateString: 'Selasa, 27 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-27-1',
                title: 'Verifikasi Berkas Tendik',
                type: 'info',
                detail: {
                    id: 'd-27',
                    type: 'Admin',
                    title: 'Verifikasi Berkas Akhir Tendik',
                    time: '09:00 - 16:00 WITA',
                    notes: 'Pemeriksaan keabsahan dokumen bebas plagiasi Turnitin, sertifikat TOEFL/IAET, dan lembar bimbingan.',
                },
            },
            {
                id: 'e-27-2',
                title: 'Validasi Sertifikat TOEFL/IAET',
                type: 'primary',
                detail: {
                    id: 'd-27-2',
                    type: 'Admin',
                    title: 'Validasi Nilai Kemampuan Bahasa Inggris Mahasiswa',
                    time: '10:00 - 14:00 WITA',
                    notes: 'Pengecekan skor minimal 450 untuk syarat kelulusan program sarjana Informatika.',
                },
            },
        ],
    },
    {
        day: 28,
        dateString: 'Rabu, 28 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 29,
        dateString: 'Kamis, 29 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 30,
        dateString: 'Jumat, 30 Oktober 2026',
        currentMonth: true,
        events: [
            {
                id: 'e-30-1',
                title: 'Tutup Gel. 2 (23:59 WITA)',
                type: 'danger',
                detail: {
                    id: 'd-30',
                    type: 'Admin',
                    title: 'Penutupan Pendaftaran Gelombang 2',
                    time: '23:59 WITA',
                    notes: 'Batas akhir pengunggahan proposal dan draft laporan skripsi periode Gasal 2026/2027.',
                },
            },
            {
                id: 'e-30-2',
                title: 'Batas Akhir Revisi Nilai Dosen',
                type: 'warning',
                detail: {
                    id: 'd-30-2',
                    type: 'Admin',
                    title: 'Batas Akhir Revisi Penilaian Ujian Dosen',
                    time: '18:00 WITA',
                    notes: 'Dosen penguji mengunci seluruh nilai akhir seminar dan sidang.',
                },
            },
            {
                id: 'e-30-3',
                title: 'Rekapitulasi Nilai Akhir TA',
                type: 'info',
                detail: {
                    id: 'd-30-3',
                    type: 'Admin',
                    title: 'Rekapitulasi Nilai Transkrip Sementara Tendik',
                    time: '09:00 - 16:00 WITA',
                    notes: 'Penyusunan transkrip nilai kelulusan tugas akhir oleh staf tendik.',
                },
            },
        ],
    },
    {
        day: 31,
        dateString: 'Sabtu, 31 Oktober 2026',
        currentMonth: true,
        events: [],
    },
    {
        day: 1,
        dateString: 'Minggu, 1 November 2026',
        currentMonth: false,
        events: [],
    },
];
