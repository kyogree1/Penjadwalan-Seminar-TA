export type VerificationStatus =
    | 'menunggu'
    | 'verifikasi_tendik'
    | 'terjadwal'
    | 'selesai'
    | 'ditolak';

export interface VerificationApplicant {
    id: number;
    tipe: 'sempro' | 'sidang';
    nama: string | null;
    nim: string | null;
    judul: string;
    tanggalDaftar: string | null;
    status: VerificationStatus;
    catatan: string | null;
    dokumen: { label: string; path: string | null }[];
}

export interface TendikVerificationProps {
    pengajuan: VerificationApplicant[];
    flash?: { success?: string | null; error?: string | null };
}
