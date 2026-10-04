/**
 * Shared domain models for the thesis (Tugas Akhir) lifecycle.
 *
 * Three roles touch a submission:
 * - mahasiswa  submits it
 * - tendik     the ONLY role that can approve it (administrative check)
 * - kaprodi    cannot approve; can revise, edit, or reject
 *
 * koordinator is not a separate role — see types/auth.ts and routes/web.php,
 * where it is folded into the kaprodi permission group.
 *
 * Verified fields (judul, berkas) are the ones tendik's approval covers. An
 * override that touches one of them invalidates the approval and sends the
 * record back to menunggu_ulang; editing anything else leaves it standing.
 */

export type Role = 'mahasiswa' | 'dosen' | 'kaprodi' | 'tendik';

/**
 * The canonical status of one submission through the pipeline.
 *
 * Everything that used to be spelled "Menunggu Verifikasi" / "Menunggu
 * Validasi" / "pending" collapses into `diajukan` — there is only one pending
 * state, and it sits in front of tendik.
 */
export type StatusPengajuan =
    | 'diajukan'
    | 'revisi'
    | 'menunggu_ulang'
    | 'disetujui'
    | 'ditolak'
    | 'selesai';

export type TipePengajuan = 'Seminar Proposal' | 'Sidang Akhir TA';

/**
 * One administrative requirement attached to a submission.
 * `valid` is tendik's per-item verdict; the record is approvable once every
 * item is valid.
 */
export type SyaratItem = {
    label: string;
    file: string;
    valid: boolean;
};

/**
 * Audit trail entry. Every override appends one, so "only tendik can approve"
 * stays provable after the fact. Rendered by RiwayatTimelineCard.
 */
export type RiwayatEntry = {
    aktor: string;
    peran: Role;
    aksi:
        | 'mengajukan'
        | 'memverifikasi'
        | 'menyetujui'
        | 'meminta_revisi'
        | 'menolak'
        | 'mengubah'
        | 'mengembalikan';
    waktu: string;
    catatan?: string;
};

export type Pengajuan = {
    id: number;
    nama: string;
    nim: string;
    angkatan?: string;
    prodi?: string;
    email?: string;
    tipe: TipePengajuan;
    judul: string;
    pembimbing1?: string;
    pembimbing2?: string;
    tanggalDaftar: string;
    status: StatusPengajuan;
    /** Which fields tendik's approval covers. */
    fieldTerverifikasi?: ('judul' | 'berkas')[];
    syarat: SyaratItem[];
    catatanTendik?: string;
    catatanKaprodi?: string;
    riwayat: RiwayatEntry[];
};

/* --------------------------------------------------------------- *
 * Tendik portal: rooms, archive, student accounts
 * --------------------------------------------------------------- */

export type StatusRuangan =
    | 'Tersedia'
    | 'Digunakan'
    | 'Terjadwal'
    | 'Penuh (Maksimal)';

export type SesiRuangan = {
    waktu: string;
    kegiatan: string;
    status: 'Berlangsung' | 'Terjadwal' | 'Selesai';
};

export type Ruangan = {
    id: number;
    nama: string;
    gedung: string;
    kapasitas: string;
    fasilitas: string[];
    status: StatusRuangan;
    sesiHariIni: SesiRuangan[];
};

export type ArsipDokumen = {
    id: number;
    kode: string;
    nama: string;
    kategori: string;
    deskripsi: string;
    tersedia: number;
    status: 'Tersedia' | 'Siap Cetak' | 'Sudah Dicetak';
};

export type CetakTerbaru = {
    id: number;
    namaMhs: string;
    nim: string;
    dokumen: string;
    tanggal: string;
    petugas: string;
    status: 'Siap Cetak' | 'Sudah Dicetak';
};

export type StatusAkun = 'Aktif' | 'Nonaktif' | 'Terkunci';

export type Mahasiswa = {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    prodi: string;
    email: string;
    tahap: string;
    statusAkun: StatusAkun;
};

/* --------------------------------------------------------------- *
 * Koordinator portal: AI Genetic Algorithm Scheduling & Plotting
 * --------------------------------------------------------------- */

export type JadwalSeminarGa = {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    judul: string;
    kbk: string;
    pembimbing1: string;
    pembimbing2: string;
    penguji1: string;
    penguji1MatchScore: number;
    penguji2: string;
    hari: string;
    tanggal: string;
    mulai: string;
    selesai: string;
    nilai: string | null;
};

export type GaOptimizationParams = {
    popSize: number;
    maxGenerations: number;
    crossoverRate: string;
    mutationRate: string;
};

export type ExamType = 'Sempro' | 'Sidang';
export type Semester = 'Gasal' | 'Genap';

export type Period = {
    id: number;
    type: ExamType;
    academicYear: string;
    semester: Semester;
    wave: number;
    quota: number;
    startDate: string;
    endDate: string;
    isOpen: boolean;
};

/* --------------------------------------------------------------- *
 * Kaprodi portal: Master Data Dosen & Workload Quota Management
 * --------------------------------------------------------------- */

export type MasterDosen = {
    id: number;
    nama: string;
    nip: string;
    prodi: string;
    role: string;
    email: string;
    jabatanAkademik: string;
    bidangKeahlian: string;
    tipe: 'Internal ITK' | 'Dosen Eksternal';
    asalInstansi?: string;
    currentQuota: number;
    maxQuota: number;
    status: 'Aktif' | 'Cuti' | 'Nonaktif';
};
