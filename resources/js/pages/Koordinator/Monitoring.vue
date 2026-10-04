<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    Download,
    Eye,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Filter,
    Layers,
    Search,
    SlidersHorizontal,
    UserCheck,
    Users,
} from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

// Mock Students Cohort Data
const students = ref([
    {
        id: 1,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        bidang: 'Rekayasa Perangkat Lunak',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        bimbinganCount: 9,
        turnitin: 14,
        tahap: 'Pengerjaan TA',
        statusBadge: 'Revisi Proposal Disetujui',
        statusVariant: 'emerald' as const,
        formTa04Valid: true,
    },
    {
        id: 2,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        bidang: 'Artificial Intelligence',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2: 'Dewi Ratnasari, S.T., M.T.',
        bimbinganCount: 12,
        turnitin: 11,
        tahap: 'Sidang Akhir',
        statusBadge: 'Siap Daftar Sidang',
        statusVariant: 'purple' as const,
        formTa04Valid: true,
    },
    {
        id: 3,
        nama: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        bidang: 'IoT & Embedded System',
        judul: 'Penerapan Internet of Things untuk Monitoring Kualitas Air Tambak Udang Berbasis LoRaWAN di Balikpapan',
        pembimbing1: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        pembimbing2: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        bimbinganCount: 6,
        turnitin: 18,
        tahap: 'Seminar Proposal',
        statusBadge: 'Menunggu Jadwal Sempro',
        statusVariant: 'blue' as const,
        formTa04Valid: false,
    },
    {
        id: 4,
        nama: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        bidang: 'Natural Language Processing',
        judul: 'Analisis Sentimen Kebijakan IKN pada Media Sosial X Menggunakan Indobert dan Topic Modeling LDA',
        pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2: 'Sri Wahyuni, S.Kom., M.T.',
        bimbinganCount: 10,
        turnitin: 16,
        tahap: 'Pengerjaan TA',
        statusBadge: 'Pengerjaan Bab 4-5',
        statusVariant: 'amber' as const,
        formTa04Valid: true,
    },
    {
        id: 5,
        nama: 'Fajar Hidayatullah',
        nim: '11221034',
        angkatan: '2022',
        bidang: 'Cloud Computing & DevOps',
        judul: 'Pengembangan Microservices Architecture untuk Skalabilitas Sistem E-Commerce Berbasis Kubernetes',
        pembimbing1: 'Hendra Pratama, S.Kom., M.Sc.',
        pembimbing2: 'Dr. Muhammad Rizal, M.Kom.',
        bimbinganCount: 8,
        turnitin: 13,
        tahap: 'Seminar Proposal',
        statusBadge: 'Lulus Sempro',
        statusVariant: 'emerald' as const,
        formTa04Valid: true,
    },
    {
        id: 6,
        nama: 'Rizky Kurniawan',
        nim: '11231048',
        angkatan: '2023',
        bidang: 'Cyber Security',
        judul: 'Analisis Kerentanan Web Application Menggunakan OWASP Top 10 pada Sistem Informasi Publik Pemprov Kaltim',
        pembimbing1: 'Dewi Ratnasari, S.T., M.T.',
        pembimbing2: 'Prof. Dr. Agus Tri Haryanto, M.T.',
        bimbinganCount: 3,
        turnitin: 24,
        tahap: 'Pengajuan Judul',
        statusBadge: 'Revisi Judul',
        statusVariant: 'rose' as const,
        formTa04Valid: false,
    },
]);

// Search & Filters
const searchQuery = ref('');
const filterAngkatan = ref('Semua');
const filterTahap = ref('Semua');

const filteredStudents = computed(() => {
    return students.value.filter((stu) => {
        const matchQuery =
            stu.nama.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            stu.nim.includes(searchQuery.value) ||
            stu.judul.toLowerCase().includes(searchQuery.value.toLowerCase());

        const matchAngkatan =
            filterAngkatan.value === 'Semua' ||
            stu.angkatan === filterAngkatan.value;

        const matchTahap =
            filterTahap.value === 'Semua' || stu.tahap === filterTahap.value;

        return matchQuery && matchAngkatan && matchTahap;
    });
});

// Detail Modal
const selectedStudent = ref<any>(null);
const showDetailModal = ref(false);

const openDetail = (stu: any) => {
    selectedStudent.value = stu;
    showDetailModal.value = true;
};
</script>

<template>
    <Head title="Monitoring Kemajuan TA • Koordinator Informatika ITK" />

    <div class="space-y-6">
        <!-- 1. Header Box -->
        <PageHeaderBox
            title="Monitoring Kemajuan Tugas Akhir Mahasiswa"
            subtitle="Pengawasan menyeluruh alur penyelesaian skripsi, kepatuhan bimbingan minimal 8x (Form TA-04), dan kelayakan berkas se-Program Studi Informatika ITK."
        >
            <template #action>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-200"
                >
                    <Download class="h-4 w-4" />
                    <span>Ekspor Laporan (Excel)</span>
                </button>
            </template>
        </PageHeaderBox>

        <!-- 2. Funnel Progress Metrics -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <StatCard
                title="Total Terdaftar"
                value="124"
                subtitle="Mahasiswa aktif TA"
                :icon="Users"
                icon-color="blue"
            />
            <StatCard
                title="Pengajuan Judul"
                value="18"
                subtitle="Tahap seleksi topik"
                :icon="FileText"
                icon-color="amber"
            />
            <StatCard
                title="Seminar Proposal"
                value="42"
                subtitle="Penyusunan naskah bab 1-3"
                :icon="FileSpreadsheet"
                icon-color="indigo"
            />
            <StatCard
                title="Pengerjaan Riset"
                value="46"
                subtitle="Tahap implementasi & uji"
                :icon="Layers"
                icon-color="blue"
            />
            <StatCard
                title="Sidang Akhir TA"
                value="18"
                subtitle="Menuju wisuda kelulusan"
                :icon="Award"
                icon-color="emerald"
            />
        </div>

        <!-- 3. Search & Filters Bar -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
            >
                <!-- Search Input -->
                <div class="relative flex-1">
                    <Search
                        class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        type="text"
                        v-model="searchQuery"
                        placeholder="Cari nama mahasiswa, NIM (1123...), atau kata kunci judul skripsi..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2.5 pr-4 pl-10 text-xs text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-800 dark:bg-slate-800/80 dark:text-white"
                    />
                </div>

                <!-- Dropdowns -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <div
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300"
                    >
                        <span>Angkatan:</span>
                        <select
                            v-model="filterAngkatan"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="Semua">Semua Angkatan</option>
                            <option value="2023">Angkatan 2023</option>
                            <option value="2022">Angkatan 2022</option>
                            <option value="2021">Angkatan 2021</option>
                            <option value="2020">Angkatan 2020</option>
                        </select>
                    </div>

                    <div
                        class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-300"
                    >
                        <span>Tahap TA:</span>
                        <select
                            v-model="filterTahap"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200"
                        >
                            <option value="Semua">Semua Tahap</option>
                            <option value="Pengajuan Judul">
                                Pengajuan Judul
                            </option>
                            <option value="Seminar Proposal">
                                Seminar Proposal
                            </option>
                            <option value="Pengerjaan TA">Pengerjaan TA</option>
                            <option value="Sidang Akhir">Sidang Akhir</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Table of Students -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-400"
                    >
                        <tr>
                            <th class="py-3.5 pr-4 pl-5">Mahasiswa</th>
                            <th class="px-4 py-3.5">Topik & Judul Skripsi</th>
                            <th class="px-4 py-3.5">Dosen Pembimbing</th>
                            <th class="px-4 py-3.5 text-center">
                                Bimbingan (TA-04)
                            </th>
                            <th class="px-4 py-3.5 text-center">Turnitin</th>
                            <th class="px-4 py-3.5">Status Progres</th>
                            <th class="py-3.5 pr-5 pl-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="stu in filteredStudents"
                            :key="stu.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                        >
                            <!-- Student Name & NIM -->
                            <td class="py-4 pr-4 pl-5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                                    >
                                        {{ stu.nama.slice(0, 2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <p
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ stu.nama }}
                                        </p>
                                        <p
                                            class="text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            {{ stu.nim }} • Akt
                                            {{ stu.angkatan }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Thesis Title & Topic -->
                            <td class="max-w-xs px-4 py-4">
                                <p
                                    class="line-clamp-2 font-semibold text-slate-800 dark:text-slate-200"
                                >
                                    "{{ stu.judul }}"
                                </p>
                                <span
                                    class="mt-1 inline-block rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ stu.bidang }}
                                </span>
                            </td>

                            <!-- Advisors -->
                            <td class="px-4 py-4 text-[11px]">
                                <p
                                    class="font-medium text-slate-700 dark:text-slate-300"
                                >
                                    1. {{ stu.pembimbing1 }}
                                </p>
                                <p class="text-slate-500 dark:text-slate-400">
                                    2. {{ stu.pembimbing2 }}
                                </p>
                            </td>

                            <!-- Guidance Logbook -->
                            <td class="px-4 py-4 text-center">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                                    :class="[
                                        stu.bimbinganCount >= 8
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
                                    ]"
                                >
                                    <CheckCircle2
                                        v-if="stu.bimbinganCount >= 8"
                                        class="h-3 w-3"
                                    />
                                    <span>{{ stu.bimbinganCount }}x Sesi</span>
                                </span>
                                <p class="mt-0.5 text-[10px] text-slate-400">
                                    {{
                                        stu.bimbinganCount >= 8
                                            ? 'Syarat Terpenuhi'
                                            : 'Kurang ' +
                                              (8 - stu.bimbinganCount) +
                                              'x'
                                    }}
                                </p>
                            </td>

                            <!-- Turnitin Similarity -->
                            <td class="px-4 py-4 text-center">
                                <span
                                    class="rounded-lg px-2 py-0.5 text-xs font-bold"
                                    :class="[
                                        stu.turnitin <= 20
                                            ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300',
                                    ]"
                                >
                                    {{ stu.turnitin }}%
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-4">
                                <StatusBadge
                                    :text="stu.statusBadge"
                                    :variant="stu.statusVariant"
                                />
                            </td>

                            <!-- Actions -->
                            <td class="py-4 pr-5 pl-4 text-right">
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-1 rounded-lg border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                                    @click="openDetail(stu)"
                                >
                                    <Eye class="h-3.5 w-3.5" />
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Student Detail Modal -->
    <Modal
        :show="showDetailModal"
        max-width="lg"
        @close="showDetailModal = false"
    >
        <div v-if="selectedStudent" class="p-6">
            <div
                class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"
            >
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Profil Kemajuan Tugas Akhir Mahasiswa
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ selectedStudent.nama }} ({{ selectedStudent.nim }})
                    </p>
                </div>
            </div>

            <div class="mt-4 space-y-3 text-xs">
                <div class="rounded-xl bg-slate-50 p-3.5 dark:bg-slate-800/50">
                    <span class="text-[10px] font-bold text-slate-400 uppercase"
                        >Judul Skripsi:</span
                    >
                    <p
                        class="mt-1 font-semibold text-slate-900 dark:text-white"
                    >
                        "{{ selectedStudent.judul }}"
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div
                        class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-800"
                    >
                        <span class="text-[10px] font-bold text-slate-400"
                            >Bidang Penelitian:</span
                        >
                        <p
                            class="mt-1 font-bold text-slate-800 dark:text-slate-200"
                        >
                            {{ selectedStudent.bidang }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-800"
                    >
                        <span class="text-[10px] font-bold text-slate-400"
                            >Turnitin Similarity:</span
                        >
                        <p
                            class="mt-1 font-bold"
                            :class="
                                selectedStudent.turnitin <= 20
                                    ? 'text-emerald-600'
                                    : 'text-rose-600'
                            "
                        >
                            {{ selectedStudent.turnitin }}% (Maksimal 20%)
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-slate-200/80 p-3.5 dark:border-slate-800"
                >
                    <span class="text-[10px] font-bold text-slate-400"
                        >Dosen Pembimbing:</span
                    >
                    <p
                        class="mt-1 font-semibold text-slate-800 dark:text-slate-200"
                    >
                        1. {{ selectedStudent.pembimbing1 }}
                    </p>
                    <p class="mt-0.5 text-slate-600 dark:text-slate-400">
                        2. {{ selectedStudent.pembimbing2 }}
                    </p>
                </div>
            </div>

            <div
                class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800"
            >
                <Button
                    variant="outline"
                    size="sm"
                    @click="showDetailModal = false"
                >
                    Tutup
                </Button>
                <Link
                    href="/pendaftaran/bimbingan"
                    class="inline-flex items-center rounded-xl bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-blue-700"
                >
                    Buka Logbook Lengkap
                </Link>
            </div>
        </div>
    </Modal>
</template>
