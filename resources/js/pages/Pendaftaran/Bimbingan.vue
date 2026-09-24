<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    Download,
    Edit2,
    FileCheck,
    Plus,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';

import DosenTeamSection from '@/Components/DosenTeamSection.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import ProfileHeaderCard from '@/Components/ProfileHeaderCard.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);
const studentName = computed(
    () => authUser.value?.name || 'Akmal Falah Maulana',
);
const studentNim = computed(
    () =>
        authUser.value?.username ||
        authUser.value?.nim_nip?.replace('NIM: ', '') ||
        '11231006',
);
const studentProdi = computed(() => authUser.value?.prodi || 'Informatika');
const studentInitials = computed(() => {
    if (!authUser.value?.name) return 'AF';
    return authUser.value.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w: string) => w[0]?.toUpperCase())
        .join('');
});

interface BimbinganLog {
    id: number;
    tanggal: string;
    dosen: string;
    rangkuman: string;
    keterangan: string;
    status: 'Delivered' | 'Process';
}

const showModal = ref(false);
const searchQuery = ref('');
const entriesPerPage = ref(10);

const bimbinganList = ref<BimbinganLog[]>([
    {
        id: 1,
        tanggal: '13/05/2026',
        dosen: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        rangkuman:
            'Diskusi arsitektur sistem & pemilihan algoritma genetika untuk penjadwalan',
        keterangan: 'Offline (Lab Riset Informatika)',
        status: 'Delivered',
    },
    {
        id: 2,
        tanggal: '15/06/2026',
        dosen: 'Rina Agustina, S.T., M.Kom.',
        rangkuman:
            'Penyusunan use case diagram dan activity diagram modul pendaftaran',
        keterangan: 'Online (Google Meet)',
        status: 'Process',
    },
    {
        id: 3,
        tanggal: '20/07/2026',
        dosen: 'Dr. Ir. Hendra Wijaya, M.Kom.',
        rangkuman:
            'Evaluasi integrasi Large Language Model dan format data training layanan akademik',
        keterangan: 'Offline (Ruang Dosen Gedung A)',
        status: 'Delivered',
    },
    {
        id: 4,
        tanggal: '05/08/2026',
        dosen: 'Rina Agustina, S.T., M.Kom.',
        rangkuman:
            'Revisi instrumen pengujian blackbox dan skenario usability testing',
        keterangan: 'Online (Zoom Meeting)',
        status: 'Process',
    },
]);

const form = useForm({
    tanggal: '',
    dosen: '',
    rangkuman: '',
    keterangan: '',
});

const openModal = () => {
    form.reset();
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitBimbingan = () => {
    if (!form.tanggal || !form.dosen || !form.rangkuman || !form.keterangan)
        return;

    bimbinganList.value.unshift({
        id: Date.now(),
        tanggal: form.tanggal,
        dosen: form.dosen,
        rangkuman: form.rangkuman,
        keterangan: form.keterangan,
        status: 'Process',
    });

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
</script>

<template>
    <AppLayout title="Dashboard">
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

            <!-- 4. Table Riwayat Logbook Bimbingan -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <!-- Controls Bar -->
                <div
                    class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div
                        class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300"
                    >
                        <span>Show</span>
                        <select
                            v-model="entriesPerPage"
                            class="rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-800"
                        >
                            <option :value="10">10</option>
                            <option :value="25">25</option>
                            <option :value="50">50</option>
                        </select>
                        <span>entries</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Search Box -->
                        <div class="relative">
                            <Search
                                class="absolute top-2.5 left-3 h-3.5 w-3.5 text-slate-400"
                            />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search..."
                                class="w-48 rounded-xl border border-slate-300 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-800 focus:border-blue-500 focus:outline-hidden sm:w-64 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            />
                        </div>

                        <!-- Button Tambah Baru -->
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-[#6147F8] px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-indigo-600 active:scale-95"
                            @click="openModal"
                        >
                            <Plus class="h-3.5 w-3.5" />
                            <span>Tambah Baru</span>
                        </button>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
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
                                <th class="px-3 py-3">Keterangan</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="(item, index) in filteredList"
                                :key="item.id"
                                class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40"
                            >
                                <td class="px-3 py-3.5 font-semibold">
                                    {{ index + 1 }}.
                                </td>
                                <td class="px-3 py-3.5 whitespace-nowrap">
                                    {{ item.tanggal }}
                                </td>
                                <td
                                    class="px-3 py-3.5 font-medium text-slate-900 dark:text-white"
                                >
                                    {{ item.dosen }}
                                </td>
                                <td class="max-w-xs truncate px-3 py-3.5">
                                    {{ item.rangkuman }}
                                </td>
                                <td class="px-3 py-3.5">
                                    {{ item.keterangan }}
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                        :class="[
                                            item.status === 'Delivered'
                                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="px-3 py-3.5 text-center">
                                    <div
                                        class="flex items-center justify-center gap-2"
                                    >
                                        <button
                                            type="button"
                                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                            title="Edit Bimbingan"
                                        >
                                            <Edit2 class="h-3.5 w-3.5" />
                                        </button>
                                        <button
                                            type="button"
                                            class="text-rose-500 hover:text-rose-700 dark:text-rose-400"
                                            title="Hapus"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                    >Tanggal *</label
                                >
                                <input
                                    v-model="form.tanggal"
                                    type="text"
                                    placeholder="mm/dd/yyyy"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                    >Pembimbing 1 *</label
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
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                    >Rangkuman (Wajib) *</label
                                >
                                <textarea
                                    v-model="form.rangkuman"
                                    rows="3"
                                    placeholder="Masukkan rangkuman kegiatan bimbingan"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 p-3 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <div class="space-y-1">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                    >Keterangan *</label
                                >
                                <input
                                    v-model="form.keterangan"
                                    type="text"
                                    placeholder="Masukkan keterangan tempat (online/offline)"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
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
                                    Simpan Data
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
