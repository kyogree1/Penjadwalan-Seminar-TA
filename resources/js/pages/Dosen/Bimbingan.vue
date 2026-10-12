<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Clock,
    FileText,
    PenTool,
    Plus,
    Printer,
    Search,
    Users,
    X,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type {
    LiveBimbinganSession,
    LiveAdvisee,
    LogbookProps,
} from '@/types/dosen-bimbingan';
const props = defineProps<LogbookProps>();
const advisees = computed(() => props.advisees);
const searchQuery = ref('');
const selectedAdviseeId = ref<number | null>(null);
const filteredAdvisees = computed(() =>
    advisees.value.filter((a) =>
        [a.nama, a.nim ?? '', a.judul].some((v) =>
            v.toLowerCase().includes(searchQuery.value.trim().toLowerCase()),
        ),
    ),
);
const selectedAdvisee = computed(
    () =>
        filteredAdvisees.value.find((a) => a.id === selectedAdviseeId.value) ??
        filteredAdvisees.value[0],
);
const approvedCount = (a: LiveAdvisee) =>
    a.sesiList.filter((s) => s.status === 'delivered').length;
const totalSesiGlobal = computed(() =>
    advisees.value.reduce((n, a) => n + a.sesiList.length, 0),
);
const approvedGlobal = computed(() =>
    advisees.value.reduce((n, a) => n + approvedCount(a), 0),
);
const pendingParafCount = computed(() =>
    advisees.value.reduce(
        (n, a) => n + a.sesiList.filter((s) => s.status === 'process').length,
        0,
    ),
);
const avgSesiCount = computed(() =>
    advisees.value.length
        ? (totalSesiGlobal.value / advisees.value.length).toFixed(1)
        : '0',
);
const toast = ref('');
const errors = ref<Record<string, string>>({});
const processing = ref(false);
const showApprovalModal = ref(false);
const activeLogbookId = ref<number | null>(null);
const feedbackInput = ref('');
const isAccBimbingan = ref(true);
const isModalTambahOpen = ref(false);
const formStudentId = ref<number | null>(null);
const blankForm = () => ({
    tanggal: new Date().toISOString().split('T')[0],
    bab: 'Bab 1 - Pendahuluan',
    rangkuman: '',
    catatanDosen: '',
});
const formSesi = ref(blankForm());
const requestOptions = (close?: () => void) => ({
    preserveScroll: true,
    onSuccess: (page: { props: Record<string, unknown> }) => {
        const flash = page.props.flash as LogbookProps['flash'] | undefined;
        toast.value =
            flash?.error ||
            flash?.success ||
            'Konfirmasi penyimpanan dari server belum tersedia.';
        if (flash?.success && !flash.error) close?.();
    },
    onError: (values: Record<string, string>) => {
        errors.value = values;
        toast.value = 'Permintaan gagal. Periksa pesan kesalahan.';
    },
    onNetworkError: () => {
        toast.value = 'Koneksi gagal. Data belum dikonfirmasi tersimpan.';
        return false;
    },
    onHttpException: () => {
        toast.value =
            'Server tidak dapat memproses permintaan. Data belum dikonfirmasi tersimpan.';
        return false;
    },
    onFinish: () => {
        processing.value = false;
    },
});
const beginRequest = () => {
    processing.value = true;
    errors.value = {};
    toast.value = '';
};
const openReviewModal = (log: LiveBimbinganSession) => {
    if (processing.value) return;
    activeLogbookId.value = log.id;
    feedbackInput.value = log.catatanDosen ?? '';
    isAccBimbingan.value = log.status === 'delivered';
    errors.value = {};
    showApprovalModal.value = true;
};
const submitReview = () => {
    if (
        processing.value ||
        !selectedAdvisee.value?.sesiList.some(
            (s) => s.id === activeLogbookId.value,
        )
    )
        return;
    beginRequest();
    router.patch(
        `/dosen/bimbingan/${activeLogbookId.value}/paraf`,
        {
            status: isAccBimbingan.value ? 'delivered' : 'process',
            catatan_dosen: feedbackInput.value,
        },
        requestOptions(() => {
            showApprovalModal.value = false;
        }),
    );
};
const quickParafSesi = (sesi: LiveBimbinganSession) => {
    if (processing.value) return;
    beginRequest();
    router.patch(
        `/dosen/bimbingan/${sesi.id}/paraf`,
        {
            status: 'delivered',
            catatan_dosen: sesi.catatanDosen ?? 'Disetujui pembimbing',
        },
        requestOptions(),
    );
};
const openModalTambahSesi = () => {
    if (processing.value || !selectedAdvisee.value) return;
    formStudentId.value = selectedAdvisee.value.id;
    formSesi.value = blankForm();
    errors.value = {};
    isModalTambahOpen.value = true;
};
const isFormSesiValid = computed(
    () =>
        !!formSesi.value.tanggal &&
        !!formSesi.value.bab &&
        !!formSesi.value.rangkuman.trim(),
);
const saveSesiBimbingan = () => {
    if (
        processing.value ||
        !isFormSesiValid.value ||
        !advisees.value.some((a) => a.id === formStudentId.value)
    )
        return;
    beginRequest();
    router.post(
        '/dosen/bimbingan/sesi',
        {
            mahasiswa_id: formStudentId.value,
            tanggal: formSesi.value.tanggal,
            rangkuman: formSesi.value.rangkuman,
            keterangan: formSesi.value.bab,
            catatan_dosen: formSesi.value.catatanDosen,
        },
        requestOptions(() => {
            isModalTambahOpen.value = false;
        }),
    );
};
</script>

<template>
    <Head title="Bimbingan Mahasiswa • Portal Dosen" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Portal Dosen Pembimbing"
            title="Bimbingan & Verifikasi Logbook (TA-04)"
            description="Pantau progres konsultasi mahasiswa bimbingan, catat sesi bimbingan resmi, dan berikan paraf persetujuan naskah secara digital."
        >
            <template #action>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="true"
                        title="Cetak lembar kendali belum tersedia"
                    >
                        <Printer class="mr-1.5 h-4 w-4" />
                        Cetak Lembar Kendali
                    </Button>
                    <Button
                        variant="primary"
                        size="sm"
                        @click="openModalTambahSesi"
                        :disabled="processing || !selectedAdvisee"
                    >
                        <Plus class="mr-1.5 h-4 w-4" />
                        Catat Sesi Baru
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

        <p class="text-xs text-slate-500">
            Hanya sesi dosen ini untuk penugasan judul disetujui. Ambang
            kelayakan belum tersedia; sesi disetujui bukan penetapan kelayakan
            sidang.
        </p>
        <p
            v-for="(message, field) in errors"
            :key="field"
            role="alert"
            class="text-xs text-rose-700"
        >
            {{ message }}
        </p>
        <!-- 4 Metric Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card class="flex items-center justify-between p-4">
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Total Sesi Bimbingan
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-slate-900 dark:text-white"
                    >
                        {{ totalSesiGlobal }} Sesi
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                >
                    <FileText class="h-5 w-5" />
                </div>
            </Card>

            <Card
                class="flex items-center justify-between border-emerald-200 p-4 dark:border-emerald-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase"
                    >
                        Sesi Disetujui Dosen Ini
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"
                    >
                        {{ approvedGlobal }} Sesi
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </Card>

            <Card
                class="flex items-center justify-between border-amber-200 p-4 dark:border-amber-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-600 uppercase"
                    >
                        Menunggu Paraf Dosen
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-amber-600 dark:text-amber-400"
                    >
                        {{ pendingParafCount }} Sesi
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                >
                    <Clock class="h-5 w-5" />
                </div>
            </Card>

            <Card
                class="flex items-center justify-between border-purple-200 p-4 dark:border-purple-900/40"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-purple-600 uppercase"
                    >
                        Rata-rata Konsultasi
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-purple-600 dark:text-purple-400"
                    >
                        {{ avgSesiCount }}
                        <span class="text-xs font-normal text-slate-400"
                            >/ Mhs</span
                        >
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                >
                    <Users class="h-5 w-5" />
                </div>
            </Card>
        </div>

        <!-- Main Advisees & Logbook View -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left: List of Advisees -->
            <div class="space-y-3 lg:col-span-4">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Daftar Mahasiswa Bimbingan
                    </h3>
                    <span
                        class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                    >
                        {{ filteredAdvisees.length }} Mahasiswa
                    </span>
                </div>

                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari nama, NIM, atau judul..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100"
                    />
                </div>

                <p
                    v-if="!filteredAdvisees.length"
                    class="text-xs text-slate-500"
                >
                    {{
                        searchQuery
                            ? 'Pencarian tidak menemukan mahasiswa.'
                            : 'Belum ada mahasiswa bimbingan dengan judul disetujui.'
                    }}
                </p>
                <div class="space-y-2">
                    <button
                        v-for="mhs in filteredAdvisees"
                        :key="mhs.id"
                        type="button"
                        class="w-full cursor-pointer rounded-2xl border p-4 text-left transition-all"
                        :class="[
                            selectedAdvisee && selectedAdvisee.id === mhs.id
                                ? 'border-blue-600 bg-blue-50/70 shadow-sm dark:border-blue-500 dark:bg-blue-950/40'
                                : 'border-slate-200 bg-white hover:border-slate-300 dark:border-slate-800 dark:bg-[#0E1626] dark:hover:border-slate-700',
                        ]"
                        @click="selectedAdviseeId = mhs.id"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <p
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ mhs.nama }}
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    NIM: {{ mhs.nim ?? '—' }}
                                </p>
                            </div>
                            <span
                                class="rounded-md px-1.5 py-0.5 text-[9px] font-bold"
                                :class="[
                                    mhs.roleAs === 'Pembimbing 1'
                                        ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300'
                                        : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ]"
                            >
                                {{ mhs.roleAs }}
                            </span>
                        </div>

                        <p
                            class="mt-2 line-clamp-2 text-[11px] leading-relaxed text-slate-600 dark:text-slate-300"
                        >
                            {{ mhs.judul }}
                        </p>

                        <p class="mt-3 text-xs text-slate-500">
                            {{ approvedCount(mhs) }} sesi disetujui •
                            {{ mhs.sesiList.length }} sesi tercatat
                        </p>
                    </button>
                </div>
            </div>

            <!-- Right: Selected Student's Logbook Detail -->
            <div class="space-y-4 lg:col-span-8">
                <Card v-if="selectedAdvisee" class="p-6">
                    <!-- Student Header Banner -->
                    <div
                        class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-5 sm:flex-row sm:items-center dark:border-slate-800"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <h2
                                    class="text-base font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ selectedAdvisee.nama }}
                                </h2>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                NIM: {{ selectedAdvisee.nim }} • Peran:
                                {{ selectedAdvisee.roleAs }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                size="sm"
                                variant="outline"
                                @click="openModalTambahSesi"
                                :disabled="processing || !selectedAdvisee"
                            >
                                <Plus class="mr-1 h-3.5 w-3.5" />
                                Tambah Sesi
                            </Button>
                        </div>
                    </div>

                    <!-- Title Box -->
                    <div
                        class="mt-4 rounded-xl bg-slate-50 p-3.5 text-xs dark:bg-slate-900/60"
                    >
                        <span
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Judul Tugas Akhir:
                        </span>
                        <p class="mt-0.5 text-slate-600 dark:text-slate-400">
                            {{ selectedAdvisee.judul }}
                        </p>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Pembimbing 1:
                            <b>{{ selectedAdvisee.pembimbing1 }}</b>
                            <span v-if="selectedAdvisee.pembimbing2">
                                • Pembimbing 2:
                                <b>{{ selectedAdvisee.pembimbing2 }}</b></span
                            >
                        </p>
                    </div>

                    <!-- Timeline Logbook Items -->
                    <div class="mt-6 space-y-4">
                        <h3
                            class="text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Riwayat Logbook Bimbingan (Formulir TA-04)
                        </h3>

                        <div
                            v-for="log in selectedAdvisee.sesiList"
                            :key="log.id"
                            class="relative rounded-2xl border p-4 transition-all"
                            :class="[
                                log.status !== 'delivered'
                                    ? 'border-amber-300 bg-amber-50/40 dark:border-amber-800/60 dark:bg-amber-950/20'
                                    : 'border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0E1626]',
                            ]"
                        >
                            <div
                                class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white"
                                        >
                                            {{ log.no }}
                                        </span>
                                        <span
                                            class="text-xs font-extrabold text-slate-900 dark:text-white"
                                        >
                                            {{ log.keterangan }}
                                        </span>
                                        <span class="text-xs text-slate-400"
                                            >•</span
                                        >
                                        <span
                                            class="text-xs font-medium text-slate-500"
                                        >
                                            {{ log.tanggal }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span
                                        v-if="log.status === 'delivered'"
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        <Check class="h-3 w-3" /> Diparaf Dosen
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                    >
                                        <Clock class="h-3 w-3" />
                                        {{
                                            log.status === 'ditolak'
                                                ? 'Ditolak'
                                                : 'Menunggu Konfirmasi'
                                        }}
                                    </span>

                                    <Button
                                        v-if="log.status !== 'delivered'"
                                        size="sm"
                                        variant="primary"
                                        @click="quickParafSesi(log)"
                                        :disabled="processing"
                                    >
                                        <PenTool class="mr-1 h-3 w-3" />
                                        Paraf
                                    </Button>

                                    <button
                                        type="button"
                                        class="cursor-pointer rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        @click="openReviewModal(log)"
                                    >
                                        Edit Arahan
                                    </button>
                                </div>
                            </div>

                            <!-- Student notes -->
                            <div
                                class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-700 dark:bg-slate-900 dark:text-slate-300"
                            >
                                <span
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    Rangkuman Pembahasan Mahasiswa:
                                </span>
                                <p class="mt-0.5 leading-relaxed">
                                    {{ log.rangkuman }}
                                </p>
                            </div>

                            <!-- Lecturer feedback -->
                            <div
                                v-if="log.catatanDosen"
                                class="mt-2 rounded-xl border border-blue-100 bg-blue-50/60 p-3 text-xs text-blue-900 dark:border-blue-900/40 dark:bg-blue-950/30 dark:text-blue-200"
                            >
                                <span class="font-bold"
                                    >Arahan / Catatan Pembimbing:</span
                                >
                                <p class="mt-0.5 leading-relaxed">
                                    {{ log.catatanDosen }}
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="selectedAdvisee.sesiList.length === 0"
                            class="py-8 text-center text-xs text-slate-400"
                        >
                            Belum ada riwayat sesi bimbingan yang tercatat.
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </div>

    <!-- MODAL 1: REVIEW & CATATAN PARAF -->
    <Modal
        :show="showApprovalModal"
        max-width="lg"
        @close="!processing && (showApprovalModal = false)"
    >
        <div class="p-6">
            <p v-if="toast" role="status" class="text-xs text-slate-700">
                {{ toast }}
            </p>
            <p
                v-for="(message, field) in errors"
                :key="field"
                role="alert"
                class="text-xs text-rose-700"
            >
                {{ message }}
            </p>
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Validasi & Catatan Pembimbing (TA-04)
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="!processing && (showApprovalModal = false)"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="mt-4 space-y-4">
                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Keputusan Paraf Bimbingan
                    </label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition-all"
                            :class="[
                                isAccBimbingan
                                    ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                            ]"
                            @click="isAccBimbingan = true"
                        >
                            <Check class="h-4 w-4" /> Setujui & Paraf
                        </button>
                        <button
                            type="button"
                            class="flex cursor-pointer items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition-all"
                            :class="[
                                !isAccBimbingan
                                    ? 'border-amber-600 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                    : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                            ]"
                            @click="isAccBimbingan = false"
                        >
                            <Clock class="h-4 w-4" /> Perlu Perbaikan (Revisi)
                        </button>
                    </div>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Catatan & Arahan Pembimbing (Opsional)
                    </label>
                    <textarea
                        v-model="feedbackInput"
                        rows="4"
                        placeholder="Tuliskan arahan perbaikan atau persetujuan materi bimbingan ini..."
                        class="mt-1 w-full rounded-xl border border-slate-300 p-3 text-xs text-slate-900 focus:border-blue-600 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Button
                    variant="secondary"
                    @click="!processing && (showApprovalModal = false)"
                >
                    Batal
                </Button>
                <Button
                    variant="primary"
                    @click="submitReview"
                    :disabled="processing"
                >
                    Simpan & Beri Paraf Digital
                </Button>
            </div>
        </div>
    </Modal>

    <!-- MODAL 2: TAMBAH SESI BIMBINGAN BARU -->
    <Modal
        :show="isModalTambahOpen"
        max-width="lg"
        @close="!processing && (isModalTambahOpen = false)"
    >
        <div class="space-y-4 p-6">
            <p v-if="toast" role="status" class="text-xs text-slate-700">
                {{ toast }}
            </p>
            <p
                v-for="(message, field) in errors"
                :key="field"
                role="alert"
                class="text-xs text-rose-700"
            >
                {{ message }}
            </p>
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Form Sesi Bimbingan Tugas Akhir
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="!processing && (isModalTambahOpen = false)"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-700 dark:text-slate-300"
                        >
                            Tanggal Konsultasi
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formSesi.tanggal"
                            type="date"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 dark:text-slate-300">
                        Bab / Topik Bahasan
                        <span class="text-rose-500">*</span>
                    </label>
                    <select
                        v-model="formSesi.bab"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    >
                        <option value="Bab 1 - Pendahuluan">
                            Bab 1 - Pendahuluan
                        </option>
                        <option value="Bab 2 - Tinjauan Pustaka">
                            Bab 2 - Tinjauan Pustaka
                        </option>
                        <option value="Bab 3 - Metodologi Penelitian & Desain">
                            Bab 3 - Metodologi Penelitian & Desain
                        </option>
                        <option value="Bab 4 - Implementasi & Hasil Pengujian">
                            Bab 4 - Implementasi & Hasil Pengujian
                        </option>
                        <option value="Bab 5 - Kesimpulan & Rekomendasi">
                            Bab 5 - Kesimpulan & Rekomendasi
                        </option>
                        <option value="Review Draf Naskah Lengkap">
                            Review Draf Naskah Lengkap
                        </option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 dark:text-slate-300">
                        Rangkuman Pembahasan Mahasiswa
                        <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="formSesi.rangkuman"
                        rows="3"
                        placeholder="Tuliskan poin pembahasan atau materi yang dikonsultasikan..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>

                <div class="space-y-1">
                    <label class="font-bold text-slate-700 dark:text-slate-300">
                        Arahan / Revisi Dosen Pembimbing
                    </label>
                    <textarea
                        v-model="formSesi.catatanDosen"
                        rows="3"
                        placeholder="Catatan arahan perbaikan dari dosen..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs text-slate-900 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>
            </div>

            <div
                class="flex justify-end gap-2 border-t border-slate-200 pt-3 dark:border-slate-800"
            >
                <Button
                    variant="outline"
                    @click="!processing && (isModalTambahOpen = false)"
                >
                    Batal
                </Button>
                <Button
                    variant="primary"
                    :disabled="processing || !isFormSesiValid"
                    @click="saveSesiBimbingan"
                >
                    Simpan Sesi
                </Button>
            </div>
        </div>
    </Modal>
</template>
