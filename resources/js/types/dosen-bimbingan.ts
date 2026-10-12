export interface LiveBimbinganSession {
    id: number;
    no: number;
    tanggal: string;
    rangkuman: string;
    keterangan: string;
    status: 'process' | 'delivered' | 'ditolak';
    catatanDosen: string | null;
}
export interface LiveAdvisee {
    id: number;
    nama: string;
    nim: string | null;
    judul: string;
    roleAs: 'Pembimbing 1' | 'Pembimbing 2';
    pembimbing1: string | null;
    pembimbing2: string | null;
    sesiList: LiveBimbinganSession[];
}
export interface LogbookProps {
    advisees: LiveAdvisee[];
    flash: { success?: string | null; error?: string | null };
}
