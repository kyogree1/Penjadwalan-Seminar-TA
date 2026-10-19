<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Bookmark,
    BookOpen,
    Copy,
    Download,
    ExternalLink,
    Eye,
    RotateCcw,
    Search,
    ShieldCheck,
    X,
} from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';
import { mockKatalogList } from '@/data/mockKatalog';

export interface SkripsiItem {
    id: number;
    tahun: string;
    prodi: string;
    bidang: string;
    judul: string;
    penulis: string;
    nim: string;
    pembimbing_1: string;
    nip_pembimbing_1?: string;
    pembimbing_2: string;
    nip_pembimbing_2?: string;
    abstrak_id: string;
    abstrak_en: string;
    tags: string[];
    metode?: string;
    tanggal_sidang?: string;
    doi?: string;
}

interface Props {
    items?: SkripsiItem[];
}

const props = defineProps<Props>();

const skripsiList = computed<SkripsiItem[]>(() => props.items ?? []);

const selectedItem = ref<SkripsiItem | null>(null);
const activeTab = ref<'id' | 'en'>('id');
const searchQuery = ref('');
const filterTahun = ref('');
const filterBidang = ref('');
const filterProdi = ref('');
const filterMetode = ref('');
const sortBy = ref('terbaru');
const onlyBookmarks = ref(false);
const bookmarkedIds = ref<number[]>([]);

// Toast state for citations
const copySuccessMsg = ref('');
let copyTimeout: ReturnType<typeof setTimeout> | null = null;

// Muat bookmark dari localStorage
if (typeof window !== 'undefined') {
    try {
        const saved = localStorage.getItem('katalog_bookmarks');
        if (saved) {
            bookmarkedIds.value = JSON.parse(saved);
        }
    } catch {
        bookmarkedIds.value = [];
    }
}

const toggleBookmark = (id: number) => {
    if (bookmarkedIds.value.includes(id)) {
        bookmarkedIds.value = bookmarkedIds.value.filter((item) => item !== id);
    } else {
        bookmarkedIds.value.push(id);
    }
    if (typeof window !== 'undefined') {
        try {
            localStorage.setItem(
                'katalog_bookmarks',
                JSON.stringify(bookmarkedIds.value),
            );
        } catch {
            // ignore
        }
    }
};

const isBookmarked = (id: number) => bookmarkedIds.value.includes(id);

const filteredSkripsiList = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return [...skripsiList.value]
        .filter((item) => {
            const searchable = [
                item.judul,
                item.penulis,
                item.bidang,
                item.metode ?? '',
                item.abstrak_id,
                ...(item.tags ?? []),
            ]
                .join(' ')
                .toLowerCase();
            return (
                (!onlyBookmarks.value || isBookmarked(item.id)) &&
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

const openDetail = (item: SkripsiItem) => {
    selectedItem.value = item;
    activeTab.value = 'id';
    copySuccessMsg.value = '';
};

const closeDetail = () => {
    selectedItem.value = null;
};

// Reset Filters
const resetFilters = () => {
    searchQuery.value = '';
    filterTahun.value = '';
    filterBidang.value = '';
    filterProdi.value = '';
    filterMetode.value = '';
    sortBy.value = 'terbaru';
    onlyBookmarks.value = false;
};

// Citation Generator (APA format)
const generateCitation = (item: SkripsiItem | null) => {
    if (!item) return '';
    const parts = item.penulis.trim().split(' ');
    const lastName = parts[parts.length - 1];
    const firstInitials = parts
        .slice(0, -1)
        .map((p) => p[0] + '.')
        .join(' ');
    const authorStr = firstInitials ? `${lastName}, ${firstInitials}` : lastName;
    const doiStr = item.doi ? ` DOI: ${item.doi}` : '';
    return `${authorStr} (${item.tahun}). ${item.judul}. Skripsi, Program Studi Informatika, Institut Teknologi Kalimantan.${doiStr}`;
};

const copyCitation = (item: SkripsiItem | null) => {
    if (!item) return;
    const cit = generateCitation(item);
    if (typeof navigator !== 'undefined' && navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(cit).then(() => {
            if (copyTimeout) clearTimeout(copyTimeout);
            copySuccessMsg.value = 'Format sitasi APA berhasil disalin ke clipboard.';
            copyTimeout = setTimeout(() => {
                copySuccessMsg.value = '';
            }, 3000);
        });
    }
};

// Similarity / Keyword Match Checker
const isSimilarityModalOpen = ref(false);
const inputTestTitle = ref('');
const hasRunSimilarity = ref(false);
const similarityScore = ref(0);
const similarTitles = ref<Array<{ judul: string; penulis: string; tahun: string | number; score: number }>>([]);

const openSimilarityModal = () => {
    isSimilarityModalOpen.value = true;
    hasRunSimilarity.value = false;
    similarTitles.value = [];
};

const closeSimilarityModal = () => {
    isSimilarityModalOpen.value = false;
};

const runSimilarityCheck = () => {
    const rawInput = inputTestTitle.value.trim().toLowerCase();
    if (!rawInput) return;

    // Tokenize meaningful words (length > 3)
    const qTokens = rawInput
        .replace(/[^\w\s]/gi, ' ')
        .split(/\s+/)
        .filter((w) => w.length > 3);

    if (!qTokens.length) return;

    // Use persisted server catalogue if available, or reference corpus from ITK repository archive
    const corpus = skripsiList.value.length > 0
        ? skripsiList.value.map((s) => ({ judul: s.judul, penulis: s.penulis, tahun: s.tahun }))
        : mockKatalogList.map((m) => ({ judul: m.judul, penulis: m.penulis, tahun: m.tahunLulus }));

    const scored = corpus.map((c) => {
        const itemTokens = c.judul
            .toLowerCase()
            .replace(/[^\w\s]/gi, ' ')
            .split(/\s+/)
            .filter((w) => w.length > 3);
        const common = qTokens.filter((t) => itemTokens.includes(t));
        const score = Math.min(
            Math.round((common.length / Math.max(qTokens.length, 1)) * 100),
            95,
        );
        return { ...c, score };
    });

    scored.sort((a, b) => b.score - a.score);
    similarTitles.value = scored.slice(0, 3).filter((s) => s.score > 15);
    similarityScore.value = scored[0] ? scored[0].score : 0;
    hasRunSimilarity.value = true;
};
</script>

<template>
    <Head title="Katalog Tugas Akhir - Repository Informatika ITK" />

    <div class="mx-auto max-w-7xl space-y-6">
        <p
            class="rounded-xl border border-amber-200 p-4 text-sm text-slate-600 dark:text-slate-300"
        >
            Katalog belum terhubung ke sumber publikasi tersimpan. Tidak ada
            abstrak, DOI, atau dokumen yang tersedia dari server saat ini.
        </p>

        <!-- Header Title Box -->
        <div
            class="dark:bg-navy-surface rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-xs dark:border-slate-800"
        >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Katalog TA
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Eksplorasi arsip penelitian dan naskah skripsi Program Studi Informatika ITK.
                    </p>
                </div>
                <button
                    type="button"
                    class="inline-flex cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-800 shadow-2xs transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    @click="openSimilarityModal"
                >
                    <ShieldCheck class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    <span>Cek Kemiripan Kata Kunci</span>
                </button>
            </div>
        </div>

        <!-- Status Kelayakan Katalog TA (Yellow Box Sesuai Mockup) -->
        <div
            class="dark:bg-navy-surface rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800"
        >
            <h3 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">
                Status Kelayakan Katalog TA
            </h3>
            <div
                class="rounded-xl border border-yellow-200 bg-[#FCFDE1] p-4 text-xs font-semibold text-slate-800 dark:border-yellow-900/50 dark:bg-yellow-950/30 dark:text-yellow-200"
            >
                Kelayakan publikasi belum dapat ditentukan dari data server.
            </div>
        </div>

        <!-- Daftar Katalog TA yang sudah dipublikasikan -->
        <div
            class="dark:bg-navy-surface rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800"
        >
            <div class="flex flex-col gap-2 pb-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Daftar Katalog TA yang sudah dipublikasikan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Pencarian dan penelusuran naskah skripsi terbitan mahasiswa.
                    </p>
                </div>
                <button
                    v-if="searchQuery || filterTahun || filterBidang || filterProdi || filterMetode || onlyBookmarks"
                    type="button"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 dark:text-rose-400"
                    @click="resetFilters"
                >
                    <RotateCcw class="h-3.5 w-3.5" />
                    <span>Reset Filter</span>
                </button>
            </div>

            <!-- Search & Filter Controls -->
            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari judul, penulis, NIM, atau kata kunci..."
                            class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50/80 pr-4 pl-10 text-xs text-slate-800 transition-colors placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        />
                    </div>
                </div>

                <!-- Dropdowns Row -->
                <div class="grid grid-cols-2 gap-3 text-xs sm:grid-cols-5">
                    <select
                        v-model="filterTahun"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="">Semua Tahun</option>
                        <option value="2026">2026</option>
                        <option value="2025">2025</option>
                        <option value="2024">2024</option>
                        <option value="2023">2023</option>
                    </select>
                    <select
                        v-model="filterProdi"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="">Semua Program Studi</option>
                        <option value="Informatika">Informatika</option>
                        <option value="Sistem Informasi">Sistem Informasi</option>
                    </select>
                    <select
                        v-model="filterBidang"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="">Semua Bidang Kajian</option>
                        <option value="Artificial Intelligence & NLP">AI & NLP</option>
                        <option value="Computational Intelligence & Optimasi">Computational Intelligence</option>
                        <option value="Human-Computer Interaction & Software Eng.">RPL & HCI</option>
                        <option value="Cloud Computing & Cyber Security">Cyber Security</option>
                        <option value="Data Science & Sistem Rekomendasi">Data Science</option>
                    </select>
                    <select
                        v-model="filterMetode"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="">Semua Metode</option>
                        <option value="Prototyping">Prototyping</option>
                        <option value="Experimental">Experimental</option>
                    </select>
                    <select
                        v-model="sortBy"
                        class="rounded-xl border border-slate-200 bg-white p-2 text-xs font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="terbaru">Urutkan: Terbaru</option>
                        <option value="terlama">Urutkan: Terlama</option>
                        <option value="judul">Urutkan: Judul (A-Z)</option>
                    </select>
                </div>

                <div
                    class="flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-500 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            :class="[
                                'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold transition-all',
                                onlyBookmarks
                                    ? 'bg-amber-500 text-white shadow-xs'
                                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                            @click="onlyBookmarks = !onlyBookmarks"
                        >
                            <Bookmark
                                class="h-3.5 w-3.5"
                                :class="{ 'fill-current': onlyBookmarks }"
                            />
                            <span>Tersimpan ({{ bookmarkedIds.length }})</span>
                        </button>
                        <span>{{
                            onlyBookmarks
                                ? 'Menampilkan referensi tersimpan'
                                : 'Menampilkan seluruh katalog publik'
                        }}</span>
                    </div>
                    <span>
                        Menampilkan {{ filteredSkripsiList.length }} dari
                        {{ skripsiList.length }} tugas akhir
                    </span>
                </div>
            </div>

            <!-- Grid Kartu Skripsi -->
            <div
                v-if="filteredSkripsiList.length"
                class="mt-6 grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
            >
                <div
                    v-for="item in filteredSkripsiList"
                    :key="item.id"
                    class="group flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-2xs transition-all hover:border-blue-400 hover:shadow-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Card Top Header -->
                    <div class="border-b border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between">
                            <span class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                S1 {{ item.prodi }} • {{ item.tahun }}
                            </span>
                            <button
                                type="button"
                                class="rounded-lg p-1 text-slate-400 hover:text-amber-500 transition-colors"
                                :title="isBookmarked(item.id) ? 'Hapus dari Tersimpan' : 'Simpan Bookmark'"
                                @click.stop="toggleBookmark(item.id)"
                            >
                                <Bookmark
                                    class="h-4 w-4"
                                    :class="{ 'fill-amber-500 text-amber-500': isBookmarked(item.id) }"
                                />
                            </button>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="flex flex-1 flex-col justify-between space-y-3 p-5">
                        <div>
                            <span class="inline-block text-[10px] font-semibold text-slate-500 dark:text-slate-400">
                                {{ item.bidang }}
                            </span>
                            <h4 class="mt-1 line-clamp-2 text-xs font-bold leading-snug text-slate-900 dark:text-white">
                                {{ item.judul }}
                            </h4>
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ item.penulis }} • NIM: {{ item.nim }}
                            </p>
                            <p class="mt-2.5 line-clamp-3 text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                                {{ item.abstrak_id }}
                            </p>
                        </div>

                        <div class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <div class="flex flex-wrap gap-1 text-[10px] text-slate-400">
                                <span v-for="tag in (item.tags || []).slice(0, 3)" :key="tag">
                                    #{{ tag }}
                                </span>
                            </div>

                            <button
                                type="button"
                                class="w-full rounded-xl bg-slate-100 py-2 text-center text-xs font-bold text-slate-700 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                @click="openDetail(item)"
                            >
                                Lihat Detail Naskah
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="dark:bg-navy-surface mt-6 rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-xs dark:border-slate-800"
            >
                <div class="mx-auto max-w-md space-y-3">
                    <div
                        class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <Search class="h-7 w-7 text-slate-400" />
                    </div>
                    <h4
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Tidak ada katalog yang sesuai
                    </h4>
                    <p
                        class="text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Arsip publikasi belum tersedia pada server saat ini, atau coba sesuaikan kata kunci pencarian.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL KATALOG TA -->
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
                    class="dark:bg-navy-surface relative w-full max-w-3xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800"
                >
                    <!-- Modal Header -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded-md bg-blue-50 px-2 py-0.5 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                            >
                                Arsip Tugas Akhir
                            </span>
                            <span v-if="selectedItem.doi" class="text-xs text-slate-400">
                                {{ selectedItem.doi }}
                            </span>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-white"
                            aria-label="Tutup detail naskah"
                            @click="closeDetail"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="mt-4 space-y-4">
                        <div>
                            <h3
                                class="text-base font-bold leading-snug text-slate-900 dark:text-white"
                            >
                                {{ selectedItem.judul }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ selectedItem.penulis }} • NIM: {{ selectedItem.nim }} • {{ selectedItem.prodi }} (Lulus: {{ selectedItem.tahun }})
                            </p>
                        </div>

                        <!-- Info Dosen Pembimbing -->
                        <div
                            class="grid grid-cols-2 gap-3 rounded-xl border border-slate-200/90 bg-slate-50/70 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                    Dosen Pembimbing I
                                </p>
                                <p class="mt-0.5 font-semibold text-slate-900 dark:text-white">
                                    {{ selectedItem.pembimbing_1 }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                    Dosen Pembimbing II
                                </p>
                                <p class="mt-0.5 font-semibold text-slate-900 dark:text-white">
                                    {{ selectedItem.pembimbing_2 }}
                                </p>
                            </div>
                        </div>

                        <!-- Tab Abstrak Bilingual (Tanpa Emoji) -->
                        <div>
                            <div
                                class="flex items-center gap-4 border-b border-slate-200 text-xs font-semibold dark:border-slate-800"
                            >
                                <button
                                    type="button"
                                    class="border-b-2 pb-2 transition-colors"
                                    :class="[
                                        activeTab === 'id'
                                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
                                            : 'border-transparent text-slate-400 hover:text-slate-600',
                                    ]"
                                    @click="activeTab = 'id'"
                                >
                                    Abstrak (Indonesia)
                                </button>
                                <button
                                    type="button"
                                    class="border-b-2 pb-2 transition-colors"
                                    :class="[
                                        activeTab === 'en'
                                            ? 'border-blue-600 text-blue-600 dark:text-blue-400'
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
                                {{ activeTab === 'id' ? selectedItem.abstrak_id : selectedItem.abstrak_en }}
                            </p>
                        </div>

                        <!-- Generator Sitasi Format APA -->
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 text-xs dark:border-slate-800 dark:bg-slate-900/40">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                                    Format Sitasi (APA 7th)
                                </span>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                    @click="copyCitation(selectedItem)"
                                >
                                    <Copy class="h-3 w-3" />
                                    <span>Salin Sitasi</span>
                                </button>
                            </div>
                            <p class="mt-1 font-mono text-[11px] leading-relaxed text-slate-700 dark:text-slate-300 select-all">
                                {{ generateCitation(selectedItem) }}
                            </p>
                            <p v-if="copySuccessMsg" class="mt-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ copySuccessMsg }}
                            </p>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div
                        class="mt-6 flex items-center justify-between border-t border-slate-100 pt-4 text-xs dark:border-slate-800"
                    >
                        <span class="text-[11px] text-slate-400">
                            Naskah lengkap fisik dapat diakses di Perpustakaan ITK
                        </span>
                        <button
                            type="button"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-2 font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            @click="closeDetail"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <!-- MODAL CEK KEMIRIPAN KATA KUNCI (ANTI-SLOP) -->
    <Modal :show="isSimilarityModalOpen" max-width="lg" @close="closeSimilarityModal">
        <div class="space-y-4 p-6">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Pencocokan Kata Kunci Judul Terdahulu
                </h3>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                    Uji draf judul rencana Tugas Akhir Anda untuk melihat penelitian terdahulu yang memiliki fokus bahasan serupa di Program Studi Informatika.
                </p>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Draf Judul Rencana Tugas Akhir:
                    </label>
                    <textarea
                        v-model="inputTestTitle"
                        rows="3"
                        placeholder="Contoh: Penerapan Algoritma Genetika dan Large Language Model untuk Penjadwalan Tugas Akhir..."
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/70 p-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <button
                    type="button"
                    class="w-full rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white transition-colors hover:bg-blue-700"
                    @click="runSimilarityCheck"
                >
                    Analisis Kata Kunci Judul
                </button>
            </div>

            <!-- Hasil Analisis -->
            <div v-if="hasRunSimilarity" class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                <div
                    class="rounded-xl p-3 text-xs"
                    :class="
                        similarityScore > 50
                            ? 'border border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-300'
                            : 'border border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-900/50 dark:bg-blue-950/40 dark:text-blue-300'
                    "
                >
                    <p class="font-bold">
                        Tingkat Kesamaan Kata Kunci: {{ similarityScore }}%
                    </p>
                    <p class="mt-0.5 text-[11px] leading-relaxed">
                        {{
                            similarityScore > 50
                                ? 'Ditemukan irisan istilah kata kunci yang cukup banyak dengan arsip skripsi terdahulu. Pastikan menonjolkan kebaruan (novelty) metode atau objek riset Anda.'
                                : 'Irisan kata kunci relatif rendah. Topik memiliki ruang kebaruan yang baik terhadap arsip penelitian prodi.'
                        }}
                    </p>
                </div>

                <div v-if="similarTitles.length > 0" class="space-y-2">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        Referensi Judul Terdahulu Terkait:
                    </p>
                    <div
                        v-for="(s, idx) in similarTitles"
                        :key="idx"
                        class="flex items-start justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/60 p-3 text-xs dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-900 dark:text-white">
                                {{ s.judul }}
                            </p>
                            <p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">
                                {{ s.penulis }} • Lulus {{ s.tahun }}
                            </p>
                        </div>
                        <span class="shrink-0 font-mono text-[11px] font-bold text-blue-600 dark:text-blue-400">
                            {{ s.score }}% kata sama
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 pt-3 dark:border-slate-800">
                <button
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                    @click="closeSimilarityModal"
                >
                    Tutup
                </button>
            </div>
        </div>
    </Modal>
</template>
