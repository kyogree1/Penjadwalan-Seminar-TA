<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Filter,
    GraduationCap,
    KeyRound,
    Lock,
    RotateCcw,
    Search,
    ShieldCheck,
    UserCheck,
    Users,
    X,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';

const searchQuery = ref('');
const filterAngkatan = ref('Semua');

const filteredStudents = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    return students.value.filter((student) => {
        const matchesSearch =
            !query ||
            student.nama.toLowerCase().includes(query) ||
            student.nim.includes(query) ||
            student.email.toLowerCase().includes(query);
        const matchesAngkatan =
            filterAngkatan.value === 'Semua' ||
            student.angkatan === filterAngkatan.value;
        return matchesSearch && matchesAngkatan;
    });
});

const students = ref([
    {
        id: 1,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        prodi: 'S1 Informatika',
        email: 'akmal.falah@student.itk.ac.id',
        tahap: 'Sidang Akhir TA',
        statusAkun: 'Aktif',
    },
    {
        id: 2,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        prodi: 'S1 Informatika',
        email: 'siti.nurhaliza@student.itk.ac.id',
        tahap: 'Sidang Akhir TA',
        statusAkun: 'Aktif',
    },
    {
        id: 3,
        nama: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'bagus.pratama@student.itk.ac.id',
        tahap: 'Seminar Proposal',
        statusAkun: 'Aktif',
    },
    {
        id: 4,
        nama: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'dinda.rahmadani@student.itk.ac.id',
        tahap: 'Pengerjaan TA Bab 4-5',
        statusAkun: 'Aktif',
    },
    {
        id: 5,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        angkatan: '2022',
        prodi: 'S1 Informatika',
        email: 'rizky.adhitya@student.itk.ac.id',
        tahap: 'Pengajuan Judul',
        statusAkun: 'Aktif',
    },
]);

// Reset Password Modal
const showResetModal = ref(false);
const selectedStudent = ref<any>(null);
const resetSuccessMsg = ref('');

// Detail Modal
const showDetailModal = ref(false);

const openDetailModal = (s: any) => {
    selectedStudent.value = s;
    showDetailModal.value = true;
};

const openResetModal = (s: any) => {
    selectedStudent.value = s;
    resetSuccessMsg.value = '';
    showResetModal.value = true;
};

const executeResetPassword = () => {
    if (selectedStudent.value) {
        resetSuccessMsg.value = `Password akun untuk ${selectedStudent.value.nama} berhasil direset ke default NIM (${selectedStudent.value.nim}).`;
    }
};
</script>

<template>
    <AppLayout title="Manajemen Akun Mahasiswa">
        <Head title="Kelola Akun Mahasiswa - Portal Tendik" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Layanan Akun & Akses"
                title="Kelola Data Mahasiswa & Reset Password Default"
                description="Bantu mahasiswa yang mengalami kendala akses login, sinkronisasi data NIM, dan kelola status hak akses portal pendaftaran skripsi."
            />

            <!-- Filter & Search Card -->
            <Card class="p-6">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center dark:border-slate-800"
                >
                    <div class="relative w-full max-w-sm">
                        <Search
                            class="absolute top-2.5 left-3 h-4 w-4 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari nama mahasiswa atau NIM..."
                            class="w-full rounded-xl border border-slate-200 py-2 pr-4 pl-9 text-xs text-slate-900 focus:border-blue-600 focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <span
                            class="text-xs font-bold text-slate-600 dark:text-slate-400"
                            >Filter Angkatan:</span
                        >
                        <select
                            v-model="filterAngkatan"
                            class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="Semua">Semua Angkatan</option>
                            <option value="2023">Angkatan 2023</option>
                            <option value="2022">Angkatan 2022</option>
                            <option value="2021">Angkatan 2021</option>
                        </select>
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
                                <th class="px-4 py-3">Nama Mahasiswa</th>
                                <th class="px-4 py-3">NIM / Username</th>
                                <th class="px-4 py-3">Email Akademik</th>
                                <th class="px-4 py-3">Tahapan TA</th>
                                <th class="px-4 py-3">Status Akun</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            v-if="filteredStudents.length"
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="s in filteredStudents"
                                :key="s.id"
                                class="cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-900/40"
                                @click="openDetailModal(s)"
                            >
                                <td class="px-4 py-3.5">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ s.nama }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        {{ s.prodi }} • Angkatan
                                        {{ s.angkatan }}
                                    </p>
                                </td>
                                <td
                                    class="px-4 py-3.5 font-mono font-bold text-blue-600 dark:text-blue-400"
                                >
                                    {{ s.nim }}
                                </td>
                                <td
                                    class="px-4 py-3.5 text-slate-600 dark:text-slate-400"
                                >
                                    {{ s.email }}
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                                    >
                                        {{ s.tahap }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                                    >
                                        {{ s.statusAkun }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right" @click.stop>
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openResetModal(s)"
                                    >
                                        <KeyRound class="mr-1 h-3.5 w-3.5" />
                                        Reset Password
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-if="!filteredStudents.length">
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <div
                                        class="flex flex-col items-center gap-3"
                                    >
                                        <div
                                            class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                                        >
                                            <Users
                                                class="h-8 w-8 text-slate-400"
                                            />
                                        </div>
                                        <div>
                                            <h4
                                                class="text-sm font-bold text-slate-900 dark:text-white"
                                            >
                                                Tidak ada mahasiswa yang sesuai
                                            </h4>
                                            <p
                                                class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                            >
                                                Coba ubah kata kunci pencarian
                                                atau filter angkatan.
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
        </div>

        <!-- Reset Password Modal -->
        <Modal
            :show="showResetModal"
            max-width="md"
            @close="showResetModal = false"
        >
            <div class="p-6">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                    Konfirmasi Reset Password Mahasiswa
                </h3>

                <div v-if="selectedStudent" class="mt-4 text-xs">
                    <p class="text-slate-600 dark:text-slate-400">
                        Apakah Anda yakin ingin mereset password akun untuk:
                    </p>
                    <div
                        class="mt-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-900"
                    >
                        <p class="font-bold text-slate-900 dark:text-white">
                            {{ selectedStudent.nama }}
                        </p>
                        <p class="text-slate-500">
                            NIM: {{ selectedStudent.nim }}
                        </p>
                    </div>
                    <p class="mt-2 text-slate-500">
                        Password akan dikembalikan ke default:
                        <strong class="text-blue-600">{{
                            selectedStudent.nim
                        }}</strong
                        >.
                    </p>

                    <div
                        v-if="resetSuccessMsg"
                        class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300"
                    >
                        {{ resetSuccessMsg }}
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="showResetModal = false">
                        Tutup
                    </Button>
                    <Button
                        v-if="!resetSuccessMsg"
                        variant="primary"
                        @click="executeResetPassword"
                    >
                        Ya, Reset Password
                    </Button>
                </div>
            </div>
        </Modal>

        <!-- Student Detail Modal -->
        <Modal
            :show="showDetailModal"
            max-width="lg"
            @close="showDetailModal = false"
        >
            <div class="p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Detail Mahasiswa
                    </h3>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                        @click="showDetailModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div v-if="selectedStudent" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                Nama Lengkap
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.nama }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                NIM
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.nim }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                Program Studi
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.prodi }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                Angkatan
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.angkatan }}
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-slate-500 dark:text-slate-400">
                                Email
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.email }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                Tahap Tugas Akhir
                            </p>
                            <p
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ selectedStudent.tahap }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">
                                Status Akun
                            </p>
                            <span
                                class="mt-1 inline-block rounded-md px-2 py-0.5 text-[10px] font-bold"
                                :class="[
                                    selectedStudent.statusAkun === 'Aktif'
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                        : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ]"
                            >
                                {{ selectedStudent.statusAkun }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button
                        variant="secondary"
                        @click="showDetailModal = false"
                    >
                        Tutup
                    </Button>
                    <Button
                        v-if="selectedStudent"
                        variant="outline"
                        @click="
                            showDetailModal = false;
                            openResetModal(selectedStudent);
                        "
                    >
                        <KeyRound class="mr-1 h-3.5 w-3.5" />
                        Reset Password
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
