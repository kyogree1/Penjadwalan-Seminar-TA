<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    AlertCircle,
    ArrowUpRight,
    Award,
    BookOpen,
    CheckCircle2,
    Clock,
    Cpu,
    FileCheck,
    FileText,
    GraduationCap,
    PenTool,
    ShieldCheck,
    Sparkles,
    TrendingUp,
    Users,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

// Prodi Lecturer Quota & Workload
const lecturers = ref([
    {
        name: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        nip: '198504122010121003',
        bidang: 'Rekayasa Perangkat Lunak',
        kuotaMax: 10,
        bimbingan1: 5,
        bimbingan2: 3,
        total: 8,
        status: 'Optimal',
    },
    {
        name: 'Prof. Dr. Agus Tri Haryanto, M.T.',
        nip: '197908152003121001',
        bidang: 'Jaringan & Keamanan Siber',
        kuotaMax: 10,
        bimbingan1: 6,
        bimbingan2: 4,
        total: 10,
        status: 'Penuh (Maksimal)',
    },
    {
        name: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        nip: '198807212015041002',
        bidang: 'Artificial Intelligence & Data',
        kuotaMax: 10,
        bimbingan1: 4,
        bimbingan2: 3,
        total: 7,
        status: 'Tersedia',
    },
    {
        name: 'Sri Wahyuni, S.Kom., M.T.',
        nip: '199003112018032001',
        bidang: 'Human-Computer Interaction',
        kuotaMax: 10,
        bimbingan1: 3,
        bimbingan2: 3,
        total: 6,
        status: 'Tersedia',
    },
]);

// Urgent Title Submissions awaiting Kaprodi approval
const pipelineStages = [
    {
        name: 'Pengajuan Judul',
        count: 18,
        percentage: 13,
        color: 'bg-amber-500',
    },
    {
        name: 'Seminar Proposal',
        count: 42,
        percentage: 30,
        color: 'bg-blue-600',
    },
    {
        name: 'Pengerjaan Riset',
        count: 46,
        percentage: 32,
        color: 'bg-indigo-600',
    },
    {
        name: 'Sidang Akhir',
        count: 18,
        percentage: 13,
        color: 'bg-emerald-500',
    },
    {
        name: 'Siap Yudisium',
        count: 18,
        percentage: 12,
        color: 'bg-purple-500',
    },
];

const deadlines = [
    {
        label: 'Batas validasi Sempro Gel. 2',
        date: '25 Okt 2026',
        tone: 'text-rose-600 dark:text-rose-400',
    },
    {
        label: 'Penutupan pendaftaran Sidang',
        date: '30 Okt 2026',
        tone: 'text-amber-600 dark:text-amber-400',
    },
];

const pendingApprovals = ref([
    {
        id: 1,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        judul: 'Implementasi Algoritma YOLOv8 untuk Deteksi Kerusakan Aspal Jalan Raya pada Citra Drone',
        bidang: 'Computer Vision',
        usulanPembimbing1: 'Gusti Ahmad Fanshuri, M.Cs.',
        usulanPembimbing2: 'Sri Wahyuni, M.T.',
        tanggal: '17 September 2026',
    },
    {
        id: 2,
        nama: 'Nabila Zahra Syahrani',
        nim: '11221056',
        judul: 'Rancang Bangun Microservices Architecture Berbasis Golang dan RabbitMQ untuk Sistem Presensi Kampus',
        bidang: 'Rekayasa Perangkat Lunak',
        usulanPembimbing1: 'Dr. Ir. Tejo Wahyu Susanto',
        usulanPembimbing2: 'Andi Kurniawan, M.Kom.',
        tanggal: '18 September 2026',
    },
]);
</script>

<template>
    <AppLayout title="Dashboard Kaprodi & Koordinator TA">
        <Head title="Dashboard Kaprodi - Informatika ITK" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Portal Ketua Program Studi"
                title="Dashboard Eksekutif Tugas Akhir Informatika"
                description="Pantau performa kelulusan mahasiswa, distribusi kuota bimbingan dosen, serta integrasi algoritma genetika untuk efisiensi sidang."
            />

            <!-- Main KPI Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Mahasiswa TA Aktif"
                    value="142"
                    subtitle="Periode Semester Ganjil 2026"
                    :icon="Users"
                    icon-color="blue"
                    trend-text="+12% angkatan 2022"
                    trend-type="up"
                />
                <StatCard
                    title="Sudah Lulus Sempro"
                    value="68 Mahasiswa"
                    subtitle="48% dari total peserta TA"
                    :icon="FileCheck"
                    icon-color="emerald"
                />
                <StatCard
                    title="Sudah Lulus Sidang"
                    value="34 Mahasiswa"
                    subtitle="Siap Yudisium Periode I"
                    :icon="Award"
                    icon-color="indigo"
                />
                <StatCard
                    title="Jadwal GA Teroptimasi"
                    value="100% Bebas Konflik"
                    subtitle="Fitness score 0.994"
                    :icon="Cpu"
                    icon-color="amber"
                />
            </div>

            <!-- Pipeline Kemajuan Prodi & Deadline Periode -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card class="p-5 lg:col-span-2">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <h3
                                class="text-sm font-extrabold text-slate-900 dark:text-white"
                            >
                                Pipeline Kemajuan Tugas Akhir
                            </h3>
                            <p class="mt-1 text-xs text-slate-500">
                                Distribusi mahasiswa pada semester aktif.
                            </p>
                        </div>
                        <span
                            class="w-fit rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            Total 142 Mahasiswa
                        </span>
                    </div>

                    <div
                        class="mt-5 flex h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                    >
                        <div
                            v-for="stage in pipelineStages"
                            :key="stage.name"
                            :class="stage.color"
                            :style="{ width: stage.percentage + '%' }"
                            :title="`${stage.name}: ${stage.count} mahasiswa (${stage.percentage}%)`"
                        />
                    </div>

                    <div
                        class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5"
                    >
                        <div
                            v-for="stage in pipelineStages"
                            :key="stage.name"
                            class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <div class="flex items-center gap-2">
                                <span
                                    class="h-2 w-2 rounded-full"
                                    :class="stage.color"
                                />
                                <span
                                    class="truncate text-[10px] font-semibold text-slate-600 dark:text-slate-300"
                                    >{{ stage.name }}</span
                                >
                            </div>
                            <div
                                class="mt-2 flex items-baseline justify-between"
                            >
                                <span
                                    class="text-lg font-extrabold text-slate-900 dark:text-white"
                                    >{{ stage.count }}</span
                                >
                                <span
                                    class="text-[10px] font-semibold text-slate-400"
                                    >{{ stage.percentage }}%</span
                                >
                            </div>
                        </div>
                    </div>
                </Card>

                <Card class="p-5">
                    <div class="flex items-center gap-2">
                        <Clock class="h-4 w-4 text-amber-500" />
                        <h3
                            class="text-xs font-extrabold tracking-wide text-slate-900 uppercase dark:text-white"
                        >
                            Batas Waktu Periode
                        </h3>
                    </div>
                    <ul class="mt-4 space-y-2.5">
                        <li
                            v-for="deadline in deadlines"
                            :key="deadline.label"
                            class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5 text-xs dark:bg-slate-900/50"
                        >
                            <span class="text-slate-600 dark:text-slate-300">{{
                                deadline.label
                            }}</span>
                            <span
                                class="shrink-0 font-bold"
                                :class="deadline.tone"
                                >{{ deadline.date }}</span
                            >
                        </li>
                    </ul>
                </Card>
            </div>

            <!-- Fast Action Banner to GA & Monitoring -->
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <Link
                    href="/kaprodi/penjadwalan"
                    class="group relative overflow-hidden rounded-2xl border border-blue-200 bg-linear-to-br from-blue-600 to-indigo-700 p-6 text-white shadow-lg transition-all hover:scale-[1.01] dark:border-blue-800"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md"
                        >
                            <Cpu class="h-6 w-6" />
                        </div>
                        <ArrowUpRight
                            class="h-5 w-5 opacity-70 transition-transform group-hover:translate-x-1 group-hover:-translate-y-1"
                        />
                    </div>
                    <h3 class="mt-4 text-base font-extrabold">
                        Optimasi Penjadwalan GA
                    </h3>
                    <p class="mt-1 text-xs text-blue-100">
                        Jalankan Algoritma Genetika untuk generate jadwal Sempro
                        & Sidang otomatis tanpa bentrok dosen dan ruangan.
                    </p>
                </Link>

                <Link
                    href="/kaprodi/monitoring"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-xs transition-all hover:border-blue-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                        >
                            <Activity class="h-6 w-6" />
                        </div>
                        <ArrowUpRight
                            class="h-5 w-5 text-slate-400 group-hover:text-blue-600"
                        />
                    </div>
                    <h3
                        class="mt-4 text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        Monitoring Progres Prodi
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Lihat rekapitulasi data mahasiswa per angkatan, status
                        Turnitin, kendala pembimbingan, dan rekap nilai.
                    </p>
                </Link>

                <Link
                    href="/kaprodi/persetujuan"
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-xs transition-all hover:border-purple-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                        >
                            <ShieldCheck class="h-6 w-6" />
                        </div>
                        <ArrowUpRight
                            class="h-5 w-5 text-slate-400 group-hover:text-purple-600"
                        />
                    </div>
                    <h3
                        class="mt-4 text-base font-extrabold text-slate-900 dark:text-white"
                    >
                        Validasi Judul & SK Pembimbing
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Tinjau usulan judul baru dari mahasiswa dan tetapkan
                        pembimbing serta penguji resmi program studi.
                    </p>
                </Link>
            </div>

            <!-- Distribution of Lecturers & Pending Approvals -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left: Distribution of Lecturer Workload -->
                <div class="space-y-4 lg:col-span-7">
                    <Card class="p-6">
                        <div
                            class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    Distribusi Beban Bimbingan Dosen
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Maksimal batas kuota bimbingan adalah 10
                                    mahasiswa per dosen
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                            >
                                4 Dosen Terdaftar
                            </span>
                        </div>

                        <div
                            class="mt-4 divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="(dos, idx) in lecturers"
                                :key="idx"
                                class="py-3.5 first:pt-0 last:pb-0"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ dos.name }}
                                        </p>
                                        <p class="text-[10px] text-slate-500">
                                            NIP: {{ dos.nip }} • Bidang:
                                            {{ dos.bidang }}
                                        </p>
                                    </div>
                                    <span
                                        class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                        :class="[
                                            dos.total >= 10
                                                ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                                : dos.total >= 8
                                                  ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300'
                                                  : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                                        ]"
                                    >
                                        {{ dos.total }} /
                                        {{ dos.kuotaMax }} Mahasiswa
                                    </span>
                                </div>

                                <!-- Progress Bar -->
                                <div class="mt-2 flex items-center gap-3">
                                    <div
                                        class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <div
                                            class="h-full rounded-full transition-all"
                                            :class="[
                                                dos.total >= 10
                                                    ? 'bg-rose-500'
                                                    : dos.total >= 8
                                                      ? 'bg-amber-500'
                                                      : 'bg-blue-600',
                                            ]"
                                            :style="{
                                                width: `${(dos.total / dos.kuotaMax) * 100}%`,
                                            }"
                                        />
                                    </div>
                                    <span
                                        class="text-[10px] font-bold text-slate-600 dark:text-slate-400"
                                    >
                                        {{
                                            Math.round(
                                                (dos.total / dos.kuotaMax) *
                                                    100,
                                            )
                                        }}%
                                    </span>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Right: Urgent Approvals -->
                <div class="space-y-4 lg:col-span-5">
                    <Card class="p-6">
                        <div
                            class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    Antrean Usulan Judul Baru
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Memerlukan review & penerbitan SK Kaprodi
                                </p>
                            </div>
                            <span
                                class="rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-bold text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                            >
                                {{ pendingApprovals.length }} Pengajuan
                            </span>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="item in pendingApprovals"
                                :key="item.id"
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-xs font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.nama }}
                                    </p>
                                    <span class="text-[10px] text-slate-400">
                                        {{ item.tanggal }}
                                    </span>
                                </div>
                                <p
                                    class="mt-1 line-clamp-2 text-xs text-slate-700 dark:text-slate-300"
                                >
                                    {{ item.judul }}
                                </p>
                                <div class="mt-2 text-[10px] text-slate-500">
                                    Usulan: {{ item.usulanPembimbing1 }}
                                </div>
                                <div class="mt-3 flex justify-end">
                                    <Link
                                        href="/kaprodi/persetujuan"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                                    >
                                        Review Pengajuan →
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
