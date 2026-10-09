/** Dashboard aggregates use persisted records, not lifecycle/semester estimates. */
export type KaprodiDashboardProps = {
    stats: {
        totalDosen: number;
        totalMahasiswa: number;
        antreanPersetujuan: number;
    };
    pendingCounts: { judul: number; sempro: number; sidang: number };
    lecturers: {
        id: number;
        name: string;
        nip: string | null;
        bimbingan1: number;
        bimbingan2: number;
        /** Distinct students across both approved supervisor positions. */
        total: number;
    }[];
    pendingApprovals: {
        id: number;
        nama: string;
        nim: string | null;
        judul: string;
        tanggal: string;
        pembimbing1: string | null;
        pembimbing2: string | null;
    }[];
    /** No persisted academic period/deadline source currently exists. */
    deadlines: null;
};
