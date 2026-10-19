<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { CheckCircle2, FileText, Info, Search } from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import type {
    TendikVerificationProps,
    VerificationApplicant,
} from '@/types/tendik-verifikasi';
import { STATUS_VERIFIKASI } from '@/lib/status';

const props = defineProps<TendikVerificationProps>();
const activeTab = ref<'sempro' | 'sidang'>('sidang');
const searchQuery = ref('');
const applicants = computed(() => props.pengajuan);
const jumlahSidang = computed(
    () => applicants.value.filter((a) => a.tipe === 'sidang').length,
);
const jumlahSempro = computed(
    () => applicants.value.filter((a) => a.tipe === 'sempro').length,
);
const filteredApplicants = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return applicants.value.filter(
        (app) =>
            app.tipe === activeTab.value &&
            [app.nama, app.nim, app.judul].some((value) =>
                value?.toLowerCase().includes(query),
            ),
    );
});

// Modal State
const showModal = ref(false);
const selected = ref<{
    id: number;
    tipe: VerificationApplicant['tipe'];
} | null>(null);
const activeApplicant = computed(() =>
    applicants.value.find(
        (app) =>
            app.id === selected.value?.id && app.tipe === selected.value?.tipe,
    ),
);
const tendikNotes = ref('');

const saving = ref(false);
const error = ref('');
const success = ref('');
const formatTanggal = (dateStr?: string | null) => {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    if (isNaN(d.getTime())) return dateStr;
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    return `${day}/${month}/${year}`;
};

const closeModal = () => {
    if (!saving.value) showModal.value = false;
};
const openVerification = (app: VerificationApplicant) => {
    if (saving.value) return;
    selected.value = { id: app.id, tipe: app.tipe };
    tendikNotes.value = app.catatan ?? '';
    error.value = '';
    success.value = '';
    showModal.value = true;
};
const submitVerification = (status: 'verifikasi_tendik' | 'ditolak') => {
    const app = activeApplicant.value;
    if (!app || saving.value) return;
    saving.value = true;
    error.value = '';
    router.patch(
        `/tendik/verifikasi/${app.tipe}/${app.id}`,
        {
            status,
            catatan: tendikNotes.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props
                    .flash as TendikVerificationProps['flash'];
                if (flash?.error) error.value = flash.error;
                else if (flash?.success) {
                    success.value = flash.success;
                    showModal.value = false;
                } else
                    error.value =
                        'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
            },
            onError: (errors) => {
                error.value =
                    Object.values(errors).join(' ') || 'Verifikasi gagal.';
            },
            onNetworkError: () => {
                error.value = 'Koneksi gagal. Silakan coba kembali.';
            },
            onHttpException: () => {
                error.value =
                    'Permintaan gagal diproses server. Silakan muat ulang data.';
            },
            onCancel: () => {
                error.value = 'Permintaan dibatalkan.';
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Verifikasi Berkas - Portal Tendik" />

    <div class="space-y-6">
        <div
            v-if="success"
            role="status"
            class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            <CheckCircle2 class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
            <span>{{ success }}</span>
        </div>
        <PageHeaderBox
            badge="Layanan Administrasi Akademik"
            title="Verifikasi Persyaratan Seminar Proposal & Sidang TA"
            description="Periksa keaslian dokumen kelayakan administratif mahasiswa sebelum didistribusikan ke modul Penjadwalan Algoritma Genetika."
        />

        <!-- Main Table of Applicants -->
        <Card class="p-6">
            <div
                class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800"
            >
                <div class="space-y-1">
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Berkas Pendaftaran Masuk
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Data pendaftaran tersimpan; kelengkapan dan keaslian
                        dokumen belum dinilai.
                    </p>
                </div>

                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end"
                >
                    <!-- Search Box -->
                    <div class="relative w-full sm:w-64">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama, NIM, atau judul..."
                            class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50/80 pr-4 pl-9 text-xs text-slate-800 transition-colors placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-100 dark:focus:bg-slate-900"
                        />
                    </div>

                    <!-- Type Filter Pills -->
                    <div
                        class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-900/80"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 transition-all"
                            :class="[
                                activeTab === 'sidang'
                                    ? 'bg-white text-slate-900 shadow-xs dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                            ]"
                            @click="activeTab = 'sidang'"
                        >
                            Sidang Akhir TA ({{ jumlahSidang }})
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 transition-all"
                            :class="[
                                activeTab === 'sempro'
                                    ? 'bg-white text-slate-900 shadow-xs dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                            ]"
                            @click="activeTab = 'sempro'"
                        >
                            Seminar Proposal ({{ jumlahSempro }})
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table
                    v-if="filteredApplicants.length"
                    class="w-full min-w-[760px] table-fixed text-left text-xs text-slate-600 dark:text-slate-300"
                >
                    <thead
                        class="border-b border-slate-200 bg-slate-50 text-[11px] font-semibold tracking-wider text-slate-600 uppercase dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400"
                    >
                        <tr>
                            <th class="w-48 px-4 py-3">Nama Mahasiswa</th>
                            <th class="w-36 px-4 py-3">Tipe</th>
                            <th class="min-w-[240px] px-4 py-3">Judul Tugas Akhir</th>
                            <th class="w-32 px-4 py-3">Tanggal Unggah</th>
                            <th class="w-36 px-4 py-3">Status</th>
                            <th class="w-32 px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="app in filteredApplicants"
                            :key="`${app.tipe}-${app.id}`"
                            class="cursor-pointer transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            @click="openVerification(app)"
                        >
                            <td class="px-4 py-3.5">
                                <p
                                    class="truncate font-semibold text-slate-900 dark:text-white"
                                >
                                    {{ app.nama }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                                    NIM: {{ app.nim }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span
                                    class="inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-semibold"
                                    :class="
                                        app.tipe === 'sempro'
                                            ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'
                                            : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300'
                                    "
                                >
                                    {{
                                        app.tipe === 'sempro'
                                            ? 'Seminar Proposal'
                                            : 'Sidang Akhir'
                                    }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5">
                                <p
                                    class="line-clamp-2 leading-relaxed text-slate-800 dark:text-slate-200"
                                >
                                    {{ app.judul }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5 tabular-nums text-slate-500 dark:text-slate-400">
                                {{ formatTanggal(app.tanggalDaftar) }}
                            </td>
                            <td class="px-4 py-3.5">
                                <StatusBadge
                                    size="sm"
                                    :variant="
                                        STATUS_VERIFIKASI[app.status].variant
                                    "
                                    :text="STATUS_VERIFIKASI[app.status].label"
                                />
                            </td>
                            <td class="px-4 py-3.5 text-right" @click.stop>
                                <Button
                                    size="sm"
                                    variant="primary"
                                    @click="openVerification(app)"
                                >
                                    Periksa
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="py-12 text-center">
                    <div class="flex flex-col items-center gap-3">
                        <div
                            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                        >
                            <Search class="h-8 w-8 text-slate-400" />
                        </div>
                        <div>
                            <h4
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                Tidak ada pendaftar yang sesuai
                            </h4>
                            <p
                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                Coba ubah kata kunci pencarian atau filter tab.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </Card>
    </div>

    <!-- Document Checklist Modal -->
    <Modal :show="showModal" max-width="lg" :closeable="!saving" @close="closeModal">
        <template #header>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Tinjauan Berkas Pendaftaran
                    </h3>
                    <span
                        v-if="activeApplicant"
                        class="rounded-md px-2 py-0.5 text-[10px] font-semibold"
                        :class="
                            activeApplicant.tipe === 'sempro'
                                ? 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300'
                                : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300'
                        "
                    >
                        {{
                            activeApplicant.tipe === 'sempro'
                                ? 'Seminar Proposal'
                                : 'Sidang TA'
                        }}
                    </span>
                </div>
                <p v-if="activeApplicant" class="text-xs text-slate-500 dark:text-slate-400">
                    {{ activeApplicant.nama }} • NIM: {{ activeApplicant.nim }}
                </p>
            </div>
        </template>

        <div v-if="activeApplicant" class="space-y-4">
            <!-- Info Pendaftar Ringkas -->
            <div
                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/40"
            >
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                    Judul Tugas Akhir
                </p>
                <p class="mt-1 text-xs font-medium leading-relaxed text-slate-800 dark:text-slate-200">
                    {{ activeApplicant.judul }}
                </p>
                <div class="mt-2.5 flex items-center justify-between border-t border-slate-200/60 pt-2 text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                    <span>Tanggal Masuk: {{ formatTanggal(activeApplicant.tanggalDaftar) }}</span>
                    <span>Status: <strong class="capitalize">{{ activeApplicant.status.replace('_', ' ') }}</strong></span>
                </div>
            </div>

            <!-- Daftar Dokumen Syarat -->
            <div class="space-y-2.5">
                <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Daftar Dokumen Persyaratan
                </p>
                <div
                    v-for="(item, idx) in activeApplicant.dokumen"
                    :key="idx"
                    class="flex items-start justify-between gap-3 rounded-xl border border-slate-200/90 bg-slate-50/70 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-900/60"
                >
                    <div class="flex items-start gap-3">
                        <div
                            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border"
                            :class="
                                item.path
                                    ? 'border-blue-200 bg-blue-50 text-blue-600 dark:border-blue-900/60 dark:bg-blue-950/60 dark:text-blue-400'
                                    : 'border-slate-200 bg-slate-100 text-slate-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500'
                            "
                        >
                            <FileText class="h-3.5 w-3.5" />
                        </div>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ item.label }}
                            </p>
                            <p class="mt-0.5 truncate text-[11px] text-slate-500 dark:text-slate-400">
                                {{ item.path || 'Berkas belum diunggah' }}
                            </p>
                        </div>
                    </div>
                    <span
                        class="shrink-0 rounded-md px-2 py-0.5 text-[10px] font-semibold"
                        :class="
                            item.path
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400'
                        "
                    >
                        {{ item.path ? 'Tersedia' : 'Kosong' }}
                    </span>
                </div>
            </div>

            <!-- Notes textarea -->
            <div>
                <label
                    class="block text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    Catatan Petugas Tendik (Opsional)
                </label>
                <textarea
                    v-model="tendikNotes"
                    :disabled="saving"
                    rows="3"
                    placeholder="Tuliskan catatan verifikasi atau alasan penolakan jika berkas belum sesuai..."
                    class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500"
                />
            </div>
        </div>

        <div
            v-if="error"
            role="alert"
            class="mt-4 flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2.5 text-xs text-rose-700 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300"
        >
            <Info class="h-4 w-4 shrink-0 text-rose-600 dark:text-rose-400" />
            <span>{{ error }}</span>
        </div>

        <p v-if="!activeApplicant" class="mt-4 text-xs text-slate-500">Data pendaftaran tidak lagi tersedia.</p>

        <div
            class="mt-4 flex items-start gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-[11px] text-slate-500 dark:border-slate-800 dark:bg-slate-900/40 dark:text-slate-400"
        >
            <Info class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400" />
            <p>
                Path berkas adalah data tersimpan, bukan bukti dokumen telah
                diperiksa secara fisik. Pratinjau dokumen belum tersedia.
            </p>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-between gap-2">
                <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    :disabled="saving"
                    @click="closeModal"
                >
                    Tutup
                </Button>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="activeApplicant"
                        :disabled="saving"
                        variant="danger"
                        size="sm"
                        @click="submitVerification('ditolak')"
                    >
                        Tolak Berkas
                    </Button>
                    <Button
                        v-if="activeApplicant"
                        :disabled="saving"
                        variant="primary"
                        size="sm"
                        @click="submitVerification('verifikasi_tendik')"
                    >
                        Verifikasi Berkas
                    </Button>
                </div>
            </div>
        </template>
    </Modal>
</template>
