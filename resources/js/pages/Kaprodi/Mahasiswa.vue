<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Award,
    Edit3,
    FileText,
    GraduationCap,
    Plus,
    Search,
    Trash2,
    Upload,
    Users,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Button from '@/Components/Button.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import Modal from '@/Components/Modal.vue';
import StudentCsvImport from '@/Components/StudentCsvImport.vue';
import Toast from '@/Components/Toast.vue';
import type {
    StudentDirectoryEntry,
    StudentDirectoryProps,
} from '@/types/kaprodi-mahasiswa';

const props = defineProps<StudentDirectoryProps>();
const mahasiswaList = computed(() => props.mahasiswaList);

// Filter & Search states
const searchQuery = ref('');
const filterAngkatan = ref('all');

// Check URL query param e.g. ?status=alumni or ?status=lulus from dashboard pipeline
const initialStatus = (() => {
    try {
        if (typeof window !== 'undefined' && window.location?.search) {
            const p = new URLSearchParams(window.location.search).get('status');
            if (p && (p.toLowerCase() === 'alumni' || p.toLowerCase() === 'lulus')) {
                return 'Lulus';
            }
        }
    } catch {
        // Fallback for isolated SSR or tests
    }
    return 'all';
})();

const filterStatus = ref<string>(initialStatus);

const angkatanOptions = computed(() => {
    const list = [
        '2024',
        '2023',
        '2022',
        '2021',
        '2020',
        '2019',
        ...mahasiswaList.value.map((m) => m.angkatan),
    ];
    return [...new Set(list.filter(Boolean))].sort().reverse();
});

// Filtered student list
const filteredMahasiswaList = computed(() =>
    mahasiswaList.value.filter((m) => {
        const query = searchQuery.value.trim().toLowerCase();
        const matchQuery =
            !query ||
            [m.nama, m.nim, m.email, m.noTelpon, m.angkatan].some((val) =>
                (val ?? '').toLowerCase().includes(query),
            );
        const matchAngkatan =
            filterAngkatan.value === 'all' ||
            m.angkatan === filterAngkatan.value;
        const matchStatus =
            filterStatus.value === 'all' ||
            (filterStatus.value === 'Lulus'
                ? m.statusTa === 'Lulus' || m.statusTa === 'Alumni'
                : m.statusTa === filterStatus.value);

        return matchQuery && matchAngkatan && matchStatus;
    }),
);

const hasActiveFilter = computed(() => {
    return (
        searchQuery.value !== '' ||
        filterAngkatan.value !== 'all' ||
        filterStatus.value !== 'all'
    );
});

// Pagination
const currentPage = ref(1);
const pageSize = 8;
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredMahasiswaList.value.length / pageSize)),
);

watch(
    [searchQuery, filterAngkatan, filterStatus],
    () => {
        currentPage.value = 1;
    },
    { flush: 'sync' },
);

watch(
    totalPages,
    (pages) => {
        currentPage.value = Math.min(currentPage.value, pages);
    },
    { flush: 'sync' },
);

const paginatedMahasiswaList = computed(() =>
    filteredMahasiswaList.value.slice(
        (currentPage.value - 1) * pageSize,
        currentPage.value * pageSize,
    ),
);

const paginationStart = computed(() =>
    filteredMahasiswaList.value.length
        ? (currentPage.value - 1) * pageSize + 1
        : 0,
);

const paginationEnd = computed(() =>
    Math.min(currentPage.value * pageSize, filteredMahasiswaList.value.length),
);

const resetFilter = () => {
    searchQuery.value = '';
    filterAngkatan.value = 'all';
    filterStatus.value = 'all';
    currentPage.value = 1;
};

// Badges styling
const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'Lulus':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
        case 'Sempro':
            return 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800';
        case 'Sidang Akhir':
            return 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800';
        case 'Bimbingan':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300 dark:border-indigo-800';
        case 'Aktif TA':
            return 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
    }
};

// Form Modal State (Tambah & Edit)
const isModalFormOpen = ref(false);
const isModalImportOpen = ref(false);
const editingMahasiswaId = ref<number | null>(null);
const isEditing = computed(() => editingMahasiswaId.value !== null);

const blankForm = () => ({
    nama: '',
    nim: '',
    email: '',
    noTelpon: '',
    angkatan: '2023',
    statusTa: 'Aktif TA',
});

const formMhs = ref(blankForm());
const errors = ref<Record<string, string>>({});
const processing = ref(false);

// Toast notification bubble state
const toast = ref<{ show: boolean; message: string; isError: boolean }>({
    show: false,
    message: '',
    isError: false,
});
let toastTimeout: ReturnType<typeof setTimeout> | null = null;
const showToast = (message: string, isError = false) => {
    toast.value = { show: true, message, isError };
    if (toastTimeout) clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 4000);
};

watch(
    () => props.flash,
    (flash) => {
        if (flash?.error) {
            showToast(flash.error, true);
        } else if (flash?.success) {
            showToast(flash.success, false);
        }
    },
    { deep: true, immediate: true },
);

const openModalTambah = () => {
    if (processing.value) return;
    editingMahasiswaId.value = null;
    formMhs.value = blankForm();
    errors.value = {};
    isModalFormOpen.value = true;
};

const openModalEdit = (mhs: StudentDirectoryEntry) => {
    if (processing.value) return;
    editingMahasiswaId.value = mhs.id;
    formMhs.value = {
        nama: mhs.nama,
        nim: mhs.nim,
        email: mhs.email,
        noTelpon: mhs.noTelpon || '',
        angkatan: mhs.angkatan,
        statusTa: mhs.statusTa || 'Aktif TA',
    };
    errors.value = {};
    isModalFormOpen.value = true;
};

const closeModalForm = () => {
    if (!processing.value) isModalFormOpen.value = false;
};

const isFormValid = computed(() =>
    ['nama', 'nim', 'email', 'noTelpon', 'angkatan'].every((k) =>
        formMhs.value[k as keyof typeof formMhs.value].trim(),
    ),
);

const requestOptions = (closeForm = false, isEdit = false) => ({
    preserveScroll: true,
    onSuccess: (page: { props: Record<string, unknown> }) => {
        const flash = page.props.flash as
            | StudentDirectoryProps['flash']
            | undefined;
        if (flash?.error) {
            showToast(flash.error, true);
        } else if (flash?.success) {
            showToast(flash.success, false);
        } else {
            showToast(
                isEdit
                    ? 'Data mahasiswa berhasil diperbarui!'
                    : 'Data mahasiswa berhasil disimpan!',
                false,
            );
        }
        if (closeForm && !flash?.error) {
            isModalFormOpen.value = false;
            editingMahasiswaId.value = null;
        }
    },
    onError: (serverErrors: Record<string, string>) => {
        errors.value = serverErrors;
        showToast(
            'Permintaan gagal. Periksa kembali isian formulir dan coba lagi.',
            true,
        );
    },
    onNetworkError: () => {
        showToast('Koneksi gagal. Periksa jaringan Anda.', true);
        return false;
    },
    onHttpException: () => {
        showToast('Server tidak dapat memproses permintaan.', true);
        return false;
    },
    onFinish: () => {
        processing.value = false;
    },
});

const saveMahasiswa = () => {
    if (processing.value || !isFormValid.value) return;
    processing.value = true;
    errors.value = {};
    const payload = {
        ...formMhs.value,
        jurusan: 'Teknik Elektro, Informatika, dan Bisnis',
        prodi: 'Informatika',
    };

    if (editingMahasiswaId.value !== null) {
        router.put(
            `/kaprodi/mahasiswa/${editingMahasiswaId.value}`,
            payload,
            requestOptions(true, true),
        );
    } else {
        router.post('/kaprodi/mahasiswa', payload, requestOptions(true, false));
    }
};

const mhsToDelete = ref<StudentDirectoryEntry | null>(null);
const showDeleteModal = ref(false);

const confirmDeleteMahasiswa = (mhs: StudentDirectoryEntry) => {
    if (processing.value) return;
    mhsToDelete.value = mhs;
    showDeleteModal.value = true;
};

const executeDeleteMahasiswa = () => {
    if (!mhsToDelete.value || processing.value) return;
    processing.value = true;
    errors.value = {};
    const id = mhsToDelete.value.id;
    router.delete(`/kaprodi/mahasiswa/${id}`, {
        ...requestOptions(),
        onSuccess: (page) => {
            showDeleteModal.value = false;
            mhsToDelete.value = null;
            requestOptions().onSuccess(page);
        },
        onError: (errs) => {
            showDeleteModal.value = false;
            requestOptions().onError(errs);
        },
        onFinish: () => {
            processing.value = false;
        },
    });
};
</script>

<template>
    <Head title="Data Master Mahasiswa • Kaprodi IF ITK" />

    <div class="space-y-6">
        <!-- Header & Actions -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="text-2xl font-black tracking-tight text-slate-900 dark:text-white"
                >
                    Data Mahasiswa
                </h1>
                <p
                    class="mt-0.5 text-xs font-normal text-slate-500 dark:text-slate-400"
                >
                    Kelola data akun mahasiswa aktif tugas akhir, nomor kontak,
                    serta status kelayakan ujian seminar.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <Button
                    variant="success"
                    size="md"
                    :disabled="processing"
                    @click="isModalImportOpen = true"
                >
                    <Upload class="mr-1.5 h-4 w-4" />
                    Import Data
                </Button>
                <Button
                    variant="primary"
                    size="md"
                    :disabled="processing"
                    @click="openModalTambah"
                >
                    <Plus class="mr-1.5 h-4 w-4" />
                    Tambah Mahasiswa
                </Button>
            </div>
        </div>

        <!-- Toast Notification Bubble (Fixed Top-Right) -->
        <Toast
            :show="toast.show"
            :message="toast.message"
            :is-error="toast.isError"
            @close="toast.show = false"
        />

        <!-- MAIN TABLE CONTAINER (Matching Daftar Judul & Data Dosen) -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
        >
            <!-- Card Header with Filters -->
            <div
                class="space-y-3 border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800"
            >
                <div
                    class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                >
                    <div class="flex items-center gap-2">
                        <Users
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                        <h2
                            class="text-sm font-black text-black sm:text-base dark:text-white"
                        >
                            Daftar Mahasiswa
                        </h2>
                    </div>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari mahasiswa..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                    </div>

                    <!-- Filter Angkatan -->
                    <div class="w-full sm:w-48">
                        <select
                            v-model="filterAngkatan"
                            aria-label="Filter angkatan"
                            class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="all">Semua Angkatan</option>
                            <option
                                v-for="angkatan in angkatanOptions"
                                :key="angkatan"
                                :value="angkatan"
                            >
                                Angkatan {{ angkatan }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Dropdown (Default: Semua, auto 'Lulus / Alumni' when direct from dashboard) -->
                    <div class="w-full sm:w-48">
                        <select
                            v-model="filterStatus"
                            aria-label="Filter status mahasiswa"
                            class="w-full cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-2 text-xs text-slate-700 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="all">Semua Status</option>
                            <option value="Aktif TA">Aktif TA</option>
                            <option value="Bimbingan">Bimbingan</option>
                            <option value="Sempro">Seminar Proposal</option>
                            <option value="Sidang Akhir">Sidang TA</option>
                            <option value="Lulus">Lulus / Alumni</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filter Reset Button if filtered -->
                <div
                    v-if="hasActiveFilter"
                    class="flex items-center justify-end pt-1 text-xs"
                >
                    <button
                        type="button"
                        class="cursor-pointer font-bold text-blue-600 hover:underline dark:text-blue-400"
                        @click="resetFilter"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold tracking-wider text-slate-900 uppercase dark:border-slate-800 dark:bg-slate-900/60 dark:text-white"
                    >
                        <tr>
                            <th class="w-12 px-3 py-3.5 text-center">NO</th>
                            <th class="min-w-[190px] px-3 py-3.5">NAMA</th>
                            <th class="min-w-[110px] px-3 py-3.5">NIM</th>
                            <th class="min-w-[90px] px-3 py-3.5 text-center">
                                ANGKATAN
                            </th>
                            <th class="min-w-[210px] px-3 py-3.5">JURUSAN</th>
                            <th class="min-w-[120px] px-3 py-3.5">PRODI</th>
                            <th class="min-w-[180px] px-3 py-3.5">EMAIL</th>
                            <th class="min-w-[130px] px-3 py-3.5">NO TELPON</th>
                            <th class="min-w-[110px] px-3 py-3.5 text-center">
                                STATUS TA
                            </th>
                            <th class="w-24 px-3 py-3.5 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800/60"
                    >
                        <tr
                            v-for="(mhs, idx) in paginatedMahasiswaList"
                            :key="mhs.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                        >
                            <td
                                class="px-3 py-3.5 text-center text-slate-900 dark:text-white"
                            >
                                {{ (currentPage - 1) * pageSize + idx + 1 }}
                            </td>
                            <td
                                class="px-3 py-3.5 text-slate-900 dark:text-white"
                            >
                                {{ mhs.nama }}
                            </td>
                            <td
                                class="px-3 py-3.5 whitespace-nowrap text-slate-900 dark:text-white"
                            >
                                {{ mhs.nim }}
                            </td>
                            <td
                                class="px-3 py-3.5 text-center text-slate-900 dark:text-white"
                            >
                                {{ mhs.angkatan }}
                            </td>
                            <td
                                class="max-w-[210px] truncate px-3 py-3.5 text-slate-900 dark:text-white"
                                :title="mhs.jurusan"
                            >
                                {{ mhs.jurusan }}
                            </td>
                            <td class="px-3 py-3.5">
                                <span
                                    class="rounded-full border border-blue-200 bg-blue-50 px-2 py-0.5 text-[10px] font-medium text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/60 dark:text-blue-300"
                                >
                                    {{ mhs.prodi }}
                                </span>
                            </td>
                            <td
                                class="px-3 py-3.5 text-slate-900 dark:text-white"
                            >
                                <a
                                    :href="`mailto:${mhs.email}`"
                                    class="block max-w-[180px] truncate text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                    :title="mhs.email"
                                >
                                    {{ mhs.email }}
                                </a>
                            </td>
                            <td
                                class="px-3 py-3.5 whitespace-nowrap text-slate-900 dark:text-white"
                            >
                                {{ mhs.noTelpon }}
                            </td>
                            <td
                                class="px-3 py-3.5 text-center whitespace-nowrap"
                            >
                                <span
                                    :class="getStatusBadgeClass(mhs.statusTa)"
                                    class="inline-block rounded-full border px-2.5 py-0.5 text-[10px] font-medium"
                                >
                                    {{ mhs.statusTa }}
                                </span>
                            </td>
                            <td
                                class="px-3 py-3.5 text-center whitespace-nowrap"
                            >
                                <div
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    <button
                                        type="button"
                                        title="Edit Mahasiswa"
                                        :disabled="processing"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-blue-600 text-white shadow-2xs transition-transform hover:bg-blue-500 active:scale-90"
                                        @click="openModalEdit(mhs)"
                                    >
                                        <Edit3 class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        title="Hapus Mahasiswa"
                                        :disabled="processing"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-rose-600 text-white shadow-2xs transition-transform hover:bg-rose-500 active:scale-90"
                                        @click="confirmDeleteMahasiswa(mhs)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State -->
                        <tr v-if="filteredMahasiswaList.length === 0">
                            <td colspan="10" class="py-14 text-center">
                                <div
                                    class="flex flex-col items-center justify-center space-y-3"
                                >
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400 dark:bg-slate-800/80"
                                    >
                                        🔍
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            Data Mahasiswa Tidak Ditemukan
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            Tidak ada mahasiswa yang cocok
                                            dengan kriteria filter atau
                                            pencarian Anda.
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:border-blue-900/60 dark:bg-blue-950/60 dark:text-blue-300"
                                        @click="resetFilter"
                                    >
                                        Reset Filter
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div
                class="flex flex-col justify-between gap-3 border-t border-slate-200 p-4 text-xs sm:flex-row sm:items-center dark:border-slate-800"
            >
                <div
                    class="text-xs font-semibold text-slate-900 dark:text-white"
                >
                    Menampilkan
                    <b class="text-slate-900 dark:text-white">{{
                        paginationStart
                    }}</b>
                    sampai
                    <b class="text-slate-900 dark:text-white">{{
                        paginationEnd
                    }}</b>
                    dari
                    <b class="text-slate-900 dark:text-white">{{
                        filteredMahasiswaList.length
                    }}</b>
                    mahasiswa
                </div>

                <div class="flex items-center gap-1 overflow-x-auto">
                    <button
                        type="button"
                        :disabled="currentPage === 1"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 text-slate-900 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
                        @click="currentPage--"
                    >
                        ‹
                    </button>
                    <button
                        v-for="p in totalPages"
                        :key="p"
                        type="button"
                        :class="
                            currentPage === p
                                ? 'border-blue-600 bg-blue-600 font-bold text-white shadow-xs'
                                : 'border border-slate-300 text-slate-900 hover:bg-slate-100 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800'
                        "
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-semibold transition-colors"
                        @click="currentPage = p"
                    >
                        {{ p }}
                    </button>
                    <button
                        type="button"
                        :disabled="currentPage === totalPages"
                        class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 text-slate-900 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
                        @click="currentPage++"
                    >
                        ›
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FORM: TAMBAH / EDIT MAHASISWA -->
        <Modal
            :show="isModalFormOpen"
            :title="isEditing ? 'Edit Data Mahasiswa' : 'Tambah Mahasiswa'"
            max-width="lg"
            @close="closeModalForm"
        >
            <form class="space-y-4" @submit.prevent="saveMahasiswa">
                <!-- Nama -->
                <div class="space-y-1">
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Nama <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formMhs.nama"
                        type="text"
                        placeholder="Masukkan Nama"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <p
                        v-if="errors.nama"
                        class="text-[11px] font-semibold text-rose-600"
                    >
                        {{ errors.nama }}
                    </p>
                </div>

                <!-- NIM -->
                <div class="space-y-1">
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        NIM <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formMhs.nim"
                        type="text"
                        placeholder="Masukkan NIM"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <p
                        v-if="errors.nim"
                        class="text-[11px] font-semibold text-rose-600"
                    >
                        {{ errors.nim }}
                    </p>
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Email <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formMhs.email"
                        type="email"
                        placeholder="Masukkan Email"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <p
                        v-if="errors.email"
                        class="text-[11px] font-semibold text-rose-600"
                    >
                        {{ errors.email }}
                    </p>
                </div>

                <!-- Nomor Telepon -->
                <div class="space-y-1">
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Nomor Telepon <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formMhs.noTelpon"
                        type="text"
                        placeholder="Masukkan Nomor Telepon"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                    <p
                        v-if="errors.noTelpon"
                        class="text-[11px] font-semibold text-rose-600"
                    >
                        {{ errors.noTelpon }}
                    </p>
                </div>

                <!-- Angkatan & Status Tugas Akhir -->
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Angkatan <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formMhs.angkatan"
                            type="text"
                            placeholder="mis. 2023"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p
                            v-if="errors.angkatan"
                            class="text-[11px] font-semibold text-rose-600"
                        >
                            {{ errors.angkatan }}
                        </p>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Status Tugas Akhir
                        </label>
                        <select
                            v-model="formMhs.statusTa"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Aktif TA">Aktif TA</option>
                            <option value="Bimbingan">Bimbingan</option>
                            <option value="Sempro">Sempro</option>
                            <option value="Sidang Akhir">Sidang Akhir</option>
                            <option value="Lulus">Lulus / Alumni</option>
                        </select>
                        <p
                            v-if="errors.statusTa"
                            class="text-[11px] font-semibold text-rose-600"
                        >
                            {{ errors.statusTa }}
                        </p>
                    </div>
                </div>
            </form>

            <template #footer>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="processing"
                    @click="closeModalForm"
                >
                    Tutup
                </Button>
                <Button
                    variant="primary"
                    size="sm"
                    :disabled="processing || !isFormValid"
                    @click="saveMahasiswa"
                >
                    {{ isEditing ? 'Simpan Perubahan' : 'Submit' }}
                </Button>
            </template>
        </Modal>

        <!-- MODAL IMPORT DATA MAHASISWA -->
        <Modal
            :show="isModalImportOpen"
            title="Import Data Mahasiswa"
            max-width="2xl"
            @close="isModalImportOpen = false"
        >
            <StudentCsvImport />

            <template #footer>
                <Button
                    variant="outline"
                    size="sm"
                    @click="isModalImportOpen = false"
                >
                    Tutup
                </Button>
            </template>
        </Modal>

        <!-- MODAL KONFIRMASI HAPUS MAHASISWA -->
        <DeleteConfirmModal
            :show="showDeleteModal"
            title="Hapus Data Mahasiswa"
            :item-name="
                mhsToDelete?.nama
                    ? `${mhsToDelete.nama} (${mhsToDelete.nim})`
                    : ''
            "
            item-type="data mahasiswa"
            :processing="processing"
            @close="showDeleteModal = false"
            @confirm="executeDeleteMahasiswa"
        />
    </div>
</template>
