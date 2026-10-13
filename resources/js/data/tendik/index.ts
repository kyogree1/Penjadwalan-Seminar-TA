import type {
    ArsipDokumen,
    CetakTerbaru,
    Mahasiswa,
    Ruangan,
} from '@/types/models';

/** Mock room data for Tendik/Ruangan. Delete when the backend sends real props. */
export const mockRuangan: Ruangan[] = [
    {
        id: 1,
        nama: 'Ruang Sidang FSTI A (GKT 304)',
        gedung: 'Gedung Kuliah Terpadu Lantai 3',
        kapasitas: '25 Orang',
        fasilitas: [
            'Proyektor HDMI 4K',
            'Sound & Mic Wireless',
            'Webcam Hybrid Meeting',
            'AC Sentral',
        ],
        status: 'Digunakan',
        sesiHariIni: [
            {
                waktu: '09:00 - 10:30 WITA',
                kegiatan: 'Sidang Akhir TA (Akmal Falah)',
                status: 'Berlangsung',
            },
            {
                waktu: '13:00 - 14:30 WITA',
                kegiatan: 'Seminar Proposal (Bagus Pratama)',
                status: 'Terjadwal',
            },
        ],
    },
    {
        id: 2,
        nama: 'Ruang Sidang FSTI B (GKT 305)',
        gedung: 'Gedung Kuliah Terpadu Lantai 3',
        kapasitas: '20 Orang',
        fasilitas: [
            'Smart TV 65 Inch',
            'Sound System',
            'Webcam Logitech MeetUp',
            'AC',
        ],
        status: 'Tersedia',
        sesiHariIni: [
            {
                waktu: '10:45 - 12:15 WITA',
                kegiatan: 'Sidang Akhir TA (Siti Nurhaliza)',
                status: 'Selesai',
            },
        ],
    },
    {
        id: 3,
        nama: 'Lab Software Engineering (GKT 208)',
        gedung: 'Gedung Kuliah Terpadu Lantai 2',
        kapasitas: '35 Komputer',
        fasilitas: [
            'Dual Monitor PC',
            'Koneksi LAN Gigabit',
            'Proyektor HD',
            'Whiteboard',
        ],
        status: 'Tersedia',
        sesiHariIni: [],
    },
    {
        id: 4,
        nama: 'Ruang Sidang Virtual Zoom FSTI',
        gedung: 'Cloud Meeting Server ITK',
        kapasitas: '300 Peserta',
        fasilitas: [
            'Breakout Rooms',
            'Cloud Recording Otomatis',
            'Live Stream YouTube',
        ],
        status: 'Tersedia',
        sesiHariIni: [
            {
                waktu: '15:00 - 16:30 WITA',
                kegiatan: 'Bimbingan Hybrid Terpadu',
                status: 'Terjadwal',
            },
        ],
    },
];

export const mockArsipDokumen: ArsipDokumen[] = [
    {
        id: 1,
        kode: 'TA-06',
        nama: 'Berita Acara Seminar Proposal (Sempro)',
        kategori: 'Berita Acara',
        deskripsi:
            'Dokumen pencatatan resmi hasil ujian seminar proposal, lembar revisi penguji, dan status kelulusan sempro.',
        tersedia: 68,
        status: 'Tersedia',
    },
    {
        id: 2,
        kode: 'TA-09',
        nama: 'Berita Acara Ujian Sidang Akhir Tugas Akhir',
        kategori: 'Berita Acara',
        deskripsi:
            'Formulir resmi penetapan nilai akhir yudisium skripsi oleh ketua penguji, anggota penguji, dan pembimbing.',
        tersedia: 34,
        status: 'Tersedia',
    },
    {
        id: 3,
        kode: 'ST-01',
        nama: 'Surat Tugas Tim Dosen Penguji & Pembimbing',
        kategori: 'Surat Tugas',
        deskripsi:
            'Surat penugasan dekanat/jurusan untuk dosen penguji 1, penguji 2, dan dosen pembimbing.',
        tersedia: 102,
        status: 'Tersedia',
    },
    {
        id: 4,
        kode: 'SK-TA',
        nama: 'Surat Keterangan Bebas Tugas Akhir (SK Bebas TA)',
        kategori: 'Surat Keterangan',
        deskripsi:
            'Syarat pengambilan ijazah & transkrip final yang menyatakan naskah skripsi telah tuntas dijilid & diunggah di repository.',
        tersedia: 24,
        status: 'Tersedia',
    },
];

export const mockCetakTerbaru: CetakTerbaru[] = [
    {
        id: 101,
        namaMhs: 'Akmal Falah Maulana',
        nim: '11231006',
        dokumen: 'Berita Acara Sidang Akhir (TA-09)',
        tanggal: '18 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Siap Cetak',
    },
    {
        id: 102,
        namaMhs: 'Siti Nurhaliza Putri',
        nim: '11211045',
        dokumen: 'Surat Tugas Tim Penguji (ST-01)',
        tanggal: '18 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Sudah Dicetak',
    },
    {
        id: 103,
        namaMhs: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        dokumen: 'Berita Acara Sempro (TA-06)',
        tanggal: '17 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Sudah Dicetak',
    },
];

export const mockMahasiswa: Mahasiswa[] = [
    {
        id: 1,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        prodi: 'S1 Informatika',
        email: 'akmal.falah@student.itk.ac.id',
        tahap: 'Sidang Akhir TA',
        statusAkun: 'Aktif',
    },
    {
        id: 2,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        prodi: 'S1 Informatika',
        email: 'siti.nurhaliza@student.itk.ac.id',
        tahap: 'Sidang Akhir TA',
        statusAkun: 'Aktif',
    },
    {
        id: 3,
        nama: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'bagus.pratama@student.itk.ac.id',
        tahap: 'Seminar Proposal',
        statusAkun: 'Aktif',
    },
    {
        id: 4,
        nama: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'dinda.rahmadani@student.itk.ac.id',
        tahap: 'Pengerjaan TA Bab 4-5',
        statusAkun: 'Aktif',
    },
    {
        id: 5,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'rizky.adhitya@student.itk.ac.id',
        tahap: 'Pengajuan Judul',
        statusAkun: 'Aktif',
    },
];
