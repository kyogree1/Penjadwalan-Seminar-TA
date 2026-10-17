<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Activity,
    ArrowUpRight,
    Award,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    Cpu,
    ExternalLink,
    Eye,
    FileCheck,
    FileEdit,
    FileText,
    GraduationCap,
    Layers,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import type { KaprodiDashboardProps } from '@/types/kaprodi-dashboard';

const props = defineProps<KaprodiDashboardProps>();

// Keep derived data tied to current Inertia props, including explicit empty results.
const queues = computed(() => [
    { name: 'Pengajuan Judul', count: props.pendingCounts.judul },
    { name: 'Seminar Proposal', count: props.pendingCounts.sempro },
    { name: 'Sidang Akhir', count: props.pendingCounts.sidang },
]);

// Safely obtain lecturer/kaprodi name with headless-runner fallback
const kaprodiName = computed(() => {
    try {
        if (typeof usePage === 'function') {
            const authUser = (
                usePage()?.props?.auth as
                    | { user?: { name?: string } }
                    | undefined
            )?.user;
            if (authUser?.name) return authUser.name;
        }
    } catch {
        // Fallback for isolated test runs
    }
    return 'Dra. Nurul Rahmania, M.T.';
});

// Interactive Tab State
const activeTab = ref<'judul' | 'all' | 'beban'>('judul');

// Modal State
const showDetailModal = ref(false);
const selectedApproval = ref<(typeof props.pendingApprovals)[number] | null>(
    null,
);

const openDetail = (item: (typeof props.pendingApprovals)[number]) => {
    selectedApproval.value = item;
    showDetailModal.value = true;
};

// Derived Pipeline Stages
const pipelineStages = computed(() => {
    const total = props.stats.totalMahasiswa;
    const judul = props.pendingCounts.judul;
    const sempro = props.pendingCounts.sempro;
    const sidang = props.pendingCounts.sidang;

    const calcPct = (count: number) =>
        total > 0 ? Math.min(100, Math.round((count / total) * 100)) : 0;

    return [
        {
            id: 'judul',
            name: 'Pengajuan Judul',
            count: judul,
            percentage: calcPct(judul),
            barColor: 'bg-amber-500',
            dotColor: 'bg-amber-500',
            icon: FileText,
            href: '/kaprodi/persetujuan',
        },
        {
            id: 'sempro',
            name: 'Seminar Proposal',
            count: sempro,
            percentage: calcPct(sempro),
            barColor: 'bg-blue-600',
            dotColor: 'bg-blue-600',
            icon: Calendar,
            href: '/kaprodi/penjadwalan',
        },
        {
            id: 'bimbingan',
            name: 'Bimbingan Berjalan',
            count: Math.max(0, total - (judul + sempro + sidang)),
            percentage: calcPct(Math.max(0, total - (judul + sempro + sidang))),
            barColor: 'bg-indigo-600',
            dotColor: 'bg-indigo-600',
            icon: UserCheck,
            href: '/kaprodi/monitoring',
        },
        {
            id: 'sidang',
            name: 'Sidang Akhir',
            count: sidang,
            percentage: calcPct(sidang),
            barColor: 'bg-emerald-600',
            dotColor: 'bg-emerald-600',
            icon: Award,
            href: '/kaprodi/penjadwalan',
        },
        {
            id: 'selesai',
            name: 'Selesai / Alumni',
            count: 0,
            percentage: 0,
            barColor: 'bg-slate-400',
            dotColor: 'bg-slate-400',
            icon: CheckCircle2,
            href: '/katalog',
        },
    ];
});
</script>

<template>
    <Head title="Dashboard Kaprodi - Informatika ITK" />

    <div class="space-y-6">
        <!-- 1. HERO BANNER WITH QUICK ACTION PILLS (STYLE SIPTA PENJADWALAN-SEMINAR-TA) -->
        <div
            class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 text-white shadow-xs md:p-8 dark:border-slate-800"
        >
            <div
                class="pointer-events-none absolute -right-12 -bottom-16 h-64 w-64 rounded-full bg-blue-500/15 blur-3xl"
            />
            <div
                class="pointer-events-none absolute -top-12 -left-12 h-48 w-48 rounded-full bg-indigo-500/15 blur-2xl"
            />

            <div
                class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold backdrop-blur-md"
                    >
                        <span
                            class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"
                        />
                        <span>Periode Gasal 2026/2027 • Gelombang 2</span>
                    </div>
                    <h1
                        class="text-xl font-extrabold tracking-tight sm:text-2xl lg:text-3xl"
                    >
                        Selamat Datang, {{ kaprodiName }}
                    </h1>
                    <p
                        class="max-w-2xl text-xs leading-relaxed text-blue-100 sm:text-sm"
                    >
                        Platform SIPTA IF mengintegrasikan penjadwalan seminar
                        proposal dan hasil dengan dukungan kecerdasan buatan
                        (Algoritma Genetika) bebas bentrok jadwal.
                    </p>
                </div>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <Link
                        href="/kaprodi/persetujuan"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95"
                    >
                        <FileEdit class="h-4 w-4 text-amber-300" />
                        <span>Review Judul</span>
                        <span
                            class="rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-black text-slate-950"
                        >
                            {{ props.pendingCounts.judul }}
                        </span>
                    </Link>

                    <Link
                        href="/kaprodi/penjadwalan"
                        class="flex items-center gap-2 rounded-xl border border-transparent bg-white px-3.5 py-2 text-xs font-bold text-blue-950 shadow-sm transition-all hover:bg-blue-50 active:scale-95"
                    >
                        <Cpu class="h-4 w-4 text-blue-700" />
                        <span>Plotting Penguji AI</span>
                    </Link>

                    <Link
                        href="/kaprodi/persetujuan"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95"
                    >
                        <ShieldAlert class="h-4 w-4 text-emerald-300" />
                        <span>Validasi Berkas</span>
                        <span
                            class="rounded-full bg-emerald-400 px-1.5 py-0.5 text-[10px] font-black text-slate-950"
                        >
                            {{
                                props.pendingCounts.sempro +
                                props.pendingCounts.sidang
                            }}
                        </span>
                    </Link>

                    <Link
                        href="/katalog"
                        class="flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-sm transition-all hover:bg-white/20 active:scale-95"
                    >
                        <BookOpen class="h-4 w-4 text-blue-200" />
                        <span>Katalog TA</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 2. KPI STAT CARDS (PRESERVING EXACT KEYS FOR TEST SUITE) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Total Mahasiswa"
                :value="props.stats.totalMahasiswa"
                subtitle="Akun mahasiswa terdaftar, bukan peserta TA aktif"
                :icon="Users"
                icon-color="blue"
            />
            <StatCard
                title="Total Dosen"
                :value="props.stats.totalDosen"
                subtitle="Akun dengan peran dosen"
                :icon="GraduationCap"
                icon-color="indigo"
            />
            <StatCard
                title="Antrean Persetujuan"
                :value="props.stats.antreanPersetujuan"
                subtitle="Pengajuan berstatus menunggu"
                :icon="ShieldCheck"
                icon-color="amber"
            />
            <StatCard
                title="Jadwal & Plotting"
                :value="props.pendingCounts.sempro + props.pendingCounts.sidang"
                subtitle="Antrean sempro & sidang terdaftar"
                :icon="Calendar"
                icon-color="emerald"
            />
        </div>

        <!-- 3. PIPELINE KEMAJUAN TUGAS AKHIR (PORTED FROM FRONT-END) -->
        <div
            class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center"
            >
                <div>
                    <h3
                        class="text-sm font-bold text-slate-900 sm:text-base dark:text-white"
                    >
                        Pipeline Kemajuan Tugas Akhir
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Distribusi tahapan mahasiswa prodi Informatika semester
                        aktif
                    </p>
                </div>
                <span
                    class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                >
                    Total {{ props.stats.totalMahasiswa }} Mahasiswa
                </span>
            </div>

            <!-- Progress Bar Multi-Segment -->
            <div
                class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 shadow-inner dark:bg-slate-800"
            >
                <div
                    v-for="stage in pipelineStages"
                    :key="stage.id"
                    :class="[
                        stage.barColor,
                        'h-full transition-all duration-500',
                    ]"
                    :style="{ width: stage.percentage + '%' }"
                    :title="
                        stage.name +
                        ': ' +
                        stage.count +
                        ' mhs (' +
                        stage.percentage +
                        '%)'
                    "
                />
            </div>

            <!-- Segment Legend Cards -->
            <div
                class="grid grid-cols-2 gap-3 pt-2 sm:grid-cols-3 lg:grid-cols-5"
            >
                <Link
                    v-for="stage in pipelineStages"
                    :key="stage.id"
                    :href="stage.href"
                    class="block space-y-1 rounded-xl border border-slate-200/70 bg-slate-50/70 p-3 transition-all hover:border-blue-500/50 hover:shadow-xs dark:border-slate-800/80 dark:bg-slate-900/50"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="h-2.5 w-2.5 rounded-full"
                            :class="stage.dotColor"
                        />
                        <span
                            class="truncate text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            {{ stage.name }}
                        </span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-base font-bold text-slate-900 dark:text-white"
                            >{{ stage.count }}</span
                        >
                        <span class="text-[10px] font-medium text-slate-400"
                            >{{ stage.percentage }}%</span
                        >
                    </div>
                </Link>
            </div>
        </div>

        <!-- 4. MAIN 2-COLUMN SECTION -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- LEFT COLUMN: TABS & QUEUES (7 COLS) -->
            <div class="space-y-6 lg:col-span-7">
                <!-- TABS CONTAINER -->
                <Card class="p-6">
                    <div
                        class="border-b border-slate-100 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="activeTab = 'judul'"
                                class="flex items-center gap-2 pb-2 text-xs font-bold transition-all sm:text-sm"
                                :class="[
                                    activeTab === 'judul'
                                        ? 'border-b-2 border-blue-600 text-blue-600 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                <span>Usulan Judul Baru</span>
                                <span
                                    class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    {{ props.pendingCounts.judul }}
                                </span>
                            </button>

                            <button
                                type="button"
                                @click="activeTab = 'all'"
                                class="flex items-center gap-2 pb-2 text-xs font-semibold transition-all sm:text-sm"
                                :class="[
                                    activeTab === 'all'
                                        ? 'border-b-2 border-blue-600 font-bold text-blue-600 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                <span>Semua Antrean</span>
                                <span
                                    class="rounded-full bg-blue-100 px-2 py-0.5 text-xs font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ props.stats.antreanPersetujuan }}
                                </span>
                            </button>

                            <button
                                type="button"
                                @click="activeTab = 'beban'"
                                class="flex items-center gap-2 pb-2 text-xs font-semibold transition-all sm:text-sm"
                                :class="[
                                    activeTab === 'beban'
                                        ? 'border-b-2 border-blue-600 font-bold text-blue-600 dark:text-blue-400'
                                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                <span>Beban Dosen</span>
                                <span
                                    class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ props.stats.totalDosen }}
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- TAB 1: USULAN JUDUL BARU -->
                    <div v-show="activeTab === 'judul'" class="mt-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    Antrean Usulan Judul Baru
                                </h3>
                                <p class="mt-1 text-xs text-slate-500">
                                    {{ props.pendingCounts.judul }} pengajuan
                                    menunggu; menampilkan hingga 5 pengajuan
                                    terlama.
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="props.pendingApprovals.length === 0"
                            class="mt-4 text-xs text-slate-500"
                        >
                            Tidak ada usulan judul menunggu.
                        </p>

                        <div v-else class="space-y-3 pt-2">
                            <div
                                v-for="item in props.pendingApprovals"
                                :key="item.id"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 shadow-2xs transition-all hover:border-blue-400/50 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div
                                    class="flex items-start justify-between gap-3"
                                >
                                    <div class="min-w-0 flex-1 space-y-1">
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-400"
                                            >
                                                USULAN JUDUL
                                            </span>
                                            <span
                                                class="text-[11px] text-slate-400"
                                                >{{ item.tanggal }}</span
                                            >
                                        </div>
                                        <p
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.nama }}
                                            <span
                                                class="font-mono text-xs font-normal text-slate-500"
                                            >
                                                (NIM:
                                                {{
                                                    item.nim ??
                                                    'Belum tersedia'
                                                }})
                                            </span>
                                        </p>
                                        <p
                                            class="text-xs leading-relaxed text-slate-700 dark:text-slate-300"
                                        >
                                            {{ item.judul }}
                                        </p>
                                        <div
                                            class="pt-1 text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            <span>
                                                Usulan pembimbing 1:
                                                <strong
                                                    class="text-slate-700 dark:text-slate-200"
                                                >
                                                    {{
                                                        item.pembimbing1 ??
                                                        'Belum diusulkan'
                                                    }}
                                                </strong>
                                            </span>
                                            <span class="mx-1.5">•</span>
                                            <span>
                                                Usulan pembimbing 2:
                                                <strong
                                                    class="text-slate-700 dark:text-slate-200"
                                                >
                                                    {{
                                                        item.pembimbing2 ??
                                                        'Belum diusulkan'
                                                    }}
                                                </strong>
                                            </span>
                                        </div>
                                    </div>

                                    <div
                                        class="flex shrink-0 items-center gap-2"
                                    >
                                        <button
                                            type="button"
                                            @click="openDetail(item)"
                                            class="flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                            <span>Detail</span>
                                        </button>
                                        <Link
                                            href="/kaprodi/persetujuan"
                                            class="flex items-center gap-1 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-2xs hover:bg-blue-700"
                                        >
                                            <span>Review</span>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <Link
                                href="/kaprodi/persetujuan"
                                class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400"
                            >
                                <span>Buka Seluruh Antrean Persetujuan</span>
                                <ArrowUpRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- TAB 2: SEMUA ANTREAN -->
                    <div v-show="activeTab === 'all'" class="mt-4 space-y-4">
                        <div>
                            <h3
                                class="text-sm font-extrabold text-slate-900 dark:text-white"
                            >
                                Antrean Pengajuan Tugas Akhir
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Jumlah berkas berstatus menunggu per jenis,
                                bukan distribusi tahap mahasiswa. Satu mahasiswa
                                dapat memiliki beberapa pengajuan.
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <div
                                v-for="queue in queues"
                                :key="queue.name"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <p class="text-xs font-medium text-slate-500">
                                    {{ queue.name }}
                                </p>
                                <p
                                    class="mt-2 text-xl font-black text-slate-900 dark:text-white"
                                >
                                    {{ queue.count }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="props.stats.antreanPersetujuan === 0"
                            class="mt-4 text-xs text-slate-500"
                        >
                            Tidak ada pengajuan menunggu.
                        </p>

                        <div class="pt-2">
                            <Link
                                href="/kaprodi/persetujuan"
                                class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400"
                            >
                                <span
                                    >Kelola Antrean di Portal Persetujuan</span
                                >
                                <ArrowUpRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>

                    <!-- TAB 3: DISTRIBUSI BEBAN DOSEN (INSIDE TABS) -->
                    <div v-show="activeTab === 'beban'" class="mt-4 space-y-4">
                        <div>
                            <h3
                                class="text-sm font-extrabold text-slate-900 dark:text-white"
                            >
                                Distribusi Beban Bimbingan Dosen
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Mahasiswa unik pada judul disetujui, seluruh
                                data tercatat. Tidak termasuk usulan pembimbing
                                atau tugas penguji; bukan ukuran bimbingan
                                aktif. Kuota dan bidang keahlian belum tersedia.
                            </p>
                            <p
                                class="mt-3 text-xs font-bold text-blue-600 dark:text-blue-400"
                            >
                                {{ props.stats.totalDosen }} Dosen Terdaftar
                            </p>
                        </div>

                        <p
                            v-if="props.lecturers.length === 0"
                            class="mt-4 text-xs text-slate-500"
                        >
                            Belum ada dosen terdaftar.
                        </p>

                        <div v-else class="space-y-3">
                            <div
                                v-for="dos in props.lecturers"
                                :key="dos.id"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <div>
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ dos.name }}
                                        </p>
                                        <p class="text-[10px] text-slate-500">
                                            NIP:
                                            {{ dos.nip ?? 'Belum tersedia' }}
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-lg bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{ dos.total }} Mahasiswa
                                    </span>
                                </div>
                                <div
                                    class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
                                >
                                    <div
                                        class="h-full rounded-full bg-blue-600 transition-all duration-300"
                                        :style="{
                                            width:
                                                Math.min(
                                                    100,
                                                    (dos.total / 10) * 100,
                                                ) + '%',
                                        }"
                                    />
                                </div>
                                <p class="mt-2 text-[10px] text-slate-500">
                                    Pembimbing 1: {{ dos.bimbingan1 }} •
                                    Pembimbing 2: {{ dos.bimbingan2 }}. Total
                                    dihitung unik lintas posisi.
                                </p>
                            </div>
                        </div>

                        <div class="pt-2">
                            <Link
                                href="/kaprodi/dosen"
                                class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400"
                            >
                                <span>Buka Master Data Dosen</span>
                                <ArrowUpRight class="h-4 w-4" />
                            </Link>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- RIGHT COLUMN: BATAS WAKTU, BEBAN & SHORTCUTS (5 COLS) -->
            <div class="space-y-6 lg:col-span-5">
                <!-- BATAS WAKTU PERIODE CARD -->
                <Card class="p-5">
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/40"
                        >
                            <Clock class="h-4 w-4 text-amber-500" />
                        </div>
                        <h3
                            class="text-xs font-extrabold text-slate-900 dark:text-white"
                        >
                            Batas Waktu Periode
                        </h3>
                    </div>

                    <p
                        v-if="props.deadlines === null"
                        class="mt-4 text-xs text-slate-500"
                    >
                        Data batas waktu periode belum tersedia di dashboard
                        ini. Kelola periode penjadwalan melalui menu
                        Penjadwalan.
                    </p>

                    <div class="mt-4 pt-2">
                        <Link
                            href="/kaprodi/penjadwalan"
                            class="inline-flex items-center gap-1 text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400"
                        >
                            <span>Kelola Periode Penjadwalan →</span>
                        </Link>
                    </div>
                </Card>

                <!-- FITUR UTAMA KOORDINATOR (SHORTCUT ACTIONS) -->
                <div class="space-y-3">
                    <h3
                        class="text-xs font-extrabold tracking-wider text-slate-400 uppercase"
                    >
                        Fitur Utama Koordinator TA
                    </h3>

                    <Link
                        v-for="action in [
                            {
                                href: '/kaprodi/penjadwalan',
                                title: 'Optimasi Penjadwalan GA',
                                desc: 'Plotting jadwal sempro & sidang bebas bentrok',
                                icon: Cpu,
                                iconColor:
                                    'text-blue-600 bg-blue-50 border-blue-200 dark:bg-blue-950/60 dark:border-blue-900 dark:text-blue-400',
                            },
                            {
                                href: '/kaprodi/monitoring',
                                title: 'Monitoring Progres Prodi',
                                desc: 'Pantau kemajuan skripsi seluruh angkatan',
                                icon: Activity,
                                iconColor:
                                    'text-indigo-600 bg-indigo-50 border-indigo-200 dark:bg-indigo-950/60 dark:border-indigo-900 dark:text-indigo-400',
                            },
                            {
                                href: '/kaprodi/persetujuan',
                                title: 'Review Judul & Pembimbing',
                                desc: 'Persetujuan usulan judul & pembimbing TA',
                                icon: ShieldCheck,
                                iconColor:
                                    'text-amber-600 bg-amber-50 border-amber-200 dark:bg-amber-950/60 dark:border-amber-900 dark:text-amber-400',
                            },
                            {
                                href: '/kaprodi/dosen',
                                title: 'Master Data Dosen',
                                desc: 'Kelola kuota, KBK, dan penugasan dosen',
                                icon: GraduationCap,
                                iconColor:
                                    'text-emerald-600 bg-emerald-50 border-emerald-200 dark:bg-emerald-950/60 dark:border-emerald-900 dark:text-emerald-400',
                            },
                        ]"
                        :key="action.href"
                        :href="action.href"
                        class="group flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs transition-all hover:border-blue-500/50 hover:shadow-sm dark:border-slate-800 dark:bg-[#0E1626]"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border"
                                :class="action.iconColor"
                            >
                                <component :is="action.icon" class="h-4 w-4" />
                            </div>
                            <div class="min-w-0">
                                <h4
                                    class="text-xs font-bold text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                                >
                                    {{ action.title }}
                                </h4>
                                <p
                                    class="truncate text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    {{ action.desc }}
                                </p>
                            </div>
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 shrink-0 text-slate-400 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                        />
                    </Link>
                </div>
            </div>
        </div>
    </div>

    <!-- POPUP MODAL: DETAIL USULAN JUDUL (PORTED ACCESSIBLE MODAL) -->
    <Modal
        :show="showDetailModal"
        title="Detail Pengajuan Tugas Akhir"
        max-width="md"
        @close="showDetailModal = false"
    >
        <div v-if="selectedApproval" class="space-y-4">
            <div>
                <span class="text-[11px] font-semibold text-slate-400"
                    >Mahasiswa Pemohon</span
                >
                <p class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ selectedApproval.nama }}
                    <span class="font-mono text-xs font-normal text-slate-500">
                        ({{ selectedApproval.nim ?? 'NIM belum tersedia' }})
                    </span>
                </p>
                <p class="text-[11px] text-slate-400">
                    Diajukan pada: {{ selectedApproval.tanggal }}
                </p>
            </div>

            <div>
                <span class="text-[11px] font-semibold text-slate-400"
                    >Judul Skripsi / TA</span
                >
                <p
                    class="mt-1 rounded-xl border border-slate-200/80 bg-slate-50 p-3 text-xs leading-relaxed font-semibold text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                >
                    {{ selectedApproval.judul }}
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <div
                    class="rounded-xl border border-slate-200/70 bg-slate-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <span class="text-[10px] text-slate-400"
                        >Usulan Pembimbing 1</span
                    >
                    <p
                        class="text-xs font-bold text-slate-800 dark:text-slate-200"
                    >
                        {{ selectedApproval.pembimbing1 ?? 'Belum ditentukan' }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-slate-200/70 bg-slate-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <span class="text-[10px] text-slate-400"
                        >Usulan Pembimbing 2</span
                    >
                    <p
                        class="text-xs font-bold text-slate-800 dark:text-slate-200"
                    >
                        {{ selectedApproval.pembimbing2 ?? 'Belum ditentukan' }}
                    </p>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-2">
                <button
                    type="button"
                    @click="showDetailModal = false"
                    class="rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                    Tutup
                </button>
                <Link
                    href="/kaprodi/persetujuan"
                    class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-blue-700"
                >
                    Buka Halaman Persetujuan
                </Link>
            </div>
        </template>
    </Modal>
</template>
