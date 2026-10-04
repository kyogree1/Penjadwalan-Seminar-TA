<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Bookmark,
    BookOpen,
    Download,
    ExternalLink,
    Eye,
    Filter,
    Search,
    Share2,
    SlidersHorizontal,
    X,
} from 'lucide-vue-next';

interface SkripsiItem {
    id: number;
    tahun: string;
    prodi: string;
    bidang: string;
    judul: string;
    penulis: string;
    nim: string;
    pembimbing_1: string;
    nip_pembimbing_1: string;
    pembimbing_2: string;
    nip_pembimbing_2: string;
    abstrak_id: string;
    abstrak_en: string;
    tags: string[];
    metode: string;
    tanggal_sidang: string;
    doi: string;
}

const selectedItem = ref<SkripsiItem | null>(null);
const activeTab = ref<'id' | 'en'>('id');
const searchQuery = ref('');
const filterTahun = ref('');
const filterBidang = ref('');
const filterProdi = ref('');
const filterMetode = ref('');
const sortBy = ref('terbaru');

const filteredSkripsiList = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return [...skripsiList.value]
        .filter((item) => {
            const searchable = [
                item.judul,
                item.penulis,
                item.bidang,
                item.metode,
                item.abstrak_id,
                ...item.tags,
            ]
                .join(' ')
                .toLowerCase();
            return (
                (!query || searchable.includes(query)) &&
                (!filterTahun.value || item.tahun === filterTahun.value) &&
                (!filterBidang.value || item.bidang === filterBidang.value) &&
                (!filterProdi.value || item.prodi === filterProdi.value) &&
                (!filterMetode.value || item.metode === filterMetode.value)
            );
        })
        .sort((a, b) => {
            if (sortBy.value === 'judul') return a.judul.localeCompare(b.judul);
            if (sortBy.value === 'terlama')
                return a.tahun.localeCompare(b.tahun);
            return b.tahun.localeCompare(a.tahun);
        });
});

const skripsiList = ref<SkripsiItem[]>([
    {
        id: 1,
        tahun: '2024',
        prodi: 'Teknik Informatika',
        bidang: 'UI/UX & Human-Computer Interaction',
        judul: 'Implementasi Augmented Reality (AR) Berbasis Web untuk Pembelajaran Interaktif Anatomi Organ Tubuh Manusia',
        penulis: 'Claudia Stephanie Tan',
        nim: '200411100233',
        pembimbing_1: 'Dr. Dian Indah Permatasari, M.Kom.',
        nip_pembimbing_1: '198402122010122004',
        pembimbing_2: 'Rina Agustina, S.T., M.Kom.',
        nip_pembimbing_2: '198607142012122001',
        abstrak_id:
            'Model anatomi fisik di laboratorium seringkali terbatas jumlahnya. Penelitian ini mengembangkan media WebAR markerless menggunakan WebXR API dan Three.js. Hasil pre-test dan post-test pada 45 siswa menunjukkan peningkatan pemahaman materi organ kardiovaskular sebesar 38.6%.',
        abstrak_en:
            'Physical anatomical models in laboratories are often limited in number. This study developed markerless WebAR media using WebXR API and Three.js. Pre-test and post-test results on 45 students showed an increase in understanding of cardiovascular organs by 38.6%.',
        tags: [
            '#WebAR',
            '#Augmented Reality',
            '#Three.js',
            '#Media Pembelajaran',
            '#Anatomi Manusia',
        ],
        metode: 'Prototyping & Field Testing',
        tanggal_sidang: '22 Mei 2024',
        doi: 'https://doi.org/10.14710/webar.2024.310',
    },
    {
        id: 2,
        tahun: '2024',
        prodi: 'Teknik Informatika',
        bidang: 'Kecerdasan Buatan (AI) & NLP',
        judul: 'Rancang Bangun Chatbot Layanan Akademik Kampus Berbasis Fine-Tuned LLaMA-3 dan Retrieval-Augmented Generation (RAG)',
        penulis: 'Muhammad Fadhil Ramadhan',
        nim: '200411100189',
        pembimbing_1: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        nip_pembimbing_1: '197903152005011002',
        pembimbing_2: 'Ahmad Fauzi, S.Kom., M.T.',
        nip_pembimbing_2: '198811052015041003',
        abstrak_id:
            'Layanan informasi akademik konvensional sering mengalami antrean respon yang lambat. Penelitian ini membangun sistem tanya jawab cerdas menggunakan model bahasa besar LLaMA-3 dengan teknik RAG untuk memitigasi halusinasi dan meningkatkan akurasi jawaban dokumen panduan akademik ITK.',
        abstrak_en:
            'Conventional academic information services often face slow response queues. This research develops an intelligent question-answering system using LLaMA-3 LLM with RAG techniques to mitigate hallucination and improve answer accuracy based on ITK academic guideline documents.',
        tags: ['#LLM', '#RAG', '#LangChain', '#Academic Chatbot', '#VectorDB'],
        metode: 'Experimental Research & Evaluation',
        tanggal_sidang: '18 Juni 2024',
        doi: 'https://doi.org/10.14710/rag.2024.412',
    },
    {
        id: 3,
        tahun: '2024',
        prodi: 'Teknik Informatika',
        bidang: 'Sistem Informasi & Optimasi',
        judul: 'Optimasi Penjadwalan Sidang Tugas Akhir Multi-Ruangan Menggunakan Algoritma Genetika Hibrida (Studi Kasus: FSTI ITK)',
        penulis: 'Akmal Falah Maulana',
        nim: '11231006',
        pembimbing_1: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        nip_pembimbing_1: '197903152005011002',
        pembimbing_2: 'Rina Agustina, S.T., M.Kom.',
        nip_pembimbing_2: '198607142012122001',
        abstrak_id:
            'Penyusunan jadwal seminar dan sidang yang melibatkan puluhan dosen dan ratusan mahasiswa merupakan masalah NP-hard. Penelitian ini mengimplementasikan algoritma genetika dengan penyesuaian penalti dinamis untuk menghasilkan jadwal bebas bentrok dan beban menguji yang proporsional.',
        abstrak_en:
            'Scheduling seminars and defenses involving dozens of lecturers and hundreds of students is an NP-hard problem. This study implements a hybrid genetic algorithm with dynamic penalty adjustment to generate conflict-free schedules with balanced examination workloads.',
        tags: [
            '#Algoritma Genetika',
            '#Penjadwalan Otomatis',
            '#Optimization',
            '#Constraint Satisfaction',
        ],
        metode: 'Design Science Research (DSR)',
        tanggal_sidang: '10 Juli 2024',
        doi: 'https://doi.org/10.14710/ga.2024.509',
    },
]);

const openDetail = (item: SkripsiItem) => {
    selectedItem.value = item;
    activeTab.value = 'id';
};

const closeDetail = () => {
    selectedItem.value = null;
};
</script>

<template>
    <Head title="Katalog Tugas Akhir - Repository Informatika ITK" />

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Header Title Box -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <h2
                class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
            >
                Katalog TA
            </h2>
        </div>

        <!-- Status Kelayakan Katalog TA (Yellow Box Sesuai Mockup) -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <h3 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">
                Status Kelayakan Katalog TA
            </h3>
            <div
                class="rounded-xl border border-yellow-200 bg-[#FCFDE1] p-4 text-xs font-semibold text-slate-800 dark:border-yellow-900/50 dark:bg-yellow-950/30 dark:text-yellow-200"
            >
                Anda belum menyelesaikan sidang TA atau sidang TA belum diterima
            </div>
        </div>

        <!-- Daftar Katalog TA yang sudah dipublikasikan -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <h3 class="mb-4 text-base font-bold text-slate-900 dark:text-white">
                Daftar Katalog TA yang sudah dipublikasikan
            </h3>

            <!-- Search & Filter Controls (Sesuai Mockup) -->
            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Search
                            class="absolute top-3 left-3.5 h-4 w-4 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari judul, penulis, keyword, atau abstrak..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-2 pr-4 pl-10 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        />
                    </div>
                </div>

                <!-- Dropdowns Row -->
                <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-5">
                    <select
                        v-model="filterTahun"
                        class="rounded-xl border border-slate-300 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Semua Tahun</option>
                        <option>2024</option>
                        <option>2023</option>
                    </select>
                    <select
                        v-model="filterProdi"
                        class="rounded-xl border border-slate-300 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Semua Program Studi</option>
                        <option>Teknik Informatika</option>
                        <option>Sistem Informasi</option>
                    </select>
                    <select
                        v-model="filterBidang"
                        class="rounded-xl border border-slate-300 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Semua Bidang Kajian</option>
                        <option>AI & Machine Learning</option>
                        <option>Human-Computer Interaction</option>
                    </select>
                    <select
                        v-model="filterMetode"
                        class="rounded-xl border border-slate-300 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Semua Metode Penelitian</option>
                        <option>Prototyping</option>
                        <option>Experimental</option>
                    </select>
                    <select
                        v-model="sortBy"
                        class="rounded-xl border border-slate-300 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="terbaru">Urutkan: Terbaru</option>
                        <option value="terlama">Urutkan: Terlama</option>
                        <option value="judul">Urutkan: Judul (A-Z)</option>
                    </select>
                </div>

                <div
                    class="flex items-center justify-between pt-1 text-[11px] text-slate-500"
                >
                    <span>Menampilkan seluruh katalog publik</span>
                    <span
                        >Menampilkan {{ filteredSkripsiList.length }} dari
                        {{ skripsiList.length }} tugas akhir</span
                    >
                </div>
            </div>

            <!-- Grid Kartu Skripsi (Sesuai Mockup) -->
            <div
                v-if="filteredSkripsiList.length"
                class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="item in filteredSkripsiList"
                    :key="item.id"
                    class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Card Banner Thumbnail -->
                    <div
                        class="relative h-32 bg-linear-to-br from-amber-600 to-orange-700 p-4 text-white"
                    >
                        <div class="flex items-start justify-between">
                            <span
                                class="rounded-md bg-black/30 px-2 py-0.5 text-[10px] font-bold backdrop-blur-xs"
                            >
                                TA SARJANA
                            </span>
                            <button
                                type="button"
                                class="rounded-full bg-white/20 p-1.5 backdrop-blur-md hover:bg-white/40"
                                title="Simpan Bookmark"
                            >
                                <Bookmark class="h-4 w-4" />
                            </button>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div
                        class="flex flex-1 flex-col justify-between space-y-3 p-5"
                    >
                        <div>
                            <!-- Tags -->
                            <div
                                class="flex flex-wrap gap-1.5 text-[10px] font-bold"
                            >
                                <span
                                    class="rounded-md bg-blue-50 px-2 py-0.5 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ item.tahun }}
                                </span>
                                <span
                                    class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ item.prodi }}
                                </span>
                            </div>

                            <!-- Judul Skripsi -->
                            <h4
                                class="mt-2.5 line-clamp-2 text-xs leading-snug font-bold text-slate-900 dark:text-white"
                            >
                                {{ item.judul }}
                            </h4>

                            <!-- Penulis & NIM -->
                            <p
                                class="mt-1 text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                {{ item.penulis }} • {{ item.nim }}
                            </p>

                            <!-- Abstrak Snippet -->
                            <p
                                class="mt-2 line-clamp-3 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                            >
                                {{ item.abstrak_id }}
                            </p>
                        </div>

                        <!-- Tags & Action Button -->
                        <div
                            class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                        >
                            <div
                                class="flex flex-wrap gap-1 text-[10px] text-slate-400"
                            >
                                <span
                                    v-for="tag in item.tags.slice(0, 3)"
                                    :key="tag"
                                    >{{ tag }}</span
                                >
                            </div>

                            <button
                                type="button"
                                class="w-full rounded-xl bg-blue-50 py-2 text-center text-xs font-bold text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300 dark:hover:bg-blue-900"
                                @click="openDetail(item)"
                            >
                                Lihat Detail &gt;
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="mt-6 rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div class="mx-auto max-w-md space-y-3">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <Search class="h-8 w-8 text-slate-400" />
                    </div>
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Tidak ada katalog yang sesuai
                    </h3>
                    <p
                        class="text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Coba ubah kata kunci pencarian atau reset filter untuk
                        melihat seluruh katalog.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL KATALOG TA (Sesuai Frame 1000000913.png) -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="selectedItem"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
            >
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                    @click="closeDetail"
                />

                <div
                    class="relative w-full max-w-4xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <!-- Modal Top Bar -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                            >
                                Akses Terbuka (Open Access)
                            </span>
                            <span class="text-xs text-slate-400">
                                HDL: 20.500.12345/1011
                            </span>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white"
                            @click="closeDetail"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal Two Column Body -->
                    <div class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-3">
                        <!-- Left: Cover Book & Download Action -->
                        <div class="space-y-4">
                            <div
                                class="rounded-xl border-2 border-blue-600 bg-white p-4 text-center shadow-xs dark:bg-slate-900"
                            >
                                <div
                                    class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-full border border-blue-600 text-xs font-bold text-blue-600"
                                >
                                    TA
                                </div>
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    TUGAS AKHIR SARJANA
                                </p>
                                <h5
                                    class="mt-2 line-clamp-4 text-xs leading-tight font-bold text-slate-900 dark:text-white"
                                >
                                    {{ selectedItem.judul }}
                                </h5>
                                <p
                                    class="mt-3 text-[10px] font-semibold text-slate-600 dark:text-slate-400"
                                >
                                    {{ selectedItem.penulis }}<br />
                                    Fakultas Sains dan Teknologi Informasi
                                </p>
                            </div>

                            <div class="space-y-2">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-blue-700"
                                >
                                    <Download class="h-4 w-4" />
                                    <span>Unduh Naskah Lengkap (PDF)</span>
                                </button>
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <Eye class="h-4 w-4" />
                                    <span>Baca Naskah Online</span>
                                </button>
                            </div>
                        </div>

                        <!-- Right: Metadata & Abstract -->
                        <div class="space-y-4 md:col-span-2">
                            <!-- Tags -->
                            <div class="flex flex-wrap gap-2 text-xs font-bold">
                                <span
                                    class="rounded-md bg-blue-50 px-2 py-0.5 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ selectedItem.tahun }}
                                </span>
                                <span
                                    class="rounded-md bg-slate-100 px-2 py-0.5 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ selectedItem.prodi }}
                                </span>
                                <span
                                    class="rounded-md bg-indigo-50 px-2 py-0.5 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                                >
                                    {{ selectedItem.bidang }}
                                </span>
                            </div>

                            <h3
                                class="text-base leading-snug font-bold text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.judul }}
                            </h3>

                            <!-- Penulis & Dosen Box -->
                            <div
                                class="grid grid-cols-2 gap-3 rounded-xl border border-slate-200/90 bg-slate-50/70 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Penulis / Peneliti
                                    </p>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedItem.penulis }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ selectedItem.nim }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Dosen Pembimbing I
                                    </p>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedItem.pembimbing_1 }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIP:
                                        {{ selectedItem.nip_pembimbing_1 }}
                                    </p>
                                </div>
                                <div class="mt-1">
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Program Studi & Fakultas
                                    </p>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        S1 {{ selectedItem.prodi }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        Fakultas Sains & Teknologi Informasi
                                        (ITK)
                                    </p>
                                </div>
                                <div class="mt-1">
                                    <p
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Dosen Pembimbing II
                                    </p>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ selectedItem.pembimbing_2 }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIP:
                                        {{ selectedItem.nip_pembimbing_2 }}
                                    </p>
                                </div>
                            </div>

                            <!-- Tab Bilingual Abstrak -->
                            <div>
                                <div
                                    class="flex items-center gap-4 border-b border-slate-200 text-xs font-bold dark:border-slate-800"
                                >
                                    <button
                                        type="button"
                                        class="border-b-2 pb-2 transition-colors"
                                        :class="[
                                            activeTab === 'id'
                                                ? 'border-blue-600 text-blue-600'
                                                : 'border-transparent text-slate-400 hover:text-slate-600',
                                        ]"
                                        @click="activeTab = 'id'"
                                    >
                                        Abstrak (Bahasa Indonesia)
                                    </button>
                                    <button
                                        type="button"
                                        class="border-b-2 pb-2 transition-colors"
                                        :class="[
                                            activeTab === 'en'
                                                ? 'border-blue-600 text-blue-600'
                                                : 'border-transparent text-slate-400 hover:text-slate-600',
                                        ]"
                                        @click="activeTab = 'en'"
                                    >
                                        Abstract (English)
                                    </button>
                                </div>

                                <p
                                    class="mt-3 text-xs leading-relaxed text-slate-700 dark:text-slate-300"
                                >
                                    {{
                                        activeTab === 'id'
                                            ? selectedItem.abstrak_id
                                            : selectedItem.abstrak_en
                                    }}
                                </p>
                            </div>

                            <!-- Keywords & Details -->
                            <div
                                class="space-y-1.5 border-t border-slate-100 pt-2 text-xs dark:border-slate-800"
                            >
                                <p
                                    class="text-[10px] font-bold text-slate-400 uppercase"
                                >
                                    Kata Kunci / Keywords
                                </p>
                                <div
                                    class="flex flex-wrap gap-1.5 text-[11px] font-medium text-blue-600 dark:text-blue-400"
                                >
                                    <span
                                        v-for="tag in selectedItem.tags"
                                        :key="tag"
                                        >{{ tag }}</span
                                    >
                                </div>
                            </div>

                            <dl
                                class="grid grid-cols-2 gap-2 border-t border-slate-100 pt-2 text-xs dark:border-slate-800"
                            >
                                <div>
                                    <dt
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Metode Penelitian
                                    </dt>
                                    <dd
                                        class="font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedItem.metode }}
                                    </dd>
                                </div>
                                <div>
                                    <dt
                                        class="text-[10px] font-bold text-slate-400 uppercase"
                                    >
                                        Tanggal Sidang
                                    </dt>
                                    <dd
                                        class="font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ selectedItem.tanggal_sidang }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
