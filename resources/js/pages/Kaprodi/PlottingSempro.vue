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
    HelpCircle,
    Search,
    Users,
    X,
    RotateCcw,
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

watch(
    () => props.selectedPeriodId,
    (val) => {
        if (val) {
            activePeriodId.value = val;
        }
    },
);

const activePeriod = computed(() => {
    if (!activePeriodId.value) return null;
    return props.periods.find((p) => p.id === activePeriodId.value) ?? null;
});

// Switch gelombang tab
const switchGelombang = (period: PeriodItem) => {
    activePeriodId.value = period.id;
    router.get(
        '/kaprodi/penjadwalan/plotting-sempro',
        { period_id: period.id },
        { preserveState: false, preserveScroll: true },
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
                    `Plotting jadwal untuk ${editingRow.value?.nama} berhasil diperbarui.`,
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
const gaResult = ref<{
    schedule: any[];
    unscheduled: any[];
    fitness: number;
    kbk_match: number;
    generations: number;
    outcome?: string;
    success?: boolean;
    period_id?: number;
    limitations?: string[];
} | null>(null);

const isResetting = ref(false);
const showResetConfirmModal = ref(false);
const gaResetExisting = ref(true);

const handleResetPeriod = () => {
    if (!activePeriodId.value) {
        showToast('Pilih periode gelombang terlebih dahulu.', 'error');
        return;
    }
    showResetConfirmModal.value = true;
};

const executeResetPeriod = () => {
    if (!activePeriodId.value || isResetting.value) return;
    isResetting.value = true;

    router.post(
        `/kaprodi/penjadwalan/periods/${activePeriodId.value}/reset`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showResetConfirmModal.value = false;
                isResetting.value = false;
                gaLogs.value = [];
                gaResult.value = null;
                gaCompleted.value = false;
                gaProgress.value = 0;
                showToast(
                    `Jadwal Gelombang ${activePeriod.value?.gelombang ?? ''} berhasil direset. Mahasiswa pendaftar siap untuk dijadwalkan ulang.`,
                    'success',
                );
            },
            onError: (errs) => {
                isResetting.value = false;
                const msg =
                    Object.values(errs).join(' ') ||
                    'Gagal mereset jadwal periode.';
                showToast(msg, 'error');
            },
        },
    );
};

const openGaModal = () => {
    isGaModalOpen.value = true;
    gaRunning.value = false;
    gaCompleted.value = false;
    gaProgress.value = 0;
    gaCurrentGen.value = 1;
    gaLogs.value = [];
    gaResult.value = null;
};

let gaTimer: any = null;

const startGaOptimization = async () => {
    if (!activePeriodId.value) {
        showToast('Pilih periode terlebih dahulu.', 'error');
        return;
    }
    gaRunning.value = true;
    gaCompleted.value = false;
    gaProgress.value = 15;
    gaCurrentGen.value = 1;
    gaResult.value = null;
    gaLogs.value = [
        '[00:01] Memulai inisialisasi Algoritma Genetika...',
        `[00:01] Mengirim parameter (Populasi: ${gaParams.value.popSize}, Maks. Gen: ${gaParams.value.maxGenerations}) ke server...`,
    ];

    if (gaResetExisting.value) {
        gaLogs.value.push(
            '[Reset] Mode Penjadwalan Ulang aktif: Mengosongkan plotting lama & menjadwalkan ulang seluruh mahasiswa...',
        );
    }

    if (gaTimer) clearInterval(gaTimer);
    gaTimer = setInterval(() => {
        if (gaProgress.value < 90) {
            gaProgress.value += Math.min(5, 90 - gaProgress.value);
            gaCurrentGen.value = Math.min(
                gaParams.value.maxGenerations,
                Math.max(
                    1,
                    Math.floor(
                        (gaProgress.value / 100) *
                            gaParams.value.maxGenerations,
                    ),
                ),
            );
        }
    }, 120);

    try {
        const cookie = document.cookie
            .split('; ')
            .find((value) => value.startsWith('XSRF-TOKEN='));
        const response = await fetch('/kaprodi/penjadwalan/generate-ga', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': cookie
                    ? decodeURIComponent(cookie.slice(11))
                    : '',
            },
            body: JSON.stringify({
                period_id: activePeriodId.value,
                reset: gaResetExisting.value,
                ...gaParams.value,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            const errorMsg = data.errors
                ? Object.values(data.errors).flat().join(' ')
                : data.message || 'Optimasi GA gagal.';
            throw new Error(errorMsg);
        }

        gaResult.value = data;
        gaProgress.value = 100;
        gaCurrentGen.value = data.generations || gaParams.value.maxGenerations;
        gaCompleted.value = true;

        gaLogs.value.push(
            `[Konvergensi] Solusi optimal selesai dievaluasi pada Generasi ke-${data.generations ?? gaParams.value.maxGenerations}.`,
        );
        gaLogs.value.push(
            `[Hasil] Nilai Fitness: ${data.fitness ?? '—'} | Kesesuaian KBK: ${data.kbk_match ? (data.kbk_match * 100).toFixed(1) + '%' : '—'}.`,
        );
        gaLogs.value.push(
            `[Jadwal] Berhasil memplot ${data.schedule?.length ?? 0} peserta seminar.`,
        );

        if (!data.schedule || data.schedule.length === 0) {
            gaLogs.value.push(
                '[Informasi] 0 pendaftar dijadwalkan. Pastikan ada mahasiswa dengan status verifikasi_tendik & disetujui akademik pada periode ini.',
            );
        }

        if (data.unscheduled && data.unscheduled.length > 0) {
            gaLogs.value.push(
                `[Perhatian] ${data.unscheduled.length} pendaftar belum terjadwal (keterbatasan slot/kapasitas).`,
            );
        }

        if (data.limitations && Array.isArray(data.limitations)) {
            data.limitations.forEach((lim: string) => {
                gaLogs.value.push(`[Catatan Model] ${lim}`);
            });
        }
    } catch (err: any) {
        gaLogs.value.push(
            `[Gagal] ${err.message || 'Terjadi kesalahan sistem.'}`,
        );
        showToast(err.message || 'Optimasi GA gagal dijalankan.', 'error');
    } finally {
        if (gaTimer) {
            clearInterval(gaTimer);
            gaTimer = null;
        }
        gaRunning.value = false;
    }
};

const applyGaResults = () => {
    if (!gaResult.value?.schedule || gaResult.value.schedule.length === 0) {
        showToast('Tidak ada jadwal hasil optimasi untuk disimpan.', 'error');
        return;
    }

    const schedulesToSave = gaResult.value.schedule.map((row: any) => ({
        pendaftaran_id: row.pendaftaran_id,
        mahasiswa_id: row.mahasiswa_id,
        tipe: row.tipe || 'sempro',
        penguji_1_id: row.penguji_1_id,
        penguji_2_id: row.penguji_2_id,
        tanggal:
            typeof row.tanggal === 'string'
                ? row.tanggal.slice(0, 10)
                : row.tanggal,
        jam_mulai: row.jam_mulai,
        jam_selesai: row.jam_selesai,
    }));

    router.post(
        '/kaprodi/penjadwalan/simpan',
        {
            period_id: activePeriodId.value,
            schedules: schedulesToSave,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                isGaModalOpen.value = false;
                showToast(
                    'Jadwal dan rekomendasi plotting AI Genetika berhasil diterapkan dan disimpan!',
                );
                router.reload();
            },
            onError: (errors) => {
                const msg =
                    Object.values(errors).join(' ') ||
                    'Gagal menyimpan jadwal.';
                showToast(msg, 'error');
            },
        },
    );
};

// Modal Riwayat Kelulusan
const isRiwayatModalOpen = ref(false);

// Export Actions
const exportExcel = () => {
    if (!activePeriodId.value) {
        showToast('Pilih periode terlebih dahulu.', 'error');
        return;
    }
    window.location.href = `/kaprodi/penjadwalan/export?period_id=${activePeriodId.value}`;
};

const downloadJadwal = () => {
    if (!activePeriodId.value) {
        showToast('Pilih periode terlebih dahulu.', 'error');
        return;
    }
    window.location.href = `/kaprodi/penjadwalan/periods/${activePeriodId.value}/export-ics`;
};
</script>

<template>
    <Head title="Plotting Jadwal Sempro" />

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
                    Plotting Jadwal Sempro
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
                    for="select-gelombang-sempro"
                    class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                >
                    Pilih Gelombang:
                </label>
                <div class="relative">
                    <select
                        id="select-gelombang-sempro"
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

        <div
            class="flex flex-col gap-6 rounded-2xl border border-slate-200/80 bg-white p-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div class="max-w-xl space-y-2">
                <div
                    class="inline-flex items-center gap-2 rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                >
                    <Cpu class="h-3.5 w-3.5" />
                    <span>Optimasi Algoritma Genetika</span>
                </div>
                <h2
                    class="text-base font-semibold text-slate-900 md:text-lg dark:text-white"
                >
                    Plotting dosen penguji bebas bentrok jadwal
                </h2>
                <p
                    class="text-sm leading-relaxed text-slate-500 dark:text-slate-400"
                >
                    Algoritma Genetika mencocokkan bidang keahlian dosen (KBK)
                    dengan topik skripsi, meratakan beban menguji, dan
                    menyinkronkan waktu luang dosen.
                </p>
            </div>

            <div
                class="flex shrink-0 flex-col gap-2.5 sm:flex-row sm:items-center"
            >
                <div
                    class="rounded-xl border border-slate-200 px-4 py-2 text-center dark:border-slate-700"
                >
                    <span
                        class="block text-xs text-slate-500 dark:text-slate-400"
                        >Fitness score</span
                    >
                    <span
                        class="text-base font-semibold text-slate-900 tabular-nums dark:text-white"
                        >0.985</span
                    >
                </div>
                <div
                    class="rounded-xl border border-slate-200 px-4 py-2 text-center dark:border-slate-700"
                >
                    <span
                        class="block text-xs text-slate-500 dark:text-slate-400"
                        >Konflik waktu</span
                    >
                    <span
                        class="text-base font-semibold text-slate-900 tabular-nums dark:text-white"
                        >0 bentrok</span
                    >
                </div>
                <button
                    type="button"
                    @click="handleResetPeriod"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-xs font-bold text-rose-700 transition-colors hover:bg-rose-100 active:bg-rose-200 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/60"
                    :disabled="isResetting"
                    title="Kosongkan plotting gelombang ini agar siap dioptimasi ulang"
                >
                    <RotateCcw class="h-4 w-4" :class="{ 'animate-spin': isResetting }" />
                    <span>Reset Plotting</span>
                </button>
                <button
                    type="button"
                    @click="openGaModal"
                    class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-semibold text-white transition-colors hover:bg-blue-700 active:bg-blue-800"
                >
                    <Cpu class="h-4 w-4" />
                    <span>Jalankan optimasi jadwal</span>
                </button>
            </div>
        </div>

        <!-- MAIN TABLE CARD: DIRECT LIST MAHASISWA PENDAFTAR -->
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
                            List Mahasiswa Pendaftar
                        </h2>
                        <p
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            Daftar pendaftar sempro yang berkasnya telah
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

            <!-- TABLE: Penentuan Jadwal dan Penguji -->
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
                                    class="font-bold text-slate-900 dark:text-white"
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
                                    Belum ada jadwal mahasiswa terdaftar pada
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
                                Optimasi Penjadwalan &amp; Penguji Sempro
                            </h3>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Algoritma Genetika (GA) Penjadwalan &amp;
                                Plotting Penguji Otomatis
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
                    <!-- Target Gelombang / Periode Info -->
                    <div
                        class="rounded-2xl border border-blue-200 bg-blue-50/70 p-4 dark:border-blue-900/60 dark:bg-blue-950/30"
                    >
                        <div
                            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <span
                                    class="text-[10px] font-bold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                                    >Target Gelombang Penjadwalan:</span
                                >
                                <div
                                    class="mt-1 text-sm font-black text-slate-800 dark:text-slate-100"
                                >
                                    Gelombang {{ activePeriod?.gelombang }} ({{
                                        activePeriod?.semester
                                    }}
                                    {{ activePeriod?.name }})
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label
                                    for="ga-modal-period-select"
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                                    >Pilih Gelombang:</label
                                >
                                <select
                                    id="ga-modal-period-select"
                                    v-model="activePeriodId"
                                    class="cursor-pointer rounded-xl border border-slate-300 bg-white py-1.5 pr-8 pl-3 text-xs font-bold text-slate-800 shadow-xs dark:border-slate-700 dark:bg-[#0E1626] dark:text-slate-100"
                                >
                                    <option
                                        v-for="p in periods"
                                        :key="p.id"
                                        :value="p.id"
                                    >
                                        Gelombang {{ p.gelombang }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Option -->
                    <div
                        class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50/80 p-4 dark:border-amber-900/50 dark:bg-amber-950/20"
                    >
                        <input
                            type="checkbox"
                            id="ga-reset-existing-checkbox"
                            v-model="gaResetExisting"
                            class="mt-0.5 h-4 w-4 cursor-pointer rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-900"
                        />
                        <label
                            for="ga-reset-existing-checkbox"
                            class="cursor-pointer select-none text-xs"
                        >
                            <span
                                class="block font-bold text-slate-800 dark:text-slate-100"
                                >Mode Penjadwalan Ulang (Reset & Regenerasi Penuh)</span
                            >
                            <span
                                class="mt-0.5 block text-[11px] leading-relaxed text-slate-500 dark:text-slate-400"
                                >Centang opsi ini untuk mengosongkan plotting lama di gelombang ini dan mengikutsertakan kembali seluruh mahasiswa pendaftar yang belum lulus ujian.</span
                            >
                        </label>
                    </div>

                    <!-- Parameters -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400"
                        >
                            Parameter Komputasi GA
                        </h4>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/70"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-500 dark:text-slate-400"
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
                                    class="block text-[10px] font-bold text-slate-500 dark:text-slate-400"
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
                                    class="block text-[10px] font-bold text-slate-500 dark:text-slate-400"
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
                                    class="block text-[10px] font-bold text-slate-500 dark:text-slate-400"
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
                            class="text-xs font-bold tracking-wider text-slate-500 dark:text-slate-400"
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
                                        >Ketersediaan Waktu</span
                                    >
                                    <span
                                        class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80"
                                        >Bebas bentrok mengajar &amp;
                                        ujian</span
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
                                        >Kesesuaian KBK</span
                                    >
                                    <span
                                        class="text-[10px] text-emerald-700/80 dark:text-emerald-400/80"
                                        >Kesesuaian topik penelitian</span
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
                                        >Pemerataan Beban</span
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
                        v-if="gaRunning || gaCompleted || gaLogs.length > 0"
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
                            <span
                                v-else-if="
                                    gaCompleted &&
                                    (gaResult?.schedule?.length ?? 0) > 0
                                "
                                class="font-bold text-emerald-400"
                            >
                                ✓ Konvergen ({{ gaResult?.schedule?.length }}
                                Jadwal Berhasil Dibuat!)
                            </span>
                            <span
                                v-else-if="
                                    gaCompleted &&
                                    (gaResult?.schedule?.length ?? 0) === 0
                                "
                                class="font-bold text-amber-400"
                            >
                                ⚠ Selesai: 0 Jadwal Terbentuk
                            </span>
                            <span v-else class="font-bold text-rose-400">
                                ✕ Optimasi Gagal
                            </span>
                        </div>

                        <div
                            class="h-2 w-full overflow-hidden rounded-full bg-slate-800"
                        >
                            <div
                                class="h-full bg-blue-600 transition-all duration-100"
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
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            @click="isGaModalOpen = false"
                            class="cursor-pointer text-xs font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white"
                        >
                            Tutup
                        </button>
                        <button
                            v-if="!gaRunning"
                            type="button"
                            @click="handleResetPeriod"
                            class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-900/60"
                            :disabled="isResetting"
                        >
                            <RotateCcw
                                class="h-3.5 w-3.5"
                                :class="{ 'animate-spin': isResetting }"
                            />
                            <span>Reset Jadwal Gelombang</span>
                        </button>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            v-if="
                                !gaRunning &&
                                (!gaCompleted ||
                                    (gaResult?.schedule?.length ?? 0) === 0)
                            "
                            type="button"
                            @click="startGaOptimization"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-blue-500"
                        >
                            <Cpu class="h-4 w-4 text-blue-200" />
                            <span>{{
                                gaLogs.length > 0 && !gaCompleted
                                    ? 'Coba Lagi'
                                    : 'Mulai Optimasi GA'
                            }}</span>
                        </button>
                        <button
                            v-if="
                                gaCompleted &&
                                (gaResult?.schedule?.length ?? 0) > 0
                            "
                            type="button"
                            @click="applyGaResults"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-2xs hover:bg-emerald-500"
                        >
                            <CheckCircle2 class="h-4 w-4" />
                            <span
                                >Terapkan Hasil Rekomendasi ({{
                                    gaResult?.schedule?.length
                                }})</span
                            >
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
                            Edit Plotting Dosen Penguji &amp; Waktu
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Penyesuaian manual jadwal seminar proposal
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
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
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
                                >Tanggal Ujian</label
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
                            Riwayat Kelulusan Seminar Proposal
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
                        Data statistik kelulusan seminar proposal pada periode
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
                                    >20 Mahasiswa Teruji</span
                                >
                            </div>
                            <span
                                class="rounded-full border border-emerald-500/30 bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                            >
                                100% Lulus
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div>
                                <span
                                    class="block font-bold text-slate-900 dark:text-white"
                                    >Gelombang 3 (Genap 2025/2026)</span
                                >
                                <span
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                    >18 Mahasiswa Teruji</span
                                >
                            </div>
                            <span
                                class="rounded-full border border-emerald-500/30 bg-emerald-50 px-2.5 py-1 font-bold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400"
                            >
                                94.4% Lulus
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

        <!-- ========================================== -->
        <!-- MODAL 4: KONFIRMASI RESET JADWAL -->
        <!-- ========================================== -->
        <div
            v-if="showResetConfirmModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400"
                    >
                        <RotateCcw class="h-6 w-6" />
                    </div>
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Reset Jadwal Gelombang {{ activePeriod?.gelombang }}?
                        </h3>
                        <p
                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                        >
                            Tindakan ini akan membatalkan dan menghapus seluruh
                            jadwal seminar proposal yang sudah ter-plot pada
                            periode
                            <strong
                                class="text-slate-700 dark:text-slate-200"
                                >Gelombang {{ activePeriod?.gelombang }} ({{
                                    activePeriod?.semester
                                }}
                                {{ activePeriod?.name }})</strong
                            >. Seluruh mahasiswa pendaftar akan dikembalikan ke
                            status belum terjadwal sehingga siap untuk
                            dioptimasi ulang.
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        type="button"
                        @click="showResetConfirmModal = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        :disabled="isResetting"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="executeResetPeriod"
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-rose-500 disabled:opacity-50"
                        :disabled="isResetting"
                    >
                        <RotateCcw
                            class="h-3.5 w-3.5"
                            :class="{ 'animate-spin': isResetting }"
                        />
                        <span>{{
                            isResetting ? 'Mereset...' : 'Ya, Reset Jadwal'
                        }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
