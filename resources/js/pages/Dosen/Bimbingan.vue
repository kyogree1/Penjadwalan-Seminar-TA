<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Check,
    CheckCircle2,
    ClipboardList,
    Clock,
    PenTool,
    Search,
    TrendingUp,
    X,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import type {
    LiveBimbinganSession,
    LiveAdvisee,
    LogbookProps,
} from '@/types/dosen-bimbingan';

const props = defineProps<LogbookProps>();

// Fallback dataset matching Front-End mockData.js with 5 sessions per supervisor standard
const defaultMockAdvisees: LiveAdvisee[] = [
    {
        id: 1,
        nama: 'Muhammad Azka Yunastio',
        nim: '11231036',
        judul: 'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Penjadwalan Seminar Tugas Akhir',
        roleAs: 'Pembimbing 1',
        pembimbing1: 'Dra. Nurul Rahmania, M.T.',
        pembimbing2: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        sesiList: [
            {
                id: 101,
                no: 1,
                tanggal: '2026-08-10',
                keterangan: 'Bab 1 - Pendahuluan',
                rangkuman:
                    'Diskusi rumusan masalah antrian penjadwalan seminar manual di prodi Informatika ITK.',
                catatanDosen:
                    'Perkuat rumusan masalah dengan perbandingan jurnal terkait Algoritma Genetika.',
                status: 'delivered',
            },
            {
                id: 102,
                no: 2,
                tanggal: '2026-08-18',
                keterangan: 'Bab 1 - Batasan Masalah & Variabel',
                rangkuman:
                    'Penetapan batasan sistem: penjadwalan tanpa ruangan fisik, fokus kuota dan ketersediaan waktu dosen.',
                catatanDosen:
                    'Batasan masalah sudah spesifik. Lanjutkan tinjauan pustaka Bab 2.',
                status: 'delivered',
            },
            {
                id: 103,
                no: 3,
                tanggal: '2026-08-25',
                keterangan: 'Bab 2 - Tinjauan Pustaka & State of the Art',
                rangkuman:
                    'Kajian komparasi penelitian penjadwalan GA 5 tahun terakhir dan metrik evaluasi fitness.',
                catatanDosen: 'Tambahkan referensi evaluasi NLP untuk chatbot.',
                status: 'delivered',
            },
            {
                id: 104,
                no: 4,
                tanggal: '2026-09-02',
                keterangan: 'Bab 3 - Representasi Kromosom GA',
                rangkuman:
                    'Perancangan kromosom: gen mahasiswa, slot waktu dosen, dan dosen penguji 1 & 2.',
                catatanDosen:
                    'Representasi kromosom valid. Tentukan penalty weight untuk bentrok jadwal.',
                status: 'delivered',
            },
            {
                id: 105,
                no: 5,
                tanggal: '2026-09-10',
                keterangan: 'Bab 3 - Use Case & Activity Diagram',
                rangkuman:
                    'Review 21 activity diagram sistem SIPTA IF dan alur swimlane aktor.',
                catatanDosen: 'Acc maju Seminar Proposal.',
                status: 'delivered',
            },
        ],
    },
    {
        id: 2,
        nama: 'Hari Yatun Nasifa',
        nim: '11231030',
        judul: 'Pengembangan Antarmuka Sistem Informasi Akademik Tugas Akhir Berbasis Desain Komponen Reusable dan Responsif',
        roleAs: 'Pembimbing 1',
        pembimbing1: 'Dian Permata Sari, S.Kom., M.Kom.',
        pembimbing2: 'Rian Kurniawan, S.Kom., M.Eng.',
        sesiList: [
            {
                id: 201,
                no: 1,
                tanggal: '2026-08-12',
                keterangan: 'Bab 1 - Latar Belakang UI/UX',
                rangkuman: 'Analisis kebutuhan antarmuka dosen dan mahasiswa.',
                catatanDosen: 'Perjelas metodologi Human-Centered Design.',
                status: 'delivered',
            },
            {
                id: 202,
                no: 2,
                tanggal: '2026-08-20',
                keterangan: 'Bab 1 - Rumusan Masalah',
                rangkuman: 'Fokus responsivitas mobile dan dark mode.',
                catatanDosen: 'Revisi batasan browser target.',
                status: 'delivered',
            },
            {
                id: 203,
                no: 3,
                tanggal: '2026-08-28',
                keterangan: 'Bab 2 - Tinjauan Pustaka',
                rangkuman: 'Komponen reusable di Vue 3 dan Tailwind CSS.',
                catatanDosen: 'Bagus, lanjutkan.',
                status: 'delivered',
            },
            {
                id: 204,
                no: 4,
                tanggal: '2026-09-05',
                keterangan: 'Bab 3 - Wireframe & Design System',
                rangkuman: 'Presentasi mockup dashboard dan halaman bimbingan.',
                catatanDosen: 'Tingkatkan kontras warna pada status badge.',
                status: 'process',
            },
        ],
    },
    {
        id: 3,
        nama: 'Junnior Marcellino Polla',
        nim: '11231034',
        judul: 'Rancang Bangun Arsitektur Microservices dan Keamanan Data pada Portal Layanan Ujian Tugas Akhir',
        roleAs: 'Pembimbing 1',
        pembimbing1: 'Dr. Budi Wicaksono, M.Kom.',
        pembimbing2: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        sesiList: [
            {
                id: 301,
                no: 1,
                tanggal: '2026-08-15',
                keterangan: 'Bab 1 - Pendahuluan',
                rangkuman:
                    'Urgensi pemisahan service autentikasi dan service penjadwalan.',
                catatanDosen: 'Perjelas arsitektur API Gateway.',
                status: 'delivered',
            },
            {
                id: 302,
                no: 2,
                tanggal: '2026-08-26',
                keterangan: 'Bab 1 - Analisis Ancaman Keamanan',
                rangkuman: 'Penerapan JWT dan otorisasi RBAC.',
                catatanDosen: 'Tambahkan mitigasi serangan CSRF & XSS.',
                status: 'delivered',
            },
            {
                id: 303,
                no: 3,
                tanggal: '2026-09-04',
                keterangan: 'Bab 2 - Tinjauan Pustaka Microservices',
                rangkuman:
                    'Perbandingan monolitik vs microservices pada aplikasi akademik.',
                catatanDosen: 'Cari acuan standar ISO/IEC 25010.',
                status: 'process',
            },
        ],
    },
    {
        id: 4,
        nama: 'Mochammad Reezqi Pratama',
        nim: '11231041',
        judul: 'Optimasi Alokasi Dosen Penguji Ujian Skripsi dengan Pendekatan Constraint Satisfaction Problem dan Algoritma Genetika',
        roleAs: 'Pembimbing 2',
        pembimbing1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        pembimbing2: 'Dra. Nurul Rahmania, M.T.',
        sesiList: [
            {
                id: 401,
                no: 1,
                tanggal: '2026-08-08',
                keterangan: 'Bab 1 - Formulasi Masalah Optimasi',
                rangkuman:
                    'Pemodelan hard constraint dan soft constraint pada penjadwalan.',
                catatanDosen:
                    'Fokuskan pada kuota dosen dan ketersediaan waktu.',
                status: 'delivered',
            },
            {
                id: 402,
                no: 2,
                tanggal: '2026-08-16',
                keterangan: 'Bab 1 - Batasan Sistem',
                rangkuman: 'Penetapan ruang lingkup seminar prodi IF.',
                catatanDosen: 'Disetujui.',
                status: 'delivered',
            },
            {
                id: 403,
                no: 3,
                tanggal: '2026-08-24',
                keterangan: 'Bab 2 - Algoritma Genetika',
                rangkuman: 'Penjelasan Two-point crossover dan mutation rate.',
                catatanDosen: 'Gunakan mutation rate 3% sesuai literatur.',
                status: 'delivered',
            },
            {
                id: 404,
                no: 4,
                tanggal: '2026-09-01',
                keterangan: 'Bab 3 - Alur Komputasi AI',
                rangkuman: 'Diagram alir optimasi penjadwalan GA.',
                catatanDosen: 'Bagus, lanjutkan.',
                status: 'delivered',
            },
            {
                id: 405,
                no: 5,
                tanggal: '2026-09-09',
                keterangan: 'Bab 3 - Draf Lengkap',
                rangkuman: 'Review kelayakan maju sempro.',
                catatanDosen: 'Acc maju sempro.',
                status: 'delivered',
            },
        ],
    },
    {
        id: 5,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        judul: 'Rancang Bangun Frontend Dashboard Monitoring Progres Bimbingan Tugas Akhir Menggunakan Progressive Web Apps',
        roleAs: 'Pembimbing 1',
        pembimbing1: 'Rian Kurniawan, S.Kom., M.Eng.',
        pembimbing2: 'Dian Permata Sari, S.Kom., M.Kom.',
        sesiList: [
            {
                id: 501,
                no: 1,
                tanggal: '2026-08-11',
                keterangan: 'Bab 1 - Pendahuluan',
                rangkuman:
                    'Latar belakang kendala monitoring bimbingan via chat informal.',
                catatanDosen: 'Perjelas fitur PWA yang akan diimplementasikan.',
                status: 'delivered',
            },
            {
                id: 502,
                no: 2,
                tanggal: '2026-08-19',
                keterangan: 'Bab 1 - Rumusan Masalah',
                rangkuman:
                    'Arsitektur komponen frontend dan sinkronisasi offline.',
                catatanDosen: 'Disetujui.',
                status: 'delivered',
            },
            {
                id: 503,
                no: 3,
                tanggal: '2026-08-27',
                keterangan: 'Bab 2 - Service Worker & PWA',
                rangkuman: 'Tinjauan teknologi caching Service Worker.',
                catatanDosen: 'Bagus.',
                status: 'delivered',
            },
            {
                id: 504,
                no: 4,
                tanggal: '2026-09-03',
                keterangan: 'Bab 3 - Desain Wireframe Dashboard',
                rangkuman: 'Rancangan tata letak kartu progres dan jadwal.',
                catatanDosen: 'Sederhanakan navigasi di piranti ponsel.',
                status: 'delivered',
            },
            {
                id: 505,
                no: 5,
                tanggal: '2026-09-11',
                keterangan: 'Bab 3 - Finalisasi Sempro',
                rangkuman: 'Validasi kelengkapan dokumen sempro.',
                catatanDosen:
                    'Disetujui penuh untuk pendaftaran seminar proposal.',
                status: 'delivered',
            },
        ],
    },
];

// Reactive advisees list: initialized with props.advisees (if passed) or defaultMockAdvisees
const advisees = ref<LiveAdvisee[]>(
    props.advisees !== undefined && props.advisees !== null
        ? JSON.parse(JSON.stringify(props.advisees))
        : defaultMockAdvisees,
);

// Keep synchronized if props change from server
watch(
    () => props.advisees,
    (newVal) => {
        if (newVal !== undefined && newVal !== null) {
            advisees.value = JSON.parse(JSON.stringify(newVal));
        }
    },
    { deep: true },
);

const searchQuery = ref('');
const selectedAdviseeId = ref<number | null>(null);

const filteredAdvisees = computed(() =>
    advisees.value.filter((a) =>
        [a.nama, a.nim ?? '', a.judul].some((v) =>
            v.toLowerCase().includes(searchQuery.value.trim().toLowerCase()),
        ),
    ),
);

// selectedAdvisee defaults to the first student on initial load
const selectedAdvisee = computed(() => {
    if (selectedAdviseeId.value === -1) return null;
    if (selectedAdviseeId.value !== null) {
        const found = filteredAdvisees.value.find(
            (a) => a.id === selectedAdviseeId.value,
        );
        if (found) return found;
    }
    return filteredAdvisees.value[0] ?? null;
});

const selectStudent = (mhs: LiveAdvisee) => {
    selectedAdviseeId.value = mhs.id;
};

const closeStudentDrawer = () => {
    selectedAdviseeId.value = -1;
};

// Helper to count only validated/approved sessions (belum diparaf tidak dihitung)
const getApprovedSessionCount = (mhs: LiveAdvisee) =>
    mhs.sesiList.filter((s) => s.status === 'delivered').length;

// 4 KPI Metrics (Standard 5 sessions per supervisor)
const totalSesiGlobal = computed(() =>
    advisees.value.reduce((n, a) => n + a.sesiList.length, 0),
);

const mhsEligibleCount = computed(
    () => advisees.value.filter((a) => getApprovedSessionCount(a) >= 5).length,
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

const showToast = (message: string) => {
    toast.value = message;
    setTimeout(() => {
        if (toast.value === message) toast.value = '';
    }, 4000);
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
    if (!activeLogbookId.value) return;

    // Update locally first
    if (selectedAdvisee.value) {
        const sesi = selectedAdvisee.value.sesiList.find(
            (s) => s.id === activeLogbookId.value,
        );
        if (sesi) {
            sesi.status = isAccBimbingan.value ? 'delivered' : 'process';
            sesi.catatanDosen = feedbackInput.value;
        }
    }

    showToast('Catatan pembimbing dan status paraf berhasil diperbarui!');
    showApprovalModal.value = false;

    // Send to backend
    router.patch(
        `/dosen/bimbingan/${activeLogbookId.value}/paraf`,
        {
            status: isAccBimbingan.value ? 'delivered' : 'process',
            catatan_dosen: feedbackInput.value,
        },
        { preserveScroll: true, onError: () => {} },
    );
};

const quickParafSesi = (sesi: LiveBimbinganSession) => {
    // Update locally immediately
    sesi.status = 'delivered';
    if (!sesi.catatanDosen) {
        sesi.catatanDosen = 'Disetujui pembimbing';
    }
    showToast(`Sesi ke-${sesi.no} berhasil diparaf dosen!`);

    // Send to backend
    router.patch(
        `/dosen/bimbingan/${sesi.id}/paraf`,
        {
            status: 'delivered',
            catatan_dosen: sesi.catatanDosen,
        },
        { preserveScroll: true, onError: () => {} },
    );
};
</script>

<template>
    <Head title="Bimbingan Tugas Akhir • Portal Dosen" />

    <div class="space-y-6">
        <!-- PAGE HEADER -->
        <div>
            <h1
                class="text-2xl font-black tracking-tight text-black dark:text-white"
            >
                Bimbingan Tugas Akhir
            </h1>
            <p class="mt-0.5 text-xs font-normal text-black dark:text-slate-400">
                Pencatatan sesi konsultasi, paraf digital dosen pembimbing,
                serta pemantauan syarat kelayakan seminar.
            </p>
        </div>

        <!-- Toast Notice -->
        <div
            v-if="toast"
            class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-xs font-semibold text-emerald-800 shadow-sm dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-300"
        >
            {{ toast }}
        </div>

        <p
            v-for="(message, field) in errors"
            :key="field"
            role="alert"
            class="text-xs text-rose-700"
        >
            {{ message }}
        </p>

        <!-- 4 KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Total Sesi Bimbingan (Adjusted to Supervisees List) -->
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-black uppercase dark:text-slate-400"
                    >
                        Total Sesi Bimbingan
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-black dark:text-white"
                    >
                        {{ totalSesiGlobal }} Sesi
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                >
                    <BookOpen class="h-5 w-5" />
                </div>
            </div>

            <!-- 2. Memenuhi Syarat (≥ 5x) -->
            <div
                class="flex items-center justify-between rounded-2xl border border-emerald-200/80 bg-white p-4 shadow-xs dark:border-emerald-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-emerald-600 uppercase"
                    >
                        Memenuhi Syarat (≥ 5x)
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-emerald-600 dark:text-emerald-400"
                    >
                        {{ mhsEligibleCount }} Mhs
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </div>

            <!-- 3. Menunggu Paraf -->
            <div
                class="flex items-center justify-between rounded-2xl border border-amber-200/80 bg-white p-4 shadow-xs dark:border-amber-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-600 uppercase"
                    >
                        Menunggu Paraf
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
            </div>

            <!-- 4. Rata-rata Konsultasi -->
            <div
                class="flex items-center justify-between rounded-2xl border border-purple-200/80 bg-white p-4 shadow-xs dark:border-purple-900/40 dark:bg-[#111827]"
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
                        <span class="text-xs font-normal text-black dark:text-slate-400"
                            >/ Mhs</span
                        >
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                >
                    <TrendingUp class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- MAIN LAYOUT: TABEL DAFTAR MAHASISWA & LEMBAR KENDALI BIMBINGAN -->
        <div class="flex w-full flex-col items-start gap-5 lg:flex-row">
            <!-- LEFT SECTION: TABEL DAFTAR MAHASISWA BIMBINGAN -->
            <div class="w-full min-w-0 flex-1 space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <!-- Card Header -->
                    <div
                        class="flex flex-col gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <BookOpen
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <h2
                                class="text-sm font-black text-black sm:text-base dark:text-white"
                            >
                                Daftar Bimbingan TA
                            </h2>
                            <span class="text-xs font-normal text-black dark:text-slate-400">
                                ({{ filteredAdvisees.length }} mahasiswa)
                            </span>
                        </div>

                        <!-- Search Input -->
                        <div class="relative w-full sm:w-64">
                            <Search
                                class="absolute top-2.5 left-2.5 h-3.5 w-3.5 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari nama, NIM, atau judul..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pr-3 pl-8 text-xs font-normal text-black placeholder:text-black/60 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500"
                            />
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="w-full overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-100 bg-slate-50/90 font-black tracking-wider text-black uppercase dark:border-slate-800 dark:bg-slate-800/70 dark:text-white"
                            >
                                <tr>
                                    <th class="w-10 px-3 py-3 text-center">
                                        NO
                                    </th>
                                    <th class="min-w-[150px] px-3 py-3">
                                        NAMA
                                    </th>
                                    <th class="min-w-[95px] px-3 py-3">NIM</th>
                                    <th class="min-w-[240px] px-3 py-3">
                                        JUDUL
                                    </th>
                                    <th class="min-w-[130px] px-3 py-3">
                                        LOG KONSULTASI
                                    </th>
                                    <th
                                        class="min-w-[120px] px-3 py-3 text-center"
                                    >
                                        STATUS SYARAT
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800/60"
                            >
                                <tr
                                    v-for="(mhs, idx) in filteredAdvisees"
                                    :key="mhs.id"
                                    @click="selectStudent(mhs)"
                                    class="cursor-pointer transition-colors"
                                    :class="[
                                        selectedAdvisee &&
                                        selectedAdvisee.id === mhs.id
                                            ? 'bg-blue-50/70 ring-1 ring-blue-500/40 ring-inset dark:bg-blue-950/40'
                                            : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40',
                                    ]"
                                >
                                    <!-- NO -->
                                    <td
                                        class="px-3 py-3 text-center font-bold text-black dark:text-slate-400"
                                    >
                                        {{ idx + 1 }}
                                    </td>

                                    <!-- NAMA -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="leading-snug font-black text-black dark:text-white"
                                        >
                                            {{ mhs.nama }}
                                        </p>
                                        <span
                                            class="text-[10px] font-normal text-black dark:text-slate-400"
                                        >
                                            Pembimbing:
                                            {{
                                                (
                                                    mhs.pembimbing1 ||
                                                    mhs.roleAs ||
                                                    ''
                                                ).split(',')[0]
                                            }}
                                        </span>
                                    </td>

                                    <!-- NIM (Standard sans font, no font-mono, no bold) -->
                                    <td
                                        class="px-3 py-3 whitespace-nowrap font-normal text-black dark:text-slate-400"
                                    >
                                        {{ mhs.nim ?? '—' }}
                                    </td>

                                    <!-- JUDUL -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="line-clamp-2 text-[11px] leading-relaxed font-bold text-black dark:text-slate-200"
                                        >
                                            {{ mhs.judul }}
                                        </p>
                                    </td>

                                    <!-- LOG KONSULTASI (Hanya sesi yang sudah diparaf yang dihitung) -->
                                    <td class="px-3 py-3">
                                        <div class="space-y-1">
                                            <div
                                                class="flex items-center justify-between text-[10px]"
                                            >
                                                <span
                                                    class="font-bold text-black dark:text-slate-300"
                                                >
                                                    {{
                                                        getApprovedSessionCount(
                                                            mhs,
                                                        )
                                                    }}
                                                    / 5 Sesi
                                                </span>
                                                <span
                                                    :class="
                                                        getApprovedSessionCount(
                                                            mhs,
                                                        ) >= 5
                                                            ? 'text-emerald-600 dark:text-emerald-400'
                                                            : 'text-amber-600 dark:text-amber-400'
                                                    "
                                                    class="font-bold"
                                                >
                                                    {{
                                                        Math.min(
                                                            Math.round(
                                                                (getApprovedSessionCount(
                                                                    mhs,
                                                                ) /
                                                                    5) *
                                                                    100,
                                                            ),
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
                                                        getApprovedSessionCount(
                                                            mhs,
                                                        ) >= 5
                                                            ? 'bg-emerald-500'
                                                            : 'bg-amber-500'
                                                    "
                                                    :style="{
                                                        width:
                                                            Math.min(
                                                                (getApprovedSessionCount(
                                                                    mhs,
                                                                ) /
                                                                    5) *
                                                                    100,
                                                                100,
                                                            ) + '%',
                                                    }"
                                                />
                                            </div>
                                        </div>
                                    </td>

                                    <!-- STATUS SYARAT (Text only, NO ICONS!) -->
                                    <td
                                        class="px-3 py-3 text-center whitespace-nowrap"
                                    >
                                        <span
                                            :class="
                                                getApprovedSessionCount(mhs) >=
                                                5
                                                    ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                    : 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                                            "
                                            class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold shadow-2xs"
                                        >
                                            {{
                                                getApprovedSessionCount(mhs) >=
                                                5
                                                    ? 'Terpenuhi'
                                                    : 'Belum Terpenuhi'
                                            }}
                                        </span>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="filteredAdvisees.length === 0">
                                    <td colspan="6" class="py-14 text-center">
                                        <div
                                            class="flex flex-col items-center justify-center space-y-3"
                                        >
                                            <div
                                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 shadow-inner dark:bg-slate-800/80"
                                            >
                                                <BookOpen class="h-6 w-6" />
                                            </div>
                                            <div class="space-y-1">
                                                <p
                                                    class="text-sm font-black text-black dark:text-slate-300"
                                                >
                                                    Belum ada data bimbingan
                                                </p>
                                                <p
                                                    class="text-xs font-normal text-black dark:text-slate-400"
                                                >
                                                    {{
                                                        searchQuery
                                                            ? 'Tidak ada riwayat catatan bimbingan yang cocok dengan kriteria pencarian.'
                                                            : 'Belum ada mahasiswa bimbingan dengan judul tugas akhir yang disetujui.'
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Academic Standard -->
                    <div
                        class="border-t border-slate-100 p-4 text-xs font-normal text-black dark:border-slate-800 dark:text-slate-400"
                    >
                        Standar akademik ITK: Minimal 5 kali bimbingan per dosen
                        pembimbing tervalidasi untuk mengajukan Seminar Proposal
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: DETAIL RIWAYAT SESI MAHASISWA (LEMBAR KENDALI BIMBINGAN) -->
            <div
                v-if="selectedAdvisee"
                class="sticky top-6 w-full shrink-0 space-y-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm lg:w-[460px] dark:border-slate-800/80 dark:bg-[#111827]"
            >
                <!-- Drawer Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2">
                        <ClipboardList
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                        <h3
                            class="text-sm font-black text-black dark:text-white"
                        >
                            Lembar Kendali Bimbingan
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="closeStudentDrawer"
                        class="cursor-pointer rounded-lg p-1 text-black transition-colors hover:text-black/70 dark:text-slate-400 dark:hover:text-slate-200"
                        title="Tutup lembar kendali"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Student Profile Badge -->
                <div
                    class="space-y-2 rounded-xl border border-slate-200/60 bg-slate-50 p-3.5 dark:border-slate-700/60 dark:bg-slate-800/60"
                >
                    <div class="flex items-center justify-between">
                        <h4
                            class="truncate text-xs font-black text-black sm:text-sm dark:text-white"
                        >
                            {{ selectedAdvisee.nama }}
                        </h4>
                        <span
                            class="rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                        >
                            {{ selectedAdvisee.nim ?? '—' }}
                        </span>
                    </div>
                    <p
                        class="line-clamp-2 text-[11px] leading-tight font-bold text-black dark:text-slate-300"
                    >
                        {{ selectedAdvisee.judul }}
                    </p>
                    <div
                        class="flex items-center justify-between border-t border-slate-200/60 pt-2 text-[10px] font-normal text-black dark:border-slate-700/60 dark:text-slate-400"
                    >
                        <span>
                            Pembimbing 1:
                            <b class="font-bold text-black dark:text-white">{{
                                (
                                    selectedAdvisee.pembimbing1 ||
                                    selectedAdvisee.roleAs ||
                                    ''
                                ).split(',')[0]
                            }}</b>
                            <span v-if="selectedAdvisee.pembimbing2">
                                • Pembimbing 2:
                                <b class="font-bold text-black dark:text-white">{{
                                    selectedAdvisee.pembimbing2.split(',')[0]
                                }}</b></span
                            >
                        </span>
                        <span
                            :class="
                                getApprovedSessionCount(selectedAdvisee) >= 5
                                    ? 'font-bold text-emerald-600 dark:text-emerald-400'
                                    : 'font-bold text-amber-600 dark:text-amber-400'
                            "
                        >
                            {{ getApprovedSessionCount(selectedAdvisee) }} / 5
                            Sesi
                        </span>
                    </div>
                </div>

                <!-- Riwayat Catatan Konsultasi (Button Tambah Sesi removed) -->
                <div class="space-y-3">
                    <div>
                        <span
                            class="text-[11px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                        >
                            Riwayat Catatan Konsultasi
                        </span>
                    </div>

                    <!-- Scrollable Timeline Cards -->
                    <div class="max-h-[440px] space-y-3 overflow-y-auto pr-1">
                        <div
                            v-for="sesi in selectedAdvisee.sesiList"
                            :key="sesi.id"
                            class="space-y-2.5 rounded-xl border border-slate-200/70 bg-slate-50/60 p-3.5 transition-all dark:border-slate-700/60 dark:bg-slate-800/40"
                        >
                            <!-- Sesi Header -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-[10px] font-bold text-white"
                                    >
                                        {{ sesi.no }}
                                    </span>
                                    <span
                                        class="text-xs font-black text-black dark:text-white"
                                    >
                                        {{
                                            sesi.keterangan ||
                                            'Bimbingan Tugas Akhir'
                                        }}
                                    </span>
                                </div>
                                <span class="text-[10px] font-normal text-black dark:text-slate-400">
                                    {{ sesi.tanggal }}
                                </span>
                            </div>

                            <!-- Rangkuman Pembahasan -->
                            <div class="space-y-1 text-xs">
                                <div
                                    class="rounded-lg border border-slate-100 bg-white p-2.5 text-black dark:border-slate-800 dark:bg-[#111827] dark:text-slate-300"
                                >
                                    <span
                                        class="mb-0.5 block text-[10px] font-black text-black uppercase dark:text-slate-400"
                                    >
                                        Rangkuman Pembahasan:
                                    </span>
                                    <p class="leading-relaxed font-normal text-black dark:text-slate-300">
                                        {{ sesi.rangkuman }}
                                    </p>
                                </div>
                            </div>

                            <!-- Arahan / Revisi Dosen -->
                            <div
                                v-if="sesi.catatanDosen"
                                class="space-y-0.5 rounded-lg border border-amber-200/60 bg-amber-50/60 p-2.5 text-xs dark:border-amber-900/40 dark:bg-amber-950/30"
                            >
                                <span
                                    class="text-[10px] font-black text-amber-800 uppercase dark:text-amber-300"
                                >
                                    Arahan / Catatan Pembimbing:
                                </span>
                                <p
                                    class="leading-snug font-normal text-black dark:text-slate-200"
                                >
                                    {{ sesi.catatanDosen }}
                                </p>
                            </div>

                            <!-- Status Paraf & Actions -->
                            <div
                                class="flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] dark:border-slate-800"
                            >
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="h-2 w-2 rounded-full"
                                        :class="
                                            sesi.status === 'delivered'
                                                ? 'bg-emerald-500'
                                                : 'bg-amber-500'
                                        "
                                    />
                                    <span
                                        class="font-bold"
                                        :class="
                                            sesi.status === 'delivered'
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-amber-600 dark:text-amber-400'
                                        "
                                    >
                                        {{
                                            sesi.status === 'delivered'
                                                ? 'Diparaf Dosen'
                                                : sesi.status === 'ditolak'
                                                  ? 'Ditolak'
                                                  : 'Menunggu Paraf'
                                        }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="sesi.status !== 'delivered'"
                                        type="button"
                                        @click="quickParafSesi(sesi)"
                                        :disabled="processing"
                                        class="flex cursor-pointer items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1 text-[10px] font-bold text-white shadow-2xs transition-colors hover:bg-emerald-700 disabled:opacity-50"
                                    >
                                        <PenTool class="h-3 w-3" />
                                        <span>Beri Paraf</span>
                                    </button>
                                    <button
                                        type="button"
                                        @click="openReviewModal(sesi)"
                                        class="cursor-pointer rounded-lg border border-slate-200 bg-white px-2 py-1 text-[10px] font-bold text-black shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                    >
                                        Edit Arahan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty sessions -->
                        <div
                            v-if="selectedAdvisee.sesiList.length === 0"
                            class="py-8 text-center text-xs font-normal text-black dark:text-slate-400"
                        >
                            Belum ada sesi bimbingan yang tercatat untuk
                            mahasiswa ini.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Placeholder if drawer closed -->
            <div
                v-else
                class="w-full shrink-0 rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center lg:w-[460px] dark:border-slate-700 dark:bg-[#111827]"
            >
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                >
                    <ClipboardList class="h-6 w-6" />
                </div>
                <h3
                    class="mt-3 text-sm font-black text-black dark:text-white"
                >
                    Lembar Kendali Bimbingan
                </h3>
                <p class="mt-1 text-xs font-normal text-black dark:text-slate-400">
                    Pilih salah satu mahasiswa dari tabel di sebelah kiri untuk
                    melihat riwayat logbook dan status paraf bimbingan.
                </p>
            </div>
        </div>
    </div>

    <!-- MODAL: REVIEW & CATATAN PARAF (No duplicate X, clean padding, button 'Simpan') -->
    <Modal
        :show="showApprovalModal"
        title="Validasi & Catatan Pembimbing"
        max-width="lg"
        @close="!processing && (showApprovalModal = false)"
    >
        <div class="space-y-4">
            <p
                v-if="toast"
                role="status"
                class="text-xs text-slate-700 dark:text-slate-300"
            >
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

            <div>
                <label
                    class="block text-xs font-bold text-black dark:text-white"
                >
                    Keputusan Paraf Bimbingan
                </label>
                <div class="mt-2 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border px-3 text-center text-xs font-bold transition-all select-none"
                        :class="[
                            isAccBimbingan
                                ? 'border-emerald-600 bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                : 'border-slate-200 text-black hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                        ]"
                        @click="isAccBimbingan = true"
                    >
                        <Check
                            class="h-4 w-4 shrink-0 text-emerald-600 dark:text-emerald-400"
                        />
                        <span class="inline-block leading-none"
                            >Setujui & Paraf</span
                        >
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-11 w-full cursor-pointer items-center justify-center gap-2 rounded-xl border px-3 text-center text-xs font-bold transition-all select-none"
                        :class="[
                            !isAccBimbingan
                                ? 'border-amber-600 bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                : 'border-slate-200 text-black hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300',
                        ]"
                        @click="isAccBimbingan = false"
                    >
                        <Clock
                            class="h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400"
                        />
                        <span class="inline-block leading-none"
                            >Perlu Perbaikan (Revisi)</span
                        >
                    </button>
                </div>
            </div>

            <div>
                <label
                    class="block text-xs font-bold text-black dark:text-white"
                >
                    Catatan & Arahan Pembimbing (Opsional)
                </label>
                <textarea
                    v-model="feedbackInput"
                    rows="4"
                    placeholder="Tuliskan arahan perbaikan atau persetujuan materi bimbingan ini..."
                    class="mt-1 w-full rounded-xl border border-slate-300 p-3 text-xs font-normal text-black placeholder:text-black/60 focus:border-blue-600 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                />
            </div>
        </div>

        <template #footer>
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
                Simpan
            </Button>
        </template>
    </Modal>
</template>
