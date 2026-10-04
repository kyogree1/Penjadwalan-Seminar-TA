<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Clock,
    Download,
    Eye,
    FileCheck,
    FileText,
    Filter,
    HelpCircle,
    Search,
    ShieldAlert,
    ShieldCheck,
    X,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';

const activeTab = ref<'Sempro' | 'Sidang'>('Sidang');
const searchQuery = ref('');

const filteredApplicants = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return applicants.value.filter((app) => {
        const matchesTab =
            activeTab.value === 'Sidang'
                ? app.tipe.includes('Sidang')
                : app.tipe.includes('Seminar');
        const matchesSearch =
            !query ||
            app.nama.toLowerCase().includes(query) ||
            app.nim.includes(query) ||
            app.judul.toLowerCase().includes(query);
        return matchesTab && matchesSearch;
    });
});

const applicants = ref([
    {
        id: 1,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        tipe: 'Sidang Akhir TA',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto',
        pembimbing2: 'Gusti Ahmad Fanshuri, M.Cs.',
        tanggalDaftar: '18 September 2026',
        items: [
            {
                label: 'Bukti Pembayaran UKT Semester Ganjil 2026/2027',
                file: 'Bukti_UKT_11231006.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Akademik (140 SKS, IPK 3.82, Bebas D/E)',
                file: 'Transkrip_11231006.pdf',
                valid: true,
            },
            {
                label: 'Formulir TA-04 (ACC Pembimbing 1 & 2)',
                file: 'Form_TA04_Signed.pdf',
                valid: true,
            },
            {
                label: 'Sertifikat TOEFL ITK (Skor 510)',
                file: 'TOEFL_Certificate.pdf',
                valid: true,
            },
            {
                label: 'Laporan Turnitin Similarity Index (14%)',
                file: 'Turnitin_Report_14pct.pdf',
                valid: true,
            },
        ],
        status: 'pending',
    },
    {
        id: 2,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        tipe: 'Sidang Akhir TA',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto',
        pembimbing2: 'Dewi Ratnasari, S.T., M.T.',
        tanggalDaftar: '18 September 2026',
        items: [
            {
                label: 'Bukti Pembayaran UKT Semester Ganjil 2026/2027',
                file: 'UKT_11211045.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Akademik (142 SKS, IPK 3.75, Bebas D/E)',
                file: 'Transkrip_11211045.pdf',
                valid: true,
            },
            {
                label: 'Formulir TA-04 (ACC Pembimbing 1 & 2)',
                file: 'ACC_TA04_11211045.pdf',
                valid: true,
            },
            {
                label: 'Sertifikat TOEFL ITK (Skor 485)',
                file: 'TOEFL_11211045.pdf',
                valid: true,
            },
            {
                label: 'Laporan Turnitin Similarity Index (11%)',
                file: 'Turnitin_11pct.pdf',
                valid: true,
            },
        ],
        status: 'pending',
    },
    {
        id: 3,
        nama: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        tipe: 'Seminar Proposal',
        judul: 'Penerapan Internet of Things untuk Monitoring Kualitas Air Tambak Udang Berbasis LoRaWAN di Balikpapan',
        pembimbing1: 'Prof. Dr. Agus Tri Haryanto',
        pembimbing2: 'Dr. Ir. Tejo Wahyu Susanto',
        tanggalDaftar: '17 September 2026',
        items: [
            {
                label: 'KRS Aktif Mata Kuliah Seminar Proposal',
                file: 'KRS_11221089.pdf',
                valid: true,
            },
            {
                label: 'Transkrip Sementara (Min 110 SKS)',
                file: 'Transkrip_11221089.pdf',
                valid: true,
            },
            {
                label: 'Formulir Persetujuan Sempro (TA-02)',
                file: 'ACC_Sempro_Signed.pdf',
                valid: true,
            },
            {
                label: 'Draft Proposal Naskah Bab 1-3',
                file: 'Proposal_Bagus_Fix.pdf',
                valid: true,
            },
        ],
        status: 'pending',
    },
]);

// Modal State
const showModal = ref(false);
const activeApplicant = ref<any>(null);
const tendikNotes = ref('');

const openVerification = (app: any) => {
    activeApplicant.value = app;
    tendikNotes.value =
        'Berkas lengkap dan memenuhi seluruh persyaratan administratif program studi.';
    showModal.value = true;
};

const verifySuccess = () => {
    if (activeApplicant.value) {
        activeApplicant.value.status = 'verified';
    }
    showModal.value = false;
};

const requestRevision = () => {
    if (activeApplicant.value) {
        activeApplicant.value.status = 'revision';
    }
    showModal.value = false;
};
</script>

<template>
    <AppLayout title="Verifikasi Berkas Mahasiswa">
        <Head title="Verifikasi Berkas - Portal Tendik" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Layanan Administrasi Akademik"
                title="Verifikasi Persyaratan Seminar Proposal & Sidang TA"
                description="Periksa keaslian dokumen kelayakan administratif mahasiswa sebelum didistribusikan ke modul Penjadwalan Algoritma Genetika."
            />

            <!-- Main Table of Applicants -->
            <Card class="p-6">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center dark:border-slate-800"
                >
                    <div>
                        <h3
                            class="text-sm font-extrabold text-slate-900 dark:text-white"
                        >
                            Berkas Pendaftaran Masuk
                        </h3>
                        <p class="text-xs text-slate-500">
                            Pendaftar yang telah mengunggah berkas persyaratan
                            secara lengkap
                        </p>
                    </div>

                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center"
                    >
                        <!-- Search Box -->
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama, NIM, atau judul..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-4 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden sm:w-64 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                            />
                        </div>

                        <!-- Type Filter Pills -->
                        <div
                            class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-100/70 p-1 text-xs font-bold dark:border-slate-800 dark:bg-slate-900"
                        >
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1 transition-all"
                                :class="[
                                    activeTab === 'Sidang'
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400',
                                ]"
                                @click="activeTab = 'Sidang'"
                            >
                                Sidang Akhir TA (2)
                            </button>
                            <button
                                type="button"
                                class="rounded-lg px-3 py-1 transition-all"
                                :class="[
                                    activeTab === 'Sempro'
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400',
                                ]"
                                @click="activeTab = 'Sempro'"
                            >
                                Seminar Proposal (1)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table
                        v-if="filteredApplicants.length"
                        class="w-full text-left text-xs text-slate-600 dark:text-slate-300"
                    >
                        <thead
                            class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Nama Mahasiswa</th>
                                <th class="px-4 py-3">Tipe Pendaftaran</th>
                                <th class="px-4 py-3">Judul Tugas Akhir</th>
                                <th class="px-4 py-3">Tanggal Unggah</th>
                                <th class="px-4 py-3">Status Verifikasi</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="app in filteredApplicants"
                                :key="app.id"
                                class="cursor-pointer hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                                @click="openVerification(app)"
                            >
                                <td class="px-4 py-3.5">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ app.nama }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ app.nim }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                                    >
                                        {{ app.tipe }}
                                    </span>
                                </td>
                                <td class="max-w-xs px-4 py-3.5">
                                    <p
                                        class="line-clamp-2 font-medium text-slate-800 dark:text-slate-200"
                                    >
                                        {{ app.judul }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 text-slate-500">
                                    {{ app.tanggalDaftar }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                        :class="[
                                            app.status === 'verified'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : app.status === 'revision'
                                                  ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                                  : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
                                        ]"
                                    >
                                        {{
                                            app.status === 'verified'
                                                ? 'Lolos Verifikasi'
                                                : app.status === 'revision'
                                                  ? 'Perlu Revisi'
                                                  : 'Menunggu Validasi'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right" @click.stop>
                                    <Button
                                        size="sm"
                                        variant="primary"
                                        @click="openVerification(app)"
                                    >
                                        Periksa Berkas
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
                                    Coba ubah kata kunci pencarian atau filter
                                    tab.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </Card>
        </div>

        <!-- Document Checklist Modal -->
        <Modal :show="showModal" max-width="lg" @close="showModal = false">
            <div class="p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <div>
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Checklist Berkas Persyaratan
                        </h3>
                        <p
                            v-if="activeApplicant"
                            class="text-xs text-slate-500"
                        >
                            {{ activeApplicant.nama }} ({{
                                activeApplicant.nim
                            }}) • {{ activeApplicant.tipe }}
                        </p>
                    </div>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="showModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div v-if="activeApplicant" class="mt-4 space-y-3">
                    <div
                        v-for="(item, idx) in activeApplicant.items"
                        :key="idx"
                        class="flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/50"
                    >
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                            >
                                <Check class="h-4 w-4" />
                            </div>
                            <div>
                                <p
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.label }}
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    {{ item.file }}
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                        >
                            <Eye class="h-3.5 w-3.5" /> Lihat PDF
                        </button>
                    </div>

                    <!-- Notes textarea -->
                    <div class="mt-4">
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Catatan Petugas Tendik (Wajib):
                        </label>
                        <textarea
                            v-model="tendikNotes"
                            rows="3"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="danger" @click="requestRevision">
                        Tolak / Minta Revisi
                    </Button>
                    <Button variant="primary" @click="verifySuccess">
                        Verifikasi & Teruskan ke Penjadwalan
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
