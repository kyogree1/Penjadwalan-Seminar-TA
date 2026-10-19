<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    CheckCircle2,
    Download,
    FileSpreadsheet,
    Info,
    KeyRound,
    Plus,
    Search,
    Trash2,
    Users,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type {
    TendikStudent,
    TendikStudentProps,
} from '@/types/tendik-mahasiswa';

/**
 * CATATAN ARSITEKTUR / REKOMENDASI UNTUK TIM BACKEND:
 * 1. Redundansi Username & NIM:
 *    Pada schema user mahasiswa, kolom `username` dan `nim_nip` keduanya menyimpan nilai yang sama (NIM).
 *    Rekomendasi normalisasi: jadikan `nim_nip` sebagai single source of truth untuk login/autentikasi mahasiswa,
 *    sehingga tidak perlu pengecekan fallback ganda (username vs nim_nip).
 * 2. Database Indexing:
 *    Disarankan menambahkan index pada:
 *    - `users(nim_nip)`
 *    - `users(email)`
 *    - `users(role)`
 *    untuk optimasi query direktori mahasiswa dan pencarian pendaftar.
 */

const props = defineProps<TendikStudentProps>();
const managementMode = ref<'create' | 'edit' | 'delete' | null>(null);
const managementId = ref<number | null>(null);
const accountForm = ref({ name: '', email: '' });
const managing = ref(false);
const managementError = ref('');
const managementSuccess = ref('');
const managementStudent = computed(() =>
    props.dbStudents.find((s) => s.id === managementId.value),
);
const openManagement = (
    mode: 'create' | 'edit' | 'delete',
    student?: TendikStudent,
) => {
    if (managing.value || resetting.value) return;
    managementMode.value = mode;
    managementId.value = student?.id ?? null;
    accountForm.value = {
        name: student?.name ?? '',
        email: student?.email ?? '',
    };
    managementError.value = '';
    managementSuccess.value = '';
};
const closeManagement = () => {
    if (!managing.value) managementMode.value = null;
};
const submitManagement = () => {
    if (managing.value || !managementMode.value) return;
    if (managementMode.value !== 'create' && !managementStudent.value) {
        managementError.value = 'Data mahasiswa tidak lagi tersedia.';
        return;
    }
    managing.value = true;
    managementError.value = '';
    const options = {
        preserveScroll: true,
        onSuccess: (page: { props: Record<string, unknown> }) => {
            const flash = page.props.flash as TendikStudentProps['flash'];
            if (flash?.error) managementError.value = flash.error;
            else if (flash?.success) {
                managementSuccess.value = flash.success;
                managementMode.value = null;
            } else
                managementError.value =
                    'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
        },
        onError: (errors: Record<string, string>) => {
            managementError.value = Object.values(errors).join(' ');
        },
        onNetworkError: () => {
            managementError.value = 'Koneksi gagal. Silakan coba kembali.';
        },
        onHttpException: () => {
            managementError.value =
                'Permintaan gagal diproses server. Silakan muat ulang data.';
        },
        onCancel: () => {
            managementError.value = 'Permintaan dibatalkan.';
        },
        onFinish: () => {
            managing.value = false;
        },
    };
    if (managementMode.value === 'create')
        router.post('/tendik/mahasiswa', { ...accountForm.value }, options);
    else if (managementMode.value === 'edit')
        router.put(
            `/tendik/mahasiswa/${managementId.value}`,
            { ...accountForm.value },
            options,
        );
    else router.delete(`/tendik/mahasiswa/${managementId.value}`, options);
};
const csvFile = ref<File | null>(null);
const csvRows = ref<{ name: string; email: string }[] | null>(null);
const csvBusy = ref(false);
const csvImporting = ref(false);
const csvError = ref('');
const csvNotice = ref('');
let csvController: AbortController | null = null;
onBeforeUnmount(() => csvController?.abort());
const selectCsv = async (event: Event) => {
    if (csvImporting.value) return;
    csvController?.abort();
    csvBusy.value = false;
    csvFile.value = (event.target as HTMLInputElement).files?.[0] ?? null;
    csvRows.value = null;
    csvError.value = '';
    csvNotice.value = '';
    if (!csvFile.value) return;
    if (
        !csvFile.value.name.toLowerCase().endsWith('.csv') ||
        csvFile.value.size > 1024 * 1024
    ) {
        csvError.value = 'Pilih CSV UTF-8 maksimal 1 MiB.';
        return;
    }
    const selected = csvFile.value;
    const request = new AbortController();
    csvController = request;
    csvBusy.value = true;
    const data = new FormData();
    data.append('file', selected);
    try {
        const token = document.cookie
            .split('; ')
            .find((value) => value.startsWith('XSRF-TOKEN='));
        const response = await fetch('/tendik/mahasiswa/import-preview', {
            method: 'POST',
            credentials: 'same-origin',
            signal: request.signal,
            headers: {
                Accept: 'application/json',
                'X-XSRF-TOKEN': token
                    ? decodeURIComponent(token.slice(11))
                    : '',
            },
            body: data,
        });
        const result = await response.json();
        if (!response.ok)
            throw new Error(
                result.errors
                    ? Object.values(result.errors).flat().join(' ')
                    : (result.message ?? 'Preview gagal.'),
            );
        if (
            !Array.isArray(result.rows) ||
            result.count !== result.rows.length ||
            !result.count ||
            result.count > 500 ||
            result.rows.some(
                (row: { name: unknown; email: unknown }) =>
                    typeof row?.name !== 'string' ||
                    typeof row?.email !== 'string',
            )
        )
            throw new Error('Respons preview tidak valid.');
        if (!request.signal.aborted && csvFile.value === selected)
            csvRows.value = result.rows;
    } catch (caught) {
        if (!request.signal.aborted)
            csvError.value =
                caught instanceof Error ? caught.message : 'Preview gagal.';
    } finally {
        if (csvController === request) csvBusy.value = false;
    }
};
const importCsv = () => {
    if (!csvFile.value || !csvRows.value || csvBusy.value || csvImporting.value)
        return;
    csvImporting.value = true;
    csvError.value = '';
    router.post(
        '/tendik/mahasiswa/import',
        { file: csvFile.value },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as TendikStudentProps['flash'];
                if (flash?.success && !flash.error) {
                    csvNotice.value = flash.success;
                    csvRows.value = null;
                    csvFile.value = null;
                } else
                    csvError.value =
                        flash?.error ??
                        'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
            },
            onError: (errors) => {
                csvError.value = Object.values(errors).join(' ');
                csvRows.value = null;
            },
            onNetworkError: () => {
                csvError.value = 'Koneksi gagal. Import belum dikonfirmasi.';
            },
            onHttpException: () => {
                csvError.value = 'Permintaan gagal diproses server.';
            },
            onCancel: () => {
                csvError.value = 'Permintaan dibatalkan.';
            },
            onFinish: () => {
                csvImporting.value = false;
            },
        },
    );
};
const searchQuery = ref('');
const students = computed(() => props.dbStudents);
const filteredStudents = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return students.value.filter((s) =>
        [s.name, s.email, s.nim_nip, s.username].some((value) =>
            value?.toLowerCase().includes(query),
        ),
    );
});
const currentPage = ref(1);
const pageSize = 8;
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredStudents.value.length / pageSize)),
);
const paginatedStudents = computed(() =>
    filteredStudents.value.slice(
        (currentPage.value - 1) * pageSize,
        currentPage.value * pageSize,
    ),
);
watch(searchQuery, () => {
    currentPage.value = 1;
});
watch(totalPages, (pages) => {
    currentPage.value = Math.min(currentPage.value, pages);
});
const selectedId = ref<number | null>(null);
const selectedStudent = computed(() =>
    students.value.find((s) => s.id === selectedId.value),
);
const showResetModal = ref(false);
const resetSuccessMsg = ref('');
const openResetModal = (s: TendikStudent) => {
    if (resetting.value) return;
    selectedId.value = s.id;
    resetSuccessMsg.value = '';
    resetError.value = '';
    managementMode.value = null;
    showResetModal.value = true;
};
const resetting = ref(false);
const resetError = ref('');
const closeReset = () => {
    if (!resetting.value) showResetModal.value = false;
};
const executeResetPassword = () => {
    if (!selectedStudent.value || resetting.value || resetSuccessMsg.value)
        return;
    resetting.value = true;
    resetError.value = '';
    router.post(
        `/tendik/mahasiswa/${selectedStudent.value.id}/reset-password`,
        {},
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as TendikStudentProps['flash'];
                if (flash?.error) resetError.value = flash.error;
                else if (flash?.success) resetSuccessMsg.value = flash.success;
                else
                    resetError.value =
                        'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
            },
            onError: (errors) => {
                resetError.value = Object.values(errors).join(' ');
            },
            onNetworkError: () => {
                resetError.value = 'Koneksi gagal. Silakan coba kembali.';
            },
            onHttpException: () => {
                resetError.value =
                    'Permintaan gagal diproses server. Silakan muat ulang data.';
            },
            onCancel: () => {
                resetError.value = 'Permintaan dibatalkan.';
            },
            onFinish: () => {
                resetting.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Kelola Akun Mahasiswa - Portal Tendik" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Layanan Akun & Akses"
            title="Kelola Data Mahasiswa & Reset Password Default"
            description="Bantu mahasiswa yang mengalami kendala akses login, dan lihat data akun terdaftar."
        />

        <!-- Flash Notice / Feedback -->
        <div
            v-if="managementSuccess"
            role="status"
            class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ managementSuccess }}</span>
        </div>

        <div
            v-if="csvNotice"
            role="status"
            class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ csvNotice }}</span>
        </div>

        <!-- Import Section (Collapsible / Card) -->
        <Card class="space-y-4 p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <FileSpreadsheet class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                            Import CSV Mahasiswa
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Unggah berkas spreadsheet CSV untuk mendaftarkan akun baru secara massal.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a
                        href="/tendik/mahasiswa/template"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        <Download class="h-3.5 w-3.5" />
                        <span>Unduh Template CSV</span>
                    </a>
                </div>
            </div>

            <div class="space-y-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                <div
                    class="flex items-start gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400"
                >
                    <Info class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
                    <p class="leading-relaxed">
                        CSV UTF-8 maksimal 1 MiB / 500 baris, kolom <code class="font-bold text-slate-700 dark:text-slate-300">name,email</code>. Hanya membuat akun baru; email duplikat semua peran atau dalam file ditolak. Tidak ada pembaruan atau import parsial. Username/NIM dan akses login memerlukan provisioning terpisah; password acak tidak ditampilkan. Format XLSX tidak tersedia.
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300" for="student-csv">
                        Pilih CSV mahasiswa
                    </label>
                    <input
                        id="student-csv"
                        type="file"
                        accept=".csv,text/csv"
                        :disabled="csvImporting"
                        class="mt-1.5 block w-full cursor-pointer text-xs text-slate-500 file:mr-4 file:rounded-xl file:border file:border-slate-200 file:bg-slate-100 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-slate-700 file:transition-colors hover:file:bg-slate-200 dark:file:border-slate-700 dark:file:bg-slate-800 dark:file:text-slate-300 dark:hover:file:bg-slate-700"
                        @change="selectCsv"
                    />
                </div>

                <div v-if="csvBusy || csvImporting" role="status" class="flex items-center gap-2 text-xs font-medium text-blue-600 dark:text-blue-400">
                    <div class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent dark:border-blue-400" />
                    <span>Memproses…</span>
                </div>

                <div
                    v-if="csvError"
                    role="alert"
                    class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    <Info class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" />
                    <span>{{ csvError }}</span>
                </div>

                <div v-if="csvRows" class="space-y-3 rounded-xl border border-slate-200/90 bg-white p-4 dark:border-slate-800 dark:bg-slate-900/60">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                            Preview server: <span class="text-blue-600 dark:text-blue-400">{{ csvRows.length }} akun baru</span>. Server memvalidasi ulang saat import.
                        </p>
                        <Button
                            size="sm"
                            variant="primary"
                            :disabled="csvBusy || csvImporting"
                            @click="importCsv"
                        >
                            Simpan import CSV
                        </Button>
                    </div>

                    <div class="max-h-60 overflow-y-auto rounded-lg border border-slate-200/80 dark:border-slate-800">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase dark:bg-slate-900/80 dark:text-slate-400">
                                <tr>
                                    <th class="px-3 py-2">Nama</th>
                                    <th class="px-3 py-2">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                <tr v-for="row in csvRows" :key="row.email" class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40">
                                    <td class="px-3 py-2 font-medium text-slate-900 dark:text-white">{{ row.name }}</td>
                                    <td class="px-3 py-2 text-slate-500 dark:text-slate-400">{{ row.email }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </Card>

        <!-- Main Table & Filter Card -->
        <Card class="p-6">
            <div
                class="flex flex-col gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
            >
                <div class="space-y-1">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Daftar Mahasiswa
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Total {{ filteredStudents.length }} akun mahasiswa terdaftar pada direktori prodi.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div class="relative w-full sm:w-64">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama, NIM, atau email..."
                            class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50/80 pr-4 pl-9 text-xs text-slate-800 transition-colors placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-100 dark:focus:bg-slate-900"
                        />
                    </div>

                    <Button
                        variant="primary"
                        size="sm"
                        :disabled="managing || resetting"
                        @click="openManagement('create')"
                    >
                        <Plus class="mr-1 h-3.5 w-3.5" />
                        Tambah Mahasiswa
                    </Button>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table
                    class="w-full min-w-[640px] table-fixed text-left text-xs text-slate-600 dark:text-slate-300"
                >
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold tracking-wider text-slate-600 uppercase dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400"
                    >
                        <tr>
                            <th class="w-1/2 px-4 py-3">Nama Mahasiswa</th>
                            <th class="w-1/4 px-4 py-3">NIM</th>
                            <th class="w-1/4 px-4 py-3">Email</th>
                        </tr>
                    </thead>
                    <tbody
                        v-if="filteredStudents.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="s in paginatedStudents"
                            :key="s.id"
                            class="cursor-pointer transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            title="Klik baris untuk kelola akun"
                            @click="openManagement('edit', s)"
                        >
                            <td class="px-4 py-3.5">
                                <p class="truncate font-semibold text-slate-900 dark:text-white">
                                    {{ s.name }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    {{ s.prodi }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5 tabular-nums text-slate-700 dark:text-slate-300">
                                {{ s.nim_nip || s.username || '—' }}
                            </td>
                            <td class="px-4 py-3.5 truncate text-slate-600 dark:text-slate-400">
                                {{ s.email }}
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="!filteredStudents.length">
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <Users class="h-7 w-7 text-slate-400" />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            Tidak ada mahasiswa yang sesuai
                                        </h4>
                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Coba ubah kata kunci pencarian nama, email, atau NIM.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <nav
                aria-label="Halaman direktori mahasiswa"
                class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs dark:border-slate-800"
            >
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage <= 1"
                    @click="currentPage--"
                >
                    Sebelumnya
                </Button>
                <span class="text-slate-500 dark:text-slate-400">
                    Halaman {{ currentPage }} dari {{ totalPages }} • {{ filteredStudents.length }} mahasiswa
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="currentPage >= totalPages"
                    @click="currentPage++"
                >
                    Berikutnya
                </Button>
            </nav>
        </Card>
    </div>

    <!-- Management Modal (Create / Edit / Delete) -->
    <Modal :show="managementMode !== null" :closeable="!managing" max-width="md" @close="closeManagement">
        <div class="space-y-4 p-6">
            <h2 class="text-base font-bold text-slate-900 dark:text-white">
                {{
                    managementMode === 'delete'
                        ? 'Hapus Akun Mahasiswa'
                        : managementMode === 'edit'
                          ? 'Kelola Akun Mahasiswa'
                          : 'Tambah Akun Mahasiswa'
                }}
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                {{
                    managementMode === 'delete'
                        ? 'Konfirmasi penghapusan akun mahasiswa dari direktori prodi.'
                        : 'Hanya nama dan email dapat dikelola. Username/NIM login terikat akun akademik.'
                }}
            </p>

            <template v-if="managementMode === 'delete'">
                <div
                    class="rounded-xl border border-rose-200 bg-rose-50/80 p-3.5 text-xs text-rose-800 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    <p class="font-semibold">Konfirmasi Penghapusan</p>
                    <p class="mt-1 leading-relaxed">
                        Hapus akun {{ managementStudent?.name }}? Akun dengan riwayat akademik tidak dapat dihapus. Server memeriksa riwayat sebelum menghapus.
                    </p>
                </div>
            </template>

            <template v-else>
                <!-- Info Identitas Akademik Terikat (Khusus Edit) -->
                <div
                    v-if="managementMode === 'edit' && managementStudent"
                    class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/50"
                >
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                NIM (Username)
                            </p>
                            <p class="mt-0.5 font-bold text-slate-900 dark:text-white tabular-nums">
                                {{ managementStudent.nim_nip || managementStudent.username || '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                                Program Studi
                            </p>
                            <p class="mt-0.5 font-semibold text-slate-900 dark:text-white">
                                {{ managementStudent.prodi }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Nama mahasiswa
                            <input
                                v-model="accountForm.name"
                                aria-label="Nama mahasiswa"
                                :disabled="managing"
                                maxlength="255"
                                placeholder="Masukkan nama lengkap mahasiswa..."
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/70 p-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />
                        </label>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Email mahasiswa
                            <input
                                v-model="accountForm.email"
                                aria-label="Email mahasiswa"
                                :disabled="managing"
                                type="email"
                                maxlength="255"
                                placeholder="nama.mahasiswa@student.itk.ac.id"
                                class="mt-1.5 block w-full rounded-xl border border-slate-200 bg-slate-50/70 p-2.5 text-xs text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />
                        </label>
                    </div>

                    <!-- Reset Password & Hapus Akun (Khusus Mode Edit) -->
                    <div
                        v-if="managementMode === 'edit' && managementStudent"
                        class="space-y-2.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                            Keamanan & Tindakan Akun
                        </p>
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/60 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                            <div>
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    Reset Password
                                </p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Kembalikan password ke nilai default akun.
                                </p>
                            </div>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                :disabled="managing || resetting"
                                @click="openResetModal(managementStudent)"
                            >
                                <KeyRound class="mr-1 h-3.5 w-3.5 text-amber-500" />
                                Reset Password
                            </Button>
                        </div>

                        <div class="flex items-center justify-between gap-3 rounded-xl border border-rose-200/70 bg-rose-50/30 p-3 dark:border-rose-900/50 dark:bg-rose-950/20">
                            <div>
                                <p class="text-xs font-semibold text-rose-800 dark:text-rose-300">
                                    Hapus Akun
                                </p>
                                <p class="text-[11px] text-rose-600/80 dark:text-rose-400">
                                    Hapus akun jika belum ada riwayat akademik.
                                </p>
                            </div>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                :disabled="managing || resetting"
                                class="border-rose-200 text-rose-700 hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950"
                                @click="openManagement('delete', managementStudent)"
                            >
                                <Trash2 class="mr-1 h-3.5 w-3.5" />
                                Hapus
                            </Button>
                        </div>
                    </div>
                </div>
            </template>

            <div v-if="managing" role="status" class="flex items-center gap-2 text-xs font-medium text-blue-600 dark:text-blue-400">
                <div class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-blue-600 border-t-transparent dark:border-blue-400" />
                <span>Memproses…</span>
            </div>

            <div
                v-if="managementError"
                role="alert"
                class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300"
            >
                <Info class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" />
                <span>{{ managementError }}</span>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <Button
                    :disabled="managing"
                    variant="secondary"
                    size="sm"
                    @click="closeManagement"
                >
                    Batal
                </Button>
                <Button
                    :disabled="managing"
                    :variant="managementMode === 'delete' ? 'danger' : 'primary'"
                    size="sm"
                    @click="submitManagement"
                >
                    {{ managementMode === 'delete' ? 'Ya, Hapus Akun' : 'Simpan Akun' }}
                </Button>
            </div>
        </div>
    </Modal>

    <!-- Reset Password Modal -->
    <Modal :show="showResetModal" max-width="md" :closeable="!resetting" @close="closeReset">
        <div class="p-6">
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Konfirmasi Reset Password Mahasiswa
            </h3>

            <div class="mt-4 space-y-4">
                <div v-if="selectedStudent" class="text-xs">
                    <p class="text-slate-600 dark:text-slate-400">
                        Apakah Anda yakin ingin mereset password akun untuk:
                    </p>
                    <div class="mt-2.5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50">
                        <p class="font-bold text-slate-900 dark:text-white">
                            {{ selectedStudent.name }}
                        </p>
                        <p class="mt-0.5 text-slate-500 dark:text-slate-400">
                            NIM: {{ selectedStudent.nim_nip || selectedStudent.username || '—' }} • {{ selectedStudent.email }}
                        </p>
                    </div>
                    <p class="mt-2 text-slate-500 dark:text-slate-400 leading-relaxed">
                        Reset mengikuti kebijakan default akun yang berlaku. Nilai password tidak ditampilkan.
                    </p>

                    <div
                        v-if="resetSuccessMsg"
                        class="mt-3.5 flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2.5 text-xs text-emerald-700 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-300"
                    >
                        <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                        <span>{{ resetSuccessMsg }}</span>
                    </div>
                </div>

                <div
                    v-if="resetError"
                    role="alert"
                    class="flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300"
                >
                    <Info class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" />
                    <span>{{ resetError }}</span>
                </div>

                <p v-if="!selectedStudent" class="text-xs text-slate-500">
                    Data mahasiswa tidak lagi tersedia.
                </p>
            </div>

            <div class="mt-6 flex w-full items-center justify-end gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                <Button
                    variant="secondary"
                    size="sm"
                    :disabled="resetting"
                    @click="closeReset"
                >
                    Tutup
                </Button>
                <Button
                    v-if="!resetSuccessMsg && selectedStudent"
                    :disabled="resetting"
                    variant="primary"
                    size="sm"
                    @click="executeResetPassword"
                >
                    Ya, Reset Password
                </Button>
            </div>
        </div>
    </Modal>
</template>
