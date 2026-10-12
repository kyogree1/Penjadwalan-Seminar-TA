<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import {
    Building2,
    Edit3,
    GraduationCap,
    Laptop,
    Plus,
    Scale,
    Search,
    Trash2,
    Upload,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import LecturerCsvImport from '@/Components/LecturerCsvImport.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type {
    LecturerDirectoryEntry,
    LecturerDirectoryProps,
} from '@/types/kaprodi-dosen';

const props = defineProps<LecturerDirectoryProps>();
const dosenList = computed(() => props.dosenList);
const searchQuery = ref('');
const filterProdi = ref('all');
const filterJabatan = ref('all');
const prodiOptions = computed(() =>
    [...new Set(dosenList.value.map((d) => d.prodi))].sort(),
);
const jabatanOptions = computed(() =>
    [
        ...new Set(
            dosenList.value.flatMap((d) => (d.jabatan ? [d.jabatan] : [])),
        ),
    ].sort(),
);
const filteredDosenList = computed(() =>
    dosenList.value.filter((d) => {
        const query = searchQuery.value.trim().toLowerCase();
        return (
            [d.name, d.nip, d.email].some((value) =>
                (value ?? '').toLowerCase().includes(query),
            ) &&
            (filterProdi.value === 'all' || d.prodi === filterProdi.value) &&
            (filterJabatan.value === 'all' || d.jabatan === filterJabatan.value)
        );
    }),
);
const currentPage = ref(1);
const pageSize = 8;
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredDosenList.value.length / pageSize)),
);
watch(
    [searchQuery, filterProdi, filterJabatan],
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
const paginatedDosenList = computed(() =>
    filteredDosenList.value.slice(
        (currentPage.value - 1) * pageSize,
        currentPage.value * pageSize,
    ),
);
const paginationStart = computed(() =>
    filteredDosenList.value.length ? (currentPage.value - 1) * pageSize + 1 : 0,
);
const paginationEnd = computed(() =>
    Math.min(currentPage.value * pageSize, filteredDosenList.value.length),
);
const totalDosenCount = computed(() => dosenList.value.length);
const prodiCount = computed(() => prodiOptions.value.length);
const jabatanCount = computed(
    () => dosenList.value.filter((d) => d.jabatan).length,
);
const resetFilter = () => {
    searchQuery.value = '';
    filterProdi.value = 'all';
    filterJabatan.value = 'all';
    currentPage.value = 1;
};
const showImport = ref(false);
const isModalFormOpen = ref(false);
const editingDosenId = ref<number | null>(null);
const isEditing = computed(() => editingDosenId.value !== null);
const blankForm = () => ({
    name: '',
    email: '',
    nip: '',
    prodi: '',
    jabatan: '',
});
const formDosen = ref(blankForm());
const errors = ref<Record<string, string>>({});
const processing = ref(false);
const feedback = ref('');
const feedbackError = ref(false);
const openModalTambah = () => {
    if (processing.value) return;
    editingDosenId.value = null;
    formDosen.value = blankForm();
    errors.value = {};
    feedback.value = '';
    isModalFormOpen.value = true;
};
const openModalEdit = (dosen: LecturerDirectoryEntry) => {
    if (processing.value) return;
    editingDosenId.value = dosen.id;
    formDosen.value = {
        name: dosen.name,
        email: dosen.email,
        nip: dosen.nip ?? '',
        prodi: dosen.prodi,
        jabatan: dosen.jabatan ?? '',
    };
    errors.value = {};
    feedback.value = '';
    isModalFormOpen.value = true;
};
const closeModalForm = () => {
    if (!processing.value) isModalFormOpen.value = false;
};
const isFormValid = computed(() =>
    ['name', 'email', 'nip', 'prodi'].every((key) =>
        formDosen.value[key as keyof typeof formDosen.value].trim(),
    ),
);
const requestOptions = (closeForm = false) => ({
    preserveScroll: true,
    onSuccess: (page: { props: Record<string, unknown> }) => {
        const flash = page.props.flash as
            | LecturerDirectoryProps['flash']
            | undefined;
        feedback.value =
            flash?.error ||
            flash?.success ||
            'Konfirmasi penyimpanan dari server belum tersedia.';
        feedbackError.value = !flash?.success || !!flash?.error;
        if (closeForm && flash?.success && !flash?.error)
            isModalFormOpen.value = false;
    },
    onError: (serverErrors: Record<string, string>) => {
        errors.value = serverErrors;
        feedbackError.value = true;
        feedback.value =
            'Permintaan gagal. Periksa pesan kesalahan dan coba lagi.';
    },
    onNetworkError: () => {
        feedbackError.value = true;
        feedback.value = 'Koneksi gagal. Data belum dikonfirmasi tersimpan.';
        return false;
    },
    onHttpException: () => {
        feedbackError.value = true;
        feedback.value =
            'Server tidak dapat memproses permintaan. Data belum dikonfirmasi tersimpan.';
        return false;
    },
    onFinish: () => {
        processing.value = false;
    },
});
const saveDosen = () => {
    if (processing.value || !isFormValid.value) return;
    processing.value = true;
    errors.value = {};
    feedback.value = '';
    const payload = { ...formDosen.value };
    if (editingDosenId.value !== null)
        router.put(
            `/kaprodi/dosen/${editingDosenId.value}`,
            payload,
            requestOptions(true),
        );
    else router.post('/kaprodi/dosen', payload, requestOptions(true));
};
const confirmDeleteDosen = (dosen: LecturerDirectoryEntry) => {
    if (
        processing.value ||
        !confirm(
            `Apakah Anda yakin ingin menghapus data dosen "${dosen.name}"?`,
        )
    )
        return;
    processing.value = true;
    errors.value = {};
    feedback.value = '';
    router.delete(`/kaprodi/dosen/${dosen.id}`, requestOptions());
};
</script>

<template>
    <Head title="Data Master Dosen • Kaprodi IF ITK" />

    <div class="space-y-6">
        <LecturerCsvImport v-if="showImport" />
        <PageHeaderBox
            badge="Manajemen Data Pengajar & Penguji"
            title="Data Master Dosen"
            description="Kelola identitas, program studi, email, dan jabatan dosen yang tersimpan."
        >
            <template #action>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="processing"
                        @click="showImport = !showImport"
                    >
                        <Upload class="mr-1.5 h-4 w-4" />
                        Import CSV
                    </Button>
                    <Button
                        variant="primary"
                        size="sm"
                        @click="openModalTambah"
                        :disabled="processing"
                    >
                        <Plus class="mr-1.5 h-4 w-4" />
                        Tambah Dosen
                    </Button>
                </div>
            </template>
        </PageHeaderBox>

        <!-- Toast Notice -->
        <div
            v-if="feedback || flash.error || flash.success"
            role="status"
            :class="
                (feedback ? feedbackError : !!flash.error)
                    ? 'text-rose-700'
                    : 'text-emerald-800'
            "
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-300"
        >
            {{ feedback || flash.error || flash.success }}
        </div>

        <p class="text-xs text-slate-500">
            XLSX, kuota menguji, bidang keahlian, dan metadata instansi
            eksternal belum tersedia.
        </p>
        <!-- Summary of persisted directory fields -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Dosen -->
            <Card class="flex items-center justify-between p-4">
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Total Dosen Terdata
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-slate-900 dark:text-white"
                    >
                        {{ totalDosenCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                >
                    <GraduationCap class="h-5 w-5" />
                </div>
            </Card>

            <!-- Dosen Internal IF -->
            <Card
                class="flex items-center justify-between border-blue-200 p-4 dark:border-blue-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-blue-600 uppercase"
                    >
                        Program Studi Terdata
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-blue-600 dark:text-blue-400"
                    >
                        {{ prodiCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                >
                    <Laptop class="h-5 w-5" />
                </div>
            </Card>

            <!-- Dosen Luar / Eksternal -->
            <Card
                class="flex items-center justify-between border-amber-200 p-4 dark:border-amber-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-600 uppercase"
                    >
                        Jabatan Terisi
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-amber-600 dark:text-amber-400"
                    >
                        {{ jabatanCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                >
                    <Building2 class="h-5 w-5" />
                </div>
            </Card>

            <!-- Rata-rata Beban Menguji -->
            <Card
                class="flex items-center justify-between border-emerald-200 p-4 dark:border-emerald-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase"
                    >
                        Kuota Menguji
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"
                    >
                        Belum tersedia
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                    <Scale class="h-5 w-5" />
                </div>
            </Card>
        </div>

        <!-- MAIN TABLE CARD -->
        <Card class="overflow-hidden">
            <div
                class="space-y-3 border-b border-slate-200 p-5 dark:border-slate-800"
            >
                <div
                    class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                >
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Daftar Pengajar & Penguji
                        </h3>
                        <span class="text-xs text-slate-400">
                            ({{ filteredDosenList.length }} data terfilter)
                        </span>
                    </div>
                </div>

                <!-- Search Bar & Dropdowns -->
                <div
                    class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2 md:grid-cols-4"
                >
                    <div class="relative sm:col-span-2">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari dosen berdasarkan nama, NIP, atau email..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                    </div>

                    <select
                        v-model="filterProdi"
                        aria-label="Filter program studi"
                        class="rounded-xl border border-slate-200 p-2 text-xs dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="all">Semua Prodi</option>
                        <option
                            v-for="prodi in prodiOptions"
                            :key="prodi"
                            :value="prodi"
                        >
                            {{ prodi }}
                        </option>
                    </select>
                    <select
                        v-model="filterJabatan"
                        aria-label="Filter jabatan"
                        class="rounded-xl border border-slate-200 p-2 text-xs dark:border-slate-700 dark:bg-slate-800"
                    >
                        <option value="all">Semua Jabatan</option>
                        <option
                            v-for="jabatan in jabatanOptions"
                            :key="jabatan"
                            :value="jabatan"
                        >
                            {{ jabatan }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Table Content -->
            <div class="w-full overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400"
                    >
                        <tr>
                            <th class="w-12 px-3 py-3.5 text-center">NO</th>
                            <th class="min-w-[200px] px-3 py-3.5">NAMA</th>
                            <th class="min-w-[150px] px-3 py-3.5 font-mono">
                                NIP
                            </th>
                            <th class="min-w-[140px] px-3 py-3.5">
                                PROGRAM STUDI
                            </th>

                            <th class="min-w-[140px] px-3 py-3.5">JABATAN</th>
                            <th class="min-w-[180px] px-3 py-3.5">EMAIL</th>
                            <th class="w-24 px-3 py-3.5 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800/60"
                    >
                        <tr
                            v-for="(dosen, idx) in paginatedDosenList"
                            :key="dosen.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                        >
                            <td
                                class="px-3 py-3.5 text-center font-semibold text-slate-400"
                            >
                                {{ (currentPage - 1) * pageSize + idx + 1 }}
                            </td>

                            <td class="px-3 py-3.5">
                                <p
                                    class="leading-snug font-bold text-slate-900 dark:text-white"
                                >
                                    {{ dosen.name }}
                                </p>
                            </td>

                            <td
                                class="px-3 py-3.5 font-mono whitespace-nowrap text-slate-600 dark:text-slate-400"
                            >
                                {{ dosen.nip ?? 'Belum tersedia' }}
                            </td>

                            <td class="px-3 py-3.5">
                                <span
                                    class="inline-block rounded-full px-2.5 py-1 text-[10px] font-bold"
                                >
                                    {{ dosen.prodi }}
                                </span>
                            </td>

                            <td class="px-3 py-3.5">
                                {{ dosen.jabatan ?? 'Belum tersedia' }}
                            </td>

                            <td
                                class="px-3 py-3.5 font-mono text-[11px] text-slate-600 dark:text-slate-400"
                            >
                                <a
                                    :href="`mailto:${dosen.email}`"
                                    class="block max-w-[200px] truncate hover:text-blue-600 dark:hover:text-blue-400"
                                    :title="dosen.email"
                                >
                                    {{ dosen.email }}
                                </a>
                            </td>

                            <td
                                class="px-3 py-3.5 text-center whitespace-nowrap"
                            >
                                <div
                                    class="flex items-center justify-center gap-1.5"
                                >
                                    <button
                                        type="button"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-blue-600 text-white shadow-2xs transition-transform hover:bg-blue-500 active:scale-90"
                                        title="Edit Dosen"
                                        :disabled="processing"
                                        @click="openModalEdit(dosen)"
                                    >
                                        <Edit3 class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-rose-600 text-white shadow-2xs transition-transform hover:bg-rose-500 active:scale-90"
                                        title="Hapus Dosen"
                                        :disabled="processing"
                                        @click="confirmDeleteDosen(dosen)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredDosenList.length === 0">
                            <td colspan="7" class="p-12 text-center">
                                <div
                                    class="flex flex-col items-center justify-center space-y-3"
                                >
                                    <div
                                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
                                    >
                                        <Search class="h-6 w-6" />
                                    </div>
                                    <div class="space-y-1">
                                        <p
                                            class="text-sm font-bold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                dosenList.length
                                                    ? 'Dosen Tidak Ditemukan'
                                                    : 'Belum ada dosen terdaftar'
                                            }}
                                        </p>
                                        <p class="text-xs text-slate-400">
                                            Tidak ada pengajar yang cocok dengan
                                            kata kunci pencarian atau filter
                                            yang aktif.
                                        </p>
                                    </div>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="resetFilter"
                                    >
                                        Reset Pencarian
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div
                class="flex flex-col justify-between gap-3 border-t border-slate-200 p-4 text-xs text-slate-500 sm:flex-row sm:items-center dark:border-slate-800"
            >
                <div>
                    Menampilkan
                    <b class="text-slate-800 dark:text-slate-200">{{
                        paginationStart
                    }}</b>
                    s/d
                    <b class="text-slate-800 dark:text-slate-200">{{
                        paginationEnd
                    }}</b>
                    dari
                    <b class="text-slate-800 dark:text-slate-200">{{
                        filteredDosenList.length
                    }}</b>
                    hasil
                </div>

                <div class="flex items-center gap-1">
                    <button
                        type="button"
                        :disabled="currentPage === 1"
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:hover:bg-slate-800"
                        @click="currentPage--"
                    >
                        ‹
                    </button>

                    <button
                        v-for="p in totalPages"
                        :key="p"
                        type="button"
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg text-xs font-semibold transition-colors"
                        :class="[
                            currentPage === p
                                ? 'bg-blue-600 font-bold text-white'
                                : 'border border-slate-200 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                        ]"
                        @click="currentPage = p"
                    >
                        {{ p }}
                    </button>

                    <button
                        type="button"
                        :disabled="currentPage === totalPages"
                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:hover:bg-slate-800"
                        @click="currentPage++"
                    >
                        ›
                    </button>
                </div>
            </div>
        </Card>
    </div>

    <Modal :show="isModalFormOpen" max-width="xl" @close="closeModalForm">
        <form class="space-y-4 p-6" @submit.prevent="saveDosen">
            <h3 class="text-base font-bold">
                {{ isEditing ? 'Edit Data Dosen' : 'Tambah Data Dosen' }}
            </h3>
            <p class="text-xs text-slate-500">
                NIP juga digunakan sebagai username akun dosen.
            </p>
            <div
                v-for="field in [
                    'name',
                    'email',
                    'nip',
                    'prodi',
                    'jabatan',
                ] as const"
                :key="field"
                class="space-y-1"
            >
                <label
                    :for="`dosen-${field}`"
                    class="block text-xs font-bold"
                    >{{
                        {
                            name: 'Nama Lengkap beserta Gelar',
                            email: 'Email Akademik',
                            nip: 'NIP',
                            prodi: 'Program Studi',
                            jabatan: 'Jabatan (opsional)',
                        }[field]
                    }}</label
                >
                <input
                    :id="`dosen-${field}`"
                    v-model="formDosen[field]"
                    :type="field === 'email' ? 'email' : 'text'"
                    :required="field !== 'jabatan'"
                    :maxlength="
                        field === 'nip'
                            ? 50
                            : field === 'prodi' || field === 'jabatan'
                              ? 100
                              : 255
                    "
                    :disabled="processing"
                    :aria-invalid="!!errors[field]"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800"
                />
                <p
                    v-if="errors[field]"
                    role="alert"
                    class="text-xs text-rose-600"
                >
                    {{ errors[field] }}
                </p>
            </div>
            <div class="flex justify-end gap-2">
                <Button
                    variant="outline"
                    :disabled="processing"
                    @click="closeModalForm"
                    >Batal</Button
                >
                <Button
                    variant="primary"
                    :disabled="processing || !isFormValid"
                    @click="saveDosen"
                    >{{
                        processing
                            ? 'Menyimpan...'
                            : isEditing
                              ? 'Simpan Perubahan'
                              : 'Tambah Dosen'
                    }}</Button
                >
            </div>
        </form>
    </Modal>
</template>
