import type { NotificationItem } from '@/types/models';

export const mockNotifications: Record<string, NotificationItem[]> = {
    mahasiswa: [
        {
            id: 1,
            title: 'Verifikasi Berkas Sempro Diterima',
            message:
                'Tendik telah memverifikasi kelengkapan berkas pendaftaran Seminar Proposal TA Anda. Status: Siap Jadwal.',
            time: '15 menit yang lalu',
            read: false,
            type: 'success',
            link: '/pendaftaran/sempro',
        },
        {
            id: 2,
            title: 'Validasi Logbook Bimbingan',
            message:
                'Dr. Ir. Hendra Wijaya, M.Kom. menyetujui catatan bimbingan Bab 4 Anda.',
            time: '2 jam yang lalu',
            read: false,
            type: 'info',
            link: '/pendaftaran/bimbingan',
        },
        {
            id: 3,
            title: 'Jadwal Seminar Proposal Ditetapkan',
            message:
                'Seminar Proposal dijadwalkan pada Senin, 30 September 2026, 10.00 WITA.',
            time: '1 hari yang lalu',
            read: false,
            type: 'calendar',
            link: '/pendaftaran/sempro',
        },
        {
            id: 4,
            title: 'Pengingat Periode Sidang Gelombang 2',
            message:
                'Pendaftaran Sidang Tugas Akhir dibuka hingga akhir bulan Oktober 2026.',
            time: '3 hari yang lalu',
            read: true,
            type: 'warning',
            link: '/pendaftaran/sidang',
        },
    ],
    dosen: [
        {
            id: 101,
            title: 'Pengajuan Bimbingan Baru',
            message:
                'Akmal Falah Maulana mengajukan logbook bimbingan Bab 4 untuk ditinjau.',
            time: '10 menit yang lalu',
            read: false,
            type: 'info',
            link: '/dosen/bimbingan',
        },
        {
            id: 102,
            title: 'Penugasan Penguji Sidang TA',
            message:
                'Anda ditugaskan sebagai Penguji 1 Sidang TA Mahasiswa Bagus Pratama.',
            time: '3 jam yang lalu',
            read: false,
            type: 'calendar',
            link: '/dosen/penilaian',
        },
        {
            id: 103,
            title: 'Input Nilai Belum Lengkap',
            message:
                'Terdapat 2 mahasiswa seminar proposal yang belum diinputkan nilai akhirnya.',
            time: 'Kemarin',
            read: true,
            type: 'warning',
            link: '/dosen/penilaian',
        },
    ],
    kaprodi: [
        {
            id: 201,
            title: 'Validasi Judul Menunggu ACC',
            message:
                'Ada 4 pengajuan judul Tugas Akhir mahasiswa baru yang memerlukan persetujuan Koordinator.',
            time: '30 menit yang lalu',
            read: false,
            type: 'info',
            link: '/kaprodi/persetujuan',
        },
        {
            id: 202,
            title: 'Optimasi Penjadwalan Selesai',
            message:
                'Algoritma Genetika menyelesaikan penjadwalan 18 sesi seminar tanpa konflik (Fitness 98.4%).',
            time: '2 jam yang lalu',
            read: false,
            type: 'success',
            link: '/kaprodi/penjadwalan',
        },
        {
            id: 203,
            title: 'Penerbitan SK Tim Penguji',
            message:
                'Draft SK Penguji periode Gasal 2026/2027 telah siap diterbitkan dan ditandatangani.',
            time: '1 hari yang lalu',
            read: true,
            type: 'calendar',
            link: '/kaprodi/persetujuan',
        },
    ],
    tendik: [
        {
            id: 301,
            title: 'Berkas Sempro Baru Masuk',
            message:
                '3 berkas pendaftaran Seminar Proposal mahasiswa baru membutuhkan verifikasi keabsahan.',
            time: '5 menit yang lalu',
            read: false,
            type: 'warning',
            link: '/tendik/verifikasi',
        },
        {
            id: 302,
            title: 'Konfirmasi Ruangan Sidang',
            message:
                'Ruangan Lab Riset A telah dikonfirmasi untuk sesi seminar besok.',
            time: '1 jam yang lalu',
            read: false,
            type: 'success',
            link: '/tendik/ruangan',
        },
        {
            id: 303,
            title: 'Berita Acara Siap Cetak',
            message:
                'Berita Acara Sidang TA tanggal 18 September telah selesai diarsipkan.',
            time: 'Kemarin',
            read: true,
            type: 'info',
            link: '/tendik/arsip',
        },
    ],
};
