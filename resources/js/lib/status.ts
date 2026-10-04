/**
 * Single source of truth for status → (label, badge) across the app.
 *
 * StatusBadge stays a dumb renderer: it accepts a `variant` + `text`, so this
 * module is the only place that decides what a status looks like. Killing the
 * inline `status === 'verified' ? ... : ...` switches in Verifikasi,
 * Persetujuan and Bimbingan.
 */
import type { BadgeVariant } from '@/Components/StatusBadge.vue';
import type { Role, StatusPengajuan } from '@/types/models';

export type StatusConfig = {
    label: string;
    variant: BadgeVariant;
};

export const STATUS_PENGAJUAN: Record<StatusPengajuan, StatusConfig> = {
    diajukan: { label: 'Menunggu Verifikasi', variant: 'amber' },
    revisi: { label: 'Perlu Revisi', variant: 'orange' },
    menunggu_ulang: { label: 'Menunggu Verifikasi Ulang', variant: 'amber' },
    disetujui: { label: 'Disetujui', variant: 'emerald' },
    ditolak: { label: 'Ditolak', variant: 'rose' },
    selesai: { label: 'Selesai', variant: 'emerald' },
};

/* --------------------------------------------------------------- *
 * Permission map — one place where "who may do what" is decided.
 *
 * tendik  is the sole approver.
 * kaprodi cannot approve; it revises, edits, and rejects.
 * --------------------------------------------------------------- */

export type AksiPengajuan = 'approve' | 'revise' | 'reject' | 'edit';

const PERMISI: Record<AksiPengajuan, Role[]> = {
    approve: ['tendik'],
    revise: ['tendik', 'kaprodi'],
    reject: ['tendik', 'kaprodi'],
    edit: ['tendik', 'kaprodi'],
};

/**
 * Can this role perform this action on this submission?
 *
 * Re-check rule: an edit to a verified field (judul, berkas) after approval
 * sends the record back to menunggu_ulang. That is a *transition* the caller
 * applies, not a permission — `can()` only answers who is allowed to try.
 */
export function can(aksi: AksiPengajuan, peran: Role): boolean {
    return PERMISI[aksi].includes(peran);
}

/* --------------------------------------------------------------- *
 * Status transitions
 * --------------------------------------------------------------- */

/**
 * The fields tendik's approval covers. An override touching any of them
 * invalidates a standing approval.
 */
const FIELD_TERVERIFIKASI = ['judul', 'berkas'] as const;

export function isFieldTerverifikasi(field: string): boolean {
    return (FIELD_TERVERIFIKASI as readonly string[]).includes(field);
}

export type HasilEdit = { ulang: true } | { ulang: false };

/**
 * Apply a kaprodi/tendik edit to an approved submission. Returns whether the
 * approval is invalidated — the caller then sets status to menunggu_ulang.
 */
export function hasilEdit(
    fieldsEdited: string[],
    statusSaatIni: StatusPengajuan,
): HasilEdit {
    if (statusSaatIni !== 'disetujui') {
        return { ulang: false };
    }
    const menyentuh = fieldsEdited.some(isFieldTerverifikasi);
    return { ulang: menyentuh };
}

export const STATUS_LABEL_PENGAJUAN = STATUS_PENGAJUAN;

/**
 * Resolve any status value to badge props, tolerating legacy strings during
 * the migration. Unknown values fall back to slate + the raw string, which is
 * exactly what StatusBadge does today — so nothing regresses visually.
 */
export function statusToBadge(status: string): StatusConfig {
    if (status in STATUS_PENGAJUAN) {
        return STATUS_PENGAJUAN[status as StatusPengajuan];
    }
    return { label: status, variant: 'slate' };
}
