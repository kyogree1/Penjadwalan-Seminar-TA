<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar as CalendarIcon,
    Check,
    ChevronLeft,
    ChevronRight,
    Clock,
    Download,
    Eye,
    FileCheck,
    FileSpreadsheet,
    FileText,
    GraduationCap,
    Info,
    MapPin,
    Sparkles,
    User,
    X,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);
const studentName = computed(
    () => authUser.value?.name || 'Akmal Falah Maulana',
);
const studentNim = computed(() => authUser.value?.nim_nip || 'NIM: 11231006');
const studentProdi = computed(() => authUser.value?.prodi || 'S1 Informatika');

// ==========================================
// 1. STATUS PERIODE (Dinamis per event & semester)
// ==========================================
interface ActivePeriod {
    id: string;
    type: 'sempro' | 'sidang';
    title: string;
    semester: 'Gasal' | 'Genap';
    academicYear: string;
    gelombang: string;
    startDate: string;
    endDate: string;
    colorTheme: {
        bg: string;
        badgeBg: string;
        text: string;
        accentText: string;
        border: string;
    };
}

const activePeriods = ref<ActivePeriod[]>([
    {
        id: 'p-sempro',
        type: 'sempro',
        title: 'SEMINAR PROPOSAL',
        semester: 'Gasal',
        academicYear: '2026/2027',
        gelombang: '2 (Dua)',
        startDate: '01 Okt 2026',
        endDate: '15 Okt 2026',
        colorTheme: {
            bg: 'bg-lime-100 dark:bg-lime-950/40',
            badgeBg: 'bg-lime-200 dark:bg-lime-900/60',
            text: 'text-slate-900 dark:text-lime-200',
            accentText: 'text-lime-800 dark:text-lime-300',
            border: 'border-lime-300/80 dark:border-lime-700/50',
        },
    },
    {
        id: 'p-sidang',
        type: 'sidang',
        title: 'SIDANG TUGAS AKHIR',
        semester: 'Gasal',
        academicYear: '2026/2027',
        gelombang: '1 (Satu)',
        startDate: '08 Okt 2026',
        endDate: '24 Okt 2026',
        colorTheme: {
            bg: 'bg-indigo-100 dark:bg-indigo-950/40',
            badgeBg: 'bg-indigo-200 dark:bg-indigo-900/60',
            text: 'text-slate-900 dark:text-indigo-200',
            accentText: 'text-indigo-800 dark:text-indigo-300',
            border: 'border-indigo-300/80 dark:border-indigo-700/50',
        },
    },
]);

// ==========================================
// 2. ROADMAP & STATUS TUGAS AKHIR (Status Dinamis)
// Selesai = Blue, Berlangsung = Amber (Kuning), Belum = Slate
// ==========================================
type StepStatus = 'completed' | 'in_progress' | 'pending';

interface RoadmapStep {
    name: string;
    status: StepStatus;
    date?: string;
}

const semproSteps = ref<RoadmapStep[]>([
    { name: 'Pengajuan', status: 'completed', date: '10 Sep 2026' },
    { name: 'Verifikasi', status: 'completed', date: '14 Sep 2026' },
    { name: 'Penilaian', status: 'in_progress', date: 'Sedang Berjalan' },
    { name: 'Hasil Seminar', status: 'pending' },
]);

const sidangSteps = ref<RoadmapStep[]>([
    { name: 'Pengajuan', status: 'completed', date: '20 Sep 2026' },
    { name: 'Verifikasi', status: 'in_progress', date: 'Dalam Review' },
    { name: 'Penilaian', status: 'pending' },
    { name: 'Hasil Sidang', status: 'pending' },
]);

const generalThesisStatuses = ref<
    { title: string; desc: string; status: StepStatus }[]
>([
    { title: 'Pengajuan', desc: 'Selesai', status: 'completed' },
    { title: 'Verifikasi', desc: 'Selesai', status: 'completed' },
    { title: 'Approval', desc: 'Dalam Proses', status: 'in_progress' },
    { title: 'Disetujui', desc: 'Menunggu Hasil', status: 'pending' },
]);

const getStepNodeClasses = (status: StepStatus) => {
    switch (status) {
        case 'completed':
            return 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950';
        case 'in_progress':
            return 'bg-amber-400 text-slate-950 ring-4 ring-amber-200 dark:ring-amber-900/60 animate-pulse';
        case 'pending':
        default:
            return 'bg-slate-300 text-slate-600 ring-4 ring-white dark:bg-slate-700 dark:text-slate-400 dark:ring-slate-900';
    }
};

const getStepProgressWidth = (steps: RoadmapStep[]) => {
    const lastActiveIdx = steps.reduce(
        (acc, cur, idx) => (cur.status !== 'pending' ? idx : acc),
        0,
    );
    const totalSegments = steps.length - 1;
    return `${(lastActiveIdx / totalSegments) * 100}%`;
};

// ==========================================
// 3. STATUS SAAT INI (Kartu-Kartu Khusus & Countdown)
// ==========================================
interface CurrentStatusCard {
    id: string;
    title: string;
    stage: string;
    description: string;
    type:
        | 'progress-sempro'
        | 'progress-sidang'
        | 'alert-sempro'
        | 'alert-sidang';
    daysLeft?: number;
    linkUrl: string;
    badgeText: string;
}

const currentStatusCards = ref<CurrentStatusCard[]>([
    {
        id: 'cs-1',
        title: 'Progress Proposal TA',
        stage: 'Tahap Penilaian & Revisi',
        description:
            'Telah melaksanakan seminar, menunggu input revisi tim dosen.',
        type: 'progress-sempro',
        linkUrl: '/pendaftaran/sempro',
        badgeText: 'Bab 1 - 3 Disetujui',
    },
    {
        id: 'cs-2',
        title: 'Progress Sidang TA',
        stage: 'Verifikasi Berkas Akhir',
        description:
            'Berkas dan naskah lengkap sedang dalam antrean verifikasi Tendik.',
        type: 'progress-sidang',
        linkUrl: '/pendaftaran/sidang',
        badgeText: 'Menunggu ACC',
    },
    {
        id: 'cs-3',
        title: 'Mendekati Seminar Proposal',
        stage: 'Jadwal: 02 Oktober 2026',
        description:
            'Waktu tersisa 2 hari lagi. Siapkan slide presentasi dan berkas cetak.',
        type: 'alert-sempro',
        daysLeft: 2,
        linkUrl: '/pendaftaran/sempro',
        badgeText: 'H-2 Seminar',
    },
    {
        id: 'cs-4',
        title: 'Mendekati Sidang TA',
        stage: 'Estimasi: 09 Oktober 2026',
        description:
            'Batas pendaftaran berkas dan penutupan slot tinggal 6 hari lagi.',
        type: 'alert-sidang',
        daysLeft: 6,
        linkUrl: '/pendaftaran/sidang',
        badgeText: 'H-6 Sidang',
    },
]);

// ==========================================
// 4. KALENDER PERSONAL MAHASISWA & AGENDA MODAL
// ==========================================
interface StudentCalendarAgenda {
    id: string;
    date: number;
    fullDateString: string;
    title: string;
    type: 'sempro' | 'sidang' | 'bimbingan' | 'pendaftaran';
    time: string;
    location?: string;
    pembimbingPenguji?: string[];
    notes?: string;
    badgeColor: string;
}

const currentMonth = ref('Oktober 2026');

const studentAgendas = ref<StudentCalendarAgenda[]>([
    {
        id: 'ag-1',
        date: 2,
        fullDateString: 'Jumat, 02 Oktober 2026',
        title: 'Seminar Proposal TA (Saya)',
        type: 'sempro',
        time: '13.30 - 15.00 WITA',
        location: 'Ruang Lab JSTI 2 / Gedung A',
        pembimbingPenguji: [
            'Pembimbing 1: Dr. Ir. Hendra Wijaya, M.Kom.',
            'Penguji 1: Prof. Dr. Agus Susanto, M.T.',
            'Penguji 2: Siti Nurhaliza, S.Kom., M.Cs.',
        ],
        notes: 'Wajib membawa jas almamater, lembar berita acara, dan presentasi 15 menit.',
        badgeColor: 'bg-lime-500',
    },
    {
        id: 'ag-2',
        date: 7,
        fullDateString: 'Rabu, 07 Oktober 2026',
        title: 'Bimbingan Pasca Sempro',
        type: 'bimbingan',
        time: '10.00 - 11.30 WITA',
        location: 'Ruang Dosen Gedung B / Lab Riset',
        pembimbingPenguji: ['Dr. Ir. Hendra Wijaya, M.Kom.'],
        notes: 'Membahas catatan revisi dewan penguji terkait perbaikan dataset pengujian.',
        badgeColor: 'bg-blue-500',
    },
    {
        id: 'ag-3',
        date: 9,
        fullDateString: 'Jumat, 09 Oktober 2026',
        title: 'Batas Pendaftaran Sidang Gelombang 1',
        type: 'pendaftaran',
        time: 'Tutup 23.59 WITA',
        location: 'Portal Daring SIPTA IF',
        notes: 'Pastikan minimal 8 kali bimbingan dan skor Turnitin di bawah 20%.',
        badgeColor: 'bg-amber-500',
    },
    {
        id: 'ag-4',
        date: 20,
        fullDateString: 'Selasa, 20 Oktober 2026',
        title: 'Sidang Tugas Akhir (Terjadwal)',
        type: 'sidang',
        time: '09.00 - 11.00 WITA',
        location: 'Ruang Sidang Utama Informatika Lt. 3',
        pembimbingPenguji: [
            'Ketua Penguji: Ir. Budi Santoso, M.Eng.',
            'Penguji 2: Siti Nurhaliza, S.Kom., M.Cs.',
            'Pembimbing 1: Dr. Ir. Hendra Wijaya, M.Kom.',
        ],
        notes: 'Ujian komprehensif tertutup. Menyiapkan prototipe software/alat.',
        badgeColor: 'bg-indigo-500',
    },
]);

const calendarGrid = [
    { day: 28, currentMonth: false },
    { day: 29, currentMonth: false },
    { day: 30, currentMonth: false },
    { day: 1, currentMonth: true },
    { day: 2, currentMonth: true },
    { day: 3, currentMonth: true },
    { day: 4, currentMonth: true },
    { day: 5, currentMonth: true },
    { day: 6, currentMonth: true },
    { day: 7, currentMonth: true },
    { day: 8, currentMonth: true },
    { day: 9, currentMonth: true },
    { day: 10, currentMonth: true },
    { day: 11, currentMonth: true },
    { day: 12, currentMonth: true },
    { day: 13, currentMonth: true },
    { day: 14, currentMonth: true, isToday: true },
    { day: 15, currentMonth: true },
    { day: 16, currentMonth: true },
    { day: 17, currentMonth: true },
    { day: 18, currentMonth: true },
    { day: 19, currentMonth: true },
    { day: 20, currentMonth: true },
    { day: 21, currentMonth: true },
    { day: 22, currentMonth: true },
    { day: 23, currentMonth: true },
    { day: 24, currentMonth: true },
    { day: 25, currentMonth: true },
    { day: 26, currentMonth: true },
    { day: 27, currentMonth: true },
    { day: 28, currentMonth: true },
    { day: 29, currentMonth: true },
    { day: 30, currentMonth: true },
    { day: 31, currentMonth: true },
    { day: 1, currentMonth: false },
];

const selectedAgenda = ref<StudentCalendarAgenda | null>(null);
const showAgendaModal = ref(false);

const getAgendasForDay = (day: number, currentMonth: boolean) => {
    if (!currentMonth) return [];
    return studentAgendas.value.filter((ag) => ag.date === day);
};

const handleDayClick = (day: number, currentMonth: boolean) => {
    if (!currentMonth) return;
    const items = getAgendasForDay(day, currentMonth);
    if (items.length > 0) {
        selectedAgenda.value = items[0];
        showAgendaModal.value = true;
    }
};

const openAgendaDetail = (agenda: StudentCalendarAgenda) => {
    selectedAgenda.value = agenda;
    showAgendaModal.value = true;
};

// ==========================================
// 5. DETAIL HASIL PENILAIAN DENGAN POPUP MODAL
// ==========================================
interface AssessmentDetail {
    id: string;
    title: string;
    period: string;
    pembimbing: string;
    penguji: string[];
    status: 'Lulus dengan Revisi' | 'Lulus Murni' | 'Tidak Lulus';
    variant: 'neutral' | 'success' | 'danger';
    finalScore: number;
    grade: string;
    revisionDeadline: string;
    examinerNotes: { by: string; note: string }[];
}

const assessments = ref<AssessmentDetail[]>([
    {
        id: 'ass-1',
        title: 'Sidang Tugas Akhir',
        period: 'Gasal 2026/2027 • Gelombang 1',
        pembimbing: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        penguji: [
            'Ir. Budi Santoso, M.Eng. (Ketua)',
            'Siti Nurhaliza, S.Kom., M.Cs. (Anggota)',
        ],
        status: 'Lulus dengan Revisi',
        variant: 'neutral',
        finalScore: 84.5,
        grade: 'A-',
        revisionDeadline: '14 Hari (s.d. 16 Oktober 2026)',
        examinerNotes: [
            {
                by: 'Ketua Penguji - Ir. Budi Santoso, M.Eng.',
                note: 'Perbaiki penjelasan arsitektur Genetic Algorithm pada bab 3 dan tambahkan perbandingan fitness value awal vs akhir.',
            },
            {
                by: 'Penguji 2 - Siti Nurhaliza, S.Kom., M.Cs.',
                note: 'Format sitasi IEEE harus dicek kembali pada daftar pustaka. Lampirkan hasil uji usability pengujian user.',
            },
            {
                by: 'Pembimbing - Dr. Ir. Hendra Wijaya, M.Kom.',
                note: 'Sudah cukup baik secara konsep, selesaikan revisi sebelum deadline agar lembar pengesahan bisa ditandatangani.',
            },
        ],
    },
    {
        id: 'ass-2',
        title: 'Seminar Proposal Tugas Akhir',
        period: 'Gasal 2026/2027 • Gelombang 2',
        pembimbing: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        penguji: [
            'Prof. Dr. Agus Susanto, M.T.',
            'Siti Nurhaliza, S.Kom., M.Cs.',
        ],
        status: 'Lulus dengan Revisi',
        variant: 'neutral',
        finalScore: 81.0,
        grade: 'A-',
        revisionDeadline: '7 Hari (s.d. 09 Oktober 2026)',
        examinerNotes: [
            {
                by: 'Prof. Dr. Agus Susanto, M.T.',
                note: 'Fokuskan batasan masalah hanya pada penjadwalan seminar informatika ITK.',
            },
            {
                by: 'Siti Nurhaliza, S.Kom., M.Cs.',
                note: 'Perjelas parameter mutasi dan crossover yang digunakan pada rancangan algoritma.',
            },
        ],
    },
    {
        id: 'ass-3',
        title: 'Seminar Proposal Tugas Akhir (Gelombang 1)',
        period: 'Genap 2025/2026 • Gelombang 1',
        pembimbing: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        penguji: ['Prof. Dr. Agus Susanto, M.T.', 'Ir. Budi Santoso, M.Eng.'],
        status: 'Tidak Lulus',
        variant: 'danger',
        finalScore: 54.0,
        grade: 'D',
        revisionDeadline: 'Silakan daftar ulang pada Gelombang berikutnya',
        examinerNotes: [
            {
                by: 'Dewan Penguji',
                note: 'Metodologi belum matang dan rumusan masalah belum menunjukkan urgensi penelitian yang kuat.',
            },
        ],
    },
]);

const selectedAssessment = ref<AssessmentDetail | null>(null);
const showAssessmentModal = ref(false);

const openAssessmentModal = (item: AssessmentDetail) => {
    selectedAssessment.value = item;
    showAssessmentModal.value = true;
};
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Dashboard Mahasiswa - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- 1. GREETING BANNER & DUA STATUS PERIODE (SEMPRO & SIDANG BERBEDA WARNA) -->
            <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
                <!-- Left: Greeting Card (2/3 lebar di layar desktop) -->
                <div
                    class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-2 dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div>
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex h-2.5 w-2.5 animate-pulse rounded-full bg-emerald-500"
                            />
                            <span
                                class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase dark:text-emerald-400"
                                >Akun Aktif</span
                            >
                        </div>
                        <h2
                            class="mt-2 text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
                        >
                            Selamat Datang, {{ studentName }}
                        </h2>
                        <p
                            class="mt-2 text-xs leading-relaxed text-slate-500 sm:text-sm dark:text-slate-400"
                        >
                            {{ studentNim }} • {{ studentProdi }}
                        </p>
                    </div>

                    <div
                        class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800/80 dark:text-slate-400"
                    >
                        Pantau progres skripsi, jadwal seminar, serta notifikasi
                        hasil penilaian Anda di bawah ini.
                    </div>
                </div>

                <!-- Right: Status periode compact, ditumpuk seperti notifikasi (1/3 lebar di layar desktop) -->
                <div class="space-y-3 lg:col-span-1">
                    <h3
                        class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Status Periode
                    </h3>
                    <Link
                        v-for="period in activePeriods"
                        :key="period.id"
                        :href="
                            period.type === 'sempro'
                                ? '/pendaftaran/sempro'
                                : '/pendaftaran/sidang'
                        "
                        class="flex items-center gap-3 rounded-xl border p-3 shadow-xs transition-colors hover:brightness-95 dark:hover:brightness-110"
                        :class="[
                            period.colorTheme.bg,
                            period.colorTheme.border,
                        ]"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                            :class="period.colorTheme.badgeBg"
                        >
                            <FileSpreadsheet
                                v-if="period.type === 'sempro'"
                                class="h-5 w-5"
                                :class="period.colorTheme.accentText"
                            />
                            <GraduationCap
                                v-else
                                class="h-5 w-5"
                                :class="period.colorTheme.accentText"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p
                                    class="truncate text-xs font-extrabold"
                                    :class="period.colorTheme.text"
                                >
                                    {{ period.title }}
                                </p>
                                <span
                                    class="shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-bold"
                                    :class="[
                                        period.colorTheme.badgeBg,
                                        period.colorTheme.accentText,
                                    ]"
                                >
                                    Gel. {{ period.gelombang }}
                                </span>
                            </div>
                            <p
                                class="mt-0.5 truncate text-[10px]"
                                :class="period.colorTheme.text"
                            >
                                {{ period.semester }}
                                {{ period.academicYear }} •
                                {{ period.startDate }}–{{ period.endDate }}
                            </p>
                        </div>

                        <span
                            class="shrink-0 text-[10px] font-bold"
                            :class="period.colorTheme.accentText"
                        >
                            Dibuka ›
                        </span>
                    </Link>
                </div>
            </div>

            <!-- 2. ROADMAP WIDGET DENGAN INDIKATOR WARNA KUNING UNTUK PROGRES AKTIF -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Roadmap Tugas Akhir
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Titik kuning menandakan tahapan yang sedang aktif
                            dan berjalan.
                        </p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold">
                        <span
                            class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300"
                        >
                            <span class="h-3 w-3 rounded-full bg-blue-600" />
                            Selesai
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300"
                        >
                            <span
                                class="h-3 w-3 rounded-full bg-amber-400 ring-2 ring-amber-200"
                            />
                            Sedang Berjalan
                        </span>
                        <span
                            class="flex items-center gap-1.5 text-slate-600 dark:text-slate-300"
                        >
                            <span
                                class="h-3 w-3 rounded-full bg-slate-300 dark:bg-slate-700"
                            />
                            Belum Tercapai
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                    <!-- Roadmap 1: Seminar Proposal -->
                    <div
                        class="rounded-2xl border border-slate-200/70 bg-slate-50/70 p-5 dark:border-slate-800/80 dark:bg-slate-900/60"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                Seminar Proposal
                            </span>
                            <Link
                                href="/pendaftaran/sempro"
                                class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400"
                            >
                                Lihat Detail &gt;
                            </Link>
                        </div>

                        <!-- Stepper Line -->
                        <div class="relative mt-7 mb-3 px-3">
                            <!-- Background Bar -->
                            <div
                                class="absolute top-2 left-6 h-0.5 w-[calc(100%-3rem)] bg-slate-200 dark:bg-slate-700"
                            />
                            <!-- Active Filled Bar -->
                            <div
                                class="absolute top-2 left-6 h-0.5 bg-blue-600 transition-all duration-500"
                                :style="{
                                    width: `calc(${getStepProgressWidth(semproSteps)} - 1.5rem)`,
                                }"
                            />

                            <div
                                class="relative flex justify-between text-center"
                            >
                                <div
                                    v-for="(step, idx) in semproSteps"
                                    :key="idx"
                                    class="flex flex-col items-center"
                                >
                                    <div
                                        class="flex h-4 w-4 items-center justify-center rounded-full transition-all"
                                        :class="getStepNodeClasses(step.status)"
                                    >
                                        <Check
                                            v-if="step.status === 'completed'"
                                            class="h-2.5 w-2.5"
                                        />
                                    </div>
                                    <span
                                        class="mt-2 text-[11px] font-semibold"
                                        :class="[
                                            step.status === 'in_progress'
                                                ? 'font-bold text-amber-600 dark:text-amber-400'
                                                : step.status === 'completed'
                                                  ? 'text-slate-800 dark:text-slate-200'
                                                  : 'text-slate-400 dark:text-slate-500',
                                        ]"
                                    >
                                        {{ step.name }}
                                    </span>
                                    <span
                                        v-if="step.date"
                                        class="text-[9px] text-slate-400"
                                    >
                                        {{ step.date }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roadmap 2: Sidang Tugas Akhir -->
                    <div
                        class="rounded-2xl border border-slate-200/70 bg-slate-50/70 p-5 dark:border-slate-800/80 dark:bg-slate-900/60"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-sm font-bold text-slate-800 dark:text-slate-200"
                            >
                                Sidang Tugas Akhir
                            </span>
                            <Link
                                href="/pendaftaran/sidang"
                                class="text-xs font-semibold text-blue-600 hover:underline dark:text-blue-400"
                            >
                                Lihat Detail &gt;
                            </Link>
                        </div>

                        <!-- Stepper Line -->
                        <div class="relative mt-7 mb-3 px-3">
                            <div
                                class="absolute top-2 left-6 h-0.5 w-[calc(100%-3rem)] bg-slate-200 dark:bg-slate-700"
                            />
                            <div
                                class="absolute top-2 left-6 h-0.5 bg-blue-600 transition-all duration-500"
                                :style="{
                                    width: `calc(${getStepProgressWidth(sidangSteps)} - 1.5rem)`,
                                }"
                            />

                            <div
                                class="relative flex justify-between text-center"
                            >
                                <div
                                    v-for="(step, idx) in sidangSteps"
                                    :key="idx"
                                    class="flex flex-col items-center"
                                >
                                    <div
                                        class="flex h-4 w-4 items-center justify-center rounded-full transition-all"
                                        :class="getStepNodeClasses(step.status)"
                                    >
                                        <Check
                                            v-if="step.status === 'completed'"
                                            class="h-2.5 w-2.5"
                                        />
                                    </div>
                                    <span
                                        class="mt-2 text-[11px] font-semibold"
                                        :class="[
                                            step.status === 'in_progress'
                                                ? 'font-bold text-amber-600 dark:text-amber-400'
                                                : step.status === 'completed'
                                                  ? 'text-slate-800 dark:text-slate-200'
                                                  : 'text-slate-400 dark:text-slate-500',
                                        ]"
                                    >
                                        {{ step.name }}
                                    </span>
                                    <span
                                        v-if="step.date"
                                        class="text-[9px] text-slate-400"
                                    >
                                        {{ step.date }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. KARTU STATUS SAAT INI (COMPACT SEPERTI NOTIFIKASI HP) -->
            <div class="space-y-2.5">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Status Saat Ini
                    </h3>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Aktivitas & tenggat waktu
                    </span>
                </div>

                <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    <Link
                        v-for="card in currentStatusCards"
                        :key="card.id"
                        :href="card.linkUrl"
                        class="flex items-center gap-3 rounded-xl border p-3 shadow-2xs transition-colors hover:border-blue-400 dark:hover:border-blue-500"
                        :class="[
                            card.type === 'alert-sempro'
                                ? 'border-lime-300/80 bg-lime-50/50 dark:border-lime-900/50 dark:bg-lime-950/20'
                                : card.type === 'alert-sidang'
                                  ? 'border-amber-300/80 bg-amber-50/50 dark:border-amber-900/50 dark:bg-amber-950/20'
                                  : 'border-slate-200/80 bg-white dark:border-slate-800 dark:bg-[#0E1626]',
                        ]"
                    >
                        <!-- Icon Notification Pill -->
                        <div
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                            :class="[
                                card.type === 'alert-sempro'
                                    ? 'bg-lime-100 text-lime-700 dark:bg-lime-950 dark:text-lime-300'
                                    : card.type === 'alert-sidang'
                                      ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                      : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                            ]"
                        >
                            <Clock
                                v-if="card.daysLeft !== undefined"
                                class="h-4 w-4"
                            />
                            <FileCheck
                                v-else-if="card.type === 'progress-sempro'"
                                class="h-4 w-4"
                            />
                            <FileText v-else class="h-4 w-4" />
                        </div>

                        <!-- Content Text -->
                        <div class="min-w-0 flex-1">
                            <div
                                class="flex items-center justify-between gap-1"
                            >
                                <p
                                    class="truncate text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ card.title }}
                                </p>
                                <span
                                    class="shrink-0 rounded-md px-1.5 py-0.5 text-[9px] font-bold"
                                    :class="[
                                        card.type === 'alert-sempro'
                                            ? 'bg-lime-200 text-lime-900 dark:bg-lime-900 dark:text-lime-200'
                                            : card.type === 'alert-sidang'
                                              ? 'bg-amber-200 text-amber-900 dark:bg-amber-900 dark:text-amber-200'
                                              : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                    ]"
                                >
                                    {{ card.badgeText }}
                                </span>
                            </div>
                            <p
                                class="truncate text-[11px] font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{ card.stage }}
                            </p>
                            <p
                                class="truncate text-[10px] text-slate-400 dark:text-slate-500"
                            >
                                {{ card.description }}
                            </p>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- 4. 3-KOLOM WIDGET (STATUS TUGAS AKHIR, BIMBINGAN, KALENDER PERSONAL MAHASISWA) -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Kolom 1: Status Tugas Akhir (Sinkron dengan Kuning Berlangsung) -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Status Tugas Akhir
                        </h3>
                        <span
                            class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                        >
                            Dalam Proses
                        </span>
                    </div>

                    <div
                        class="relative mt-6 space-y-7 pl-6 before:absolute before:top-2 before:bottom-2 before:left-[11px] before:w-0.5 before:bg-slate-300 dark:before:bg-slate-700"
                    >
                        <div
                            v-for="(item, idx) in generalThesisStatuses"
                            :key="idx"
                            class="relative"
                        >
                            <span
                                class="absolute top-1 -left-6 h-3.5 w-3.5 rounded-full transition-all"
                                :class="[
                                    item.status === 'completed'
                                        ? 'bg-blue-600 ring-4 ring-blue-100 dark:ring-blue-950'
                                        : item.status === 'in_progress'
                                          ? 'animate-pulse bg-amber-400 ring-4 ring-amber-200 dark:ring-amber-900/60'
                                          : 'bg-slate-400 ring-4 ring-slate-100 dark:ring-slate-900',
                                ]"
                            />
                            <p
                                class="text-xs font-bold"
                                :class="[
                                    item.status === 'in_progress'
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-slate-800 dark:text-slate-200',
                                ]"
                            >
                                {{ item.title }}
                            </p>
                            <p
                                class="text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                {{ item.desc }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Kolom 2: Statistik Bimbingan & Riwayat Bimbingan -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Statistik Bimbingan
                    </h3>

                    <div class="mt-4 space-y-3">
                        <div
                            class="flex items-center gap-3 rounded-xl border border-slate-200/90 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xs font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                            >
                                D1
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    Dr. Ir. Hendra Wijaya, M.Kom.
                                </p>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Pembimbing 1 • 6 Sesi Selesai
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-xl border border-slate-200/90 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-xs font-bold text-indigo-600 dark:bg-indigo-950 dark:text-indigo-400"
                            >
                                D2
                            </div>
                            <div class="min-w-0">
                                <p
                                    class="truncate text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    Rina Agustina, S.T., M.Kom.
                                </p>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    Pembimbing 2 • 4 Sesi Selesai
                                </p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="mt-6 border-t border-slate-200/80 pt-4 dark:border-slate-800"
                    >
                        <div class="mb-3 flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-slate-800 dark:text-slate-200"
                            >
                                Riwayat Bimbingan Terakhir
                            </span>
                            <Link
                                href="/pendaftaran/bimbingan"
                                class="text-[11px] font-semibold text-blue-600 hover:underline dark:text-blue-400"
                            >
                                Lihat Semua &gt;
                            </Link>
                        </div>

                        <div class="space-y-2">
                            <div
                                class="rounded-xl border border-slate-200/80 bg-white p-2.5 text-xs text-slate-700 shadow-2xs dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                            >
                                <p
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Diskusi arsitektur sistem & algoritma
                                </p>
                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    15 Sep 2026 • Bersama Pembimbing 1
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-slate-200/80 bg-white p-2.5 text-xs text-slate-700 shadow-2xs dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                            >
                                <p
                                    class="font-semibold text-slate-900 dark:text-white"
                                >
                                    Revisi metodologi penelitian BAB 3
                                </p>
                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    08 Sep 2026 • Bersama Pembimbing 2
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kolom 3: Kalender Khusus Mahasiswa & Interaksi Klik Tanggal -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Kalender Agenda Saya
                        </h3>
                        <span class="text-[10px] font-medium text-slate-500"
                            >Klik tanggal bertitik</span
                        >
                    </div>

                    <div
                        class="mt-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs transition-colors dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-3 text-xs font-bold dark:border-slate-800"
                        >
                            <span
                                class="flex items-center gap-2 text-slate-900 dark:text-white"
                            >
                                <CalendarIcon
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                />
                                {{ currentMonth }}
                            </span>
                            <div class="flex items-center gap-1">
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                    title="Bulan Sebelumnya"
                                >
                                    <ChevronLeft class="h-3.5 w-3.5" />
                                </button>
                                <button
                                    type="button"
                                    class="flex h-6 w-6 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                    title="Bulan Berikutnya"
                                >
                                    <ChevronRight class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div
                            class="grid grid-cols-7 pt-3 text-center text-[10px] font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                        >
                            <span>Mo</span><span>Tu</span><span>We</span
                            ><span>Th</span><span>Fr</span><span>Sa</span
                            ><span>Su</span>
                        </div>

                        <div
                            class="mt-2 grid grid-cols-7 gap-1 text-center text-xs"
                        >
                            <div
                                v-for="(item, idx) in calendarGrid.slice(0, 28)"
                                :key="idx"
                                class="relative mx-auto flex h-7 w-7 cursor-pointer items-center justify-center rounded-xl text-[11px] transition-all"
                                :class="[
                                    item.isToday
                                        ? 'bg-blue-600 font-bold text-white shadow-md ring-2 shadow-blue-600/30 ring-blue-600/20'
                                        : item.currentMonth
                                          ? 'font-medium text-slate-700 hover:bg-blue-50 hover:text-blue-600 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-blue-400'
                                          : 'text-slate-300 hover:text-slate-400 dark:text-slate-600 dark:hover:text-slate-500',
                                    getAgendasForDay(
                                        item.day,
                                        item.currentMonth,
                                    ).length > 0 && !item.isToday
                                        ? 'font-extrabold ring-1 ring-blue-400 dark:ring-blue-500'
                                        : '',
                                ]"
                                @click="
                                    handleDayClick(item.day, item.currentMonth)
                                "
                            >
                                {{ item.day }}
                                <!-- Dot indicator for agenda -->
                                <span
                                    v-if="
                                        getAgendasForDay(
                                            item.day,
                                            item.currentMonth,
                                        ).length > 0
                                    "
                                    class="absolute -bottom-0.5 h-1.5 w-1.5 rounded-full"
                                    :class="
                                        item.isToday
                                            ? 'bg-amber-300'
                                            : getAgendasForDay(
                                                  item.day,
                                                  item.currentMonth,
                                              )[0].badgeColor
                                    "
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Agenda Mahasiswa List -->
                    <div
                        class="mt-6 border-t border-slate-200/80 pt-4 dark:border-slate-800"
                    >
                        <h4
                            class="mb-3 text-xs font-bold text-slate-800 dark:text-slate-200"
                        >
                            Agenda Mahasiswa Terdekat
                        </h4>

                        <div class="space-y-2.5">
                            <div
                                v-for="agenda in studentAgendas.slice(0, 3)"
                                :key="agenda.id"
                                class="flex cursor-pointer items-center justify-between rounded-xl border border-slate-200/80 bg-white p-3 shadow-2xs transition-colors hover:border-blue-400 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-500"
                                @click="openAgendaDetail(agenda)"
                            >
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold text-white"
                                        :class="agenda.badgeColor"
                                    >
                                        {{ agenda.date }}
                                    </div>
                                    <div>
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ agenda.title }}
                                        </p>
                                        <p class="text-[10px] text-slate-400">
                                            {{ agenda.fullDateString }}
                                        </p>
                                    </div>
                                </div>
                                <span
                                    class="text-[11px] font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ agenda.time.split(' ')[0] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. HASIL PENILAIAN CARD DENGAN KLIK DETAIL POPUP MODAL -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Hasil Penilaian & Sidang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Klik item di bawah untuk melihat rincian skor,
                            masukan dosen penguji, dan lembar revisi.
                        </p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in assessments"
                        :key="item.id"
                        class="flex cursor-pointer flex-col gap-2 rounded-xl border border-slate-200/80 p-4 transition-all hover:border-blue-400 hover:shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900/40 dark:hover:border-blue-500"
                        @click="openAssessmentModal(item)"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h4
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </h4>
                                <span class="text-[10px] text-slate-400">
                                    ({{ item.period }})
                                </span>
                            </div>
                            <p
                                class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                {{ item.pembimbing }} • Skor Akhir:
                                <span
                                    class="font-bold text-slate-700 dark:text-slate-300"
                                    >{{ item.finalScore }} ({{
                                        item.grade
                                    }})</span
                                >
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span
                                class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-bold shadow-2xs"
                                :class="[
                                    item.status === 'Tidak Lulus'
                                        ? 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'
                                        : item.status.includes('Revisi')
                                          ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                                          : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
                                ]"
                            >
                                {{ item.status }}
                            </span>
                            <span
                                class="flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400"
                            >
                                Detail <Eye class="h-3.5 w-3.5" />
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- POPUP MODAL 1: DETAIL AGENDA MAHASISWA     -->
        <!-- ========================================== -->
        <Modal
            :show="showAgendaModal"
            max-width="lg"
            title="Rincian Agenda Akademik"
            @close="showAgendaModal = false"
        >
            <div v-if="selectedAgenda" class="space-y-4">
                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                    <div class="flex items-center gap-2">
                        <span
                            class="h-3 w-3 rounded-full"
                            :class="selectedAgenda.badgeColor"
                        />
                        <span
                            class="text-xs font-bold text-slate-500 uppercase dark:text-slate-400"
                        >
                            {{ selectedAgenda.type.toUpperCase() }}
                        </span>
                    </div>
                    <h4
                        class="mt-1 text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        {{ selectedAgenda.title }}
                    </h4>
                    <p class="mt-1 text-xs text-slate-600 dark:text-slate-300">
                        {{ selectedAgenda.fullDateString }} •
                        {{ selectedAgenda.time }}
                    </p>
                </div>

                <div class="space-y-2 text-xs">
                    <div
                        v-if="selectedAgenda.location"
                        class="flex items-start gap-2"
                    >
                        <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-blue-600" />
                        <div>
                            <span
                                class="font-bold text-slate-800 dark:text-slate-200"
                                >Lokasi / Ruangan:</span
                            >
                            <p class="text-slate-600 dark:text-slate-400">
                                {{ selectedAgenda.location }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="selectedAgenda.pembimbingPenguji?.length"
                        class="mt-2 flex items-start gap-2"
                    >
                        <User class="mt-0.5 h-4 w-4 shrink-0 text-indigo-600" />
                        <div>
                            <span
                                class="font-bold text-slate-800 dark:text-slate-200"
                                >Dosen Pembimbing & Penguji:</span
                            >
                            <ul
                                class="mt-0.5 list-inside list-disc space-y-0.5 text-slate-600 dark:text-slate-400"
                            >
                                <li
                                    v-for="(
                                        dos, dIdx
                                    ) in selectedAgenda.pembimbingPenguji"
                                    :key="dIdx"
                                >
                                    {{ dos }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div
                        v-if="selectedAgenda.notes"
                        class="mt-2 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-900/50 dark:bg-amber-950/40"
                    >
                        <Info
                            class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <div>
                            <span
                                class="font-bold text-amber-900 dark:text-amber-200"
                                >Catatan Mahasiswa:</span
                            >
                            <p
                                class="mt-0.5 text-amber-800 dark:text-amber-300"
                            >
                                {{ selectedAgenda.notes }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    @click="showAgendaModal = false"
                >
                    Tutup
                </button>
            </template>
        </Modal>

        <!-- ========================================== -->
        <!-- POPUP MODAL 2: DETAIL HASIL PENILAIAN/SIDANG -->
        <!-- ========================================== -->
        <Modal
            :show="showAssessmentModal"
            max-width="xl"
            title="Detail Hasil Penilaian & Berita Acara"
            @close="showAssessmentModal = false"
        >
            <div v-if="selectedAssessment" class="space-y-5">
                <!-- Banner Status & Nilai -->
                <div
                    class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/60"
                >
                    <div>
                        <p
                            class="text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            {{ selectedAssessment.period }}
                        </p>
                        <h4
                            class="text-base font-extrabold text-slate-900 dark:text-white"
                        >
                            {{ selectedAssessment.title }}
                        </h4>
                        <p
                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            Pembimbing: {{ selectedAssessment.pembimbing }}
                        </p>
                    </div>

                    <div class="text-right">
                        <span
                            class="inline-block rounded-lg px-3 py-1 text-xs font-bold uppercase shadow-2xs"
                            :class="[
                                selectedAssessment.status === 'Tidak Lulus'
                                    ? 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'
                                    : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                            ]"
                        >
                            {{ selectedAssessment.status }}
                        </span>
                        <p
                            class="mt-1 text-lg font-black text-slate-900 dark:text-white"
                        >
                            {{ selectedAssessment.finalScore }}
                            <span class="text-xs font-bold text-slate-500"
                                >({{ selectedAssessment.grade }})</span
                            >
                        </p>
                    </div>
                </div>

                <!-- Info Batas Revisi -->
                <div
                    class="rounded-xl border border-blue-100 bg-blue-50/60 p-3 text-xs dark:border-blue-900/40 dark:bg-blue-950/20"
                >
                    <span class="font-bold text-blue-900 dark:text-blue-300"
                        >Tenggat Waktu Revisi:</span
                    >
                    <span
                        class="ml-1 font-semibold text-blue-800 dark:text-blue-400"
                        >{{ selectedAssessment.revisionDeadline }}</span
                    >
                </div>

                <!-- Catatan Revisi Dari Dewan Penguji -->
                <div>
                    <h5
                        class="mb-2 text-xs font-bold tracking-wider text-slate-900 uppercase dark:text-white"
                    >
                        Catatan & Masukan Tim Penguji
                    </h5>
                    <div class="space-y-2.5">
                        <div
                            v-for="(
                                catatan, cIdx
                            ) in selectedAssessment.examinerNotes"
                            :key="cIdx"
                            class="rounded-xl border border-slate-200/80 bg-white p-3 text-xs shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                        >
                            <p
                                class="font-bold text-slate-800 dark:text-slate-200"
                            >
                                {{ catatan.by }}
                            </p>
                            <p
                                class="mt-1 leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{ catatan.note }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex w-full items-center justify-between">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        title="Unduh Lembar Berita Acara Sementara"
                    >
                        <Download class="h-3.5 w-3.5" /> Unduh Berita Acara
                    </button>

                    <button
                        type="button"
                        class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700"
                        @click="showAssessmentModal = false"
                    >
                        Selesai
                    </button>
                </div>
            </template>
        </Modal>
    </AppLayout>
</template>
