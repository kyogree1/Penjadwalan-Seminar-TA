export interface TendikRoom {
    id: number;
    nama_ruangan: string;
    gedung: string | null;
    lantai: string | null;
    kapasitas: number;
    status: 'tersedia' | 'tidak_tersedia';
    keterangan: string | null;
}
export interface TendikRoomProps {
    dbRuanganList: TendikRoom[];
}
export interface TendikDashboardProps {
    stats: {
        antreanVerifikasi: number;
        antreanSempro: number;
        antreanSidang: number;
        totalRuangan: number;
        ruanganTersedia: number;
        totalMahasiswa: number;
    };
}
export interface TendikArchiveProps {
    archiveCapabilities: {
        persistence: false;
        printing: false;
    };
}
