/** Persisted student directory fields for Data Master Mahasiswa */
export type StudentDirectoryEntry = {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    jurusan: string;
    prodi: string;
    email: string;
    noTelpon: string;
    statusTa: string;
};

export type StudentDirectoryProps = {
    mahasiswaList: StudentDirectoryEntry[];
    flash: { success?: string | null; error?: string | null };
};
