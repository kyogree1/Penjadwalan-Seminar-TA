export interface TendikStudent {
    id: number;
    name: string;
    email: string;
    username: string | null;
    nim_nip: string | null;
    prodi: string;
    created_at: string | null;
}

export interface TendikStudentProps {
    dbStudents: TendikStudent[];
    flash?: { success?: string | null; error?: string | null };
}
