<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Archive,
    ArrowRight,
    Building2,
    CheckCircle2,
    Clock,
    FileCheck2,
    FileText,
    Layers,
    ShieldAlert,
    UserCheck,
    Users,
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import type { TendikDashboardProps } from '@/types/tendik-facilities';

const props = defineProps<TendikDashboardProps>();

const quickActions = [
    {
        title: 'Verifikasi Berkas',
        desc: 'Periksa kelengkapan administrasi berkas Sempro dan Sidang.',
        href: '/tendik/verifikasi',
        icon: FileCheck2,
        badge: computed(() =>
            props.stats.antreanVerifikasi > 0
                ? `${props.stats.antreanVerifikasi} Perlu Tindakan`
                : 'Selesai',
        ),
        badgeVariant: computed(() =>
            props.stats.antreanVerifikasi > 0 ? 'amber' : 'emerald',
        ),
        color: 'text-amber-600 dark:text-amber-400',
        bg: 'bg-amber-50 dark:bg-amber-950/40 border-amber-200/80 dark:border-amber-900/50',
    },
    {
        title: 'Direktori Mahasiswa',
        desc: 'Manajemen akun mahasiswa terdaftar dan reset kata sandi.',
        href: '/tendik/mahasiswa',
        icon: Users,
        badge: computed(() => `${props.stats.totalMahasiswa} Akun`),
        badgeVariant: computed(() => 'indigo'),
        color: 'text-indigo-600 dark:text-indigo-400',
        bg: 'bg-indigo-50 dark:bg-indigo-950/40 border-indigo-200/80 dark:border-indigo-900/50',
    },
    {
        title: 'Status Arsip',
        desc: 'Pantau ketersediaan dokumen dan rekapitulasi data akademik.',
        href: '/tendik/arsip',
        icon: Archive,
        badge: computed(() => 'Terbatas'),
        badgeVariant: computed(() => 'slate'),
        color: 'text-slate-600 dark:text-slate-400',
        bg: 'bg-slate-50 dark:bg-slate-900/40 border-slate-200/80 dark:border-slate-800',
    },
];
</script>

<template>
    <Head title="Dashboard Tendik - SIPTA IF" />
    <div class="space-y-6">
        <PageHeaderBox
            badge="Portal Tendik"
            title="Administrasi Tugas Akhir"
            description="Pusat kendali verifikasi berkas, fasilitas ujian, dan manajemen akun mahasiswa Informatika."
        />

        <!-- Primary KPI Metrics -->
        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                title="Antrean Verifikasi"
                :value="props.stats.antreanVerifikasi"
                subtitle="Pendaftaran berstatus menunggu"
                :icon="FileText"
                icon-color="amber"
            />
            <StatCard
                title="Akun Mahasiswa"
                :value="props.stats.totalMahasiswa"
                subtitle="Akun dengan peran mahasiswa"
                :icon="Users"
                icon-color="indigo"
            />
        </div>

        <!-- Verification Queue Section -->
        <div class="grid grid-cols-1 gap-6">
            <Card class="flex flex-col justify-between p-6">
                <div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Clock class="h-5 w-5 text-amber-500" />
                            <h3
                                class="text-base font-bold text-slate-900 dark:text-white"
                            >
                                Antrean Pendaftaran
                            </h3>
                        </div>
                        <span
                            class="rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            :class="[
                                props.stats.antreanVerifikasi > 0
                                    ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-300'
                                    : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300',
                            ]"
                        >
                            {{
                                props.stats.antreanVerifikasi > 0
                                    ? `${props.stats.antreanVerifikasi} Menunggu Verifikasi`
                                    : 'Semua Berkas Terverifikasi'
                            }}
                        </span>
                    </div>

                    <p
                        class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Jumlah pendaftaran berstatus menunggu dari seluruh
                        periode; bukan jumlah mahasiswa unik. Segera tinjau
                        berkas agar mahasiswa dapat dijadwalkan oleh Kaprodi.
                    </p>

                    <!-- Breakdown Cards -->
                    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div
                            class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                Seminar Proposal
                            </span>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span
                                    class="text-2xl font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ props.stats.antreanSempro }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    {{ props.stats.antreanSempro }} Sempro
                                </span>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <span
                                class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >
                                Sidang Tugas Akhir
                            </span>
                            <div class="mt-2 flex items-baseline gap-2">
                                <span
                                    class="text-2xl font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ props.stats.antreanSidang }}
                                </span>
                                <span
                                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                                >
                                    {{ props.stats.antreanSidang }} Sidang
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <Link
                        href="/tendik/verifikasi"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-bold text-white shadow-xs transition-colors hover:bg-blue-700 sm:w-auto"
                    >
                        <span>Buka Verifikasi Berkas</span>
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </Card>
        </div>

        <!-- Quick Modules Grid -->
        <div>
            <h3 class="mb-3 text-sm font-bold text-slate-900 dark:text-white">
                Menu & Modul Layanan Tendik
            </h3>
            <div class="grid gap-4 sm:grid-cols-3">
                <Link
                    v-for="action in quickActions"
                    :key="action.title"
                    :href="action.href"
                    class="group flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-blue-400 hover:shadow-sm dark:border-slate-800 dark:bg-[#0E1626] dark:hover:border-blue-500"
                >
                    <div>
                        <div class="flex items-center justify-between">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl border transition-colors group-hover:scale-105"
                                :class="action.bg"
                            >
                                <component
                                    :is="action.icon"
                                    class="h-5 w-5"
                                    :class="action.color"
                                />
                            </div>
                            <span
                                class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                :class="[
                                    action.badgeVariant.value === 'amber'
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                        : action.badgeVariant.value ===
                                            'emerald'
                                          ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'
                                          : action.badgeVariant.value === 'blue'
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300'
                                            : action.badgeVariant.value ===
                                                'indigo'
                                              ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950 dark:text-indigo-300'
                                              : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300',
                                ]"
                            >
                                {{ action.badge.value }}
                            </span>
                        </div>
                        <h4
                            class="mt-4 text-sm font-bold text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                        >
                            {{ action.title }}
                        </h4>
                        <p
                            class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                        >
                            {{ action.desc }}
                        </p>
                    </div>

                    <div
                        class="mt-5 flex items-center gap-1.5 text-xs font-semibold text-blue-600 transition-transform group-hover:translate-x-1 dark:text-blue-400"
                    >
                        <span>Buka Layanan</span>
                        <ArrowRight class="h-3.5 w-3.5" />
                    </div>
                </Link>
            </div>
        </div>
    </div>
</template>
