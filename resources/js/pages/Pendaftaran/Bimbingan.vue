<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import {
    Download,
    Edit2,
    FileCheck,
    Plus,
    Search,
    Trash2,
    X,
} from "lucide-vue-next";

import StatusBadge from "@/Components/StatusBadge.vue";

import DosenTeamSection from "@/Components/DosenTeamSection.vue";
import PageHeaderBox from "@/Components/PageHeaderBox.vue";
import ProfileHeaderCard from "@/Components/ProfileHeaderCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);
const studentName = computed(
    () => authUser.value?.name || "Akmal Falah Maulana",
);
const studentNim = computed(
    () =>
        authUser.value?.username ||
        authUser.value?.nim_nip?.replace("NIM: ", "") ||
        "11231006",
);
const studentProdi = computed(() => authUser.value?.prodi || "Informatika");
const studentInitials = computed(() => {
    if (!authUser.value?.name) return "AF";
    return authUser.value.name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((w: string) => w[0]?.toUpperCase())
        .join("");
});

interface BimbinganLog {
    id: number;
    tanggal: string;
    dosen: string;
    rangkuman: string;
    keterangan: string;
    metode: "online" | "offline";
    tindakLanjut?: string;
    status: "draft" | "diajukan" | "disetujui" | "revisi" | "ditolak";
    createdAt: string;
    updatedAt?: string;
}

const showModal = ref(false);
const selectedLog = ref<BimbinganLog | null>(null);
const editingId = ref<number | null>(null);
const searchQuery = ref("");
const entriesPerPage = ref(10);
const currentPage = ref(1);

const bimbinganList = ref<BimbinganLog[]>([
    {
        id: 1,
        tanggal: "2026-05-13",
        dosen: "Dr. Ir. Hendra Wijaya, M.Kom.",
        rangkuman:
            "Diskusi arsitektur sistem & pemilihan algoritma genetika untuk penjadwalan tugas akhir",
        keterangan: "Lab Riset Informatika",
        metode: "offline",
        tindakLanjut: "Mengkaji paperGA scheduling, prepare dataset",
        status: "disetujui",
        createdAt: "2026-05-13T10:00:00Z",
    },
    {
        id: 2,
        tanggal: "2026-06-15",
        dosen: "Rina Agustina, S.T., M.Kom.",
        rangkuman:
            "Penyusunan use case diagram dan activity diagram modul pendaftaran seminar",
        keterangan: "Google Meet",
        metode: "online",
        tindakLanjut: "Revisi ERD, prepare prototype UI",
        status: "disetujui",
        createdAt: "2026-06-15T14:30:00Z",
    },
    {
        id: 3,
        tanggal: "2026-07-20",
        dosen: "Dr. Ir. Hendra Wijaya, M.Kom.",
        rangkuman:
            "Evaluasi integrasi Large Language Model dan format data training untuk layanan akademik",
        keterangan: "Ruang Dosen Gedung A",
        metode: "offline",
        tindakLanjut: "Test prompting, evaluasi hasil",
        status: "revisi",
        createdAt: "2026-07-20T09:15:00Z",
    },
    {
        id: 4,
        tanggal: "2026-08-05",
        dosen: "Rina Agustina, S.T., M.Kom.",
        rangkuman:
            "Revisi instrumen pengujian blackbox dan definition skenario usability testing",
        keterangan: "Zoom Meeting",
        metode: "online",
        tindakLanjut: "Finalisasi test plan",
        status: "diajukan",
        createdAt: "2026-08-05T16:45:00Z",
    },
]);

const form = useForm({
    tanggal: "",
    dosen: "",
    rangkuman: "",
    keterangan: "",
    metode: "offline" as "online" | "offline",
    tindakLanjut: "",
});

const openModal = () => {
    editingId.value = null;
    form.reset();
    form.metode = "offline";
    showModal.value = true;
};

const editLog = (log: BimbinganLog) => {
    if (log.status !== "draft" && log.status !== "revisi") return;
    editingId.value = log.id;
    form.tanggal = log.tanggal;
    form.dosen = log.dosen;
    form.rangkuman = log.rangkuman;
    form.keterangan = log.keterangan;
    form.metode = log.metode;
    form.tindakLanjut = log.tindakLanjut || "";
    showModal.value = true;
};

const deleteLog = (id: number) => {
    const log = bimbinganList.value.find((l) => l.id === id);
    if (!log || (log.status !== "draft" && log.status !== "revisi")) return;
    if (!confirm("Hapus catatan bimbingan ini?")) return;
    bimbinganList.value = bimbinganList.value.filter((l) => l.id !== id);
};

const closeModal = () => {
    showModal.value = false;
    editingId.value = null;
};

const openDetail = (log: BimbinganLog) => {
    selectedLog.value = log;
};

const closeDetail = () => {
    selectedLog.value = null;
};

const submitBimbingan = () => {
    if (
        !form.tanggal ||
        !form.dosen ||
        !form.rangkuman.trim() ||
        !form.keterangan.trim()
    )
        return;

    const now = new Date().toISOString();

    if (editingId.value) {
        const idx = bimbinganList.value.findIndex(
            (l) => l.id === editingId.value,
        );
        if (idx !== -1) {
            bimbinganList.value[idx] = {
                ...bimbinganList.value[idx],
                tanggal: form.tanggal,
                dosen: form.dosen,
                rangkuman: form.rangkuman,
                keterangan: form.keterangan,
                metode: form.metode,
                tindakLanjut: form.tindakLanjut || undefined,
                status: "diajukan",
                updatedAt: now,
            };
        }
    } else {
        bimbinganList.value.unshift({
            id: Date.now(),
            tanggal: form.tanggal,
            dosen: form.dosen,
            rangkuman: form.rangkuman,
            keterangan: form.keterangan,
            metode: form.metode,
            tindakLanjut: form.tindakLanjut || undefined,
            status: "diajukan",
            createdAt: now,
        });
    }

    closeModal();
};

const filteredList = computed(() => {
    if (!searchQuery.value) return bimbinganList.value;
    const q = searchQuery.value.toLowerCase();
    return bimbinganList.value.filter(
        (item) =>
            item.dosen.toLowerCase().includes(q) ||
            item.rangkuman.toLowerCase().includes(q) ||
            item.keterangan.toLowerCase().includes(q),
    );
});

const paginatedList = computed(() => {
    const start = (currentPage.value - 1) * entriesPerPage.value;
    const end = start + entriesPerPage.value;
    return filteredList.value.slice(start, end);
});

const totalPages = computed(() =>
    Math.ceil(filteredList.value.length / entriesPerPage.value),
);

const canEdit = (log: BimbinganLog) =>
    log.status === "draft" || log.status === "revisi";

const formatTanggal = (tanggal: string) => {
    const d = new Date(tanggal);
    return d.toLocaleDateString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
};

const formatWaktu = (isoString: string) => {
    const d = new Date(isoString);
    return d.toLocaleString("id-ID", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};
</script>

<template>
    <AppLayout title="Bimbingan TA">
        <Head title="Bimbingan Tugas Akhir - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- 1. Header Box (Reusable Component) -->
            <PageHeaderBox title="Bimbingan" />

            <!-- 2. Profile & Download Form TA-04 Card (Reusable Component) -->
            <ProfileHeaderCard
                :name="studentName"
                :nim="studentNim"
                angkatan="Akt 2023"
                :prodi="studentProdi"
                :avatar-initials="studentInitials"
            >
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#5246D2] px-5 py-3 text-xs font-bold text-white shadow-md shadow-indigo-600/30 transition-all hover:bg-indigo-700 active:scale-95"
                >
                    <Download class="h-4 w-4" />
                    <span>Unduh Lembar Monitoring Bimbingan (Form TA-04)</span>
                </button>
            </ProfileHeaderCard>

            <!-- 3. Pembimbing & Penguji Grid (Reusable Component) -->
            <DosenTeamSection />

            <!-- 4. Logbook Bimbingan -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3
                    class="mb-4 border-b border-slate-100 pb-3 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    Logbook Bimbingan
                </h3>

                <!-- Controls Bar -->
                <div
                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300"
                    >
                        <span>Tampilkan</span>
                        <select
                            v-model="entriesPerPage"
                            class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                        <span>data</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative flex-1 sm:flex-none">
                            <Search
                                class="absolute top-2.5 left-3 h-3.5 w-3.5 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Cari bimbingan..."
                                class="w-full rounded-xl border border-slate-300 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden sm:w-64 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            />
                        </div>

                        <!-- Button Tambah Baru -->
                        <button
                            type="button"
                            class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-xl bg-[#6147F8] px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-indigo-600 active:scale-95"
                            @click="openModal"
                        >
                            <Plus class="h-3.5 w-3.5" />
                            <span>Tambah Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="filteredList.length === 0"
                    class="flex flex-col items-center justify-center gap-2 py-12 text-center"
                >
                    <FileCheck
                        class="h-10 w-10 text-slate-300 dark:text-slate-700"
                    />
                    <p
                        class="text-sm font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Belum ada catatan bimbingan
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{
                            searchQuery
                                ? "Pencarian tidak menemukan hasil. Coba kata kunci lain."
                                : 'Klik "Tambah Baru" untuk mulai mencatat kegiatan bimbingan.'
                        }}
                    </p>
                </div>

                <!-- Desktop Table -->
                <div v-else class="hidden overflow-x-auto md:block">
                    <table
                        class="w-full text-left text-xs text-slate-700 dark:text-slate-300"
                    >
                        <thead
                            class="border-b border-slate-200 bg-slate-50/80 font-bold text-slate-800 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-200"
                        >
                            <tr>
                                <th class="px-3 py-3">No.</th>
                                <th class="px-3 py-3">Tanggal</th>
                                <th class="px-3 py-3">Dosen Pembimbing</th>
                                <th class="px-3 py-3">Rangkuman</th>
                                <th class="px-3 py-3">Metode</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="(item, index) in paginatedList"
                                :key="item.id"
                                class="cursor-pointer hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                                @click="openDetail(item)"
                            >
                                <td class="px-3 py-3.5 font-semibold">
                                    {{
                                        (currentPage - 1) * entriesPerPage +
                                        index +
                                        1
                                    }}.
                                </td>
                                <td
                                    class="px-3 py-3.5 whitespace-nowrap font-medium text-slate-900 dark:text-white"
                                >
                                    {{ formatTanggal(item.tanggal) }}
                                </td>
                                <td class="px-3 py-3.5">
                                    {{ item.dosen }}
                                </td>
                                <td class="px-3 py-3.5">
                                    <div class="max-w-xs">
                                        {{ item.rangkuman }}
                                    </div>
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1.5 rounded-lg px-2 py-0.5 text-[11px] font-medium"
                                        :class="[
                                            item.metode === 'online'
                                                ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                                                : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                        ]"
                                    >
                                        {{
                                            item.metode === "online"
                                                ? "Daring"
                                                : "Tatap Muka"
                                        }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <StatusBadge :status="item.status" />
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                        @click.stop
                                    >
                                        <button
                                            v-if="canEdit(item)"
                                            type="button"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            title="Edit Bimbingan"
                                            @click="editLog(item)"
                                        >
                                            <Edit2 class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            v-if="canEdit(item)"
                                            type="button"
                                            class="text-rose-500 hover:text-rose-700 dark:text-rose-400"
                                            title="Hapus"
                                            @click="deleteLog(item.id)"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                        <span
                                            v-if="!canEdit(item)"
                                            class="text-[11px] italic text-slate-400 dark:text-slate-600"
                                            >Dikunci</span
                                        >
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div v-if="filteredList.length > 0" class="space-y-3 md:hidden">
                    <div
                        v-for="item in paginatedList"
                        :key="item.id"
                        class="cursor-pointer rounded-xl border border-slate-200/80 p-4 dark:border-slate-800"
                        @click="openDetail(item)"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ formatTanggal(item.tanggal) }}
                                </p>
                                <p
                                    class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    {{ item.dosen }}
                                </p>
                            </div>
                            <StatusBadge :status="item.status" size="sm" />
                        </div>
                        <p
                            class="mt-3 text-xs leading-relaxed text-slate-700 dark:text-slate-300"
                        >
                            {{ item.rangkuman }}
                        </p>
                        <div
                            class="mt-3 flex flex-wrap items-center gap-2 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            <span
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-0.5 font-medium"
                                :class="[
                                    item.metode === 'online'
                                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                                        : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ]"
                            >
                                {{
                                    item.metode === "online"
                                        ? "Daring"
                                        : "Tatap Muka"
                                }}
                            </span>
                            <span>•</span>
                            <span>{{ item.keterangan }}</span>
                        </div>
                        <div
                            class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                            @click.stop
                        >
                            <button
                                v-if="canEdit(item)"
                                type="button"
                                class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 dark:text-blue-400"
                                @click="editLog(item)"
                            >
                                <Edit2 class="h-3 w-3" /> Edit
                            </button>
                            <button
                                v-if="canEdit(item)"
                                type="button"
                                class="inline-flex items-center gap-1 text-[11px] font-medium text-rose-500 dark:text-rose-400"
                                @click="deleteLog(item.id)"
                            >
                                <Trash2 class="h-3 w-3" /> Hapus
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="totalPages > 1"
                    class="mt-5 flex items-center justify-center gap-2 text-xs text-slate-600 dark:text-slate-300"
                >
                    <button
                        type="button"
                        :disabled="currentPage === 1"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium transition-colors hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40 dark:border-slate-700 dark:hover:bg-slate-800"
                        @click="currentPage--"
                    >
                        Sebelumnya
                    </button>
                    <span class="font-medium">
                        Halaman {{ currentPage }} dari {{ totalPages }}
                    </span>
                    <button
                        type="button"
                        :disabled="currentPage === totalPages"
                        class="rounded-lg border border-slate-300 px-3 py-1.5 font-medium transition-colors hover:bg-slate-50 disabled:pointer-events-none disabled:opacity-40 dark:border-slate-700 dark:hover:bg-slate-800"
                        @click="currentPage++"
                    >
                        Berikutnya
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FORM BIMBINGAN (Sesuai Frame 1000000914.png) -->
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
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                        @click="closeModal"
                    />

                    <div
                        class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
                    >
                        <h3
                            class="border-b border-slate-100 pb-3 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                        >
                            Form Bimbingan Tugas Akhir
                        </h3>

                        <form
                            class="mt-4 space-y-4"
                            @submit.prevent="submitBimbingan"
                        >
                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Tanggal
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.tanggal"
                                    type="date"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Dosen Pembimbing
                                    <span class="text-rose-500">*</span></label
                                >
                                <select
                                    v-model="form.dosen"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                >
                                    <option value="" disabled>
                                        Pilih Dosen Pembimbing
                                    </option>
                                    <option
                                        value="Dr. Ir. Hendra Wijaya, M.Kom."
                                    >
                                        Dr. Ir. Hendra Wijaya, M.Kom.
                                        (Pembimbing 1)
                                    </option>
                                    <option value="Rina Agustina, S.T., M.Kom.">
                                        Rina Agustina, S.T., M.Kom. (Pembimbing
                                        2)
                                    </option>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Rangkuman Pembahasan
                                    <span class="text-rose-500">*</span></label
                                >
                                <textarea
                                    v-model="form.rangkuman"
                                    rows="3"
                                    placeholder="Jelaskan topik yang dibahas, keputusan utama, dan rekomendasi pembimbing"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 p-3 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Metode Bimbingan
                                    <span class="text-rose-500">*</span></label
                                >
                                <div class="flex gap-3">
                                    <label
                                        class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"
                                    >
                                        <input
                                            v-model="form.metode"
                                            type="radio"
                                            value="online"
                                            class="h-3 w-3 text-blue-600 focus:ring-blue-500"
                                        />
                                        Daring
                                    </label>
                                    <label
                                        class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300"
                                    >
                                        <input
                                            v-model="form.metode"
                                            type="radio"
                                            value="offline"
                                            class="h-3 w-3 text-blue-600 focus:ring-blue-500"
                                        />
                                        Tatap Muka
                                    </label>
                                </div>
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Tempat/Lokasi atau Platform
                                    <span class="text-rose-500">*</span></label
                                >
                                <input
                                    v-model="form.keterangan"
                                    type="text"
                                    placeholder='Contoh: "Ruang Dosen Gedung A" atau "Google Meet"'
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Tindak Lanjut / Target Lainnya</label
                                >
                                <textarea
                                    v-model="form.tindakLanjut"
                                    rows="2"
                                    placeholder="Langkah yang akan dilakukan sebelum bimbingan berikutnya (opsional)"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 p-3 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div
                                class="flex items-center justify-end gap-3 pt-3"
                            >
                                <button
                                    type="button"
                                    class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    @click="closeModal"
                                >
                                    Tutup
                                </button>
                                <button
                                    type="submit"
                                    class="rounded-xl bg-[#8CE79B] px-5 py-2 text-xs font-bold text-slate-900 hover:bg-[#7BD68A]"
                                >
                                    Ajukan Logbook
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- MODAL DETAIL LOGBOOK -->
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
                    v-if="selectedLog"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <div
                        class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                        @click="closeDetail"
                    />

                    <div
                        class="relative w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3
                                    class="text-lg font-bold text-slate-900 dark:text-white"
                                >
                                    Detail Logbook Bimbingan
                                </h3>
                                <p
                                    class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    {{ formatTanggal(selectedLog.tanggal) }}
                                </p>
                            </div>
                            <button
                                type="button"
                                class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                @click="closeDetail"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="mt-6 space-y-4">
                            <!-- Status -->
                            <div class="flex items-center gap-2">
                                <span
                                    class="text-xs font-semibold text-slate-600 dark:text-slate-400"
                                    >Status:</span
                                >
                                <StatusBadge :status="selectedLog.status" />
                            </div>

                            <!-- Dosen Pembimbing -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-600 dark:text-slate-400"
                                    >Dosen Pembimbing</label
                                >
                                <p
                                    class="mt-1 text-sm text-slate-900 dark:text-white"
                                >
                                    {{ selectedLog.dosen }}
                                </p>
                            </div>

                            <!-- Metode & Tempat -->
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 dark:text-slate-400"
                                        >Metode Bimbingan</label
                                    >
                                    <p
                                        class="mt-1 text-sm text-slate-900 dark:text-white"
                                    >
                                        {{
                                            selectedLog.metode === "online"
                                                ? "Daring"
                                                : "Tatap Muka"
                                        }}
                                    </p>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-semibold text-slate-600 dark:text-slate-400"
                                        >Tempat/Platform</label
                                    >
                                    <p
                                        class="mt-1 text-sm text-slate-900 dark:text-white"
                                    >
                                        {{ selectedLog.keterangan }}
                                    </p>
                                </div>
                            </div>

                            <!-- Rangkuman -->
                            <div>
                                <label
                                    class="block text-xs font-semibold text-slate-600 dark:text-slate-400"
                                    >Rangkuman Pembahasan</label
                                >
                                <p
                                    class="mt-1 whitespace-pre-wrap text-sm leading-relaxed text-slate-700 dark:text-slate-300"
                                >
                                    {{ selectedLog.rangkuman }}
                                </p>
                            </div>

                            <!-- Tindak Lanjut -->
                            <div v-if="selectedLog.tindakLanjut">
                                <label
                                    class="block text-xs font-semibold text-slate-600 dark:text-slate-400"
                                    >Tindak Lanjut / Target</label
                                >
                                <p
                                    class="mt-1 whitespace-pre-wrap text-sm leading-relaxed text-slate-700 dark:text-slate-300"
                                >
                                    {{ selectedLog.tindakLanjut }}
                                </p>
                            </div>

                            <!-- Metadata -->
                            <div
                                class="border-t border-slate-100 pt-4 dark:border-slate-800"
                            >
                                <div
                                    class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400"
                                >
                                    <span
                                        >Diajukan:
                                        {{
                                            formatWaktu(selectedLog.createdAt)
                                        }}</span
                                    >
                                    <span v-if="selectedLog.updatedAt"
                                        >Diperbarui:
                                        {{
                                            formatWaktu(selectedLog.updatedAt)
                                        }}</span
                                    >
                                </div>
                            </div>

                            <!-- Actions -->
                            <div
                                v-if="canEdit(selectedLog)"
                                class="flex items-center gap-3 border-t border-slate-100 pt-4 dark:border-slate-800"
                            >
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    @click="
                                        editLog(selectedLog);
                                        closeDetail();
                                    "
                                >
                                    <Edit2 class="h-3.5 w-3.5" /> Edit Logbook
                                </button>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-bold text-rose-700 hover:bg-rose-100 dark:border-rose-900 dark:bg-rose-950/60 dark:text-rose-300"
                                    @click="
                                        deleteLog(selectedLog.id);
                                        closeDetail();
                                    "
                                >
                                    <Trash2 class="h-3.5 w-3.5" /> Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
