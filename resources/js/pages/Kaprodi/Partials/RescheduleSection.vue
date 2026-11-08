<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    BookOpen,
    Calendar,
    CalendarClock,
    Check,
    CheckCircle2,
    Clock,
    Filter,
    GraduationCap,
    MapPin,
    RotateCcw,
    Search,
    User,
    X,
    XCircle,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import StatCard from '@/Components/StatCard.vue';

export interface RescheduleRequestItem {
    id: number;
    // Dosen Pemohon
    dosenId: number;
    dosenNama: string;
    dosenNip: string;
    dosenRole: 'Pembimbing 1' | 'Pembimbing 2' | 'Penguji 1' | 'Penguji 2';
    // Mahasiswa & Ujian
    mahasiswaId: number;
    mahasiswaNama: string;
    mahasiswaNim: string;
    mahasiswaAngkatan: string;
    tipeUjian: 'sempro' | 'sidang';
    judulTa: string;
    // Jadwal Asli (Lama)
    jadwalAsli: {
        hari: string;
        tanggal: string;
        jamMulai: string;
        jamSelesai: string;
        ruangan: string;
    };
    // Usulan Jadwal Baru
    jadwalUsulan: {
        hari: string;
        tanggal: string;
        jamMulai: string;
        jamSelesai: string;
        ruangan?: string;
    };
    // Alasan & Catatan
    alasan: string;
    catatanDosen: string;
    tanggalPengajuan: string;
    // Status Keputusan Koorprodi
    status: 'menunggu' | 'disetujui' | 'ditolak';
    catatanKoorprodi?: string;
    disetujuiAt?: string;
    disetujuiOleh?: string;
}

const props = defineProps<{
    initialRequests?: RescheduleRequestItem[];
}>();

const emit = defineEmits<{
    (e: 'back-to-judul'): void;
}>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user ?? null);
const koorprodiName = computed(
    () => authUser.value?.name ?? 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
);

// Toast Notification
const toast = ref({
    show: false,
    message: '',
    isError: false,
});
const showToast = (message: string, isError = false) => {
    toast.value = { show: true, message, isError };
    setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// Initial realistic dataset representing actual faculty requests
const requests = ref<RescheduleRequestItem[]>(
    props.initialRequests ?? [
        {
            id: 1,
            dosenId: 2,
            dosenNama: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            dosenNip: '197902142006041002',
            dosenRole: 'Penguji 1',
            mahasiswaId: 101,
            mahasiswaNama: 'Muhammad Farhan Al-Ghifari',
            mahasiswaNim: '11211025',
            mahasiswaAngkatan: '2021',
            tipeUjian: 'sempro',
            judulTa:
                'Analisis Sentimen Opini Publik terhadap Layanan Publik IKN Menggunakan Algoritma RoBERTa',
            jadwalAsli: {
                hari: 'Senin',
                tanggal: '17 Nov 2026',
                jamMulai: '09:00',
                jamSelesai: '10:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            jadwalUsulan: {
                hari: 'Rabu',
                tanggal: '19 Nov 2026',
                jamMulai: '13:00',
                jamSelesai: '14:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            alasan: 'Bentrok Jadwal Mengajar / Kuliah',
            catatanDosen:
                'Terdapat jadwal kuliah wajib Pengantar Kecerdasan Buatan di Ruang Lab Komputer 2 yang tidak dapat digeser.',
            tanggalPengajuan: '05 Nov 2026, 09:30 WITA',
            status: 'menunggu',
        },
        {
            id: 2,
            dosenId: 19,
            dosenNama: 'Dian Permata Sari, S.Kom., M.Kom.',
            dosenNip: '198810232015042003',
            dosenRole: 'Penguji 2',
            mahasiswaId: 102,
            mahasiswaNama: 'Bagus Setiawan',
            mahasiswaNim: '11211018',
            mahasiswaAngkatan: '2021',
            tipeUjian: 'sidang',
            judulTa:
                'Implementasi NLP dan Named Entity Recognition untuk Ekstraksi Otomatis Dokumen Skripsi',
            jadwalAsli: {
                hari: 'Selasa',
                tanggal: '18 Nov 2026',
                jamMulai: '10:45',
                jamSelesai: '12:15 WITA',
                ruangan: 'Ruang Sidang 2 (Gedung Lab B)',
            },
            jadwalUsulan: {
                hari: 'Kamis',
                tanggal: '20 Nov 2026',
                jamMulai: '09:00',
                jamSelesai: '10:30 WITA',
                ruangan: 'Ruang Sidang 2 (Gedung Lab B)',
            },
            alasan: 'Tugas Dinas Luar Kampus',
            catatanDosen:
                'Menghadiri agenda koordinasi akreditasi program studi di Samarinda bersama tim LPM.',
            tanggalPengajuan: '05 Nov 2026, 14:15 WITA',
            status: 'menunggu',
        },
        {
            id: 3,
            dosenId: 18,
            dosenNama: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
            dosenNip: '198604172014041001',
            dosenRole: 'Pembimbing 1',
            mahasiswaId: 103,
            mahasiswaNama: 'Kevin Julio Alexander',
            mahasiswaNim: '11221045',
            mahasiswaAngkatan: '2022',
            tipeUjian: 'sempro',
            judulTa:
                'Pengembangan Sistem Deteksi Intrusi Jaringan Berbasis Deep Learning dan Packet Inspection',
            jadwalAsli: {
                hari: 'Rabu',
                tanggal: '19 Nov 2026',
                jamMulai: '13:00',
                jamSelesai: '14:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            jadwalUsulan: {
                hari: 'Jumat',
                tanggal: '21 Nov 2026',
                jamMulai: '09:00',
                jamSelesai: '10:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            alasan: 'Keperluan Medis / Kesehatan',
            catatanDosen:
                'Ada jadwal kontrol berkala kesehatan di RS Pertamina Balikpapan pada hari Rabu siang.',
            tanggalPengajuan: '06 Nov 2026, 08:20 WITA',
            status: 'menunggu',
        },
        {
            id: 4,
            dosenId: 18,
            dosenNama: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
            dosenNip: '198604172014041001',
            dosenRole: 'Penguji 1',
            mahasiswaId: 104,
            mahasiswaNama: 'Mochammad Reezqi Pratama',
            mahasiswaNim: '11231041',
            mahasiswaAngkatan: '2023',
            tipeUjian: 'sidang',
            judulTa:
                'Optimasi Alokasi Dosen Penguji Ujian Skripsi dengan Pendekatan CSP dan Algoritma Genetika',
            jadwalAsli: {
                hari: 'Kamis',
                tanggal: '13 Nov 2026',
                jamMulai: '08:30',
                jamSelesai: '10:00 WITA',
                ruangan: 'Ruang Sidang 3 (Gedung Lab B)',
            },
            jadwalUsulan: {
                hari: 'Jumat',
                tanggal: '14 Nov 2026',
                jamMulai: '14:00',
                jamSelesai: '15:30 WITA',
                ruangan: 'Ruang Sidang 3 (Gedung Lab B)',
            },
            alasan: 'Bentrok Jadwal Ujian Lain',
            catatanDosen:
                'Bentrok dengan sidang skripsi prodi Teknik Elektro yang telah dijadwalkan lebih awal.',
            tanggalPengajuan: '03 Nov 2026, 11:00 WITA',
            status: 'disetujui',
            catatanKoorprodi:
                'Reschedule disetujui. Ruang Sidang 3 telah dialokasikan ulang untuk Jumat jam 14:00 WITA.',
            disetujuiAt: '04 Nov 2026, 10:15 WITA',
            disetujuiOleh: 'Koorprodi Informatika ITK',
        },
        {
            id: 5,
            dosenId: 20,
            dosenNama: 'Rian Kurniawan, S.Kom., M.Eng.',
            dosenNip: '199003152019031008',
            dosenRole: 'Pembimbing 2',
            mahasiswaId: 105,
            mahasiswaNama: 'Siti Nurhaliza',
            mahasiswaNim: '11211077',
            mahasiswaAngkatan: '2021',
            tipeUjian: 'sempro',
            judulTa:
                'Klasifikasi Citra X-Ray Paru-Paru Menggunakan Arsitektur Vision Transformer',
            jadwalAsli: {
                hari: 'Jumat',
                tanggal: '07 Nov 2026',
                jamMulai: '10:00',
                jamSelesai: '11:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            jadwalUsulan: {
                hari: 'Senin',
                tanggal: '10 Nov 2026',
                jamMulai: '08:00',
                jamSelesai: '09:30 WITA',
                ruangan: 'Ruang Sidang 1 (Gedung Lab A)',
            },
            alasan: 'Bentrok Rapat Jurusan',
            catatanDosen:
                'Rapat koordinasi pimpinan jurusan FSTI yang diadakan mendadak.',
            tanggalPengajuan: '02 Nov 2026, 15:40 WITA',
            status: 'ditolak',
            catatanKoorprodi:
                'Slot ruangan penuh pada Senin pagi. Mohon koordinasikan kembali alternatif hari Selasa atau Rabu siang.',
            disetujuiAt: '03 Nov 2026, 09:00 WITA',
            disetujuiOleh: 'Koorprodi Informatika ITK',
        },
    ],
);

// Filters and Search
const activeStatusFilter = ref<'all' | 'menunggu' | 'disetujui' | 'ditolak'>(
    'all',
);
const activeTypeFilter = ref<'all' | 'sempro' | 'sidang'>('all');
const searchQuery = ref('');

// Computed Statistics
const totalCount = computed(() => requests.value.length);
const pendingCount = computed(
    () => requests.value.filter((r) => r.status === 'menunggu').length,
);
const approvedCount = computed(
    () => requests.value.filter((r) => r.status === 'disetujui').length,
);
const rejectedCount = computed(
    () => requests.value.filter((r) => r.status === 'ditolak').length,
);

// Filtered List
const filteredRequests = computed(() => {
    return requests.value.filter((item) => {
        // Status filter
        if (
            activeStatusFilter.value !== 'all' &&
            item.status !== activeStatusFilter.value
        ) {
            return false;
        }
        // Type filter
        if (
            activeTypeFilter.value !== 'all' &&
            item.tipeUjian !== activeTypeFilter.value
        ) {
            return false;
        }
        // Search query
        if (searchQuery.value.trim()) {
            const q = searchQuery.value.toLowerCase();
            const matchDosen =
                item.dosenNama.toLowerCase().includes(q) ||
                item.dosenNip.includes(q);
            const matchMhs =
                item.mahasiswaNama.toLowerCase().includes(q) ||
                item.mahasiswaNim.includes(q);
            const matchJudul = item.judulTa.toLowerCase().includes(q);
            const matchAlasan = item.alasan.toLowerCase().includes(q);
            return matchDosen || matchMhs || matchJudul || matchAlasan;
        }
        return true;
    });
});

// Modals State
const isModalApproveOpen = ref(false);
const isModalRejectOpen = ref(false);
const selectedItem = ref<RescheduleRequestItem | null>(null);

const formApprove = ref({
    catatan: '',
    ruangan: '',
});
const formReject = ref({
    alasan: '',
});

// Open Modals
const openApproveModal = (item: RescheduleRequestItem) => {
    selectedItem.value = item;
    formApprove.value = {
        catatan: `Reschedule disetujui. Jadwal baru berlaku untuk ${item.tipeUjian === 'sempro' ? 'Seminar Proposal' : 'Sidang TA'}.`,
        ruangan: item.jadwalUsulan.ruangan || item.jadwalAsli.ruangan,
    };
    isModalApproveOpen.value = true;
};

const openRejectModal = (item: RescheduleRequestItem) => {
    selectedItem.value = item;
    formReject.value = {
        alasan: '',
    };
    isModalRejectOpen.value = true;
};

watch(
    () => props.initialRequests,
    (val) => {
        if (val) {
            requests.value = val;
        }
    },
    { immediate: true },
);

// Actions
const confirmApprove = () => {
    if (!selectedItem.value) return;
    const item = selectedItem.value;
    const note =
        formApprove.value.catatan.trim() ||
        'Reschedule disetujui oleh Koorprodi.';

    router.patch(
        `/persetujuan/reschedule/${item.id}`,
        {
            status: 'disetujui',
            ruangan: formApprove.value.ruangan || undefined,
            catatan_kaprodi: note,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                item.status = 'disetujui';
                item.catatanKoorprodi = note;
                item.disetujuiAt = new Date().toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                });
                item.disetujuiOleh = koorprodiName.value;
                if (formApprove.value.ruangan) {
                    item.jadwalUsulan.ruangan = formApprove.value.ruangan;
                }
                isModalApproveOpen.value = false;
                showToast(
                    `Permohonan reschedule dari "${item.dosenNama}" untuk mahasiswa "${item.mahasiswaNama}" berhasil disetujui!`,
                );
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs).join(' ') ||
                    'Gagal menyetujui reschedule.';
                showToast(msg, true);
            },
        },
    );
};

const confirmReject = () => {
    if (!selectedItem.value) return;
    const item = selectedItem.value;
    if (!formReject.value.alasan.trim()) {
        showToast('Harap masukkan alasan penolakan reschedule.', true);
        return;
    }
    const note = formReject.value.alasan.trim();

    router.patch(
        `/persetujuan/reschedule/${item.id}`,
        {
            status: 'ditolak',
            catatan_kaprodi: note,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                item.status = 'ditolak';
                item.catatanKoorprodi = note;
                item.disetujuiAt = new Date().toLocaleString('id-ID', {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                });
                item.disetujuiOleh = koorprodiName.value;
                isModalRejectOpen.value = false;
                showToast(
                    `Permohonan reschedule dari "${item.dosenNama}" telah ditolak.`,
                    true,
                );
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs).join(' ') ||
                    'Gagal menolak reschedule.';
                showToast(msg, true);
            },
        },
    );
};

const resetDecision = (item: RescheduleRequestItem) => {
    router.patch(
        `/persetujuan/reschedule/${item.id}`,
        {
            status: 'menunggu',
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                item.status = 'menunggu';
                item.catatanKoorprodi = undefined;
                item.disetujuiAt = undefined;
                item.disetujuiOleh = undefined;
                showToast(
                    `Status permohonan dari "${item.dosenNama}" dikembalikan ke Menunggu Persetujuan.`,
                );
            },
            onError: (errs) => {
                const msg =
                    Object.values(errs).join(' ') ||
                    'Gagal memperbarui status permohonan.';
                showToast(msg, true);
            },
        },
    );
};
</script>

<template>
    <div class="space-y-6">
        <!-- TOAST NOTIFICATION POPUP -->
        <div
            v-if="toast.show"
            class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md transition-all duration-300"
            :class="[
                toast.isError
                    ? 'border-rose-300 bg-rose-50/95 text-rose-800 dark:border-rose-800 dark:bg-rose-950/90 dark:text-rose-200'
                    : 'border-emerald-300 bg-emerald-50/95 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-200',
            ]"
        >
            <span class="text-xs font-bold sm:text-sm">{{
                toast.message
            }}</span>
        </div>

        <!-- HEADER & TITLE -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <CalendarClock class="h-5 w-5" />
                    </div>
                    <div>
                        <h1
                            class="text-2xl font-black tracking-tight text-black dark:text-white"
                        >
                            Daftar Pengajuan Reschedule Jadwal
                        </h1>
                        <p
                            class="mt-0.5 text-xs font-normal text-slate-500 dark:text-slate-400"
                        >
                            Kelola dan berikan persetujuan atas permohonan
                            perubahan jadwal seminar proposal dan sidang tugas
                            akhir dari dosen.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Role Badge / Action Info -->
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 rounded-xl border border-purple-200 bg-purple-50 px-3 py-1.5 text-xs font-bold text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300"
                >
                    <GraduationCap class="h-3.5 w-3.5" />
                    Portal Koorprodi / Kaprodi
                </span>
            </div>
        </div>

        <!-- STAT CARDS -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <StatCard
                title="Total Pengajuan"
                :value="`${totalCount} Pengajuan`"
                :icon="CalendarClock"
                icon-color="blue"
            />
            <StatCard
                title="Menunggu Persetujuan"
                :value="`${pendingCount} Pengajuan`"
                :icon="Clock"
                icon-color="amber"
            />
            <StatCard
                title="Reschedule Disetujui"
                :value="`${approvedCount} Disetujui`"
                :icon="CheckCircle2"
                icon-color="emerald"
            />
            <StatCard
                title="Pengajuan Ditolak"
                :value="`${rejectedCount} Ditolak`"
                :icon="XCircle"
                icon-color="rose"
            />
        </div>

        <!-- FILTER & SEARCH BAR -->
        <div
            class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <!-- Status Tabs -->
            <div class="flex flex-wrap items-center gap-1.5">
                <button
                    type="button"
                    @click="activeStatusFilter = 'all'"
                    class="flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all"
                    :class="[
                        activeStatusFilter === 'all'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                    ]"
                >
                    <span>Semua</span>
                    <span
                        class="py-0.2 rounded-full px-1.5 text-[10px]"
                        :class="
                            activeStatusFilter === 'all'
                                ? 'bg-white/20 text-white'
                                : 'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200'
                        "
                    >
                        {{ totalCount }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeStatusFilter = 'menunggu'"
                    class="flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all"
                    :class="[
                        activeStatusFilter === 'menunggu'
                            ? 'bg-amber-500 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                    ]"
                >
                    <span>Menunggu</span>
                    <span
                        class="py-0.2 rounded-full px-1.5 text-[10px]"
                        :class="
                            activeStatusFilter === 'menunggu'
                                ? 'bg-white/20 text-white'
                                : 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300'
                        "
                    >
                        {{ pendingCount }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeStatusFilter = 'disetujui'"
                    class="flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all"
                    :class="[
                        activeStatusFilter === 'disetujui'
                            ? 'bg-emerald-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                    ]"
                >
                    <span>Disetujui</span>
                    <span
                        class="py-0.2 rounded-full px-1.5 text-[10px]"
                        :class="
                            activeStatusFilter === 'disetujui'
                                ? 'bg-white/20 text-white'
                                : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                        "
                    >
                        {{ approvedCount }}
                    </span>
                </button>

                <button
                    type="button"
                    @click="activeStatusFilter = 'ditolak'"
                    class="flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-1.5 text-xs font-bold transition-all"
                    :class="[
                        activeStatusFilter === 'ditolak'
                            ? 'bg-rose-600 text-white shadow-xs'
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                    ]"
                >
                    <span>Ditolak</span>
                    <span
                        class="py-0.2 rounded-full px-1.5 text-[10px]"
                        :class="
                            activeStatusFilter === 'ditolak'
                                ? 'bg-white/20 text-white'
                                : 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300'
                        "
                    >
                        {{ rejectedCount }}
                    </span>
                </button>
            </div>

            <!-- Right Controls: Type Filter & Search -->
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <!-- Type Filter -->
                <select
                    v-model="activeTypeFilter"
                    class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-700 outline-hidden transition-colors hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                >
                    <option value="all">Semua Tipe Ujian</option>
                    <option value="sempro">Seminar Proposal</option>
                    <option value="sidang">Sidang TA</option>
                </select>

                <!-- Search Input -->
                <div class="relative w-full sm:w-64">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari dosen, mhs, NIM..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pr-3 pl-8 text-xs font-normal text-slate-800 outline-hidden transition-colors placeholder:text-slate-400 focus:border-blue-500 focus:bg-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:focus:border-blue-500"
                    />
                </div>
            </div>
        </div>

        <!-- RESCHEDULE REQUESTS LIST -->
        <div v-if="filteredRequests.length > 0" class="space-y-4">
            <div
                v-for="item in filteredRequests"
                :key="item.id"
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white transition-all hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <!-- Card Header -->
                <div
                    class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-slate-800/80 dark:bg-slate-900/40"
                >
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Tipe Ujian Badge -->
                        <span
                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold"
                            :class="[
                                item.tipeUjian === 'sempro'
                                    ? 'border border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300'
                                    : 'border border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-800/60 dark:bg-purple-950/40 dark:text-purple-300',
                            ]"
                        >
                            <Calendar class="h-3 w-3" />
                            {{
                                item.tipeUjian === 'sempro'
                                    ? 'Seminar Proposal'
                                    : 'Sidang Tugas Akhir'
                            }}
                        </span>

                        <!-- Status Badge -->
                        <span
                            class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[11px] font-bold"
                            :class="[
                                item.status === 'menunggu'
                                    ? 'border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300'
                                    : item.status === 'disetujui'
                                      ? 'border border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                                      : 'border border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300',
                            ]"
                        >
                            <Clock
                                v-if="item.status === 'menunggu'"
                                class="h-3 w-3 text-amber-500"
                            />
                            <CheckCircle2
                                v-else-if="item.status === 'disetujui'"
                                class="h-3 w-3 text-emerald-500"
                            />
                            <XCircle v-else class="h-3 w-3 text-rose-500" />
                            {{
                                item.status === 'menunggu'
                                    ? 'Menunggu Persetujuan Koorprodi'
                                    : item.status === 'disetujui'
                                      ? 'Reschedule Disetujui'
                                      : 'Reschedule Ditolak'
                            }}
                        </span>
                    </div>

                    <div class="text-[11px] text-slate-400">
                        Diajukan pada:
                        <span
                            class="font-medium text-slate-600 dark:text-slate-300"
                            >{{ item.tanggalPengajuan }}</span
                        >
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-5">
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                        <!-- Left: Dosen Pemohon & Mahasiswa (5 cols) -->
                        <div class="space-y-4 lg:col-span-5">
                            <!-- Dosen Pemohon -->
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 dark:border-slate-800/80 dark:bg-slate-900/30"
                            >
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Dosen Pemohon Reschedule
                                </p>
                                <div class="mt-2 flex items-start gap-3">
                                    <div
                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-100 font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300"
                                    >
                                        {{
                                            item.dosenNama
                                                .split(' ')
                                                .filter(Boolean)
                                                .slice(0, 2)
                                                .map((w) => w[0])
                                                .join('')
                                        }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.dosenNama }}
                                        </h4>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            NIP: {{ item.dosenNip }}
                                        </p>
                                        <div class="mt-1">
                                            <span
                                                class="inline-flex rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300"
                                            >
                                                Peran: {{ item.dosenRole }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Mahasiswa Terkait -->
                            <div
                                class="rounded-xl border border-slate-100 bg-slate-50/50 p-3.5 dark:border-slate-800/80 dark:bg-slate-900/30"
                            >
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Mahasiswa Teruji
                                </p>
                                <div class="mt-1.5">
                                    <h5
                                        class="text-xs font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.mahasiswaNama }}
                                        <span
                                            class="text-[11px] font-normal text-slate-500"
                                            >({{ item.mahasiswaNim }})</span
                                        >
                                    </h5>
                                    <p
                                        class="mt-1 line-clamp-2 text-[11px] text-slate-600 italic dark:text-slate-300"
                                    >
                                        "{{ item.judulTa }}"
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Right: Comparison of Schedules & Reasons (7 cols) -->
                        <div
                            class="flex flex-col justify-between space-y-4 lg:col-span-7"
                        >
                            <!-- Comparison Box -->
                            <div
                                class="rounded-xl border border-slate-200/70 p-4 dark:border-slate-800"
                            >
                                <p
                                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                                >
                                    Perbandingan Jadwal
                                </p>
                                <div
                                    class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2"
                                >
                                    <!-- Jadwal Awal -->
                                    <div
                                        class="rounded-xl border border-rose-200/80 bg-rose-50/50 p-3 dark:border-rose-900/50 dark:bg-rose-950/20"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-[11px] font-bold text-rose-700 dark:text-rose-400"
                                        >
                                            <Calendar class="h-3.5 w-3.5" />
                                            <span
                                                >Jadwal Semula (Bentrokan)</span
                                            >
                                        </div>
                                        <p
                                            class="mt-2 text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.jadwalAsli.hari }},
                                            {{ item.jadwalAsli.tanggal }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-300"
                                        >
                                            {{ item.jadwalAsli.jamMulai }} -
                                            {{ item.jadwalAsli.jamSelesai }}
                                        </p>
                                        <p
                                            class="mt-1 flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            <MapPin class="h-3 w-3 shrink-0" />
                                            {{ item.jadwalAsli.ruangan }}
                                        </p>
                                    </div>

                                    <!-- Usulan Jadwal Baru -->
                                    <div
                                        class="rounded-xl border border-emerald-200/80 bg-emerald-50/50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/20"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-[11px] font-bold text-emerald-700 dark:text-emerald-400"
                                        >
                                            <CalendarClock
                                                class="h-3.5 w-3.5"
                                            />
                                            <span>Usulan Jadwal Baru</span>
                                        </div>
                                        <p
                                            class="mt-2 text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.jadwalUsulan.hari }},
                                            {{ item.jadwalUsulan.tanggal }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-xs text-slate-600 dark:text-slate-300"
                                        >
                                            {{ item.jadwalUsulan.jamMulai }} -
                                            {{ item.jadwalUsulan.jamSelesai }}
                                        </p>
                                        <p
                                            class="mt-1 flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            <MapPin class="h-3 w-3 shrink-0" />
                                            {{
                                                item.jadwalUsulan.ruangan ||
                                                item.jadwalAsli.ruangan
                                            }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Alasan & Catatan Dosen -->
                                <div
                                    class="mt-3.5 space-y-1.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                                >
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-[10px] font-bold text-slate-400 uppercase"
                                            >Alasan:</span
                                        >
                                        <span
                                            class="rounded-md bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-900 dark:bg-amber-950/60 dark:text-amber-300"
                                        >
                                            {{ item.alasan }}
                                        </span>
                                    </div>
                                    <p
                                        class="text-xs text-slate-700 dark:text-slate-300"
                                    >
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                            >Catatan dosen:</span
                                        >
                                        "{{ item.catatanDosen }}"
                                    </p>
                                </div>

                                <!-- Catatan Koorprodi bila sudah ditindaklanjuti -->
                                <div
                                    v-if="item.catatanKoorprodi"
                                    class="mt-3 rounded-lg border p-2.5 text-xs"
                                    :class="[
                                        item.status === 'disetujui'
                                            ? 'border-emerald-200 bg-emerald-50/70 text-emerald-900 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200'
                                            : 'border-rose-200 bg-rose-50/70 text-rose-900 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200',
                                    ]"
                                >
                                    <p class="font-bold">
                                        Catatan Koorprodi ({{
                                            item.disetujuiAt
                                        }}):
                                    </p>
                                    <p class="mt-0.5 font-normal">
                                        {{ item.catatanKoorprodi }}
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons for Koorprodi -->
                            <div
                                class="flex flex-wrap items-center justify-end gap-2 pt-2"
                            >
                                <template v-if="item.status === 'menunggu'">
                                    <Button
                                        variant="danger"
                                        size="sm"
                                        class="gap-1.5 text-xs"
                                        @click="openRejectModal(item)"
                                    >
                                        <X class="h-3.5 w-3.5" />
                                        <span>Tolak Pengajuan</span>
                                    </Button>

                                    <Button
                                        variant="primary"
                                        size="sm"
                                        class="gap-1.5 bg-emerald-600 text-xs hover:bg-emerald-700 focus:ring-emerald-500"
                                        @click="openApproveModal(item)"
                                    >
                                        <Check class="h-3.5 w-3.5" />
                                        <span>Setujui Pengajuan</span>
                                    </Button>
                                </template>

                                <template v-else>
                                    <button
                                        type="button"
                                        @click="resetDecision(item)"
                                        class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700"
                                        title="Kembalikan permohonan ke status Menunggu"
                                    >
                                        <RotateCcw class="h-3.5 w-3.5" />
                                        <span>Ubah Keputusan</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- EMPTY STATE -->
        <div
            v-else
            class="flex flex-col items-center justify-center rounded-2xl border border-slate-200/80 bg-white p-12 text-center shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500"
            >
                <CalendarClock class="h-6 w-6" />
            </div>
            <h3 class="mt-4 text-sm font-bold text-slate-900 dark:text-white">
                Tidak ada permohonan reschedule
            </h3>
            <p class="mt-1 max-w-sm text-xs text-slate-500 dark:text-slate-400">
                Tidak ditemukan permohonan perubahan jadwal dengan kriteria
                pencarian atau filter yang dipilih.
            </p>
        </div>

        <!-- ============================================== -->
        <!-- MODAL 1: PERSETUJUAN PENGAJUAN                 -->
        <!-- ============================================== -->
        <Modal
            :show="isModalApproveOpen"
            max-width="md"
            @close="isModalApproveOpen = false"
        >
            <div class="space-y-4" v-if="selectedItem">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                    >
                        <Check class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Setujui Pengajuan Jadwal
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Konfirmasi persetujuan perubahan jadwal ujian.
                        </p>
                    </div>
                </div>

                <!-- Brief Details -->
                <div
                    class="rounded-xl border border-emerald-200 bg-emerald-50/50 p-3.5 text-xs dark:border-emerald-900/50 dark:bg-emerald-950/20"
                >
                    <p class="font-bold text-slate-900 dark:text-white">
                        Dosen:
                        <span class="font-normal"
                            >{{ selectedItem.dosenNama }} ({{
                                selectedItem.dosenRole
                            }})</span
                        >
                    </p>
                    <p class="font-bold text-slate-900 dark:text-white">
                        Mahasiswa:
                        <span class="font-normal"
                            >{{ selectedItem.mahasiswaNama }} ({{
                                selectedItem.mahasiswaNim
                            }})</span
                        >
                    </p>
                    <div
                        class="mt-2 border-t border-emerald-200/80 pt-2 text-[11px] text-slate-700 dark:border-emerald-800 dark:text-slate-300"
                    >
                        <span
                            class="font-semibold text-emerald-800 dark:text-emerald-300"
                            >Jadwal Baru yang Disetujui:</span
                        >
                        <p class="font-bold">
                            {{ selectedItem.jadwalUsulan.hari }},
                            {{ selectedItem.jadwalUsulan.tanggal }} |
                            {{ selectedItem.jadwalUsulan.jamMulai }} -
                            {{ selectedItem.jadwalUsulan.jamSelesai }}
                        </p>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-3">
                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Ruangan Ujian yang Dialokasikan
                        </label>
                        <input
                            v-model="formApprove.ruangan"
                            type="text"
                            placeholder="Contoh: Ruang Sidang 1 (Gedung Lab A)"
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs text-slate-900 outline-hidden transition-colors focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Catatan Persetujuan Koorprodi (Opsional)
                        </label>
                        <textarea
                            v-model="formApprove.catatan"
                            rows="3"
                            placeholder="Tuliskan arahan atau konfirmasi koordinasi ruangan..."
                            class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-900 outline-hidden transition-colors focus:border-blue-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        ></textarea>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex items-center justify-end gap-2 pt-2">
                    <Button
                        variant="secondary"
                        size="sm"
                        @click="isModalApproveOpen = false"
                    >
                        Batal
                    </Button>
                    <Button
                        variant="primary"
                        size="sm"
                        class="bg-emerald-600 hover:bg-emerald-700"
                        @click="confirmApprove"
                    >
                        Konfirmasi Setujui Pengajuan
                    </Button>
                </div>
            </div>
        </Modal>

        <!-- ============================================== -->
        <!-- MODAL 2: PENOLAKAN PENGAJUAN                   -->
        <!-- ============================================== -->
        <Modal
            :show="isModalRejectOpen"
            max-width="md"
            @close="isModalRejectOpen = false"
        >
            <div class="space-y-4" v-if="selectedItem">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300"
                    >
                        <X class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            Tolak Pengajuan Jadwal
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Sampaikan alasan penolakan kepada dosen pemohon.
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-rose-200 bg-rose-50/50 p-3.5 text-xs dark:border-rose-900/50 dark:bg-rose-950/20"
                >
                    <p class="font-bold text-slate-900 dark:text-white">
                        Dosen:
                        <span class="font-normal"
                            >{{ selectedItem.dosenNama }} ({{
                                selectedItem.dosenRole
                            }})</span
                        >
                    </p>
                    <p class="font-bold text-slate-900 dark:text-white">
                        Mahasiswa:
                        <span class="font-normal"
                            >{{ selectedItem.mahasiswaNama }} ({{
                                selectedItem.mahasiswaNim
                            }})</span
                        >
                    </p>
                </div>

                <div>
                    <label
                        class="block text-xs font-bold text-slate-700 dark:text-slate-300"
                    >
                        Alasan Penolakan <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        v-model="formReject.alasan"
                        rows="3"
                        placeholder="Contoh: Slot ruangan pada waktu usulan telah terisi penuh. Harap koordinasikan alternatif sesi lain."
                        class="mt-1 w-full rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-900 outline-hidden transition-colors focus:border-rose-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    ></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <Button
                        variant="secondary"
                        size="sm"
                        @click="isModalRejectOpen = false"
                    >
                        Batal
                    </Button>
                    <Button variant="danger" size="sm" @click="confirmReject">
                        Tolak Pengajuan
                    </Button>
                </div>
            </div>
        </Modal>
    </div>
</template>
