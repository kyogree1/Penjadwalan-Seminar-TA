<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Calendar,
    CheckCircle2,
    Clock,
    Download,
    Edit2,
    ExternalLink,
    Eye,
    EyeOff,
    Layers,
    Pause,
    Play,
    Plus,
    Power,
    Trash2,
    Users,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import Modal from '@/Components/Modal.vue';
import Toast from '@/Components/Toast.vue';

interface StudentCandidate {
    id: number;
    mahasiswa_id: number;
    name: string;
    nim: string;
    prodi: string;
    judul_ta: string;
    status: string;
    status_akademik: string;
}

interface ScheduleItem {
    id: number;
    mahasiswa: string;
    nim: string;
    judul: string;
    pembimbing_1: string;
    pembimbing_2: string;
    penguji_1: string;
    penguji_2: string;
    ruangan: string;
    tanggal: string;
    jam_mulai: string;
    jam_selesai: string;
}

interface PeriodItem {
    id: number;
    name: string;
    tipe: 'sempro' | 'sidang';
    semester: string;
    prodi: string;
    gelombang: number;
    capacity: number;
    start_date: string | null;
    end_date: string | null;
    is_open: boolean;
    is_published: boolean;
    google_calendar_synced: boolean;
    google_calendar_synced_at: string | null;
    slots: Array<{ tanggal: string; jam_mulai: string; jam_selesai: string }>;
    mahasiswa_count: number;
    mahasiswa_list: StudentCandidate[];
    schedules: ScheduleItem[];
}

const props = defineProps<{
    periods: PeriodItem[];
    activePeriod: PeriodItem | null;
}>();

// Toast State
const toast = ref({
    show: false,
    message: '',
    isError: false,
});

const showToast = (message: string, isError = false) => {
    toast.value = { show: true, message, isError };
    setTimeout(() => {
        toast.value.show = false;
    }, 4000);
};

const formatPeriodYear = (name?: string) => {
    if (!name) return '2026/2027';
    const match = name.match(/\d{4}\/\d{4}/);
    return match ? match[0] : name;
};

// Active Period & Gelombang
const activePeriodItem = computed(
    () => props.activePeriod ?? props.periods.find((p) => p.is_open) ?? null,
);
const activeGelombangNum = computed(
    () => activePeriodItem.value?.gelombang ?? 1,
);

// Modal Tambah / Edit
const showFormModal = ref(false);
const isEditing = ref(false);
const editingPeriodId = ref<number | null>(null);
const formLoading = ref(false);

const formData = ref({
    semester: 'Gasal',
    periode: '2026/2027',
    gelombang: 1,
    capacity: 25,
    start_date: new Date().toISOString().slice(0, 10),
    end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
        .toISOString()
        .slice(0, 10),
    is_open: false,
});

const openAddModal = () => {
    isEditing.value = false;
    editingPeriodId.value = null;
    const nextWave =
        props.periods.length > 0
            ? Math.max(...props.periods.map((p) => p.gelombang)) + 1
            : 1;
    formData.value = {
        semester: 'Gasal',
        periode: '2026/2027',
        gelombang: nextWave,
        capacity: 25,
        start_date: new Date().toISOString().slice(0, 10),
        end_date: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
            .toISOString()
            .slice(0, 10),
        is_open: false,
    };
    showFormModal.value = true;
};

const openEditModal = (period: PeriodItem) => {
    isEditing.value = true;
    editingPeriodId.value = period.id;
    formData.value = {
        semester: period.semester || 'Gasal',
        periode: period.name || '2026/2027',
        gelombang: period.gelombang,
        capacity: period.capacity || 25,
        start_date: period.start_date || new Date().toISOString().slice(0, 10),
        end_date:
            period.end_date ||
            new Date(Date.now() + 30 * 24 * 60 * 60 * 1000)
                .toISOString()
                .slice(0, 10),
        is_open: period.is_open,
    };
    showFormModal.value = true;
};

const submitForm = () => {
    if (formLoading.value) return;
    formLoading.value = true;

    const payload = {
        name: formData.value.periode,
        tipe: 'sidang',
        semester: formData.value.semester,
        prodi: 'Informatika',
        gelombang: Number(formData.value.gelombang),
        capacity: Number(formData.value.capacity),
        start_date: formData.value.start_date,
        end_date: formData.value.end_date,
        is_open: Boolean(formData.value.is_open),
    };

    if (isEditing.value && editingPeriodId.value) {
        router.patch(
            `/kaprodi/penjadwalan/periods/${editingPeriodId.value}`,
            payload,
            {
                preserveScroll: true,
                onSuccess: () => {
                    showFormModal.value = false;
                    showToast('Periode Sidang TA berhasil diperbarui.');
                },
                onError: (errs) => {
                    const msg =
                        Object.values(errs)[0] || 'Gagal menyimpan periode.';
                    showToast(String(msg), true);
                },
                onFinish: () => {
                    formLoading.value = false;
                },
            },
        );
    } else {
        router.post('/kaprodi/penjadwalan/periods', payload, {
            preserveScroll: true,
            onSuccess: () => {
                showFormModal.value = false;
                showToast('Periode Sidang TA baru berhasil ditambahkan.');
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs)[0] || 'Gagal menambahkan periode.';
                showToast(String(msg), true);
            },
            onFinish: () => {
                formLoading.value = false;
            },
        });
    }
};

// Toggle Status (Aktif / Nonaktif)
const togglingId = ref<number | null>(null);
const toggleStatus = (period: PeriodItem) => {
    togglingId.value = period.id;
    router.post(
        `/kaprodi/penjadwalan/periods/toggle/${period.id}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    `Gelombang ${period.gelombang} sekarang ${!period.is_open ? 'Aktif' : 'Nonaktif'}.`,
                );
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs)[0] ||
                    'Gagal memperbarui status periode.';
                showToast(String(msg), true);
            },
            onFinish: () => {
                togglingId.value = null;
            },
        },
    );
};

// Toggle Publish (Visibilitas Jadwal ke Mahasiswa & Dosen)
const publishingId = ref<number | null>(null);
const togglePublish = (period: PeriodItem) => {
    publishingId.value = period.id;
    router.post(
        `/kaprodi/penjadwalan/periods/toggle-publish/${period.id}`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    `Jadwal Gelombang ${period.gelombang} ${!period.is_published ? 'berhasil dipublikasikan ke Mahasiswa & Dosen' : 'disembunyikan (Draft)'}.`,
                );
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs)[0] ||
                    'Gagal memperbarui visibilitas jadwal.';
                showToast(String(msg), true);
            },
            onFinish: () => {
                publishingId.value = null;
            },
        },
    );
};

// Modal Google Calendar Sync
const showCalendarModal = ref(false);
const selectedPeriodForCalendar = ref<PeriodItem | null>(null);
const syncCalendarLoading = ref(false);

const openCalendarModal = (period: PeriodItem) => {
    selectedPeriodForCalendar.value = period;
    showCalendarModal.value = true;
};

const handleSyncCalendar = () => {
    if (!selectedPeriodForCalendar.value || syncCalendarLoading.value) return;
    syncCalendarLoading.value = true;

    router.post(
        `/kaprodi/penjadwalan/periods/${selectedPeriodForCalendar.value.id}/sync-calendar`,
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    `Informasi Sidang TA Gelombang ${selectedPeriodForCalendar.value?.gelombang} berhasil masuk ke Google Calendar Informatika.`,
                );
                if (selectedPeriodForCalendar.value) {
                    selectedPeriodForCalendar.value.google_calendar_synced = true;
                }
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs)[0] ||
                    'Gagal menyinkronkan ke Google Calendar.';
                showToast(String(msg), true);
            },
            onFinish: () => {
                syncCalendarLoading.value = false;
            },
        },
    );
};

const openGoogleCalendar = () => {
    window.open('https://calendar.google.com/calendar/u/0/r', '_blank');
};

// Modal Hapus
const showDeleteModal = ref(false);
const periodToDelete = ref<PeriodItem | null>(null);
const deleteLoading = ref(false);

const confirmDelete = (period: PeriodItem) => {
    periodToDelete.value = period;
    showDeleteModal.value = true;
};

const handleDelete = () => {
    if (!periodToDelete.value || deleteLoading.value) return;
    deleteLoading.value = true;

    router.delete(`/kaprodi/penjadwalan/periods/${periodToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            showToast('Periode Sidang TA berhasil dihapus.');
            periodToDelete.value = null;
        },
        onError: (errs) => {
            const msg = Object.values(errs)[0] || 'Gagal menghapus periode.';
            showToast(String(msg), true);
        },
        onFinish: () => {
            deleteLoading.value = false;
        },
    });
};
</script>

<template>
    <Head title="List Gelombang Sidang Tugas Akhir • SIPTA IF ITK" />

    <div class="space-y-6">
        <!-- Toast Notification -->
        <Toast
            :show="toast.show"
            :message="toast.message"
            :is-error="toast.isError"
            @close="toast.show = false"
        />

        <!-- Page Header -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Jadwal Sidang TA
                </h1>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <Button variant="primary" size="md" @click="openAddModal">
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Periode
                </Button>

                <Link
                    :href="
                        activePeriodItem
                            ? `/kaprodi/penjadwalan/plotting-ta?period_id=${activePeriodItem.id}`
                            : '/kaprodi/penjadwalan/plotting-ta'
                    "
                    class="flex items-center gap-2 rounded-xl bg-[#0b3084] px-4 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-[#082361] active:scale-95"
                >
                    <span
                        >Masuk ke Penentuan Jadwal (Gel.
                        {{ activeGelombangNum }})</span
                    >
                    <span>→</span>
                </Link>
            </div>
        </div>

        <!-- Table Card: List Gelombang Sidang TA (Matches ContohScreen/jadwal_ta.png & periode_ta.png) -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
        >
            <!-- Card Header -->
            <div
                class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800"
            >
                <h2
                    class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white"
                >
                    <svg
                        class="h-4 w-4 text-purple-600"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 6h16M4 10h16M4 14h16M4 18h16"
                        />
                    </svg>
                    <span>List Gelombang</span>
                </h2>
                <span class="text-xs text-slate-400"
                    >Prodi Informatika ITK</span
                >
            </div>

            <!-- Empty State when no periods (Matches ContohScreen/jadwal_ta.png) -->
            <div v-if="!periods.length" class="py-24 text-center">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800/80"
                >
                    <Calendar class="h-8 w-8" />
                </div>
                <h3
                    class="text-sm font-bold text-slate-700 dark:text-slate-300"
                >
                    Tidak ada data jadwal sidang TA.
                </h3>
                <p class="mx-auto mt-1 max-w-sm text-xs text-slate-400">
                    Periode dan pendaftaran sidang Tugas Akhir belum dibuka.
                    Anda dapat menambahkan periode gelombang sidang dengan
                    tombol di atas.
                </p>
                <div class="mt-4">
                    <Button variant="primary" size="sm" @click="openAddModal">
                        <Plus class="mr-1.5 h-3.5 w-3.5" />
                        Tambah Periode Sidang
                    </Button>
                </div>
            </div>

            <!-- Table when periods exist -->
            <div v-else class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-100 bg-slate-50 font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800 dark:bg-slate-800/60"
                    >
                        <tr>
                            <th class="w-12 px-4 py-3.5 text-center">NO</th>
                            <th class="px-4 py-3.5">PERIODE</th>
                            <th class="px-4 py-3.5">SEMESTER</th>
                            <th class="px-4 py-3.5 text-center">GELOMBANG</th>
                            <th class="px-4 py-3.5 text-center">JADWAL</th>
                            <th class="px-4 py-3.5 text-center">STATUS</th>
                            <th class="px-4 py-3.5 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800/60"
                    >
                        <tr
                            v-for="(item, index) in periods"
                            :key="item.id"
                            class="transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/40"
                        >
                            <td
                                class="px-4 py-4 text-center font-medium text-slate-500"
                            >
                                {{ index + 1 }}.
                            </td>
                            <td
                                class="px-4 py-4 font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ formatPeriodYear(item.name) }}
                            </td>
                            <td
                                class="px-4 py-4 font-medium text-slate-600 dark:text-slate-300"
                            >
                                {{ item.semester || 'Gasal' }}
                            </td>
                            <td class="px-4 py-4 text-center font-bold">
                                {{ item.gelombang }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <Link
                                    v-if="item.is_open"
                                    :href="`/kaprodi/penjadwalan/plotting-ta?period_id=${item.id}`"
                                    class="mx-auto flex w-fit items-center gap-1.5 rounded-xl bg-[#0b3084] px-3.5 py-1.5 font-bold text-white shadow-xs transition-all hover:bg-[#082361] active:scale-95"
                                >
                                    <Calendar class="h-3.5 w-3.5" />
                                    <span>Lihat Jadwal &amp; Plotting</span>
                                </Link>
                                <Link
                                    v-else
                                    :href="`/kaprodi/penjadwalan/plotting-ta?period_id=${item.id}`"
                                    class="mx-auto flex w-fit items-center gap-1.5 rounded-xl bg-slate-100 px-3.5 py-1.5 font-semibold text-slate-600 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                >
                                    <Calendar class="h-3.5 w-3.5" />
                                    <span>Lihat Jadwal</span>
                                </Link>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span
                                    v-if="item.is_open"
                                    class="inline-flex items-center rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                                >
                                    Active
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-300"
                                >
                                    Nonactive
                                </span>
                            </td>
                            <!-- AKSI COLUMN (5 Icons Matching ContohScreen/periode_sempro.png) -->
                            <td class="px-4 py-4 text-center">
                                <div
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    <!-- 1. Edit Periode -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400"
                                        title="Edit Periode"
                                        @click="openEditModal(item)"
                                    >
                                        <Edit2 class="h-3.5 w-3.5" />
                                    </button>

                                    <!-- 2. Status Gelombang (Pause jika aktif, Play jika nonaktif) -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 transition-colors"
                                        :class="[
                                            item.is_open
                                                ? 'text-amber-500 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40'
                                                : 'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40',
                                        ]"
                                        :title="
                                            item.is_open
                                                ? 'Nonaktifkan Gelombang'
                                                : 'Aktifkan Gelombang'
                                        "
                                        :disabled="togglingId === item.id"
                                        @click="toggleStatus(item)"
                                    >
                                        <Pause
                                            v-if="item.is_open"
                                            class="h-3.5 w-3.5 fill-current"
                                        />
                                        <Play
                                            v-else
                                            class="h-3.5 w-3.5 fill-current"
                                        />
                                    </button>

                                    <!-- 3. Visibilitas / Publikasi Jadwal (Mata: Eye jika dipublikasikan, EyeOff jika draft) -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 transition-colors"
                                        :class="[
                                            item.is_published
                                                ? 'text-blue-600 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-950/40'
                                                : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200',
                                        ]"
                                        :title="
                                            item.is_published
                                                ? 'Jadwal Sudah Dipublikasikan (Klik untuk sembunyikan)'
                                                : 'Publikasikan Jadwal ke Mahasiswa & Dosen'
                                        "
                                        :disabled="publishingId === item.id"
                                        @click="togglePublish(item)"
                                    >
                                        <Eye
                                            v-if="item.is_published"
                                            class="h-3.5 w-3.5"
                                        />
                                        <EyeOff v-else class="h-3.5 w-3.5" />
                                    </button>

                                    <!-- 4. Google Calendar Sync (CheckCircle jika tersinkron, Calendar jika belum) -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 transition-colors"
                                        :class="[
                                            item.google_calendar_synced
                                                ? 'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 dark:hover:bg-emerald-950/40'
                                                : 'text-slate-400 hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-slate-800 dark:hover:text-blue-400',
                                        ]"
                                        :title="
                                            item.google_calendar_synced
                                                ? 'Tersinkronkan ke Google Calendar Informatika (Klik untuk kelola)'
                                                : 'Sinkronkan Jadwal ke Google Calendar Informatika'
                                        "
                                        @click="openCalendarModal(item)"
                                    >
                                        <CheckCircle2
                                            v-if="item.google_calendar_synced"
                                            class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400"
                                        />
                                        <Calendar v-else class="h-3.5 w-3.5" />
                                    </button>

                                    <!-- 5. Hapus Periode -->
                                    <button
                                        type="button"
                                        class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                                        title="Hapus Periode"
                                        @click="confirmDelete(item)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MODAL: TAMBAH / EDIT PERIODE SIDANG TA -->
        <Modal
            :show="showFormModal"
            :title="
                isEditing
                    ? 'Edit Periode Sidang TA'
                    : 'Tambah Periode Sidang TA'
            "
            max-width="md"
            @close="showFormModal = false"
        >
            <form class="space-y-4" @submit.prevent="submitForm">
                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Semester <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="formData.semester"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option value="Gasal">Gasal</option>
                        <option value="Genap">Genap</option>
                        <option value="Antara">Antara</option>
                    </select>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Periode <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formData.periode"
                        type="text"
                        required
                        placeholder="Contoh: 2026/2027"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Gelombang <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model.number="formData.gelombang"
                        type="number"
                        min="1"
                        required
                        placeholder="Masukkan Gelombang"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Tanggal Mulai
                        </label>
                        <input
                            v-model="formData.start_date"
                            type="date"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            Tanggal Selesai
                        </label>
                        <input
                            v-model="formData.end_date"
                            type="date"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        />
                    </div>
                </div>

                <div>
                    <label
                        class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Kapasitas Mahasiswa
                    </label>
                    <input
                        v-model.number="formData.capacity"
                        type="number"
                        min="1"
                        required
                        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs text-slate-800 shadow-xs focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    />
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input
                        id="ta-period-active-toggle"
                        v-model="formData.is_open"
                        type="checkbox"
                        class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800"
                    />
                    <label
                        for="ta-period-active-toggle"
                        class="cursor-pointer text-xs font-medium text-slate-700 dark:text-slate-300"
                    >
                        Jadikan Gelombang Aktif (Membuka Pendaftaran &amp;
                        Penjadwalan Sidang)
                    </label>
                </div>

                <div
                    class="flex items-center justify-end gap-2.5 border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                        @click="showFormModal = false"
                    >
                        Tutup
                    </button>
                    <Button
                        variant="primary"
                        size="md"
                        type="submit"
                        :disabled="formLoading"
                    >
                        {{ formLoading ? 'Menyimpan...' : 'Simpan Data' }}
                    </Button>
                </div>
            </form>
        </Modal>

        <!-- MODAL: GOOGLE CALENDAR SYNC -->
        <Modal
            :show="showCalendarModal"
            title="Integrasi Google Calendar Informatika"
            max-width="lg"
            @close="showCalendarModal = false"
        >
            <div class="space-y-4">
                <!-- Info Header Box -->
                <div
                    class="rounded-xl border border-purple-100 bg-purple-50/60 p-4 dark:border-purple-900/40 dark:bg-purple-950/20"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-purple-600 text-white shadow-xs"
                            >
                                <Calendar class="h-5 w-5" />
                            </div>
                            <div>
                                <h3
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    Google Calendar Program Studi Informatika
                                </h3>
                                <p
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                >
                                    Kalender Resmi:
                                    <span
                                        class="font-semibold text-purple-600 dark:text-purple-400"
                                        >informatika@itk.ac.id</span
                                    >
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="
                                selectedPeriodForCalendar?.google_calendar_synced
                            "
                            class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300"
                        >
                            <span
                                class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                            ></span>
                            Tersinkron
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            Belum Sinkron
                        </span>
                    </div>

                    <div
                        class="mt-3 grid grid-cols-2 gap-2 border-t border-purple-200/50 pt-3 text-xs text-slate-600 dark:border-purple-900/40 dark:text-slate-300"
                    >
                        <div>
                            <span class="text-slate-400">Periode:</span>
                            <span
                                class="ml-1 font-bold text-slate-800 dark:text-slate-200"
                            >
                                Gelombang
                                {{ selectedPeriodForCalendar?.gelombang }} ({{
                                    formatPeriodYear(
                                        selectedPeriodForCalendar?.name,
                                    )
                                }})
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400">Terakhir Sync:</span>
                            <span
                                class="ml-1 font-medium text-slate-700 dark:text-slate-300"
                            >
                                {{
                                    selectedPeriodForCalendar?.google_calendar_synced_at ||
                                    'Belum pernah'
                                }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Schedule List inside Period -->
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <h4
                            class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Daftar Agenda Jadwal Sidang TA ({{
                                selectedPeriodForCalendar?.schedules?.length ||
                                0
                            }}
                            Sesi)
                        </h4>
                        <span class="text-[11px] text-slate-400">
                            {{ selectedPeriodForCalendar?.slots?.length || 0 }}
                            slot waktu terdaftar
                        </span>
                    </div>

                    <div
                        class="max-h-56 overflow-y-auto rounded-xl border border-slate-200/80 dark:border-slate-800/80"
                    >
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-100 bg-slate-50 font-bold tracking-wider text-slate-400 uppercase dark:border-slate-800 dark:bg-slate-800/60"
                            >
                                <tr>
                                    <th class="w-8 px-3 py-2 text-center">
                                        NO
                                    </th>
                                    <th class="px-3 py-2">
                                        WAKTU &amp; RUANGAN
                                    </th>
                                    <th class="px-3 py-2">MAHASISWA</th>
                                    <th class="px-3 py-2">PENGUJI</th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800/60"
                            >
                                <tr
                                    v-for="(
                                        sch, sIdx
                                    ) in selectedPeriodForCalendar?.schedules ||
                                    []"
                                    :key="sch.id"
                                    class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                                >
                                    <td
                                        class="px-3 py-2.5 text-center font-medium text-slate-400"
                                    >
                                        {{ sIdx + 1 }}.
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <p
                                            class="font-bold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ sch.tanggal }}
                                        </p>
                                        <p class="text-[11px] text-slate-500">
                                            {{ sch.jam_mulai }} -
                                            {{ sch.jam_selesai }} ·
                                            {{ sch.ruangan }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-2.5">
                                        <p
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            {{ sch.mahasiswa }}
                                        </p>
                                        <p class="text-[11px] text-slate-400">
                                            {{ sch.nim }}
                                        </p>
                                    </td>
                                    <td
                                        class="px-3 py-2.5 text-[11px] text-slate-600 dark:text-slate-300"
                                    >
                                        <p>1. {{ sch.penguji_1 }}</p>
                                        <p>2. {{ sch.penguji_2 }}</p>
                                    </td>
                                </tr>

                                <tr
                                    v-if="
                                        !selectedPeriodForCalendar?.schedules
                                            ?.length
                                    "
                                >
                                    <td
                                        colspan="4"
                                        class="py-8 text-center text-slate-400"
                                    >
                                        <Calendar
                                            class="mx-auto mb-1 h-6 w-6 text-slate-300 dark:text-slate-600"
                                        />
                                        <p class="font-medium">
                                            Belum ada sesi jadwal yang
                                            ter-plotting di gelombang ini.
                                        </p>
                                        <p class="mt-0.5 text-[11px]">
                                            Sinkronisasi akan mendaftarkan slot
                                            periode gelombang ke Google
                                            Calendar.
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div
                    class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2">
                        <a
                            :href="`/kaprodi/penjadwalan/periods/${selectedPeriodForCalendar?.id}/export-ics`"
                            target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            title="Download format berkas iCalendar standar"
                        >
                            <Download class="h-3.5 w-3.5" />
                            <span>Unduh .ICS</span>
                        </a>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="openGoogleCalendar"
                        >
                            <ExternalLink class="h-3.5 w-3.5" />
                            <span>Buka Google Calendar</span>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-xl bg-slate-100 px-3.5 py-2 text-xs font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="showCalendarModal = false"
                        >
                            Tutup
                        </button>

                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white shadow-md transition-all hover:bg-purple-700 active:scale-95 disabled:opacity-50"
                            :disabled="syncCalendarLoading"
                            @click="handleSyncCalendar"
                        >
                            <CheckCircle2
                                v-if="
                                    selectedPeriodForCalendar?.google_calendar_synced
                                "
                                class="h-3.5 w-3.5"
                            />
                            <Calendar v-else class="h-3.5 w-3.5" />
                            <span>{{
                                syncCalendarLoading
                                    ? 'Menyinkronkan...'
                                    : selectedPeriodForCalendar?.google_calendar_synced
                                      ? 'Sinkron Ulang Sekarang'
                                      : 'Sinkronkan ke Google Calendar'
                            }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- DELETE CONFIRM MODAL -->
        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Periode Sidang TA"
            :message="`Apakah Anda yakin ingin menghapus Gelombang ${periodToDelete?.gelombang} (${periodToDelete?.name})? Tindakan ini tidak dapat dibatalkan.`"
            :processing="deleteLoading"
            @close="showDeleteModal = false"
            @confirm="handleDelete"
        />
    </div>
</template>
