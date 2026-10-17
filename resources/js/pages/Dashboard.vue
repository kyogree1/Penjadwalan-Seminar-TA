<script setup lang="ts">
import type { Auth } from '@/types/auth';
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Calendar,
    Check,
    CheckCircle2,
    Clock,
    Download,
    Eye,
    FileCheck,
    FileSpreadsheet,
    FileText,
    GraduationCap,
    X,
    XCircle,
} from 'lucide-vue-next';

import Modal from '@/Components/Modal.vue';
import StudentLecturers from '@/Components/StudentLecturers.vue';
import {
    STATUS_PENGAJUAN,
    STATUS_VERIFIKASI,
    statusToBadge,
} from '@/lib/status';
import type {
    RegistrationRecord,
    ScheduleRecord,
} from '@/types/r2-read-contracts';

const page = usePage();
const authUser = computed(() => (page.props.auth as Auth | undefined)?.user);
const studentName = computed(() => authUser.value?.name ?? '');
const studentNim = computed(
    () => authUser.value?.nim_nip ?? 'NIM belum tersedia',
);
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

interface Props {
    dbJudul: RegistrationRecord | null;
    dbSempro: RegistrationRecord | null;
    dbSidang: RegistrationRecord | null;
    activePeriods?: ActivePeriod[];
    dbTotalBimbinganAcc?: number;
    dbJadwal: ScheduleRecord | null;
    dbJadwalList?: ScheduleRecord[];
    dbApprovedTitle?: RegistrationRecord | null;
    dbSemproSteps?: RoadmapStep[];
    dbSidangSteps?: RoadmapStep[];
}

const props = defineProps<Props>();

const activePeriods = computed<ActivePeriod[]>(() => props.activePeriods ?? []);

const semproSteps = computed<RoadmapStep[]>(() => props.dbSemproSteps ?? []);

const sidangSteps = computed<RoadmapStep[]>(() => props.dbSidangSteps ?? []);

const getStepNodeClasses = (status: StepStatus) => {
    switch (status) {
        case 'completed':
            return 'bg-blue-600 text-white ring-4 ring-blue-100 dark:ring-blue-950';
        case 'in_progress':
            return 'bg-amber-400 text-slate-950 ring-4 ring-amber-200 dark:ring-amber-900/60 font-bold';
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
    description: string;
    linkUrl: string;
    icon: any;
    cardClasses: string;
    iconClasses: string;
}

const formatStatusLabel = (status: string) => {
    if (status in STATUS_VERIFIKASI) {
        return STATUS_VERIFIKASI[
            status as keyof typeof STATUS_VERIFIKASI
        ].label;
    }
    if (status in STATUS_PENGAJUAN) {
        return STATUS_PENGAJUAN[status as keyof typeof STATUS_PENGAJUAN].label;
    }
    return statusToBadge(status).label;
};

const getStatusTheme = (status: string) => {
    switch (status) {
        case 'verifikasi_tendik':
        case 'disetujui':
            return {
                icon: CheckCircle2,
                cardClasses:
                    'border-emerald-300/80 bg-emerald-50/50 hover:border-emerald-400 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:hover:border-emerald-600',
                iconClasses:
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
            };
        case 'selesai':
            return {
                icon: FileCheck,
                cardClasses:
                    'border-emerald-300/80 bg-emerald-50/50 hover:border-emerald-400 dark:border-emerald-900/50 dark:bg-emerald-950/20 dark:hover:border-emerald-600',
                iconClasses:
                    'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
            };
        case 'terjadwal':
            return {
                icon: Calendar,
                cardClasses:
                    'border-blue-300/80 bg-blue-50/50 hover:border-blue-400 dark:border-blue-900/50 dark:bg-blue-950/20 dark:hover:border-blue-600',
                iconClasses:
                    'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
            };
        case 'ditolak':
            return {
                icon: XCircle,
                cardClasses:
                    'border-rose-300/80 bg-rose-50/50 hover:border-rose-400 dark:border-rose-900/50 dark:bg-rose-950/20 dark:hover:border-rose-600',
                iconClasses:
                    'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300',
            };
        case 'revisi':
            return {
                icon: AlertCircle,
                cardClasses:
                    'border-amber-300/80 bg-amber-50/50 hover:border-amber-400 dark:border-amber-900/50 dark:bg-amber-950/20 dark:hover:border-amber-600',
                iconClasses:
                    'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
            };
        case 'diajukan':
        case 'menunggu':
        case 'menunggu_ulang':
        default:
            return {
                icon: Clock,
                cardClasses:
                    'border-amber-200/80 bg-amber-50/40 hover:border-amber-400 dark:border-amber-900/40 dark:bg-amber-950/20 dark:hover:border-amber-600',
                iconClasses:
                    'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
            };
    }
};

const currentStatusCards = computed<CurrentStatusCard[]>(() => {
    const cards: CurrentStatusCard[] = [];

    for (const [type, record] of [
        ['sempro', props.dbSempro],
        ['sidang', props.dbSidang],
    ] as const) {
        if (!record) continue;
        const theme = getStatusTheme(record.status);
        cards.push({
            id: `${type}-${record.id}`,
            title: formatStatusLabel(record.status),
            description: type === 'sempro' ? 'Seminar Proposal' : 'Sidang',
            linkUrl: `/pendaftaran/${type}`,
            icon: theme.icon,
            cardClasses: theme.cardClasses,
            iconClasses: theme.iconClasses,
        });
    }
    return cards;
});

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

const assessments = computed<AssessmentDetail[]>(() => []);

const selectedAssessment = ref<AssessmentDetail | null>(null);
const showAssessmentModal = ref(false);

const openAssessmentModal = (item: AssessmentDetail) => {
    selectedAssessment.value = item;
    showAssessmentModal.value = true;
};
</script>

<template>
    <Head title="Dashboard Mahasiswa - SIPTA IF" />

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- 1. GREETING BANNER & DUA STATUS PERIODE (SEMPRO & SIDANG BERBEDA WARNA) -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Left: Greeting Card (2/3 lebar di layar desktop) -->
            <div
                class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-2 dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div>
                    <h2
                        class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white"
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
                    :class="[period.colorTheme.bg, period.colorTheme.border]"
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
                        <p
                            class="truncate text-xs font-extrabold"
                            :class="period.colorTheme.text"
                        >
                            {{ period.title }}
                        </p>
                        <p
                            class="mt-0.5 truncate text-[10px]"
                            :class="period.colorTheme.text"
                        >
                            {{ period.semester }}
                            {{ period.academicYear }} •
                            {{ period.startDate }}–{{ period.endDate }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- 2. ROADMAP WIDGET DENGAN INDIKATOR WARNA KUNING UNTUK PROGRES AKTIF (DI BAWAH HERO) -->
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
                        Titik kuning menandakan tahapan yang sedang aktif dan
                        berjalan.
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

                        <div class="relative flex justify-between text-center">
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

                        <div class="relative flex justify-between text-center">
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
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
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
                    class="flex items-center gap-3 rounded-xl border p-3 shadow-2xs transition-colors"
                    :class="card.cardClasses"
                >
                    <!-- Icon Notification Pill -->
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"
                        :class="card.iconClasses"
                    >
                        <component :is="card.icon" class="h-4 w-4" />
                    </div>

                    <!-- Content Text -->
                    <div class="min-w-0 flex-1">
                        <p
                            class="truncate text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ card.title }}
                        </p>
                        <p
                            class="truncate text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            {{ card.description }}
                        </p>
                    </div>
                </Link>
            </div>
        </div>

        <!-- Section Dosen Pembimbing & Penguji serta Bimbingan Tugas Akhir (DI BAWAH ROADMAP) -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <!-- Kotak 1 & 2: Dosen Pembimbing & Penguji (2 Kolom di Desktop) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs lg:col-span-2 dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <StudentLecturers
                    :approved-title="props.dbApprovedTitle ?? null"
                    :schedules="props.dbJadwalList ?? []"
                />
            </div>

            <!-- Kotak 3: Bimbingan Tugas Akhir (1 Kolom Sejajar dengan Dosen di Desktop) -->
            <div
                class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Bimbingan Tugas Akhir
                        </h3>
                        <span
                            class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                        >
                            Logbook
                        </span>
                    </div>
                    <p
                        class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Pantau riwayat catatan, lembar konsultasi, dan
                        perkembangan bimbingan skripsi Anda bersama dosen
                        pembimbing.
                    </p>

                    <div
                        class="mt-4 rounded-xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/80 dark:bg-slate-900/50"
                    >
                        <span
                            class="text-[11px] font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Total Sesi Tercatat
                        </span>
                        <div class="mt-1 flex items-baseline gap-1.5">
                            <span
                                class="text-2xl font-extrabold text-slate-900 dark:text-white"
                            >
                                {{ props.dbTotalBimbinganAcc ?? 0 }}
                            </span>
                            <span class="text-xs font-medium text-slate-400">
                                sesi konsultasi
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <Link
                        href="/pendaftaran/bimbingan"
                        class="flex w-full items-center justify-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-blue-700"
                    >
                        <span>Lihat Detail</span>
                        <span aria-hidden="true">&gt;</span>
                    </Link>
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
                        Klik item di bawah untuk melihat rincian skor, masukan
                        dosen penguji, dan lembar revisi.
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
                                >{{ item.finalScore }} ({{ item.grade }})</span
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
                        <p class="font-bold text-slate-800 dark:text-slate-200">
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
</template>
