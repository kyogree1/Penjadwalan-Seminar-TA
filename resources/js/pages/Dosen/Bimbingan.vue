<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Award,
    Check,
    CheckCircle2,
    Clock,
    FileCheck,
    FileText,
    Filter,
    MessageSquare,
    PenTool,
    Plus,
    Search,
    UserCheck,
    Users,
    X,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

// Advisees list for Dr. Ir. Tejo Wahyu Susanto
const advisees = ref([
    {
        id: 1,
        name: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        roleAs: 'Pembimbing 1',
        totalBimbingan: 9,
        targetBimbingan: 10,
        status: 'Pengerjaan TA Bab 4-5',
        logbooks: [
            {
                id: 101,
                tanggal: '15 September 2026',
                materi: 'Revisi Bab 4: Implementasi Algoritma Genetika & Crossover Rate',
                catatanMahasiswa:
                    'Sudah menyelesaikan perbaikan mutation rate 0.05 dan seleksi roulette wheel.',
                catatanDosen:
                    'Algoritma sudah cukup bagus. Tambahkan grafik konvergensi fitness di subbab 4.3.',
                status: 'approved',
            },
            {
                id: 102,
                tanggal: '10 September 2026',
                materi: 'Pengujian Blackbox & Perancangan Dashboard Tendik',
                catatanMahasiswa:
                    'Menambahkan use case pengelolaan ruangan dan validasi berkas fisik.',
                catatanDosen:
                    'Perhatikan hak akses role kaprodi dan dosen penguji agar tidak tumpang tindih.',
                status: 'approved',
            },
            {
                id: 103,
                tanggal: '18 September 2026',
                materi: 'Draft Naskah Lengkap Bab 1 s.d. Bab 5 & Hasil Kuesioner SUS',
                catatanMahasiswa:
                    'Mengajukan ACC lembar TA-04 untuk pendaftaran sidang akhir.',
                catatanDosen: '',
                status: 'pending',
            },
        ],
    },
    {
        id: 2,
        name: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        roleAs: 'Pembimbing 1',
        totalBimbingan: 12,
        targetBimbingan: 10,
        status: 'Siap Daftar Sidang',
        logbooks: [
            {
                id: 201,
                tanggal: '12 September 2026',
                materi: 'Review Hasil Pengujian Confusion Matrix & Perbandingan dengan ResNet50',
                catatanMahasiswa:
                    'Akurasi mencapai 94.2% pada dataset APTOS 2019.',
                catatanDosen:
                    'Hasil sangat memuaskan, silakan lanjut susun slide presentasi sidang.',
                status: 'approved',
            },
        ],
    },
    {
        id: 3,
        name: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        judul: 'Penerapan Internet of Things untuk Monitoring Kualitas Air Tambak Udang Berbasis LoRaWAN di Balikpapan',
        roleAs: 'Pembimbing 2',
        totalBimbingan: 6,
        targetBimbingan: 10,
        status: 'Proposal (Sempro)',
        logbooks: [
            {
                id: 301,
                tanggal: '08 September 2026',
                materi: 'Kalibrasi Sensor pH dan Turbidity dengan NodeMCU ESP32',
                catatanMahasiswa:
                    'Pengujian transmisi packet loss pada jarak 2.5 km.',
                catatanDosen:
                    'Lakukan kalibrasi ulang larutan buffer pH 4 dan pH 7 sebelum pengujian lapangan.',
                status: 'approved',
            },
        ],
    },
]);

const selectedAdviseeId = ref(1);
const selectedAdvisee = computed(() =>
    advisees.value.find((a) => a.id === selectedAdviseeId.value),
);

// Review Modal State
const showApprovalModal = ref(false);
const activeLogbook = ref<any>(null);
const feedbackInput = ref('');
const isAccBimbingan = ref(true);

const openReviewModal = (logbook: any) => {
    activeLogbook.value = logbook;
    feedbackInput.value = logbook.catatanDosen || '';
    isAccBimbingan.value = true;
    showApprovalModal.value = true;
};

const submitReview = () => {
    if (activeLogbook.value) {
        activeLogbook.value.catatanDosen = feedbackInput.value;
        activeLogbook.value.status = isAccBimbingan.value
            ? 'approved'
            : 'revision';
    }
    showApprovalModal.value = false;
};
</script>

<template>
    <AppLayout title="Bimbingan Mahasiswa (TA-04)">
        <Head title="Bimbingan Mahasiswa - Portal Dosen" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Portal Dosen Pembimbing"
                title="Bimbingan & Verifikasi Logbook (TA-04)"
                description="Pantau progres konsultasi mahasiswa bimbingan, berikan feedback naskah, dan setujui logbook bimbingan secara digital."
            />

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Bimbingan Aktif"
                    value="4 Mahasiswa"
                    subtitle="3 Pembimbing 1 • 1 Pembimbing 2"
                    :icon="Users"
                    icon-color="blue"
                />
                <StatCard
                    title="Menunggu Validasi"
                    value="1 Logbook"
                    subtitle="Perlu review & paraf dosen"
                    :icon="Clock"
                    icon-color="amber"
                />
                <StatCard
                    title="Siap Maju Sidang"
                    value="2 Mahasiswa"
                    subtitle="Syarat minimal 10x terpenuhi"
                    :icon="FileCheck"
                    icon-color="emerald"
                />
                <StatCard
                    title="Beban Kuota"
                    value="8 / 10"
                    subtitle="Sisa kuota: 2 mahasiswa"
                    :icon="UserCheck"
                    icon-color="indigo"
                />
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
                            {{ advisees.length }} Mahasiswa
                        </span>
                    </div>

                    <div class="space-y-2">
                        <button
                            v-for="mhs in advisees"
                            :key="mhs.id"
                            type="button"
                            class="w-full rounded-2xl border p-4 text-left transition-all"
                            :class="[
                                selectedAdviseeId === mhs.id
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
                                        {{ mhs.name }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ mhs.nim }} • Angkatan
                                        {{ mhs.angkatan }}
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

                            <div
                                class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-[10px] dark:border-slate-800"
                            >
                                <span class="font-medium text-slate-500">
                                    Progress Bimbingan:
                                </span>
                                <span
                                    class="font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ mhs.totalBimbingan }} /
                                    {{ mhs.targetBimbingan }} Pertemuan
                                </span>
                            </div>
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
                                        {{ selectedAdvisee.name }}
                                    </h2>
                                    <span
                                        class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        {{ selectedAdvisee.status }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">
                                    NIM: {{ selectedAdvisee.nim }} • Peran:
                                    {{ selectedAdvisee.roleAs }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 dark:border-blue-900 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    Logbook:
                                    {{ selectedAdvisee.logbooks.length }} Sesi
                                    Tercatat
                                </span>
                            </div>
                        </div>

                        <!-- Title Box -->
                        <div
                            class="mt-4 rounded-xl bg-slate-50 p-3.5 text-xs dark:bg-slate-900/60"
                        >
                            <span
                                class="font-bold text-slate-700 dark:text-slate-300"
                                >Judul Tugas Akhir:</span
                            >
                            <p
                                class="mt-0.5 text-slate-600 dark:text-slate-400"
                            >
                                {{ selectedAdvisee.judul }}
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
                                v-for="(log, idx) in selectedAdvisee.logbooks"
                                :key="log.id"
                                class="relative rounded-2xl border p-4 transition-all"
                                :class="[
                                    log.status === 'pending'
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
                                                class="text-xs font-extrabold text-slate-900 dark:text-white"
                                            >
                                                Pertemuan ke-{{
                                                    selectedAdvisee.logbooks
                                                        .length - idx
                                                }}
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
                                        <p
                                            class="mt-1 text-xs font-bold text-blue-600 dark:text-blue-400"
                                        >
                                            {{ log.materi }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            v-if="log.status === 'approved'"
                                            class="inline-flex items-center gap-1 rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                        >
                                            <Check class="h-3 w-3" /> Disetujui
                                            (ACC)
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                        >
                                            <Clock class="h-3 w-3" /> Menunggu
                                            Validasi
                                        </span>

                                        <button
                                            type="button"
                                            class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                            @click="openReviewModal(log)"
                                        >
                                            <span
                                                v-if="log.status === 'pending'"
                                                >Review & ACC</span
                                            >
                                            <span v-else>Edit Catatan</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Student notes -->
                                <div
                                    class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-700 dark:bg-slate-900 dark:text-slate-300"
                                >
                                    <span
                                        class="font-bold text-slate-900 dark:text-white"
                                        >Uraian Kemajuan Mahasiswa:</span
                                    >
                                    <p class="mt-0.5 leading-relaxed">
                                        {{ log.catatanMahasiswa }}
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
                        </div>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Approval / Review Modal -->
        <Modal
            :show="showApprovalModal"
            max-width="lg"
            @close="showApprovalModal = false"
        >
            <div class="p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Validasi & Catatan Pembimbing (TA-04)
                    </h3>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="showApprovalModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Keputusan Bimbingan
                        </label>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <button
                                type="button"
                                class="flex items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition-all"
                                :class="[
                                    isAccBimbingan
                                        ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                                ]"
                                @click="isAccBimbingan = true"
                            >
                                <Check class="h-4 w-4" /> Setujui (ACC
                                Bimbingan)
                            </button>
                            <button
                                type="button"
                                class="flex items-center justify-center gap-2 rounded-xl border p-2.5 text-xs font-bold transition-all"
                                :class="[
                                    !isAccBimbingan
                                        ? 'border-amber-600 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                        : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                                ]"
                                @click="isAccBimbingan = false"
                            >
                                <Clock class="h-4 w-4" /> Perlu Perbaikan
                                (Revisi)
                            </button>
                        </div>
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Catatan & Arahan untuk Mahasiswa (Wajib)
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
                        @click="showApprovalModal = false"
                    >
                        Batal
                    </Button>
                    <Button variant="primary" @click="submitReview">
                        Simpan & Beri Paraf Digital
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
