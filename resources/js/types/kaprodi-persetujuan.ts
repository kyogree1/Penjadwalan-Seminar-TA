import type {
    TitleReviewStatus,
    AcademicReviewStatus,
    RegistrationLifecycleStatus,
} from './models';

export type ReviewStudent = {
    id: number;
    name: string;
    nim_nip: string | null;
};
export type SupervisorOption = { id: number; name: string };
export type JudulReview = {
    id: number;
    mahasiswa: ReviewStudent | null;
    judul_ta: string;
    bidang_penelitian: string;
    status: TitleReviewStatus;
    pembimbing_1_id: number | null;
    pembimbing_2_id: number | null;
    pembimbing1: SupervisorOption | null;
    pembimbing2: SupervisorOption | null;
    catatan_kaprodi: string | null;
};
export type AcademicReview = {
    id: number;
    mahasiswa: ReviewStudent | null;
    judul_ta: string;
    status: RegistrationLifecycleStatus;
    catatan: string | null;
    status_akademik: AcademicReviewStatus;
    catatan_kaprodi: string | null;
    proposal_file?: string | null;
    lembar_kehadiran_file?: string | null;
    turnitin_file?: string | null;
    iaet_file?: string | null;
    skor_iaet_file?: string | null;
    draft_laporan_file?: string | null;
};
export type KaprodiReviewProps = {
    dbJudul: JudulReview[];
    dbSempro: AcademicReview[];
    dbSidang: AcademicReview[];
    dosenOptions: SupervisorOption[];
    flash?: { success?: string | null; error?: string | null };
};
