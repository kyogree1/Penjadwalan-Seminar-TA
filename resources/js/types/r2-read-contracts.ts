export interface RegistrationRecord {
    id: number;
    judul_ta: string;
    status: string;
    created_at?: string;
    pembimbing1?: { name: string } | null;
    pembimbing2?: { name: string } | null;
    catatan?: string | null;
}
export interface ScheduleRecord {
    id: number;
    mahasiswa_id: number;
    tipe: 'sempro' | 'sidang';
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
    gelombang: number;
    status: string;
    catatan: string | null;
    penilaian_saya?: { nilai: number; catatan: string | null } | null;
    jumlah_penilaian?: number;
    nilai_akhir?: number | null;
    dapat_menilai?: boolean;
    documents?: { label: string; url: string }[];
    mahasiswa?: {
        id: number;
        name: string;
        nim_nip: string | null;
        username?: string | null;
    } | null;
    ruangan?: { nama_ruangan: string; gedung?: string | null } | null;
    pembimbing1?: { name: string } | null;
    pembimbing2?: { name: string } | null;
    penguji1?: { name: string } | null;
    penguji2?: { name: string } | null;
}
