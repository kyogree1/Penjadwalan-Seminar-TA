export type LecturerDirectoryEntry = {
    id: number;
    name: string;
    email: string;
    nip: string | null;
    prodi: string;
    jabatan: string | null;
    role?: string;
    bidangKeahlian?: string;
    currentQuota?: number;
    maxQuota?: number;
};

export type LecturerDirectoryProps = {
    dosenList: LecturerDirectoryEntry[];
    flash: { success?: string | null; error?: string | null };
};
