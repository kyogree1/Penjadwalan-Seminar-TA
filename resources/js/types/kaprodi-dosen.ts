/** Persisted lecturer directory fields; no workload or external affiliation metadata. */
export type LecturerDirectoryEntry = {
    id: number;
    name: string;
    email: string;
    nip: string | null;
    prodi: string;
    jabatan: string | null;
};

export type LecturerDirectoryProps = {
    dosenList: LecturerDirectoryEntry[];
    flash: { success?: string | null; error?: string | null };
};
