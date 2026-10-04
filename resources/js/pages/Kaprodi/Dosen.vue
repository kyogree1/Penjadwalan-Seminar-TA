<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    Award,
    Building2,
    Check,
    Download,
    Edit3,
    FileSpreadsheet,
    FileText,
    Filter,
    GraduationCap,
    Laptop,
    Plus,
    Scale,
    Search,
    Trash2,
    Upload,
    Users,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import { mockMasterDosenList } from '@/data/kaprodi/dosen';
import type { MasterDosen } from '@/types/models';

const props = withDefaults(
    defineProps<{
        dosenList?: MasterDosen[];
    }>(),
    {
        dosenList: () => mockMasterDosenList,
    },
);

const dosenList = ref<MasterDosen[]>([...props.dosenList]);

const searchQuery = ref('');
const filterProdi = ref('all');
const filterJabatan = ref('all');
const filterRole = ref('all');

const filteredDosenList = computed(() => {
    return dosenList.value.filter((dosen) => {
        const query = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !query ||
            dosen.nama.toLowerCase().includes(query) ||
            dosen.nip.toLowerCase().includes(query) ||
            dosen.email.toLowerCase().includes(query) ||
            dosen.bidangKeahlian.toLowerCase().includes(query);

        let matchProdi = true;
        if (filterProdi.value === 'Informatika') {
            matchProdi =
                dosen.prodi === 'Informatika' && dosen.tipe === 'Internal ITK';
        } else if (filterProdi.value === 'lintas') {
            matchProdi =
                dosen.prodi !== 'Informatika' ||
                dosen.tipe === 'Dosen Eksternal';
        }

        const matchJabatan =
            filterJabatan.value === 'all' ||
            dosen.jabatanAkademik === filterJabatan.value;
        const matchRole =
            filterRole.value === 'all' || dosen.role === filterRole.value;

        return matchSearch && matchProdi && matchJabatan && matchRole;
    });
});

// Pagination
const currentPage = ref(1);
const pageSize = 8;
const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredDosenList.value.length / pageSize)),
);
const paginatedDosenList = computed(() => {
    const start = (currentPage.value - 1) * pageSize;
    return filteredDosenList.value.slice(start, start + pageSize);
});
const paginationStart = computed(() =>
    filteredDosenList.value.length === 0
        ? 0
        : (currentPage.value - 1) * pageSize + 1,
);
const paginationEnd = computed(() =>
    Math.min(currentPage.value * pageSize, filteredDosenList.value.length),
);

// KPI Stats
const totalDosenCount = computed(() => dosenList.value.length);
const internalIfCount = computed(
    () =>
        dosenList.value.filter(
            (d) => d.prodi === 'Informatika' && d.tipe === 'Internal ITK',
        ).length,
);
const externalCount = computed(
    () =>
        dosenList.value.filter(
            (d) => d.prodi !== 'Informatika' || d.tipe === 'Dosen Eksternal',
        ).length,
);
const avgQuotaLoad = computed(() => {
    if (dosenList.value.length === 0) return 0;
    const sum = dosenList.value.reduce((acc, d) => acc + d.currentQuota, 0);
    return (sum / dosenList.value.length).toFixed(1);
});

const resetFilter = () => {
    searchQuery.value = '';
    filterProdi.value = 'all';
    filterJabatan.value = 'all';
    filterRole.value = 'all';
    currentPage.value = 1;
};

// Styling Helpers
const getProdiBadgeClass = (prodi: string) => {
    if (prodi === 'Informatika')
        return 'bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800';
    if (prodi.includes('Industri') || prodi.includes('Migas'))
        return 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800';
    return 'bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800';
};

const getRoleBadgeClass = (role: string) => {
    if (role === 'Koorprodi')
        return 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300';
    if (role === 'Tim KBK')
        return 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300';
    if (role === 'Penguji Eksternal')
        return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300';
    return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
};

const getQuotaStatusColor = (current: number, max: number) => {
    const ratio = current / max;
    if (ratio >= 1) return 'text-rose-600 dark:text-rose-400';
    if (ratio >= 0.8) return 'text-amber-600 dark:text-amber-400';
    return 'text-emerald-600 dark:text-emerald-400';
};

const getQuotaBarColor = (current: number, max: number) => {
    const ratio = current / max;
    if (ratio >= 1) return 'bg-rose-500';
    if (ratio >= 0.8) return 'bg-amber-500';
    return 'bg-emerald-500';
};

// Toast
const toast = ref('');
const showToast = (msg: string) => {
    toast.value = msg;
    setTimeout(() => {
        toast.value = '';
    }, 3500);
};

// Modal Tambah / Edit
const isModalFormOpen = ref(false);
const isEditing = ref(false);
const editingDosenId = ref<number | null>(null);

const formDosen = ref<{
    nama: string;
    email: string;
    nip: string;
    jabatanAkademik: string;
    tipe: 'Internal ITK' | 'Dosen Eksternal';
    prodi: string;
    asalInstansi: string;
    role: string;
    maxQuota: number;
    bidangKeahlian: string;
}>({
    nama: '',
    email: '',
    nip: '',
    jabatanAkademik: '',
    tipe: 'Internal ITK',
    prodi: 'Informatika',
    asalInstansi: '',
    role: 'Dosen',
    maxQuota: 10,
    bidangKeahlian: '',
});

const openModalTambah = () => {
    isEditing.value = false;
    editingDosenId.value = null;
    formDosen.value = {
        nama: '',
        email: '',
        nip: '',
        jabatanAkademik: 'Lektor',
        tipe: 'Internal ITK',
        prodi: 'Informatika',
        asalInstansi: '',
        role: 'Dosen',
        maxQuota: 10,
        bidangKeahlian: '',
    };
    isModalFormOpen.value = true;
};

const openModalEdit = (dosen: MasterDosen) => {
    isEditing.value = true;
    editingDosenId.value = dosen.id;
    formDosen.value = {
        nama: dosen.nama,
        email: dosen.email,
        nip: dosen.nip,
        jabatanAkademik: dosen.jabatanAkademik,
        tipe: dosen.tipe,
        prodi: dosen.prodi,
        asalInstansi: dosen.asalInstansi || '',
        role: dosen.role,
        maxQuota: dosen.maxQuota,
        bidangKeahlian: dosen.bidangKeahlian,
    };
    isModalFormOpen.value = true;
};

const closeModalForm = () => {
    isModalFormOpen.value = false;
};

const isFormValid = computed(() => {
    return (
        formDosen.value.nama.trim() !== '' &&
        formDosen.value.email.trim() !== '' &&
        formDosen.value.nip.trim() !== '' &&
        formDosen.value.jabatanAkademik !== '' &&
        formDosen.value.bidangKeahlian.trim() !== ''
    );
});

const saveDosen = () => {
    if (!isFormValid.value) return;

    if (isEditing.value && editingDosenId.value !== null) {
        const idx = dosenList.value.findIndex(
            (d) => d.id === editingDosenId.value,
        );
        if (idx !== -1) {
            dosenList.value[idx] = {
                ...dosenList.value[idx],
                nama: formDosen.value.nama,
                email: formDosen.value.email,
                nip: formDosen.value.nip,
                jabatanAkademik: formDosen.value.jabatanAkademik,
                tipe: formDosen.value.tipe,
                prodi:
                    formDosen.value.tipe === 'Dosen Eksternal'
                        ? formDosen.value.asalInstansi || 'Eksternal'
                        : formDosen.value.prodi,
                asalInstansi: formDosen.value.asalInstansi,
                role: formDosen.value.role,
                maxQuota: formDosen.value.maxQuota,
                bidangKeahlian: formDosen.value.bidangKeahlian,
            };
            showToast(
                `Data dosen "${formDosen.value.nama}" berhasil diperbarui!`,
            );
        }
    } else {
        const newDosen: MasterDosen = {
            id: Math.max(0, ...dosenList.value.map((d) => d.id)) + 1,
            nama: formDosen.value.nama,
            email: formDosen.value.email,
            nip: formDosen.value.nip,
            jabatanAkademik: formDosen.value.jabatanAkademik,
            tipe: formDosen.value.tipe,
            prodi:
                formDosen.value.tipe === 'Dosen Eksternal'
                    ? formDosen.value.asalInstansi || 'Eksternal'
                    : formDosen.value.prodi,
            asalInstansi: formDosen.value.asalInstansi,
            role: formDosen.value.role,
            bidangKeahlian: formDosen.value.bidangKeahlian,
            currentQuota: 0,
            maxQuota: formDosen.value.maxQuota,
            status: 'Aktif',
        };
        dosenList.value.unshift(newDosen);
        showToast(`Dosen baru "${formDosen.value.nama}" berhasil ditambahkan!`);
    }
    closeModalForm();
};

const confirmDeleteDosen = (dosen: MasterDosen) => {
    if (
        confirm(`Apakah Anda yakin ingin menghapus data dosen "${dosen.nama}"?`)
    ) {
        dosenList.value = dosenList.value.filter((d) => d.id !== dosen.id);
        showToast(`Dosen "${dosen.nama}" berhasil dihapus.`);
    }
};

// Modal Import
const isModalImportOpen = ref(false);
const openModalImport = () => {
    isModalImportOpen.value = true;
};
const processImport = () => {
    isModalImportOpen.value = false;
    showToast('Data dosen dari berkas eksternal berhasil diimpor!');
};
</script>

<template>
    <Head title="Data Master Dosen • Kaprodi IF ITK" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Manajemen Data Pengajar & Penguji"
            title="Data Master Dosen & Kuota Beban Menguji"
            description="Kelola data dosen pembimbing & penguji, asal program studi/instansi, serta batas beban menguji untuk optimasi Algoritma Genetika."
        >
            <template #action>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="openModalImport"
                    >
                        <Upload class="mr-1.5 h-4 w-4" />
                        Import Data
                    </Button>
                    <Button
                        variant="primary"
                        size="sm"
                        @click="openModalTambah"
                    >
                        <Plus class="mr-1.5 h-4 w-4" />
                        Tambah Dosen
                    </Button>
                </div>
            </template>
        </PageHeaderBox>

        <!-- Toast Notice -->
        <div
            v-if="toast"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-300"
        >
            {{ toast }}
        </div>

        <!-- 4 KPI SUMMARY CARDS -->
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
                        Dosen Informatika ITK
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-blue-600 dark:text-blue-400"
                    >
                        {{ internalIfCount }}
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
                        Lintas Prodi / Mitra
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-amber-600 dark:text-amber-400"
                    >
                        {{ externalCount }}
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
                        Rata-rata Beban Uji (GA)
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"
                    >
                        {{ avgQuotaLoad }}
                        <span class="text-xs font-normal text-slate-400"
                            >/ 10</span
                        >
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

                    <!-- Prodi Filter Pills -->
                    <div
                        class="flex items-center gap-1.5 overflow-x-auto text-xs"
                    >
                        <button
                            type="button"
                            class="cursor-pointer rounded-xl px-3 py-1.5 font-bold whitespace-nowrap transition-all"
                            :class="[
                                filterProdi === 'all'
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300',
                            ]"
                            @click="filterProdi = 'all'"
                        >
                            Semua Prodi
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xl px-3 py-1.5 font-bold whitespace-nowrap transition-all"
                            :class="[
                                filterProdi === 'Informatika'
                                    ? 'bg-blue-600 text-white shadow-2xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300',
                            ]"
                            @click="filterProdi = 'Informatika'"
                        >
                            Informatika
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer rounded-xl px-3 py-1.5 font-bold whitespace-nowrap transition-all"
                            :class="[
                                filterProdi === 'lintas'
                                    ? 'bg-amber-600 text-white shadow-2xs'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300',
                            ]"
                            @click="filterProdi = 'lintas'"
                        >
                            Lintas Prodi / Eksternal
                        </button>
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
                            placeholder="Cari dosen berdasarkan nama, NIP, email, atau bidang..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                        />
                    </div>

                    <select
                        v-model="filterJabatan"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option value="all">Semua Jabatan Akademik</option>
                        <option value="Guru Besar">Guru Besar</option>
                        <option value="Lektor Kepala">Lektor Kepala</option>
                        <option value="Lektor">Lektor</option>
                        <option value="Asisten Ahli">Asisten Ahli</option>
                        <option value="Tenaga Pengajar">Tenaga Pengajar</option>
                        <option value="Praktisi Ahli">Praktisi Ahli</option>
                    </select>

                    <select
                        v-model="filterRole"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    >
                        <option value="all">Semua Role</option>
                        <option value="Koorprodi">Koorprodi</option>
                        <option value="Tim KBK">Tim KBK</option>
                        <option value="Dosen">Dosen Biasa</option>
                        <option value="Dosen Penguji">Dosen Penguji</option>
                        <option value="Penguji Eksternal">
                            Penguji Eksternal
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
                                PRODI / INSTANSI
                            </th>
                            <th class="min-w-[110px] px-3 py-3.5">ROLE</th>
                            <th class="min-w-[140px] px-3 py-3.5">
                                BEBAN MENGUJI (GA)
                            </th>
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
                                    {{ dosen.nama }}
                                </p>
                                <div
                                    class="mt-0.5 flex items-center gap-1.5 text-[10px] text-slate-400"
                                >
                                    <span>{{ dosen.jabatanAkademik }}</span>
                                    <span>•</span>
                                    <span
                                        class="max-w-[150px] truncate font-medium text-blue-600 dark:text-blue-400"
                                        :title="dosen.bidangKeahlian"
                                    >
                                        {{ dosen.bidangKeahlian }}
                                    </span>
                                </div>
                            </td>

                            <td
                                class="px-3 py-3.5 font-mono whitespace-nowrap text-slate-600 dark:text-slate-400"
                            >
                                {{ dosen.nip }}
                            </td>

                            <td class="px-3 py-3.5">
                                <span
                                    :class="getProdiBadgeClass(dosen.prodi)"
                                    class="inline-block rounded-full px-2.5 py-1 text-[10px] font-bold"
                                >
                                    {{ dosen.prodi }}
                                </span>
                            </td>

                            <td class="px-3 py-3.5">
                                <span
                                    :class="getRoleBadgeClass(dosen.role)"
                                    class="inline-block rounded-full px-2 py-0.5 text-[10px] font-bold"
                                >
                                    {{ dosen.role }}
                                </span>
                            </td>

                            <td class="px-3 py-3.5">
                                <div class="space-y-1">
                                    <div
                                        class="flex items-center justify-between text-[10px]"
                                    >
                                        <span
                                            class="font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            {{ dosen.currentQuota }} /
                                            {{ dosen.maxQuota }} Mhs
                                        </span>
                                        <span
                                            :class="
                                                getQuotaStatusColor(
                                                    dosen.currentQuota,
                                                    dosen.maxQuota,
                                                )
                                            "
                                            class="font-black"
                                        >
                                            {{
                                                Math.round(
                                                    (dosen.currentQuota /
                                                        dosen.maxQuota) *
                                                        100,
                                                )
                                            }}%
                                        </span>
                                    </div>
                                    <div
                                        class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all duration-300"
                                            :class="
                                                getQuotaBarColor(
                                                    dosen.currentQuota,
                                                    dosen.maxQuota,
                                                )
                                            "
                                            :style="{
                                                width: `${Math.min((dosen.currentQuota / dosen.maxQuota) * 100, 100)}%`,
                                            }"
                                        />
                                    </div>
                                </div>
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
                                        @click="openModalEdit(dosen)"
                                    >
                                        <Edit3 class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg bg-rose-600 text-white shadow-2xs transition-transform hover:bg-rose-500 active:scale-90"
                                        title="Hapus Dosen"
                                        @click="confirmDeleteDosen(dosen)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="filteredDosenList.length === 0">
                            <td colspan="8" class="p-12 text-center">
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
                                            Dosen Tidak Ditemukan
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

    <!-- MODAL TAMBAH / EDIT DOSEN -->
    <Modal :show="isModalFormOpen" max-width="xl" @close="closeModalForm">
        <div class="space-y-4 overflow-hidden p-6">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ isEditing ? 'Edit Data Dosen' : 'Tambah Data Dosen' }}
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="closeModalForm"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Nama Lengkap beserta Gelar
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formDosen.nama"
                            type="text"
                            placeholder="Contoh: Dr. Budi Santoso, M.Kom."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Email Akademik / Instansi
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formDosen.email"
                            type="email"
                            placeholder="contoh@lecturer.itk.ac.id"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            NIP / NUPTK / ID Eksternal
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formDosen.nip"
                            type="text"
                            placeholder="Masukkan NIP atau NUPTK"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Jabatan Akademik
                            <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="formDosen.jabatanAkademik"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Guru Besar">Guru Besar</option>
                            <option value="Lektor Kepala">Lektor Kepala</option>
                            <option value="Lektor">Lektor</option>
                            <option value="Asisten Ahli">Asisten Ahli</option>
                            <option value="Tenaga Pengajar">
                                Tenaga Pengajar
                            </option>
                            <option value="Praktisi Ahli">Praktisi Ahli</option>
                        </select>
                    </div>
                </div>

                <!-- Tipe Dosen Selection -->
                <div class="flex items-center gap-4 pt-1">
                    <label
                        class="inline-flex cursor-pointer items-center gap-2 font-semibold text-slate-700 dark:text-slate-300"
                    >
                        <input
                            v-model="formDosen.tipe"
                            type="radio"
                            value="Internal ITK"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>Dosen Internal ITK</span>
                    </label>
                    <label
                        class="inline-flex cursor-pointer items-center gap-2 font-semibold text-slate-700 dark:text-slate-300"
                    >
                        <input
                            v-model="formDosen.tipe"
                            type="radio"
                            value="Dosen Eksternal"
                            class="text-blue-600 focus:ring-blue-500"
                        />
                        <span>Dosen Lintas Prodi / Eksternal</span>
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Program Studi (Internal)
                        </label>
                        <select
                            v-model="formDosen.prodi"
                            :disabled="formDosen.tipe === 'Dosen Eksternal'"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Informatika">Informatika</option>
                            <option value="Sistem Informasi">
                                Sistem Informasi
                            </option>
                            <option value="Teknik Mesin">Teknik Mesin</option>
                            <option value="Teknik Perkapalan">
                                Teknik Perkapalan
                            </option>
                            <option value="Teknik Biomedis">
                                Teknik Biomedis
                            </option>
                            <option value="Teknik Elektro">
                                Teknik Elektro
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Asal Instansi (Eksternal)
                        </label>
                        <input
                            v-model="formDosen.asalInstansi"
                            :disabled="formDosen.tipe === 'Internal ITK'"
                            type="text"
                            placeholder="Contoh: PT. Pertamina, Unmul, dll."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Role Akademik
                        </label>
                        <select
                            v-model="formDosen.role"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Dosen">Dosen Biasa</option>
                            <option value="Koorprodi">Koorprodi</option>
                            <option value="Tim KBK">Tim KBK</option>
                            <option value="Dosen Penguji">Dosen Penguji</option>
                            <option value="Penguji Eksternal">
                                Penguji Eksternal
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Batas Kuota Menguji
                            <span
                                class="font-normal text-blue-600 dark:text-blue-400"
                                >(Variabel GA)</span
                            >
                        </label>
                        <input
                            v-model.number="formDosen.maxQuota"
                            type="number"
                            min="1"
                            max="25"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 dark:text-slate-300">
                        Bidang Keahlian / KBK
                        <span class="text-rose-500">*</span>
                    </label>
                    <input
                        v-model="formDosen.bidangKeahlian"
                        type="text"
                        placeholder="Contoh: Artificial Intelligence, Cyber Security, Software Engineering"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
            </div>

            <div
                class="flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800"
            >
                <Button variant="outline" @click="closeModalForm">
                    Batal
                </Button>
                <Button
                    variant="primary"
                    :disabled="!isFormValid"
                    @click="saveDosen"
                >
                    {{ isEditing ? 'Simpan Perubahan' : 'Tambah Dosen' }}
                </Button>
            </div>
        </div>
    </Modal>

    <!-- MODAL IMPORT DATA DOSEN -->
    <Modal
        :show="isModalImportOpen"
        max-width="md"
        @close="isModalImportOpen = false"
    >
        <div class="space-y-4 p-6">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Import Data Dosen
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="isModalImportOpen = false"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div
                class="cursor-pointer space-y-2 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition-colors hover:border-blue-500 dark:border-slate-700 dark:bg-slate-800/40"
                @click="processImport"
            >
                <div class="flex justify-center">
                    <FileSpreadsheet class="h-10 w-10 text-emerald-600" />
                </div>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-300">
                    Pilih berkas CSV atau Excel (.xlsx)
                </p>
                <p class="text-[11px] text-slate-400">
                    Format kolom: Nama, NIP, Prodi, Email, Jabatan, Bidang,
                    Kuota
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <Button variant="outline" @click="isModalImportOpen = false">
                    Tutup
                </Button>
                <Button variant="primary" @click="processImport">
                    Proses Import
                </Button>
            </div>
        </div>
    </Modal>
</template>
