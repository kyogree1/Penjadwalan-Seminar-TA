<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Clock,
    FileCheck,
    FileText,
    Filter,
    HelpCircle,
    PenTool,
    Search,
    ShieldCheck,
    Users,
    X,
} from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import {
    mockJudulSubmissions,
    mockSemproSubmissions,
    mockSidangSubmissions,
} from '@/data/kaprodi/persetujuan';
import { statusToBadge } from '@/lib/status';
import type {
    AcademicSubmission,
    JudulSubmission,
    StatusPengajuan,
} from '@/types/models';

const props = withDefaults(
    defineProps<{
        judulSubmissions?: JudulSubmission[];
        semproSubmissions?: AcademicSubmission[];
        sidangSubmissions?: AcademicSubmission[];
    }>(),
    {
        judulSubmissions: () => mockJudulSubmissions,
        semproSubmissions: () => mockSemproSubmissions,
        sidangSubmissions: () => mockSidangSubmissions,
    },
);

const activeSection = ref<'judul' | 'sempro' | 'sidang'>('judul');
const academicSearch = ref('');
const academicAngkatan = ref('Semua');

const submissions = ref<JudulSubmission[]>([...props.judulSubmissions]);
const semproSubmissions = ref<AcademicSubmission[]>([
    ...props.semproSubmissions,
]);
const sidangSubmissions = ref<AcademicSubmission[]>([
    ...props.sidangSubmissions,
]);

const activeAcademicSubmissions = computed(() =>
    activeSection.value === 'sidang'
        ? sidangSubmissions.value
        : semproSubmissions.value,
);

const filteredAcademicSubmissions = computed(() => {
    const query = academicSearch.value.toLowerCase().trim();
    return activeAcademicSubmissions.value.filter((item) => {
        const matchesAngkatan =
            academicAngkatan.value === 'Semua' ||
            item.angkatan === academicAngkatan.value;
        const matchesQuery =
            !query ||
            [item.nama, item.nim, item.judul].some((value) =>
                value.toLowerCase().includes(query),
            );
        return matchesAngkatan && matchesQuery;
    });
});

const selectedAcademicSubmission = ref<AcademicSubmission | null>(null);
const showAcademicModal = ref(false);
const academicNote = ref('');
const academicDecision = ref<'Sempro' | 'Sidang TA'>('Sempro');

// Toast
const toast = ref('');
const showToast = (msg: string) => {
    toast.value = msg;
    setTimeout(() => {
        toast.value = '';
    }, 3500);
};

const openAcademicDetail = (item: AcademicSubmission) => {
    selectedAcademicSubmission.value = item;
    academicNote.value = item.catatan || '';
    academicDecision.value =
        activeSection.value === 'sidang' ? 'Sidang TA' : 'Sempro';
    showAcademicModal.value = true;
};

const updateAcademicStatus = (status: StatusPengajuan) => {
    if (
        !selectedAcademicSubmission.value ||
        (status === 'revisi' && !academicNote.value.trim()) ||
        (status === 'disetujui' &&
            selectedAcademicSubmission.value.requirements?.some(
                (req) => !req.ready,
            ))
    )
        return;

    selectedAcademicSubmission.value.status = status;
    selectedAcademicSubmission.value.catatan = academicNote.value.trim();
    showAcademicModal.value = false;
    showToast(
        `Pengajuan ${academicDecision.value} untuk ${selectedAcademicSubmission.value.nama} berhasil diperbarui statusnya (${statusToBadge(status).label}).`,
    );
};

// Judul Approval Modal State
const showModal = ref(false);
const activeItem = ref<JudulSubmission | null>(null);
const selectedP1 = ref('');
const selectedP2 = ref('');
const skNumber = ref('042/ITK/FSTI/IF/TA/2026');

const openApprovalModal = (item: JudulSubmission) => {
    activeItem.value = item;
    selectedP1.value = item.pembimbing1Final || item.pembimbing1Usulan;
    selectedP2.value = item.pembimbing2Final || item.pembimbing2Usulan;
    showModal.value = true;
};

const confirmApproval = () => {
    if (activeItem.value) {
        activeItem.value.status = 'disetujui';
        activeItem.value.pembimbing1Final = selectedP1.value;
        activeItem.value.pembimbing2Final = selectedP2.value;
        showToast(
            `Judul mahasiswa "${activeItem.value.nama}" berhasil disetujui dan SK Pembimbing (${skNumber.value}) diterbitkan!`,
        );
    }
    showModal.value = false;
};
</script>

<template>
    <Head title="Persetujuan Akademik • SIPTA IF" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Wewenang Ketua Program Studi"
            title="Persetujuan Akademik Tugas Akhir"
            description="Tinjau judul, Seminar Proposal, dan Sidang TA. Verifikasi kelengkapan berkas fisik tetap menjadi kewenangan Tendik."
        />

        <!-- Toast Notice -->
        <div
            v-if="toast"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-300"
        >
            {{ toast }}
        </div>

        <!-- Tab Selection -->
        <div
            class="flex w-fit gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800"
        >
            <button
                type="button"
                class="cursor-pointer rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                :class="[
                    activeSection === 'judul'
                        ? 'bg-white text-blue-700 shadow-xs dark:bg-slate-900 dark:text-blue-300'
                        : 'text-slate-500 dark:text-slate-400',
                ]"
                @click="activeSection = 'judul'"
            >
                Persetujuan Judul ({{ submissions.length }})
            </button>
            <button
                type="button"
                class="cursor-pointer rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                :class="[
                    activeSection === 'sempro'
                        ? 'bg-white text-blue-700 shadow-xs dark:bg-slate-900 dark:text-blue-300'
                        : 'text-slate-500 dark:text-slate-400',
                ]"
                @click="activeSection = 'sempro'"
            >
                Pengajuan Sempro ({{ semproSubmissions.length }})
            </button>
            <button
                type="button"
                class="cursor-pointer rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                :class="[
                    activeSection === 'sidang'
                        ? 'bg-white text-blue-700 shadow-xs dark:bg-slate-900 dark:text-blue-300'
                        : 'text-slate-500 dark:text-slate-400',
                ]"
                @click="activeSection = 'sidang'"
            >
                Pengajuan Sidang TA ({{ sidangSubmissions.length }})
            </button>
        </div>

        <!-- SECTION 1: PERSETUJUAN JUDUL -->
        <Card v-if="activeSection === 'judul'" class="p-6">
            <div
                class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center dark:border-slate-800"
            >
                <div>
                    <h3
                        class="text-sm font-extrabold text-slate-900 dark:text-white"
                    >
                        Daftar Pengajuan Judul Mahasiswa
                    </h3>
                    <p class="text-xs text-slate-500">
                        Persetujuan judul merupakan syarat utama sebelum
                        mahasiswa memulai konsultasi bimbingan TA-04
                    </p>
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
                            <th class="px-4 py-3">Mahasiswa</th>
                            <th class="px-4 py-3">Topik & Judul Tugas Akhir</th>
                            <th class="px-4 py-3">Bidang Keahlian</th>
                            <th class="px-4 py-3">Dosen Pembimbing</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="item in submissions"
                            :key="item.id"
                            class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40"
                        >
                            <td class="px-4 py-3.5">
                                <p
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.nama }}
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    NIM: {{ item.nim }} • Angkatan
                                    {{ item.angkatan }}
                                </p>
                            </td>
                            <td class="max-w-xs px-4 py-3.5">
                                <p
                                    class="line-clamp-2 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    {{ item.judul }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <span
                                    class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ item.bidang }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-[11px]">
                                <p>
                                    <span class="font-semibold text-slate-500"
                                        >P1:</span
                                    >
                                    {{ item.pembimbing1Final }}
                                </p>
                                <p>
                                    <span class="font-semibold text-slate-500"
                                        >P2:</span
                                    >
                                    {{ item.pembimbing2Final }}
                                </p>
                            </td>
                            <td class="px-4 py-3.5">
                                <StatusBadge
                                    size="sm"
                                    :variant="
                                        statusToBadge(item.status).variant
                                    "
                                    :text="statusToBadge(item.status).label"
                                />
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="openApprovalModal(item)"
                                >
                                    {{
                                        item.status === 'disetujui'
                                            ? 'Detail SK'
                                            : 'Review & ACC'
                                    }}
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- SECTION 2 & 3: SEMPRO & SIDANG -->
        <Card v-else class="space-y-4 p-5">
            <div class="flex flex-wrap gap-3">
                <div class="relative min-w-0 flex-1">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="academicSearch"
                        :aria-label="
                            'Cari pengajuan ' +
                            (activeSection === 'sidang'
                                ? 'Sidang TA'
                                : 'Sempro')
                        "
                        placeholder="Cari nama, NIM, atau judul..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    />
                </div>
                <select
                    v-model="academicAngkatan"
                    aria-label="Filter angkatan"
                    class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                >
                    <option>Semua</option>
                    <option>2023</option>
                    <option>2022</option>
                    <option>2021</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                    >
                        <tr>
                            <th class="p-3">Mahasiswa</th>
                            <th class="p-3">Judul</th>
                            <th class="p-3">Pembimbing</th>
                            <th v-if="activeSection === 'sidang'" class="p-3">
                                Kelayakan
                            </th>
                            <th class="p-3">Keputusan Akademik</th>
                            <th class="p-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in filteredAcademicSubmissions"
                            :key="item.id"
                            class="border-t border-slate-200 dark:border-slate-800"
                        >
                            <td class="p-3">
                                <p
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.nama }}
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    NIM: {{ item.nim }} · Angkatan
                                    {{ item.angkatan }}
                                </p>
                            </td>
                            <td class="max-w-sm p-3">
                                <p
                                    class="line-clamp-2 text-slate-800 dark:text-slate-200"
                                >
                                    {{ item.judul }}
                                </p>
                            </td>
                            <td class="p-3 text-[11px]">
                                {{ item.pembimbing }}
                            </td>
                            <td v-if="activeSection === 'sidang'" class="p-3">
                                <div
                                    v-if="'requirements' in item"
                                    class="space-y-0.5"
                                >
                                    <div
                                        v-for="req in item.requirements"
                                        :key="req.label"
                                        class="flex items-center gap-1 text-[10px]"
                                    >
                                        <CheckCircle2
                                            v-if="req.ready"
                                            class="h-3 w-3 text-emerald-500"
                                        />
                                        <X
                                            v-else
                                            class="h-3 w-3 text-red-400"
                                        />
                                        <span
                                            :class="
                                                req.ready
                                                    ? 'text-slate-600 dark:text-slate-400'
                                                    : 'font-semibold text-red-600 dark:text-red-400'
                                            "
                                            >{{ req.label }}</span
                                        >
                                    </div>
                                </div>
                            </td>
                            <td class="p-3">
                                <StatusBadge
                                    size="sm"
                                    :variant="
                                        statusToBadge(item.status).variant
                                    "
                                    :text="statusToBadge(item.status).label"
                                />
                            </td>
                            <td class="p-3 text-right">
                                <Button
                                    size="sm"
                                    variant="outline"
                                    @click="openAcademicDetail(item)"
                                >
                                    Tinjau
                                </Button>
                            </td>
                        </tr>
                        <tr v-if="!filteredAcademicSubmissions.length">
                            <td
                                :colspan="activeSection === 'sidang' ? 6 : 5"
                                class="p-6 text-center text-slate-500"
                            >
                                Tidak ada pengajuan yang sesuai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <!-- MODAL 1: ACADEMIC REVIEW MODAL -->
    <Modal
        :show="showAcademicModal"
        max-width="lg"
        @close="showAcademicModal = false"
    >
        <div v-if="selectedAcademicSubmission" class="space-y-4 p-6 text-sm">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Tinjauan Akademik {{ academicDecision }}
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="showAcademicModal = false"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="space-y-1.5 text-xs">
                <p class="font-bold text-slate-900 dark:text-white">
                    {{ selectedAcademicSubmission.nama }} ·
                    {{ selectedAcademicSubmission.nim }}
                </p>
                <p class="text-slate-700 dark:text-slate-300">
                    {{ selectedAcademicSubmission.judul }}
                </p>
                <p class="text-slate-500">
                    Pembimbing:
                    <b>{{ selectedAcademicSubmission.pembimbing }}</b>
                </p>
                <p class="text-slate-500">
                    Jadwal Pelaksanaan:
                    <b>{{ selectedAcademicSubmission.jadwal }}</b>
                </p>
            </div>

            <div
                class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs dark:border-slate-700 dark:bg-slate-800/60"
            >
                <span
                    class="mb-1 block font-bold text-slate-700 dark:text-slate-300"
                >
                    Lampiran Berkas Mahasiswa:
                </span>
                <ul class="space-y-1 text-slate-600 dark:text-slate-400">
                    <li
                        v-for="file in selectedAcademicSubmission.files"
                        :key="file"
                        class="flex items-center gap-1.5"
                    >
                        <span>📄</span>
                        <span>{{ file }}</span>
                    </li>
                </ul>
            </div>

            <div>
                <label
                    class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300"
                    for="sempro-note"
                >
                    Catatan Akademik & Arahan Kaprodi
                </label>
                <textarea
                    id="sempro-note"
                    v-model="academicNote"
                    rows="3"
                    placeholder="Tuliskan catatan telaah akademik untuk mahasiswa..."
                    class="w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                />
            </div>

            <div
                class="flex flex-wrap justify-end gap-2 border-t border-slate-200 pt-2 dark:border-slate-800"
            >
                <Button variant="secondary" @click="showAcademicModal = false">
                    Tutup
                </Button>
                <Button
                    variant="outline"
                    :disabled="!academicNote.trim()"
                    @click="updateAcademicStatus('revisi')"
                >
                    Minta Revisi
                </Button>
                <Button
                    variant="primary"
                    :disabled="
                        selectedAcademicSubmission.requirements?.some(
                            (req) => !req.ready,
                        )
                    "
                    @click="updateAcademicStatus('disetujui')"
                >
                    Setujui Akademik
                </Button>
            </div>
        </div>
    </Modal>

    <!-- MODAL 2: PENETAPAN PEMBIMBING & SK (JUDUL) -->
    <Modal :show="showModal" max-width="lg" @close="showModal = false">
        <div class="p-6">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
            >
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Penetapan Dosen Pembimbing & Penerbitan SK
                </h3>
                <button
                    type="button"
                    class="cursor-pointer text-slate-400 hover:text-slate-600"
                    @click="showModal = false"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div v-if="activeItem" class="mt-4 space-y-4 text-xs">
                <div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">
                        Mahasiswa Pengusul:
                    </span>
                    <p class="font-semibold text-slate-900 dark:text-white">
                        {{ activeItem.nama }} ({{ activeItem.nim }})
                    </p>
                </div>

                <div>
                    <span class="font-bold text-slate-700 dark:text-slate-300">
                        Judul Tugas Akhir:
                    </span>
                    <p
                        class="mt-1 rounded-xl bg-slate-50 p-3 leading-relaxed text-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        {{ activeItem.judul }}
                    </p>
                </div>

                <!-- P1 Selection -->
                <div>
                    <label
                        class="block font-bold text-slate-700 dark:text-slate-300"
                    >
                        Tetapkan Dosen Pembimbing 1 (Utama)
                    </label>
                    <input
                        v-model="selectedP1"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <!-- P2 Selection -->
                <div>
                    <label
                        class="block font-bold text-slate-700 dark:text-slate-300"
                    >
                        Tetapkan Dosen Pembimbing 2 (Pendamping)
                    </label>
                    <input
                        v-model="selectedP2"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <!-- Nomor SK -->
                <div>
                    <label
                        class="block font-bold text-slate-700 dark:text-slate-300"
                    >
                        Nomor Surat Keputusan (SK) Kaprodi
                    </label>
                    <input
                        v-model="skNumber"
                        type="text"
                        class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <Button variant="secondary" @click="showModal = false">
                    Tutup
                </Button>
                <Button variant="primary" @click="confirmApproval">
                    Setujui & Terbitkan SK
                </Button>
            </div>
        </div>
    </Modal>
</template>
