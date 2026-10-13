export interface TendikDashboardProps {
    stats: {
        antreanVerifikasi: number;
        antreanSempro: number;
        antreanSidang: number;
        totalMahasiswa: number;
        totalTerjadwal: number;
    };
}
export interface TendikArchiveProps {
    archiveCapabilities: {
        persistence: boolean;
        printing: boolean;
    };
    summary: {
        totalSelesaiSempro: number;
        totalSelesaiSidang: number;
        totalLulus: number;
        periodeTerakhir: string | null;
    };
    arsipList: ArsipItem[];
}

export interface ArsipItem {
    id: number;
    tipe: 'Seminar Proposal' | 'Sidang Tugas Akhir';
    mahasiswa: string | null;
    nim: string | null;
    prodi: string | null;
    judul: string;
    tanggal_selesai: string | null;
}
