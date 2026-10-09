<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Activity,
    ArrowUpRight,
    Clock,
    Cpu,
    ShieldCheck,
    Users,
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import type { KaprodiDashboardProps } from '@/types/kaprodi-dashboard';

const props = defineProps<KaprodiDashboardProps>();
// Keep derived data tied to current Inertia props, including explicit empty results.
const queues = computed(() => [
    { name: 'Pengajuan Judul', count: props.pendingCounts.judul },
    { name: 'Seminar Proposal', count: props.pendingCounts.sempro },
    { name: 'Sidang Akhir', count: props.pendingCounts.sidang },
]);
</script>

<template>
    <Head title="Dashboard Kaprodi - Informatika ITK" />
    <div class="space-y-6">
        <PageHeaderBox
            badge="Portal Ketua Program Studi"
            title="Dashboard Eksekutif Tugas Akhir Informatika"
            description="Pantau data akun, antrean pengajuan, dan penugasan pembimbing yang tercatat."
        />
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <StatCard
                title="Total Mahasiswa"
                :value="props.stats.totalMahasiswa"
                subtitle="Akun mahasiswa terdaftar, bukan peserta TA aktif"
                :icon="Users"
                icon-color="blue"
            />
            <StatCard
                title="Total Dosen"
                :value="props.stats.totalDosen"
                subtitle="Akun dengan peran dosen"
                :icon="Users"
                icon-color="indigo"
            />
            <StatCard
                title="Antrean Persetujuan"
                :value="props.stats.antreanPersetujuan"
                subtitle="Pengajuan berstatus menunggu"
                :icon="ShieldCheck"
                icon-color="amber"
            />
        </div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Card class="p-5 lg:col-span-2">
                <h3
                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                >
                    Antrean Pengajuan Tugas Akhir
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Jumlah berkas berstatus menunggu per jenis, bukan distribusi
                    tahap mahasiswa. Satu mahasiswa dapat memiliki beberapa
                    pengajuan.
                </p>
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div
                        v-for="queue in queues"
                        :key="queue.name"
                        class="rounded-xl border border-slate-200 bg-slate-50/70 p-3 dark:border-slate-800 dark:bg-slate-900/50"
                    >
                        <p class="text-xs text-slate-500">{{ queue.name }}</p>
                        <p
                            class="mt-2 text-lg font-extrabold text-slate-900 dark:text-white"
                        >
                            {{ queue.count }}
                        </p>
                    </div>
                </div>
                <p
                    v-if="props.stats.antreanPersetujuan === 0"
                    class="mt-4 text-xs text-slate-500"
                >
                    Tidak ada pengajuan menunggu.
                </p>
            </Card>
            <Card class="p-5">
                <div class="flex items-center gap-2">
                    <Clock class="h-4 w-4 text-amber-500" />
                    <h3
                        class="text-xs font-extrabold text-slate-900 dark:text-white"
                    >
                        Batas Waktu Periode
                    </h3>
                </div>
                <p
                    v-if="props.deadlines === null"
                    class="mt-4 text-xs text-slate-500"
                >
                    Data batas waktu periode belum tersedia. Sistem belum
                    menyimpan periode akademik dan tenggat pendaftaran.
                </p>
            </Card>
        </div>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Link
                v-for="action in [
                    {
                        href: '/kaprodi/penjadwalan',
                        title: 'Optimasi Penjadwalan GA',
                        icon: Cpu,
                    },
                    {
                        href: '/kaprodi/monitoring',
                        title: 'Monitoring Progres Prodi',
                        icon: Activity,
                    },
                    {
                        href: '/kaprodi/persetujuan',
                        title: 'Validasi Judul & SK Pembimbing',
                        icon: ShieldCheck,
                    },
                ]"
                :key="action.href"
                :href="action.href"
                class="group flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-6 shadow-xs hover:border-blue-500 dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center gap-3">
                    <component
                        :is="action.icon"
                        class="h-5 w-5 text-blue-600"
                    />
                    <h3
                        class="text-sm font-extrabold text-slate-900 dark:text-white"
                    >
                        {{ action.title }}
                    </h3>
                </div>
                <ArrowUpRight class="h-5 w-5 text-slate-400" />
            </Link>
        </div>
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <Card class="p-6 lg:col-span-7">
                <h3
                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                >
                    Distribusi Beban Bimbingan Dosen
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Mahasiswa unik pada judul disetujui, seluruh data tercatat.
                    Tidak termasuk usulan pembimbing atau tugas penguji; bukan
                    ukuran bimbingan aktif. Kuota dan bidang keahlian belum
                    tersedia.
                </p>
                <p class="mt-3 text-xs font-bold text-blue-600">
                    {{ props.stats.totalDosen }} Dosen Terdaftar
                </p>
                <p
                    v-if="props.lecturers.length === 0"
                    class="mt-4 text-xs text-slate-500"
                >
                    Belum ada dosen terdaftar.
                </p>
                <div
                    v-else
                    class="mt-4 divide-y divide-slate-100 dark:divide-slate-800"
                >
                    <div
                        v-for="dos in props.lecturers"
                        :key="dos.id"
                        class="py-3.5"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ dos.name }}
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    NIP: {{ dos.nip ?? 'Belum tersedia' }}
                                </p>
                            </div>
                            <span class="text-xs font-bold text-blue-600"
                                >{{ dos.total }} Mahasiswa</span
                            >
                        </div>
                        <p class="mt-2 text-[10px] text-slate-500">
                            Pembimbing 1: {{ dos.bimbingan1 }} • Pembimbing 2:
                            {{ dos.bimbingan2 }}. Total dihitung unik lintas
                            posisi.
                        </p>
                    </div>
                </div>
            </Card>
            <Card class="p-6 lg:col-span-5">
                <h3
                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                >
                    Antrean Usulan Judul Baru
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    {{ props.pendingCounts.judul }} pengajuan menunggu;
                    menampilkan hingga 5 pengajuan terlama.
                </p>
                <p
                    v-if="props.pendingApprovals.length === 0"
                    class="mt-4 text-xs text-slate-500"
                >
                    Tidak ada usulan judul menunggu.
                </p>
                <div v-else class="mt-4 space-y-3">
                    <div
                        v-for="item in props.pendingApprovals"
                        :key="item.id"
                        class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                    >
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ item.nama }}
                        </p>
                        <p class="mt-1 text-[10px] text-slate-500">
                            NIM: {{ item.nim ?? 'Belum tersedia' }} •
                            {{ item.tanggal }}
                        </p>
                        <p
                            class="mt-2 text-xs text-slate-700 dark:text-slate-300"
                        >
                            {{ item.judul }}
                        </p>
                        <p class="mt-2 text-[10px] text-slate-500">
                            Usulan pembimbing 1:
                            {{ item.pembimbing1 ?? 'Belum diusulkan'
                            }}<br />Usulan pembimbing 2:
                            {{ item.pembimbing2 ?? 'Belum diusulkan' }}
                        </p>
                    </div>
                </div>
                <Link
                    href="/kaprodi/persetujuan"
                    class="mt-4 inline-block text-xs font-bold text-blue-600 dark:text-blue-400"
                    >Review Pengajuan →</Link
                >
            </Card>
        </div>
    </div>
</template>
