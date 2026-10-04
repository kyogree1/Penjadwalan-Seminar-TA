<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    AlertCircle,
    CalendarDays,
    Check,
    CheckCircle2,
    Clock,
    Download,
    Edit3,
    FileSpreadsheet,
    FileText,
    Lock,
    LockOpen,
    Search,
    SlidersHorizontal,
    Sparkles,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import {
    defaultGaParams,
    mockDosenListAll,
    mockJadwalGa,
    mockPeriods,
} from '@/data/koordinator/jadwalGa';
import type {
    GaOptimizationParams,
    JadwalSeminarGa,
    Period,
} from '@/types/models';

type PeriodDraft = Omit<Period, 'id' | 'isOpen'>;

const props = withDefaults(
    defineProps<{
        periods?: Period[];
        jadwalGa?: JadwalSeminarGa[];
        dosenList?: string[];
        gaParams?: GaOptimizationParams;
    }>(),
    {
        periods: () => mockPeriods,
        jadwalGa: () => mockJadwalGa,
        dosenList: () => mockDosenListAll,
        gaParams: () => defaultGaParams,
    },
);

const isDate = (value: string) =>
    /^\d{4}-\d{2}-\d{2}$/.test(value) &&
    !Number.isNaN(Date.parse(value)) &&
    new Date(value).toISOString().slice(0, 10) === value;
const isConsecutiveAcademicYear = (value: string) => {
    const match = /^(\d{4})\/(\d{4})$/.exec(value.trim());
    return Boolean(match && Number(match[2]) === Number(match[1]) + 1);
};

const validatePeriodInput = (
    draft: PeriodDraft,
    periodList: Period[],
    editingId: number | null = null,
) => {
    if (!isConsecutiveAcademicYear(draft.academicYear))
        return 'Tahun akademik harus berurutan, contoh 2026/2027.';
    if (!Number.isInteger(draft.wave) || draft.wave < 1)
        return 'Gelombang harus berupa bilangan bulat positif.';
    if (!Number.isInteger(draft.quota) || draft.quota < 1)
        return 'Kuota harus berupa bilangan bulat positif.';
    if (
        !isDate(draft.startDate) ||
        !isDate(draft.endDate) ||
        draft.startDate > draft.endDate
    )
        return 'Tanggal mulai harus sama dengan atau sebelum tanggal selesai.';

    const duplicate = periodList.some(
        (period) =>
            period.id !== editingId &&
            period.type === draft.type &&
            period.academicYear === draft.academicYear.trim() &&
            period.semester === draft.semester &&
            period.wave === draft.wave,
    );
    return duplicate
        ? 'Periode dengan jenis, tahun, semester, dan gelombang yang sama sudah ada.'
        : '';
};

const periods = ref<Period[]>([...props.periods]);

const activeTab = ref<'periods' | 'results'>('results');
const selectedPeriodId = ref(2);
const showPeriodModal = ref(false);
const editingPeriodId = ref<number | null>(null);
const periodError = ref('');
const blankPeriod = (): PeriodDraft => ({
    type: 'Sempro',
    academicYear: '2026/2027',
    semester: 'Gasal',
    wave: 1,
    quota: 20,
    startDate: '',
    endDate: '',
});
const periodDraft = ref<PeriodDraft>(blankPeriod());

const selectedPeriod = computed(() =>
    periods.value.find((period) => period.id === selectedPeriodId.value),
);

const openCreatePeriod = () => {
    editingPeriodId.value = null;
    periodDraft.value = blankPeriod();
    periodError.value = '';
    showPeriodModal.value = true;
};

const openEditPeriod = (period: Period) => {
    editingPeriodId.value = period.id;
    periodDraft.value = {
        type: period.type,
        academicYear: period.academicYear,
        semester: period.semester,
        wave: period.wave,
        quota: period.quota,
        startDate: period.startDate,
        endDate: period.endDate,
    };
    periodError.value = '';
    showPeriodModal.value = true;
};

const savePeriod = () => {
    periodDraft.value.academicYear = periodDraft.value.academicYear.trim();
    periodError.value = validatePeriodInput(
        periodDraft.value,
        periods.value,
        editingPeriodId.value,
    );
    if (periodError.value) return;

    if (editingPeriodId.value === null) {
        periods.value.push({
            ...periodDraft.value,
            id: Math.max(0, ...periods.value.map(({ id }) => id)) + 1,
            isOpen: false,
        });
    } else {
        const index = periods.value.findIndex(
            ({ id }) => id === editingPeriodId.value,
        );
        if (index !== -1)
            periods.value[index] = {
                ...periods.value[index],
                ...periodDraft.value,
            };
    }
    showPeriodModal.value = false;
};

const togglePeriod = (period: Period) => {
    period.isOpen = !period.isOpen;
};

const viewResults = (period: Period) => {
    selectedPeriodId.value = period.id;
    activeTab.value = 'results';
};

/* --------------------------------------------------------------- *
 * AI Genetic Algorithm Scheduling & Plotting Logic
 * --------------------------------------------------------------- */
const dosenListAll = ref<string[]>([...props.dosenList]);
const jadwalList = ref<JadwalSeminarGa[]>([...props.jadwalGa]);

const searchQuery = ref('');
const filterAngkatan = ref('all');
const filterKbk = ref('all');

const filteredJadwalList = computed(() => {
    return jadwalList.value.filter((item) => {
        const query = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !query ||
            item.nama.toLowerCase().includes(query) ||
            item.nim.includes(query) ||
            item.judul.toLowerCase().includes(query) ||
            item.penguji1.toLowerCase().includes(query) ||
            item.penguji2.toLowerCase().includes(query);

        const matchAngkatan =
            filterAngkatan.value === 'all' ||
            item.angkatan === filterAngkatan.value;
        const matchKbk =
            filterKbk.value === 'all' || item.kbk === filterKbk.value;

        return matchSearch && matchAngkatan && matchKbk;
    });
});

// GA Modal & Optimization Engine Simulation
const isGaModalOpen = ref(false);
const gaRunning = ref(false);
const gaCompleted = ref(false);
const gaProgress = ref(0);
const gaCurrentGen = ref(1);
const gaLogs = ref<string[]>([]);
const gaParams = ref<GaOptimizationParams>({ ...props.gaParams });

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
        `[00:01] Mengambil dataset seminar validasi (${jadwalList.value.length} berkas tervalidasi).`,
        `[00:01] Mengekstrak waktu luang & ketersediaan dosen penguji...`,
        `[00:02] Menghitung Random Forest Topic Compatibility Score (.pkl model)...`,
        `[00:02] Inisialisasi populasi awal (${gaParams.value.popSize} kromosom)...`,
    ];

    let progress = 0;
    let gen = 1;
    const interval = setInterval(() => {
        progress += 10;
        gen += 10;
        gaProgress.value = Math.min(progress, 100);
        gaCurrentGen.value = Math.min(gen, gaParams.value.maxGenerations);

        if (progress === 30)
            gaLogs.value.push(
                `[Generasi 25] Fitness: 0.742 | 2 bentrok waktu pada Penguji 1.`,
            );
        else if (progress === 60)
            gaLogs.value.push(
                `[Generasi 55] Two-Point Crossover & Mutasi (3%)... Fitness: 0.890.`,
            );
        else if (progress === 90)
            gaLogs.value.push(
                `[Generasi 85] Fitness: 0.985 | Zero Conflict Constraint terpenuhi 100%!`,
            );

        if (progress >= 100) {
            clearInterval(interval);
            gaRunning.value = false;
            gaCompleted.value = true;
            gaLogs.value.push(
                `[Konvergensi] Solusi optimal ditemukan pada Generasi ke-88 (Fitness: 0.985).`,
            );
            gaLogs.value.push(
                `[Selesai] ${jadwalList.value.length}/${jadwalList.value.length} Mahasiswa berhasil di-plot bebas bentrok jadwal.`,
            );
        }
    }, 250);
};

const actionNotice = ref('');
const showToast = (msg: string) => {
    actionNotice.value = msg;
    setTimeout(() => {
        actionNotice.value = '';
    }, 3500);
};

const applyGaResults = () => {
    isGaModalOpen.value = false;
    showToast(
        'Berhasil menerapkan jadwal dan plotting rekomendasi AI Genetika!',
    );
};

// Edit Plotting Modal
const isEditModalOpen = ref(false);
const selectedStudent = ref<JadwalSeminarGa | null>(null);
const editForm = ref({
    penguji1: '',
    penguji2: '',
    tanggal: '',
    mulai: '',
    selesai: '',
});
const editError = ref('');

const openEditPlotting = (student: JadwalSeminarGa) => {
    selectedStudent.value = student;
    editForm.value = {
        penguji1: student.penguji1,
        penguji2: student.penguji2,
        tanggal: student.tanggal,
        mulai: student.mulai,
        selesai: student.selesai,
    };
    editError.value = '';
    isEditModalOpen.value = true;
};

const saveEditPlotting = () => {
    if (!selectedStudent.value) return;
    if (editForm.value.penguji1 === editForm.value.penguji2) {
        editError.value =
            'Penguji 1 dan Penguji 2 tidak boleh dosen yang sama!';
        return;
    }
    selectedStudent.value.penguji1 = editForm.value.penguji1;
    selectedStudent.value.penguji2 = editForm.value.penguji2;
    selectedStudent.value.tanggal = editForm.value.tanggal;
    selectedStudent.value.mulai = editForm.value.mulai;
    selectedStudent.value.selesai = editForm.value.selesai;
    isEditModalOpen.value = false;
    showToast(
        `Plotting seminar untuk ${selectedStudent.value.nama} berhasil diperbarui!`,
    );
};
</script>

<template>
    <AppLayout title="Penjadwalan Ujian">
        <Head title="Penjadwalan Ujian • Koordinator TA ITK" />

        <div class="space-y-6">
            <PageHeaderBox
                title="Periode & Hasil Penjadwalan AI"
                subtitle="Kelola periode gelombang Sempro/Sidang, jalankan penjadwalan Algoritma Genetika (GA), dan lakukan plotting penguji otomatis."
            >
                <template #action>
                    <Button
                        v-if="activeTab === 'periods'"
                        variant="primary"
                        size="sm"
                        @click="openCreatePeriod"
                    >
                        <CalendarDays class="mr-1.5 h-4 w-4" />
                        Tambah Periode
                    </Button>
                    <Button
                        v-else
                        variant="primary"
                        size="sm"
                        @click="openGaModal"
                    >
                        <Sparkles class="mr-1.5 h-4 w-4 text-amber-300" />
                        Jalankan Optimasi AI (GA)
                    </Button>
                </template>
            </PageHeaderBox>

            <!-- Toast Notice -->
            <div
                v-if="actionNotice"
                class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-300"
            >
                {{ actionNotice }}
            </div>

            <!-- Tab Selection -->
            <div class="flex gap-2" role="tablist" aria-label="Penjadwalan">
                <Button
                    variant="outline"
                    role="tab"
                    :class="[
                        activeTab === 'results'
                            ? 'border-blue-600 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-950/50 dark:text-blue-300'
                            : '',
                    ]"
                    :aria-selected="activeTab === 'results'"
                    @click="activeTab = 'results'"
                >
                    <Sparkles class="mr-1.5 h-3.5 w-3.5" />
                    Hasil Jadwal & Optimasi AI (GA)
                </Button>
                <Button
                    variant="outline"
                    role="tab"
                    :class="[
                        activeTab === 'periods'
                            ? 'border-blue-600 bg-blue-50 text-blue-700 dark:border-blue-700 dark:bg-blue-950/50 dark:text-blue-300'
                            : '',
                    ]"
                    :aria-selected="activeTab === 'periods'"
                    @click="activeTab = 'periods'"
                >
                    <CalendarDays class="mr-1.5 h-3.5 w-3.5" />
                    Kelola Periode Gelombang
                </Button>
            </div>

            <!-- TAB 1: KELOLA PERIODE -->
            <Card v-if="activeTab === 'periods'" class="overflow-hidden">
                <div
                    class="flex items-center gap-3 border-b border-slate-200 p-5 dark:border-slate-800"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <CalendarDays class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Periode Sempro & Sidang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Periode dibedakan berdasarkan jenis ujian, semester,
                            tahun akademik, dan gelombang.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-xs">
                        <thead
                            class="bg-slate-50 text-slate-500 dark:bg-slate-900/50 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-3">Jenis</th>
                                <th class="p-3">Tahun Akademik</th>
                                <th class="p-3">Semester</th>
                                <th class="p-3">Gelombang</th>
                                <th class="p-3">Kuota</th>
                                <th class="p-3">Rentang Tanggal</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="period in periods"
                                :key="period.id"
                                class="border-t border-slate-200 dark:border-slate-800"
                            >
                                <td class="p-3 font-semibold">
                                    {{ period.type }}
                                </td>
                                <td class="p-3">{{ period.academicYear }}</td>
                                <td class="p-3">{{ period.semester }}</td>
                                <td class="p-3">{{ period.wave }}</td>
                                <td class="p-3">{{ period.quota }} peserta</td>
                                <td class="p-3">
                                    {{ period.startDate }} –
                                    {{ period.endDate }}
                                </td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-1 font-semibold"
                                        :class="
                                            period.isOpen
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        {{
                                            period.isOpen ? 'Dibuka' : 'Ditutup'
                                        }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="viewResults(period)"
                                        >
                                            Lihat Hasil
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            :aria-label="`Edit periode ${period.type} gelombang ${period.wave}`"
                                            @click="openEditPeriod(period)"
                                        >
                                            <Edit3 class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="togglePeriod(period)"
                                        >
                                            <Lock
                                                v-if="period.isOpen"
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            <LockOpen
                                                v-else
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            {{
                                                period.isOpen ? 'Tutup' : 'Buka'
                                            }}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <!-- TAB 2: HASIL JADWAL & OPTIMASI AI (GA) -->
            <div v-else class="space-y-6">
                <!-- AI Genetic Algorithm Top Banner Card -->
                <div
                    class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 p-6 text-white shadow-md"
                >
                    <div
                        class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
                    >
                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-md bg-amber-400/20 px-2 py-0.5 text-[10px] font-bold tracking-wider text-amber-300 uppercase backdrop-blur-sm"
                                >
                                    AI Scheduling Engine v2.1
                                </span>
                                <span class="text-xs text-blue-200">
                                    • Gasal 2026/2027 Gel. 2
                                </span>
                            </div>
                            <h2 class="text-xl font-black tracking-tight">
                                Penentuan Jadwal & Plotting Penguji Otomatis
                            </h2>
                            <p
                                class="max-w-2xl text-xs leading-relaxed text-slate-300 md:text-sm"
                            >
                                Algoritma Genetika secara otomatis mencocokkan
                                bidang keahlian dosen (KBK) dengan topik
                                skripsi, meratakan beban menguji, dan
                                menyinkronkan waktu luang dosen
                                <span class="font-semibold text-amber-300">
                                    • Sesuai arahan pembimbing, batasan ruangan
                                    fisik ditiadakan </span
                                >.
                            </p>
                        </div>

                        <!-- KPI Stats & Trigger Button -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div
                                class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-center backdrop-blur-md"
                            >
                                <span
                                    class="block text-[10px] font-bold text-blue-200 uppercase"
                                    >Fitness Score</span
                                >
                                <span
                                    class="text-lg font-black text-emerald-400"
                                    >0.985</span
                                >
                            </div>
                            <div
                                class="rounded-xl border border-white/10 bg-white/10 px-4 py-2 text-center backdrop-blur-md"
                            >
                                <span
                                    class="block text-[10px] font-bold text-blue-200 uppercase"
                                    >Konflik Waktu</span
                                >
                                <span
                                    class="text-lg font-black text-emerald-400"
                                    >0 Bentrok</span
                                >
                            </div>
                            <button
                                type="button"
                                class="flex cursor-pointer items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-3 text-xs font-extrabold text-white shadow-lg shadow-blue-900/40 transition-all hover:from-blue-500 hover:to-indigo-500 active:scale-95 md:text-sm"
                                @click="openGaModal"
                            >
                                <Sparkles class="h-4 w-4 text-amber-300" />
                                <span>✨ Jalankan Optimasi AI (GA)</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Table Card -->
                <Card class="overflow-hidden">
                    <div
                        class="space-y-4 border-b border-slate-200 p-5 dark:border-slate-800"
                    >
                        <div
                            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
                        >
                            <div>
                                <h3
                                    class="text-base font-bold text-slate-900 dark:text-white"
                                >
                                    List Mahasiswa Pendaftar
                                </h3>
                                <p class="text-xs text-slate-400">
                                    Daftar pendaftar sempro yang berkasnya telah
                                    tervalidasi dan siap diuji
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex flex-wrap items-center gap-2">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="
                                        showToast(
                                            'Melihat riwayat kelulusan sempro periode lalu.',
                                        )
                                    "
                                >
                                    <Clock class="mr-1.5 h-3.5 w-3.5" />
                                    Riwayat Kelulusan
                                </Button>
                                <button
                                    type="button"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white shadow-2xs transition-colors hover:bg-emerald-700"
                                    @click="
                                        showToast(
                                            'Mengunduh Jadwal_Sempro_Gelombang2_Informatika.xlsx...',
                                        )
                                    "
                                >
                                    <FileSpreadsheet class="h-3.5 w-3.5" />
                                    Export Excel
                                </button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="
                                        showToast(
                                            'Status Monitoring: Seluruh Mahasiswa Ter-plot Bebas Bentrok.',
                                        )
                                    "
                                >
                                    <CheckCircle2
                                        class="mr-1.5 h-3.5 w-3.5 text-blue-600"
                                    />
                                    Monitoring Status
                                </Button>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    class="bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300"
                                    @click="
                                        showToast(
                                            'Mengunduh Surat_Tugas_Dan_Jadwal_Sempro.pdf...',
                                        )
                                    "
                                >
                                    <Download class="mr-1.5 h-3.5 w-3.5" />
                                    Download Jadwal
                                </Button>
                            </div>
                        </div>

                        <!-- Filter Toolbar -->
                        <div
                            class="flex flex-col justify-between gap-3 pt-2 sm:flex-row sm:items-center"
                        >
                            <div class="flex flex-wrap items-center gap-3">
                                <!-- Search -->
                                <div class="relative w-64">
                                    <Search
                                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                                    />
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Cari nama, NIM, atau judul..."
                                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                    />
                                </div>

                                <!-- Filter Angkatan -->
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-medium text-slate-500"
                                        >Angkatan:</span
                                    >
                                    <select
                                        v-model="filterAngkatan"
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="all">
                                            Semua Angkatan
                                        </option>
                                        <option value="2023">
                                            Angkatan 2023
                                        </option>
                                        <option value="2022">
                                            Angkatan 2022
                                        </option>
                                        <option value="2021">
                                            Angkatan 2021
                                        </option>
                                    </select>
                                </div>

                                <!-- Filter KBK -->
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xs font-medium text-slate-500"
                                        >KBK:</span
                                    >
                                    <select
                                        v-model="filterKbk"
                                        class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        <option value="all">Semua KBK</option>
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
                            </div>

                            <span class="text-xs text-slate-500">
                                Menampilkan {{ filteredJadwalList.length }} dari
                                {{ jadwalList.length }} mahasiswa
                            </span>
                        </div>
                    </div>

                    <!-- The Table (Tanpa Ruangan Fisik per Spesifikasi GA Proposal) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="w-10 px-3 py-3.5 text-center">
                                        NO
                                    </th>
                                    <th class="min-w-[170px] px-4 py-3.5">
                                        MAHASISWA
                                    </th>
                                    <th class="min-w-[280px] px-4 py-3.5">
                                        JUDUL TUGAS AKHIR
                                    </th>
                                    <th class="min-w-[170px] px-3 py-3.5">
                                        PEMBIMBING
                                    </th>
                                    <th class="min-w-[170px] px-3 py-3.5">
                                        PENGUJI 1 (KETUA)
                                    </th>
                                    <th class="min-w-[170px] px-3 py-3.5">
                                        PENGUJI 2
                                    </th>
                                    <th class="min-w-[120px] px-3 py-3.5">
                                        HARI / TGL
                                    </th>
                                    <th class="min-w-[110px] px-3 py-3.5">
                                        WAKTU
                                    </th>
                                    <th
                                        class="min-w-[100px] px-3 py-3.5 text-center"
                                    >
                                        PENGATURAN
                                    </th>
                                    <th class="w-16 px-3 py-3.5 text-center">
                                        NILAI
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800/60"
                            >
                                <tr
                                    v-for="(row, index) in filteredJadwalList"
                                    :key="row.id"
                                    class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                                >
                                    <td
                                        class="px-3 py-4 text-center font-bold text-slate-400"
                                    >
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <p
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ row.nama }}
                                        </p>
                                        <p class="font-mono text-slate-400">
                                            {{ row.nim }}
                                        </p>
                                    </td>
                                    <td class="max-w-xs px-4 py-4">
                                        <p
                                            class="line-clamp-2 font-medium text-slate-800 dark:text-slate-200"
                                        >
                                            {{ row.judul }}
                                        </p>
                                        <span
                                            class="mt-1 inline-block rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            {{ row.kbk }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ row.pembimbing1 }}
                                        </p>
                                        <p
                                            v-if="row.pembimbing2"
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{ row.pembimbing2 }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <div class="space-y-1">
                                            <span
                                                class="block text-[11px] leading-tight font-bold text-blue-900 dark:text-blue-300"
                                            >
                                                {{ row.penguji1 }}
                                            </span>
                                            <span
                                                class="inline-flex items-center gap-1 rounded border border-purple-200 bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/50 dark:text-purple-300"
                                            >
                                                <Check
                                                    class="h-3 w-3 text-purple-600"
                                                />
                                                <span
                                                    >{{
                                                        row.penguji1MatchScore
                                                    }}% Match KBK</span
                                                >
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4">
                                        <p
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ row.penguji2 }}
                                        </p>
                                        <span class="text-[10px] text-slate-400"
                                            >Penguji Anggota</span
                                        >
                                    </td>
                                    <td class="px-3 py-4">
                                        <p
                                            class="font-bold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ row.hari }}
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ row.tanggal }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-4">
                                        <span
                                            class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 font-mono text-[11px] font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            {{ row.mulai }} - {{ row.selesai }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <Button
                                            size="sm"
                                            variant="primary"
                                            @click="openEditPlotting(row)"
                                        >
                                            <Edit3 class="mr-1 h-3.5 w-3.5" />
                                            Edit
                                        </Button>
                                    </td>
                                    <td class="px-3 py-4 text-center">
                                        <span
                                            v-if="row.nilai"
                                            class="font-bold text-emerald-600 dark:text-emerald-400"
                                        >
                                            {{ row.nilai }}
                                        </span>
                                        <span
                                            v-else
                                            class="font-bold text-slate-400"
                                            >—</span
                                        >
                                    </td>
                                </tr>
                                <tr v-if="filteredJadwalList.length === 0">
                                    <td
                                        colspan="10"
                                        class="p-8 text-center text-slate-500"
                                    >
                                        Tidak ada jadwal seminar yang sesuai
                                        dengan filter pencarian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div
                        class="flex flex-col items-center justify-between gap-2 border-t border-slate-200 bg-slate-50 p-4 text-xs text-slate-500 sm:flex-row dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400"
                    >
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-blue-500" />
                            <span
                                >Model Rekomendasi:
                                <strong
                                    >Genetic Algorithm (GA) v2.1 + Random Forest
                                    Topic Compatibility</strong
                                ></span
                            >
                        </div>
                        <div class="text-[11px]">
                            Menampilkan 1 - {{ filteredJadwalList.length }} dari
                            {{ jadwalList.length }} hasil
                        </div>
                    </div>
                </Card>
            </div>
        </div>

        <!-- MODAL 1: ADD/EDIT PERIOD -->
        <Modal
            :show="showPeriodModal"
            max-width="lg"
            @close="showPeriodModal = false"
        >
            <form class="space-y-4 p-6" @submit.prevent="savePeriod">
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        {{ editingPeriodId === null ? 'Tambah' : 'Edit' }}
                        Periode
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Semua kolom wajib diisi.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-xs font-semibold">
                        Jenis ujian
                        <select
                            v-model="periodDraft.type"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <option value="Sempro">Sempro</option>
                            <option value="Sidang">Sidang</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold">
                        Tahun akademik
                        <input
                            v-model="periodDraft.academicYear"
                            required
                            placeholder="2026/2027"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Semester
                        <select
                            v-model="periodDraft.semester"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <option value="Gasal">Gasal</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold">
                        Gelombang
                        <input
                            v-model.number="periodDraft.wave"
                            required
                            type="number"
                            min="1"
                            step="1"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Kuota peserta
                        <input
                            v-model.number="periodDraft.quota"
                            required
                            type="number"
                            min="1"
                            step="1"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <div />
                    <label class="text-xs font-semibold">
                        Tanggal mulai
                        <input
                            v-model="periodDraft.startDate"
                            required
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Tanggal selesai
                        <input
                            v-model="periodDraft.endDate"
                            required
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                </div>
                <p
                    v-if="periodError"
                    role="alert"
                    class="text-xs font-semibold text-rose-600"
                >
                    {{ periodError }}
                </p>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showPeriodModal = false"
                    >
                        Batal
                    </Button>
                    <Button type="submit" variant="primary">Simpan</Button>
                </div>
            </form>
        </Modal>

        <!-- MODAL 2: AI GENETIC ALGORITHM OPTIMIZER -->
        <Modal
            :show="isGaModalOpen"
            max-width="2xl"
            @close="isGaModalOpen = false"
        >
            <div class="overflow-hidden">
                <div
                    class="flex items-center justify-between bg-gradient-to-r from-blue-900 to-indigo-900 px-6 py-5 text-white"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10"
                        >
                            <Sparkles class="h-6 w-6 text-amber-300" />
                        </div>
                        <div>
                            <h3 class="text-base font-black">
                                AI Schedule & Examiner Optimizer
                            </h3>
                            <p class="text-xs text-blue-200">
                                Algoritma Genetika (GA) Penjadwalan & Plotting
                                Penguji Otomatis
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer text-xl text-white/70 hover:text-white"
                        @click="isGaModalOpen = false"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-6 overflow-y-auto p-6">
                    <!-- Parameters -->
                    <div class="space-y-3">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Parameter Komputasi GA (Proposal Hal. 40)
                        </h4>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-400 uppercase"
                                    >Ukuran Populasi</span
                                >
                                <input
                                    v-model.number="gaParams.popSize"
                                    type="number"
                                    class="w-full bg-transparent text-sm font-black text-slate-800 focus:outline-hidden dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-400 uppercase"
                                    >Maks. Generasi</span
                                >
                                <input
                                    v-model.number="gaParams.maxGenerations"
                                    type="number"
                                    class="w-full bg-transparent text-sm font-black text-slate-800 focus:outline-hidden dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-400 uppercase"
                                    >Crossover Rate</span
                                >
                                <input
                                    v-model="gaParams.crossoverRate"
                                    type="text"
                                    class="w-full bg-transparent text-sm font-black text-slate-800 focus:outline-hidden dark:text-white"
                                />
                            </div>
                            <div
                                class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/60"
                            >
                                <span
                                    class="block text-[10px] font-bold text-slate-400 uppercase"
                                    >Mutasi Gen</span
                                >
                                <input
                                    v-model="gaParams.mutationRate"
                                    type="text"
                                    class="w-full bg-transparent text-sm font-black text-slate-800 focus:outline-hidden dark:text-white"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Action Trigger -->
                    <div
                        v-if="!gaRunning && !gaCompleted"
                        class="rounded-2xl border border-dashed border-slate-300 p-6 text-center dark:border-slate-700"
                    >
                        <p class="text-xs text-slate-500">
                            Sistem akan mengoptimasi jadwal untuk
                            <strong>{{ jadwalList.length }} mahasiswa</strong>
                            dengan mencocokkan bidang keahlian dosen (KBK) dan
                            jadwal luang bebas konflik.
                        </p>
                        <button
                            type="button"
                            class="mt-4 inline-flex cursor-pointer items-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-xs font-bold text-white shadow-lg transition-all hover:bg-blue-500 active:scale-95"
                            @click="startGaOptimization"
                        >
                            <Sparkles class="h-4 w-4 text-amber-300" />
                            <span>Mulai Komputasi Genetika</span>
                        </button>
                    </div>

                    <!-- Realtime Execution Terminal & Progress -->
                    <div v-if="gaRunning || gaCompleted" class="space-y-4">
                        <div class="space-y-2">
                            <div
                                class="flex items-center justify-between text-xs font-bold"
                            >
                                <span
                                    class="text-slate-600 dark:text-slate-300"
                                >
                                    {{
                                        gaRunning
                                            ? `Optimasi Berjalan (Generasi ${gaCurrentGen}/${gaParams.maxGenerations})...`
                                            : 'Optimasi Selesai (Konvergensi Ditemukan)'
                                    }}
                                </span>
                                <span class="text-blue-600 dark:text-blue-400">
                                    {{ gaProgress }}%
                                </span>
                            </div>
                            <div
                                class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                            >
                                <div
                                    class="h-full bg-gradient-to-r from-blue-600 to-indigo-600 transition-all duration-300"
                                    :style="{ width: `${gaProgress}%` }"
                                />
                            </div>
                        </div>

                        <!-- Logs Console -->
                        <div
                            class="space-y-1.5 rounded-2xl bg-slate-950 p-4 font-mono text-[11px] text-slate-300 shadow-inner"
                        >
                            <div
                                v-for="(log, idx) in gaLogs"
                                :key="idx"
                                class="flex items-start gap-2"
                                :class="{
                                    'font-bold text-emerald-400':
                                        log.includes('Fitness') ||
                                        log.includes('Selesai'),
                                    'text-amber-300': log.includes('bentrok'),
                                }"
                            >
                                <span class="text-slate-600">›</span>
                                <span>{{ log }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    v-if="gaCompleted"
                    class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <Button variant="outline" @click="isGaModalOpen = false">
                        Tutup
                    </Button>
                    <Button variant="primary" @click="applyGaResults">
                        <Check class="mr-1.5 h-4 w-4" />
                        Terapkan Hasil Rekomendasi ke Jadwal
                    </Button>
                </div>
            </div>
        </Modal>

        <!-- MODAL 3: MANUAL EDIT PLOTTING -->
        <Modal
            :show="isEditModalOpen && selectedStudent !== null"
            max-width="lg"
            @close="isEditModalOpen = false"
        >
            <div v-if="selectedStudent" class="overflow-hidden">
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800"
                >
                    <div>
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Edit Plotting Dosen Penguji & Waktu Ujian
                        </h3>
                        <p class="text-xs text-slate-500">
                            Penyesuaian manual jadwal seminar proposal
                        </p>
                    </div>
                    <button
                        type="button"
                        class="cursor-pointer text-slate-400 hover:text-slate-600"
                        @click="isEditModalOpen = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="max-h-[75vh] space-y-4 overflow-y-auto p-6">
                    <div
                        class="space-y-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-xs dark:border-slate-700/60 dark:bg-slate-800/60"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="font-bold text-slate-900 dark:text-white"
                                >{{ selectedStudent.nama }}</span
                            >
                            <span class="font-mono text-slate-500">{{
                                selectedStudent.nim
                            }}</span>
                        </div>
                        <p
                            class="font-medium text-slate-700 dark:text-slate-300"
                        >
                            {{ selectedStudent.judul }}
                        </p>
                        <div
                            class="flex items-center gap-3 pt-1 text-[11px] text-slate-500"
                        >
                            <span
                                >KBK:
                                <b class="text-blue-600 dark:text-blue-400">{{
                                    selectedStudent.kbk
                                }}</b></span
                            >
                            <span
                                >Pembimbing:
                                <b class="text-slate-700 dark:text-slate-300">{{
                                    selectedStudent.pembimbing1
                                }}</b></span
                            >
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Dosen Penguji 1 (Ketua Penguji)
                        </label>
                        <select
                            v-model="editForm.penguji1"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                        >
                            Dosen Penguji 2 (Penguji Anggota)
                        </label>
                        <select
                            v-model="editForm.penguji2"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
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
                            >
                                Hari & Tanggal
                            </label>
                            <input
                                v-model="editForm.tanggal"
                                type="text"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                            />
                        </div>
                        <div class="space-y-1.5">
                            <label
                                class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                            >
                                Jam Pelaksanaan
                            </label>
                            <div class="flex items-center gap-1.5">
                                <input
                                    v-model="editForm.mulai"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-center font-mono text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                />
                                <span class="text-xs text-slate-400">-</span>
                                <input
                                    v-model="editForm.selesai"
                                    type="text"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-center font-mono text-xs text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                                />
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="editError"
                        class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 p-3 text-xs font-bold text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/60 dark:text-rose-300"
                    >
                        <AlertCircle class="h-4 w-4 shrink-0" />
                        <span>{{ editError }}</span>
                    </div>

                    <div
                        class="flex items-start gap-2 rounded-xl border border-blue-200 bg-blue-50 p-3 text-[11px] text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-300"
                    >
                        <span class="text-base">💡</span>
                        <span
                            ><strong>Bebas Batasan Ruang:</strong> Jadwal
                            disinkronkan berdasarkan ketersediaan waktu dosen &
                            mahasiswa tanpa alokasi ruangan fisik.</span
                        >
                    </div>
                </div>

                <div
                    class="flex justify-end gap-3 border-t border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <Button variant="outline" @click="isEditModalOpen = false">
                        Batal
                    </Button>
                    <Button variant="primary" @click="saveEditPlotting">
                        Simpan Penyesuaian
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
