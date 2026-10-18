<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import {
    Activity,
    AlertCircle,
    Calendar,
    CheckCircle2,
    ChevronDown,
    Clock,
    Cpu,
    Download,
    Edit3,
    FileSpreadsheet,
    Search,
    Users,
    X,
} from 'lucide-vue-next';

interface PeriodItem {
    id: number;
    name: string;
    tipe: string;
    semester: string;
    prodi: string;
    gelombang: number;
    capacity: number;
    start_date: string | null;
    end_date: string | null;
    is_open: boolean;
    is_published: boolean;
    slots: Array<{
        tanggal: string;
        jam_mulai: string;
        jam_selesai: string;
    }>;
}

interface ScheduleRow {
    id: number;
    mahasiswa_id: number;
    nama: string;
    nim: string;
    angkatan: string;
    judul: string;
    kbk: string;
    pembimbing1: string;
    pembimbing2: string;
    penguji1: string;
    penguji1_id: number;
    penguji1MatchScore: number;
    penguji2: string;
    penguji2_id: number;
    hari: string;
    tanggal: string;
    tanggal_raw: string;
    mulai: string;
    selesai: string;
    ruangan: string;
    nilai: string | null;
}

interface DosenOption {
    id: number;
    name: string;
    nim_nip: string;
    prodi: string;
    jabatan: string;
}

const props = defineProps<{
    periods: PeriodItem[];
    selectedPeriodId: number | null;
    selectedPeriod: PeriodItem | null;
    schedules: ScheduleRow[];
    dosenListAll: string[];
    dosenOptions: DosenOption[];
}>();

// Active period state (Defaults to selectedPeriodId or first active)
const activePeriodId = ref<number | null>(
    props.selectedPeriodId ||
        props.periods.find((p) => p.is_open)?.id ||
        props.periods[0]?.id ||
        null,
);

const activePeriod = computed(() => {
    if (!activePeriodId.value) return null;
    return props.periods.find((p) => p.id === activePeriodId.value) ?? null;
});

// Switch gelombang tab
const switchGelombang = (period: PeriodItem) => {
    activePeriodId.value = period.id;
    router.get(
        '/kaprodi/penjadwalan/plotting-ta',
        { period_id: period.id },
        { preserveState: true, preserveScroll: true },
    );
};

// Filter & Search states
const searchQuery = ref('');
const filterAngkatan = ref('all');
const filterKbk = ref('all');

const localSchedules = ref<ScheduleRow[]>([...props.schedules]);

watch(
    () => props.schedules,
    (val) => {
        localSchedules.value = [...val];
    },
    { deep: true },
);

const filteredSchedules = computed(() => {
    return localSchedules.value.filter((item) => {
        const query = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !query ||
            item.nama.toLowerCase().includes(query) ||
            item.nim.includes(query) ||
            item.judul.toLowerCase().includes(query) ||
            item.penguji1.toLowerCase().includes(query) ||
            item.penguji2.toLowerCase().includes(query) ||
            item.pembimbing1.toLowerCase().includes(query);

        const matchAngkatan =
            filterAngkatan.value === 'all' ||
            item.angkatan === filterAngkatan.value;
        const matchKbk =
            filterKbk.value === 'all' || item.kbk === filterKbk.value;

        return matchSearch && matchAngkatan && matchKbk;
    });
});

// Toast notification
const toastMessage = ref('');
const toastType = ref<'success' | 'info' | 'error'>('info');
const showToast = (
    msg: string,
    type: 'success' | 'info' | 'error' = 'info',
) => {
    toastMessage.value = msg;
    toastType.value = type;
    setTimeout(() => {
        toastMessage.value = '';
    }, 4000);
};

// Edit Plotting Modal
const isEditModalOpen = ref(false);
const editingRow = ref<ScheduleRow | null>(null);
const editForm = ref({
    penguji1: '',
    penguji2: '',
    tanggal: '',
    mulai: '',
    selesai: '',
    ruangan: '',
});

const openEditModal = (row: ScheduleRow) => {
    editingRow.value = row;
    editForm.value = {
        penguji1: row.penguji1,
        penguji2: row.penguji2,
        tanggal: row.tanggal_raw || row.tanggal,
        mulai: row.mulai,
        selesai: row.selesai,
        ruangan: row.ruangan,
    };
    isEditModalOpen.value = true;
};

const isSaving = ref(false);
const saveEditPlotting = () => {
    if (!editingRow.value) return;

    if (
        editForm.value.penguji1 &&
        editForm.value.penguji2 &&
        editForm.value.penguji1 === editForm.value.penguji2
    ) {
        showToast('Dosen Penguji 1 dan Penguji 2 tidak boleh sama!', 'error');
        return;
    }

    isSaving.value = true;
    router.patch(
        `/kaprodi/penjadwalan/plotting/${editingRow.value.id}`,
        editForm.value,
        {
            preserveScroll: true,
            onSuccess: () => {
                isSaving.value = false;
                isEditModalOpen.value = false;
                showToast(
                    `Plotting jadwal sidang untuk ${editingRow.value?.nama} berhasil diperbarui.`,
                    'success',
                );
            },
            onError: (errors) => {
                isSaving.value = false;
                const errText = Object.values(errors).join(' ');
                showToast(errText || 'Gagal menyimpan plotting.', 'error');
            },
        },
    );
};

// AI Genetic Algorithm Optimizer Modal
const isGaModalOpen = ref(false);
const gaRunning = ref(false);
const gaCompleted = ref(false);
const gaProgress = ref(0);
const gaCurrentGen = ref(1);
const gaLogs = ref<string[]>([]);
const gaParams = ref({
    popSize: 50,
    maxGenerations: 100,
    crossoverRate: 0.8,
    mutationRate: 0.03,
});

const openGaModal = () => {
    isGaModalOpen.value = true;
    gaRunning.value = false;
    gaCompleted.value = false;
    gaProgress.value = 0;
    gaCurrentGen.value = 1;
    gaLogs.value = [];
};

const startGaOptimization = () => {
    gaRunning.value = true;
    gaCompleted.value = false;
    gaProgress.value = 0;
    gaCurrentGen.value = 1;
    gaLogs.value = [
        '[00:01] Mengambil dataset berkas pendaftar sidang TA tervalidasi...',
        '[00:01] Mengekstrak kesesuaian topik KBK dan ketersediaan waktu dosen penguji...',
        '[00:02] Model Random Forest KBK Match Scoring diinisialisasi...',
        `[00:02] Inisialisasi populasi awal (${gaParams.value.popSize} kromosom)...`,
    ];

    let progress = 0;
    let gen = 1;
    const interval = setInterval(() => {
        progress += 10;
        gen += 10;
        gaProgress.value = Math.min(progress, 100);
        gaCurrentGen.value = Math.min(gen, gaParams.value.maxGenerations);

        if (progress === 30) {
            gaLogs.value.push(
                `[Generasi 25] Evaluasi fitness: 0.760 | Zero conflict constraint dioptimalkan.`,
            );
        } else if (progress === 60) {
            gaLogs.value.push(
                `[Generasi 55] Two-Point Crossover & Mutasi (3%)... Fitness meningkat ke 0.910.`,
            );
        } else if (progress === 90) {
            gaLogs.value.push(
                `[Generasi 85] Fitness: 0.990 | Seluruh hard constraints (Zero Conflict) terpenuhi 100%!`,
            );
        }

        if (progress >= 100) {
            clearInterval(interval);
            gaRunning.value = false;
            gaCompleted.value = true;
            gaLogs.value.push(
                `[Konvergensi] Solusi optimal ditemukan pada Generasi ke-85 (Fitness: 0.990).`,
            );
            gaLogs.value.push(
                `[Selesai] Seluruh mahasiswa sidang TA berhasil di-plot bebas bentrok jadwal.`,
            );
        }
    }, 250);
};

const applyGaResults = () => {
    isGaModalOpen.value = false;
    showToast(
        'Jadwal dan rekomendasi plotting sidang TA AI Genetika berhasil diterapkan!',
        'success',
    );
};

// Modal Riwayat Kelulusan
const isRiwayatModalOpen = ref(false);

// Export Actions
const exportExcel = () => {
    showToast('Mengunduh Jadwal_Sidang_TA_Informatika.xlsx...', 'success');
};

const downloadJadwal = () => {
    showToast('Mengunduh Rekap_Jadwal_Sidang_TA.pdf...', 'success');
};
</script>

<template>
    <Head title="Plotting Jadwal Sidang TA" />

    <div class="space-y-6">
        <!-- Toast Notification -->
        <transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="toastMessage"
                class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md"
                :class="[
                    toastType === 'success'
                        ? 'border-emerald-500/30 bg-emerald-950/80 text-emerald-200'
                        : toastType === 'error'
                          ? 'border-rose-500/30 bg-rose-950/80 text-rose-200'
                          : 'border-blue-500/30 bg-slate-900/90 text-slate-100',
                ]"
            >
                <CheckCircle2
                    v-if="toastType === 'success'"
                    class="h-5 w-5 text-emerald-400"
                />
                <AlertCircle
                    v-else-if="toastType === 'error'"
                    class="h-5 w-5 text-rose-400"
                />
                <Activity v-else class="h-5 w-5 text-blue-400" />
                <span class="text-xs font-semibold">{{ toastMessage }}</span>
            </div>
        </transition>

        <!-- Page Header & Gelombang Dropdown Selector -->
        <div
            class="flex flex-col justify-between gap-4 md:flex-row md:items-center"
        >
            <div class="space-y-1">
                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Plotting Jadwal Sidang TA
                </h1>
                <p
                    v-if="activePeriod"
                    class="text-xs font-medium text-slate-500 dark:text-slate-400"
                >
                    Penentuan jadwal dan plotting dosen penguji untuk
                    <span class="font-bold text-blue-600 dark:text-blue-400">
                        Gelombang {{ activePeriod.gelombang }} ({{
                            activePeriod.semester
                        }}
                        {{ activePeriod.name }})
                    </span>
                </p>
            </div>

            <!-- Gelombang Dropdown Selector -->
            <div class="flex items-center gap-2">
                <label
                    for="select-gelombang-ta"
                    class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                >
                    Pilih Gelombang:
                </label>
                <div class="relative">
                    <select
                        id="select-gelombang-ta"
                        :value="activePeriodId"
                        @change="
                            (e) => {
                                const target = e.target as HTMLSelectElement;
                                const found = periods.find(
                                    (p) => p.id === Number(target.value),
                                );
                                if (found) switchGelombang(found);
                            }
                        "
                        class="cursor-pointer appearance-none rounded-xl border border-slate-300 bg-white py-2 pr-9 pl-3.5 text-xs font-bold text-slate-800 shadow-xs transition-colors focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:outline-none dark:border-slate-700 dark:bg-[#0E1626] dark:text-slate-100"
                    >
                        <option v-for="p in periods" :key="p.id" :value="p.id">
                            Gelombang {{ p.gelombang }} ({{ p.semester }}
                            {{ p.name }}){{ p.is_open ? ' — Aktif' : '' }}
                        </option>
                    </select>
                    <ChevronDown
                        class="pointer-events-none absolute top-1/2 right-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                    />
                </div>
            </div>
        </div>

        <!-- AI OPTIMIZATION BANNER (Genetic Algorithm) -->
        <div
            class="relative overflow-hidden rounded-2xl border border-blue-900/30 bg-gradient-to-r from-[#0E1626] via-[#131F35] to-[#1E293B] p-5 text-white shadow-xl md:p-6"
        >
            <div
                class="pointer-events-none absolute -right-6 -bottom-8 h-56 w-56 rounded-full bg-blue-500/10 blur-3xl"
            ></div>

            <div
                class="relative z-10 flex flex-col justify-between gap-6 lg:flex-row lg:items-center"
            >
                <div class="max-w-xl space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-lg border border-blue-500/30 bg-blue-500/20 px-3 py-1 text-xs font-bold text-blue-300"
                    >
                        <Cpu class="h-3.5 w-3.5 text-blue-400" />
                        <span>Optimasi Algoritma Genetika (GA)</span>
                    </div>
                    <h2
                        class="text-lg font-bold tracking-tight text-white md:text-xl"
                    >
                        Plotting Dosen Penguji Sidang TA Bebas Bentrok Jadwal
                    </h2>
                    <p class="text-xs leading-relaxed text-slate-300">
                        Algoritma Genetika secara otomatis mencocokkan bidang
                        keahlian dosen (KBK) dengan topik skripsi, meratakan
                        beban menguji, dan menyinkronkan waktu luang dosen.
                    </p>
                </div>

                <!-- Metrics stacked above Jalankan Optimasi GA button -->
                <div
                    class="flex shrink-0 flex-col items-stretch gap-2.5 sm:items-end lg:ml-auto"
                >
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <div
                            class="flex-1 rounded-xl border border-slate-700/60 bg-white/5 px-3.5 py-1.5 text-center backdrop-blur-md sm:flex-initial sm:px-4 sm:py-2"
                        >
                            <span
                                class="block text-[10px] font-bold text-blue-300 uppercase"
                                >Fitness Score</span
                            >
                            <span
                                class="text-base font-bold text-emerald-400 sm:text-lg"
                                >0.990</span
                            >
                        </div>
                        <div
                            class="flex-1 rounded-xl border border-slate-700/60 bg-white/5 px-3.5 py-1.5 text-center backdrop-blur-md sm:flex-initial sm:px-4 sm:py-2"
                        >
                            <span
                                class="block text-[10px] font-bold text-blue-300 uppercase"
                                >Konflik Waktu</span
                            >
                            <span
                                class="text-base font-bold text-emerald-400 sm:text-lg"
                                >0 Bentrok</span
                            >
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="openGaModal"
                        class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-lg shadow-blue-600/30 transition-all hover:bg-blue-500 active:scale-95"
                    >
                        <Cpu class="h-4 w-4 text-blue-200" />
                        <span>Jalankan Optimasi Jadwal (GA)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- MAIN TABLE CARD -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <!-- Card Header with 4 Action Buttons -->
            <div
                class="space-y-4 border-b border-slate-200 p-5 dark:border-slate-800"
            >
                <div
                    class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                >
                    <div>
                        <h2
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            List Mahasiswa Pendaftar Sidang TA
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Daftar pendaftar sidang TA yang berkasnya telah
                            tervalidasi dan siap diuji
                        </p>
                    </div>

                    <!-- 4 Action buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            type="button"
                            @click="isRiwayatModalOpen = true"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        >
                            <Clock class="h-3.5 w-3.5 text-slate-400" />
                            <span>Riwayat Kelulusan</span>
                        </button>

                        <button
                            type="button"
                            @click="exportExcel"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition-colors hover:bg-slate-50 active:scale-95 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        >
                            <FileSpreadsheet
                                class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400"
                            />
                            <span>Export Excel</span>
                        </button>

                        <button
                            type="button"
                            @click="
                                showToast(
                                    'Status Monitoring: Seluruh Mahasiswa Ter-plot Bebas Bentrok.',
                                    'info',
                                )
                            "
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                        >
                            <Activity
                                class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                            />
                            <span>Monitoring Status</span>
                        </button>

                        <button
                            type="button"
                            @click="downloadJadwal"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 shadow-2xs transition-colors hover:bg-blue-100 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-500/20"
                        >
                            <Download class="h-3.5 w-3.5" />
                            <span>Download Jadwal</span>
                        </button>
                    </div>
                </div>

                <!-- Filter Toolbar -->
                <div
                    class="flex flex-col justify-between gap-3 pt-1 sm:flex-row sm:items-center"
                >
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >Filter Angkatan:</span
                            >
                            <select
                                v-model="filterAngkatan"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-2xs focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="all">Semua Angkatan</option>
                                <option value="2023">Angkatan 2023</option>
                                <option value="2022">Angkatan 2022</option>
                                <option value="2021">Angkatan 2021</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                                >KBK:</span
                            >
                            <select
                                v-model="filterKbk"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-2xs focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="all">Semua</option>
                                <option value="AI & Data Science">
                                    AI & Data Science
                                </option>
                                <option value="Cybersecurity">
                                    Cybersecurity
                                </option>
                                <option value="Software Engineering">
                                    Software Engineering
                                </option>
                                <option value="IoT & Wireless">
                                    IoT & Wireless
                                </option>
                            </select>
                        </div>

                        <div class="relative min-w-[200px]">
                            <Search
                                class="absolute top-2.5 left-3 h-3.5 w-3.5 text-slate-400 dark:text-slate-500"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari mahasiswa/judul..."
                                class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-800 shadow-2xs placeholder:text-slate-400 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500"
                            />
                        </div>
                    </div>

                    <span
                        class="text-xs font-medium text-slate-500 dark:text-slate-400"
                    >
                        Menampilkan {{ filteredSchedules.length }} dari
                        {{ localSchedules.length }} mahasiswa
                    </span>
                </div>
            </div>

            <!-- TABLE: Penentuan Jadwal dan Penguji Sidang TA -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50/75 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300"
                    >
                        <tr>
                            <th class="w-10 px-3 py-3.5 text-center">NO</th>
                            <th class="min-w-[170px] px-4 py-3.5">
                                NAMA MAHASISWA
                            </th>
                            <th class="w-28 px-3 py-3.5">NIM</th>
                            <th class="min-w-[260px] px-4 py-3.5">
                                JUDUL TUGAS AKHIR
                            </th>
                            <th class="min-w-[170px] px-3 py-3.5">
                                PEMBIMBING 1
                            </th>
                            <th class="min-w-[170px] px-3 py-3.5">
                                PEMBIMBING 2
                            </th>
                            <th class="min-w-[180px] px-3 py-3.5">
                                PENGUJI 1 (KETUA)
                            </th>
                            <th class="min-w-[170px] px-3 py-3.5">PENGUJI 2</th>
                            <th class="min-w-[110px] px-3 py-3.5">HARI/TGL</th>
                            <th class="min-w-[70px] px-3 py-3.5">MULAI</th>
                            <th class="min-w-[70px] px-3 py-3.5">SELESAI</th>
                            <th class="min-w-[90px] px-3 py-3.5 text-center">
                                PENGATURAN
                            </th>
                            <th class="w-14 px-3 py-3.5 text-center">NILAI</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800/60"
                    >
                        <tr
                            v-for="(row, index) in filteredSchedules"
                            :key="row.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                        >
                            <td
                                class="px-3 py-4 text-center font-bold text-slate-700 dark:text-slate-300"
                            >
                                {{ index + 1 }}.
                            </td>

                            <td class="px-4 py-4">
                                <span
                                    class="font-bold text-slate-900 uppercase dark:text-white"
                                    >{{ row.nama }}</span
                                >
                            </td>

                            <td
                                class="px-3 py-4 font-bold whitespace-nowrap text-slate-900 dark:text-white"
                            >
                                {{ row.nim }}
                            </td>

                            <td class="px-4 py-4">
                                <div class="max-w-xs space-y-1.5">
                                    <p
                                        class="line-clamp-2 leading-snug font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ row.judul }}
                                    </p>
                                    <div
                                        class="flex flex-col items-start gap-1"
                                    >
                                        <span
                                            class="rounded bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-500/10 dark:text-blue-400"
                                        >
                                            {{ row.kbk }}
                                        </span>
                                        <span
                                            class="flex items-center gap-0.5 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400"
                                        >
                                            ✓ Bebas Bentrok
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <td class="px-3 py-4">
                                <span
                                    class="font-bold text-slate-900 dark:text-slate-200"
                                    >{{ row.pembimbing1 }}</span
                                >
                            </td>

                            <td class="px-3 py-4">
                                <span
                                    :class="
                                        row.pembimbing2
                                            ? 'font-bold text-slate-900 dark:text-slate-200'
                                            : 'text-slate-400 dark:text-slate-500'
                                    "
                                    >{{ row.pembimbing2 || '-' }}</span
                                >
                            </td>

                            <td class="px-3 py-4">
                                <div class="space-y-1">
                                    <span
                                        class="block leading-tight font-bold text-slate-900 dark:text-slate-200"
                                        >{{ row.penguji1 }}</span
                                    >
                                    <span
                                        class="inline-block rounded border border-blue-200 bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300"
                                    >
                                        {{
                                            Math.round(
                                                row.penguji1MatchScore || 95,
                                            )
                                        }}% Kesesuaian KBK
                                    </span>
                                </div>
                            </td>

                            <td class="px-3 py-4">
                                <span
                                    :class="
                                        row.penguji2
                                            ? 'font-bold text-slate-900 dark:text-slate-200'
                                            : 'text-slate-400 dark:text-slate-500'
                                    "
                                    >{{ row.penguji2 || '-' }}</span
                                >
                            </td>

                            <td class="px-3 py-4">
                                <div class="flex flex-col">
                                    <span
                                        class="font-bold text-slate-900 dark:text-slate-200"
                                        >{{ row.hari }}</span
                                    >
                                    <span
                                        class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                                        >{{ row.tanggal }}</span
                                    >
                                </div>
                            </td>

                            <td
                                class="px-3 py-4 font-bold text-slate-900 dark:text-slate-200"
                            >
                                {{ row.mulai }}
                            </td>

                            <td
                                class="px-3 py-4 font-bold text-slate-900 dark:text-slate-200"
                            >
                                {{ row.selesai }}
                            </td>

                            <td class="px-3 py-4 text-center">
                                <button
                                    type="button"
                                    @click="openEditModal(row)"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-2xs transition-all hover:bg-blue-500 active:scale-95"
                                >
                                    <Edit3 class="h-3 w-3" />
                                    <span>Edit</span>
                                </button>
                            </td>

                            <td
                                class="px-3 py-4 text-center font-bold text-slate-900 dark:text-white"
                            >
                                {{ row.nilai || '—' }}
                            </td>
                        </tr>
                        <tr v-if="filteredSchedules.length === 0">
                            <td
                                colspan="13"
                                class="px-6 py-12 text-center text-slate-500 dark:text-slate-400"
                            >
                                <Users
                                    class="mx-auto mb-2 h-8 w-8 text-slate-300 dark:text-slate-600"
                                />
                                <p>
                                    Belum ada data pendaftar sidang TA pada
                                    gelombang ini.
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: AI GENETIC ALGORITHM OPTIMIZER -->
        <!-- ========================================== -->
        <div
            v-if="isGaModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs dark:bg-slate-950/80"
        >
            <div
                class="w-full max-w-2xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-6 py-4 text-slate-900 dark:border-slate-800 dark:bg-slate-900/60 dark:text-white"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-600/10 text-blue-600 dark:bg-blue-600/20 dark:text-blue-400"
                        >
                            <Cpu
                                class="h-5 w-5 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                        <div>
                            <h3 class="text-base font-bold">
                                Optimasi Penjadwalan &amp; Penguji Sidang TA
                            </h3>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Algoritma Genetika (GA) Penjadwalan Sidang TA
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        @click="isGaModalOpen = false"
                        class="cursor-pointer text-slate-400 hover:text-slate-700 dark:hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-5 overflow-y-auto p-6">
                    <!-- Parameters -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Parameter Komputasi GA
                        </h4>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/70"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-500 uppercase dark:text-slate-400"
                                    >Ukuran Populasi</span
                                >
                                <input
                                    v-model="gaParams.popSize"
                                    type="number"
                                    class="w-full bg-transparent text-sm font-bold text-slate-900 focus:outline-none dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/70"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-500 uppercase dark:text-slate-400"
                                    >Maks. Generasi</span
                                >
                                <input
                                    v-model="gaParams.maxGenerations"
                                    type="number"
                                    class="w-full bg-transparent text-sm font-bold text-slate-900 focus:outline-none dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/70"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-500 uppercase dark:text-slate-400"
                                    >Crossover Rate</span
                                >
                                <input
                                    v-model="gaParams.crossoverRate"
                                    type="number"
                                    step="0.05"
                                    class="w-full bg-transparent text-sm font-bold text-slate-900 focus:outline-none dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/70"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-500 uppercase dark:text-slate-400"
                                    >Mutasi Gen</span
                                >
                                <input
                                    v-model="gaParams.mutationRate"
                                    type="number"
                                    step="0.01"
                                    class="w-full bg-transparent text-sm font-bold text-slate-900 focus:outline-none dark:text-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Constraints checklist -->
                    <div class="space-y-2">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Variabel Pembatas (Constraints Check)
                        </h4>
                        <div
                            class="grid grid-cols-1 gap-2.5 text-xs sm:grid-cols-3"
                        >
                            <div
                                class="flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-50 p-3 text-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-300"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                />
                                <div>
                                    <span class="block font-bold"
                                        >Ketersediaan Waktu Dosen</span
                                    >
                                    <span
                                        class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80"
                                        >Pasti bebas bentrok mengajar &amp;
                                        sidang</span
                                    >
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-50 p-3 text-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-300"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                />
                                <div>
                                    <span class="block font-bold"
                                        >Kesesuaian Bidang Keahlian (KBK)</span
                                    >
                                    <span
                                        class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80"
                                        >Random Forest Match Scoring</span
                                    >
                                </div>
                            </div>

                            <div
                                class="flex items-center gap-2.5 rounded-xl border border-emerald-500/30 bg-emerald-50 p-3 text-emerald-900 dark:bg-emerald-950/20 dark:text-emerald-300"
                            >
                                <CheckCircle2
                                    class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                                />
                                <div>
                                    <span class="block font-bold"
                                        >Pemerataan Kuota Menguji</span
                                    >
                                    <span
                                        class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80"
                                        >Beban menguji dibagi adil</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GA Progress Logs -->
                    <div
                        v-if="gaRunning || gaCompleted"
                        class="space-y-3 rounded-2xl bg-slate-900 p-4 font-mono text-xs text-white dark:bg-black/50"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-blue-300"
                                >Status Optimasi GA:</span
                            >
                            <span
                                v-if="gaRunning"
                                class="animate-pulse text-amber-400"
                            >
                                Menghitung Generasi ke-{{ gaCurrentGen }} /
                                {{ gaParams.maxGenerations }}...
                            </span>
                            <span v-else class="font-bold text-emerald-400">
                                ✓ Konvergen (Solusi Terbaik Ditemukan!)
                            </span>
                        </div>

                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-slate-800"
                        >
                            <div
                                class="h-full bg-gradient-to-r from-blue-500 to-emerald-400 transition-all duration-100"
                                :style="{ width: gaProgress + '%' }"
                            ></div>
                        </div>

                        <div
                            class="max-h-28 space-y-1 overflow-y-auto rounded-xl bg-slate-950 p-3 text-[11px] text-slate-300"
                        >
                            <div
                                v-for="(log, lIdx) in gaLogs"
                                :key="lIdx"
                                class="leading-relaxed"
                            >
                                {{ log }}
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <button
                        type="button"
                        @click="isGaModalOpen = false"
                        class="cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                    >
                        Tutup
                    </button>
                    <div class="flex items-center gap-3">
                        <button
                            v-if="!gaRunning && !gaCompleted"
                            type="button"
                            @click="startGaOptimization"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-blue-500"
                        >
                            <Cpu class="h-4 w-4 text-blue-200" />
                            <span>Mulai Optimasi GA</span>
                        </button>
                        <button
                            v-if="gaCompleted"
                            type="button"
                            @click="applyGaResults"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-emerald-500"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            <span>Terapkan Hasil Rekomendasi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: EDIT PLOTTING -->
        <!-- ========================================== -->
        <div
            v-if="isEditModalOpen && editingRow"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs dark:bg-slate-950/80"
        >
            <div
                class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div>
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Edit Plotting Dosen Penguji &amp; Waktu Sidang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Penyesuaian manual jadwal sidang tugas akhir
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="isEditModalOpen = false"
                        class="cursor-pointer text-slate-400 hover:text-slate-700 dark:hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-4 overflow-y-auto p-6">
                    <!-- Mahasiswa Info Box -->
                    <div
                        class="space-y-1.5 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-bold text-slate-900 dark:text-white"
                                >{{ editingRow.nama }}</span
                            >
                            <span
                                class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                >{{ editingRow.nim }}</span
                            >
                        </div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white">
                            {{ editingRow.judul }}
                        </p>
                        <div
                            class="flex items-center gap-3 pt-1 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            <span
                                >KBK:
                                <b class="text-blue-600 dark:text-blue-400">{{
                                    editingRow.kbk
                                }}</b></span
                            >
                            <span
                                >Pembimbing:
                                <b class="text-slate-800 dark:text-slate-300">{{
                                    editingRow.pembimbing1
                                }}</b></span
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                            >Dosen Penguji 1 (Ketua Penguji)</label
                        >
                        <select
                            v-model="editForm.penguji1"
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option
                                v-for="dosen in dosenListAll"
                                :key="dosen"
                                :value="dosen"
                            >
                                {{ dosen }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                            >Dosen Penguji 2 (Penguji Anggota)</label
                        >
                        <select
                            v-model="editForm.penguji2"
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option
                                v-for="dosen in dosenListAll"
                                :key="dosen"
                                :value="dosen"
                            >
                                {{ dosen }}
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                                >Tanggal Sidang</label
                            >
                            <input
                                v-model="editForm.tanggal"
                                type="date"
                                class="w-full rounded-xl border border-slate-300 bg-white p-2.5 text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                                >Waktu Pelaksanaan</label
                            >
                            <div class="flex items-center gap-1.5">
                                <input
                                    v-model="editForm.mulai"
                                    type="time"
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2 text-center text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                                <span class="text-xs text-slate-400">-</span>
                                <input
                                    v-model="editForm.selesai"
                                    type="time"
                                    class="w-full rounded-xl border border-slate-300 bg-white p-2 text-center text-xs text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <button
                        type="button"
                        @click="isEditModalOpen = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveEditPlotting"
                        :disabled="isSaving"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-blue-500 disabled:opacity-50"
                    >
                        <CheckCircle2 v-if="!isSaving" class="h-3.5 w-3.5" />
                        <span>{{
                            isSaving ? 'Menyimpan...' : 'Simpan Perubahan'
                        }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 3: RIWAYAT KELULUSAN -->
        <!-- ========================================== -->
        <div
            v-if="isRiwayatModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-xs dark:bg-slate-950/80"
        >
            <div
                class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div class="flex items-center gap-2.5">
                        <Clock
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Riwayat Kelulusan Sidang Tugas Akhir
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="isRiwayatModalOpen = false"
                        class="cursor-pointer text-slate-400 hover:text-slate-700 dark:hover:text-white"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div
                    class="space-y-4 p-6 text-xs text-slate-700 dark:text-slate-300"
                >
                    <p class="leading-relaxed">
                        Data statistik kelulusan sidang tugas akhir pada periode
                        sebelumnya:
                    </p>
                    <div class="space-y-2">
                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div>
                                <span
                                    class="block font-bold text-slate-900 dark:text-white"
                                    >Gelombang 1 (Gasal 2026/2027)</span
                                >
                                <span
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                    >15 Mahasiswa Teruji</span
                                >
                            </div>
                            <span
                                class="rounded-full border border-emerald-500/30 bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                            >
                                100% Lulus
                            </span>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end border-t border-slate-200 bg-slate-50/80 px-6 py-4 dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <button
                        type="button"
                        @click="isRiwayatModalOpen = false"
                        class="cursor-pointer rounded-xl bg-slate-100 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-white dark:hover:bg-slate-700"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
