<script setup lang="ts">
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { KeyRound, Search, Users, X } from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type {
    TendikStudent,
    TendikStudentProps,
} from '@/types/tendik-mahasiswa';
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
const showDetailModal = ref(false);
const resetSuccessMsg = ref('');
const openDetailModal = (s: TendikStudent) => {
    if (resetting.value) return;
    selectedId.value = s.id;
    showDetailModal.value = true;
};
const openResetModal = (s: TendikStudent) => {
    if (resetting.value) return;
    selectedId.value = s.id;
    resetSuccessMsg.value = '';
    resetError.value = '';
    showDetailModal.value = false;
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

        <div class="flex flex-wrap gap-3">
            <Button
                :disabled="managing || resetting"
                @click="openManagement('create')"
                >Tambah Mahasiswa</Button
            >
        </div>
        <p v-if="managementSuccess" role="status">{{ managementSuccess }}</p>

        <Card class="space-y-3 p-6">
            <h2 class="font-bold">Import CSV mahasiswa</h2>
            <p>
                CSV UTF-8 maksimal 1 MiB / 500 baris, kolom name,email. Hanya
                membuat akun baru; email duplikat semua peran atau dalam file
                ditolak. Tidak ada pembaruan atau import parsial. Username/NIM
                dan akses login memerlukan provisioning terpisah; password acak
                tidak ditampilkan. XLSX tidak tersedia.
            </p>
            <a href="/tendik/mahasiswa/template" class="underline"
                >Unduh template CSV</a
            >
            <label class="block" for="student-csv">Pilih CSV mahasiswa</label>
            <input
                id="student-csv"
                type="file"
                accept=".csv,text/csv"
                :disabled="csvImporting"
                @change="selectCsv"
            />
            <p v-if="csvBusy || csvImporting" role="status">Memproses…</p>
            <p v-if="csvError" role="alert">{{ csvError }}</p>
            <p v-if="csvNotice" role="status">{{ csvNotice }}</p>
            <template v-if="csvRows">
                <p>
                    Preview server: {{ csvRows.length }} akun baru. Server
                    memvalidasi ulang saat import.
                </p>
                <div class="max-h-80 overflow-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="row in csvRows" :key="row.email">
                                <td>{{ row.name }}</td>
                                <td>{{ row.email }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Button :disabled="csvBusy || csvImporting" @click="importCsv"
                    >Simpan import CSV</Button
                >
            </template>
        </Card>

        <!-- Filter & Search Card -->
        <Card class="p-6">
            <div
                class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center dark:border-slate-800"
            >
                <div class="relative w-full max-w-sm">
                    <Search
                        class="absolute top-2.5 left-3 h-4 w-4 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, NIM, username, atau email..."
                        class="w-full rounded-xl border border-slate-200 py-2 pr-4 pl-9 text-xs text-slate-900 focus:border-blue-600 focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                    />
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table
                    class="w-full text-left text-xs text-slate-600 dark:text-slate-300"
                >
                    <thead
                        class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-4 py-3">Nama Mahasiswa</th>
                            <th class="px-4 py-3">NIM</th>
                            <th class="px-4 py-3">Username</th>
                            <th class="px-4 py-3">Email Akademik</th>

                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        v-if="filteredStudents.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="s in paginatedStudents"
                            :key="s.id"
                            class="cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-900/40"
                            @click="openDetailModal(s)"
                        >
                            <td class="px-4 py-3.5">
                                <p class="font-bold">{{ s.name }}</p>
                                <p>{{ s.prodi }}</p>
                            </td>
                            <td class="px-4 py-3.5">{{ s.nim_nip || '—' }}</td>
                            <td class="px-4 py-3.5">{{ s.username || '—' }}</td>
                            <td class="px-4 py-3.5">{{ s.email }}</td>
                            <td class="px-4 py-3.5 text-right" @click.stop>
                                <Button
                                    size="sm"
                                    variant="outline"
                                    :disabled="managing || resetting"
                                    @click="openManagement('edit', s)"
                                    >Edit</Button
                                >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    :disabled="managing || resetting"
                                    @click="openManagement('delete', s)"
                                    >Hapus</Button
                                >
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="openResetModal(s)"
                                >
                                    <KeyRound class="mr-1 h-3.5 w-3.5" />
                                    Reset Password
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="!filteredStudents.length">
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <Users class="h-8 w-8 text-slate-400" />
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
                                            Coba ubah kata kunci pencarian atau
                                            email atau username.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav
                aria-label="Halaman direktori mahasiswa"
                class="mt-4 flex items-center justify-between gap-3 text-xs"
            >
                <Button
                    variant="outline"
                    :disabled="currentPage <= 1"
                    @click="currentPage--"
                    >Sebelumnya</Button
                >
                <span
                    >Halaman {{ currentPage }} dari {{ totalPages }} •
                    {{ filteredStudents.length }} mahasiswa</span
                >
                <Button
                    variant="outline"
                    :disabled="currentPage >= totalPages"
                    @click="currentPage++"
                    >Berikutnya</Button
                >
            </nav>
        </Card>
    </div>

    <Modal :show="managementMode !== null" @close="closeManagement">
        <div class="space-y-4 p-6">
            <h2 class="font-bold">
                {{
                    managementMode === 'delete'
                        ? 'Hapus Akun Mahasiswa'
                        : 'Data Akun Mahasiswa'
                }}
            </h2>
            <p>
                Hanya nama dan email dapat dikelola. Username, NIM, dan akses
                login memerlukan provisioning terpisah. Password acak tidak
                ditampilkan.
            </p>
            <template v-if="managementMode === 'delete'">
                <p>
                    Hapus akun {{ managementStudent?.name }}? Akun dengan
                    riwayat akademik tidak dapat dihapus. Server memeriksa
                    riwayat sebelum menghapus.
                </p>
            </template>
            <template v-else>
                <label class="block"
                    >Nama mahasiswa<input
                        v-model="accountForm.name"
                        aria-label="Nama mahasiswa"
                        :disabled="managing"
                        maxlength="255"
                        class="block w-full rounded border p-2"
                /></label>
                <label class="block"
                    >Email mahasiswa<input
                        v-model="accountForm.email"
                        aria-label="Email mahasiswa"
                        :disabled="managing"
                        type="email"
                        maxlength="255"
                        class="block w-full rounded border p-2"
                /></label>
            </template>
            <p v-if="managing" role="status">Memproses…</p>
            <p v-if="managementError" role="alert">{{ managementError }}</p>
            <Button
                :disabled="managing"
                variant="secondary"
                @click="closeManagement"
                >Batal</Button
            >
            <Button :disabled="managing" @click="submitManagement">{{
                managementMode === 'delete' ? 'Ya, Hapus Akun' : 'Simpan Akun'
            }}</Button>
        </div>
    </Modal>

    <!-- Reset Password Modal -->
    <Modal :show="showResetModal" max-width="md" @close="closeReset">
        <div class="p-6">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                Konfirmasi Reset Password Mahasiswa
            </h3>

            <div v-if="selectedStudent" class="mt-4 text-xs">
                <p class="text-slate-600 dark:text-slate-400">
                    Apakah Anda yakin ingin mereset password akun untuk:
                </p>
                <div class="mt-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-900">
                    <p class="font-bold text-slate-900 dark:text-white">
                        {{ selectedStudent.name }}
                    </p>
                    <p class="text-slate-500">
                        NIM: {{ selectedStudent.nim_nip }}
                    </p>
                </div>
                <p class="mt-2 text-slate-500">
                    Reset mengikuti kebijakan default akun yang berlaku. Nilai
                    password tidak ditampilkan.
                </p>

                <div
                    v-if="resetSuccessMsg"
                    class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300"
                >
                    {{ resetSuccessMsg }}
                </div>
            </div>

            <p v-if="resetError" role="alert" class="mt-4 text-xs text-red-600">
                {{ resetError }}
            </p>
            <p v-if="!selectedStudent" class="mt-4 text-xs">
                Data mahasiswa tidak lagi tersedia.
            </p>
            <div class="mt-6 flex justify-end gap-2">
                <Button
                    variant="secondary"
                    :disabled="resetting"
                    @click="closeReset"
                >
                    Tutup
                </Button>
                <Button
                    v-if="!resetSuccessMsg && selectedStudent"
                    :disabled="resetting"
                    variant="primary"
                    @click="executeResetPassword"
                >
                    Ya, Reset Password
                </Button>
            </div>
        </div>
    </Modal>

    <!-- Student Detail Modal -->
    <Modal
        :show="showDetailModal"
        max-width="lg"
        @close="showDetailModal = false"
    >
        <div class="p-6">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Detail Mahasiswa
                </h3>
                <button
                    type="button"
                    class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                    @click="showDetailModal = false"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div v-if="selectedStudent" class="mt-4 space-y-4">
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <p class="text-slate-500 dark:text-slate-400">
                            Nama Lengkap
                        </p>
                        <p
                            class="mt-1 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ selectedStudent.name }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400">NIM</p>
                        <p
                            class="mt-1 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ selectedStudent.nim_nip }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500 dark:text-slate-400">
                            Program Studi
                        </p>
                        <p
                            class="mt-1 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ selectedStudent.prodi }}
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-slate-500 dark:text-slate-400">Email</p>
                        <p
                            class="mt-1 font-semibold text-slate-900 dark:text-white"
                        >
                            {{ selectedStudent.email }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Button variant="secondary" @click="showDetailModal = false">
                    Tutup
                </Button>
                <Button
                    v-if="selectedStudent"
                    variant="outline"
                    @click="
                        showDetailModal = false;
                        openResetModal(selectedStudent);
                    "
                >
                    <KeyRound class="mr-1 h-3.5 w-3.5" />
                    Reset Password
                </Button>
            </div>
        </div>
    </Modal>
</template>
