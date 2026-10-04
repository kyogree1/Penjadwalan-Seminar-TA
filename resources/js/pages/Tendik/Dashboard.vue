<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    arsip,
    mahasiswa,
    ruangan as ruanganRoute,
    verifikasi,
} from '@/routes/tendik';
import {
    ArrowUpRight,
    Building2,
    FileCheck,
    FileText,
    KeyRound,
    Printer,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { mockPengajuan } from '@/data/tendik/pengajuan';
import { mockRuangan } from '@/data/tendik';
import type { Pengajuan, Ruangan } from '@/types/models';

const props = withDefaults(
    defineProps<{
        pengajuan?: Pengajuan[];
        ruangan?: Ruangan[];
    }>(),
    {
        pengajuan: () => mockPengajuan,
        ruangan: () => mockRuangan,
    },
);

const pengajuan = ref<Pengajuan[]>([...props.pengajuan]);
const ruangan = ref<Ruangan[]>([...props.ruangan]);

// Dashboard only shows the queue in front of tendik.
const pendingVerifications = computed(() =>
    pengajuan.value.filter(
        (p) => p.status === 'diajukan' || p.status === 'menunggu_ulang',
    ),
);

// Today's room occupancy, derived from the same room list as Ruangan.vue.
const roomsStatus = computed(() =>
    ruangan.value.map((r) => {
        const sedangBerjalan = r.sesiHariIni.find(
            (s) => s.status === 'Berlangsung',
        );
        return {
            nama: r.nama,
            status: r.status,
            sesi: sedangBerjalan
                ? `${sedangBerjalan.waktu} (${sedangBerjalan.kegiatan})`
                : r.sesiHariIni.length
                  ? 'Siap untuk sesi berikutnya'
                  : 'Tersedia sepanjang hari',
            operator: 'Standby',
        };
    }),
);
</script>

<template>
    <AppLayout title="Dashboard Tenaga Kependidikan (Tendik)">
        <Head title="Dashboard Tendik - Administrasi Informatika ITK" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Portal Tenaga Kependidikan & Admin TA"
                title="Pusat Administrasi & Layanan Akademik Tugas Akhir"
                description="Kelola verifikasi berkas pendaftaran Sempro & Sidang, alokasi ruangan fisik/hybrid, pencetakan berkas Berita Acara resmi, dan manajemen akun."
            />

            <!-- Metric Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Berkas Masuk Baru"
                    value="3 Antrean"
                    subtitle="Perlu validasi fisik & digital"
                    :icon="FileText"
                    icon-color="amber"
                />
                <StatCard
                    title="Terverifikasi Lolos"
                    value="42 Berkas"
                    subtitle="Bulan ini (Siap dijadwalkan)"
                    :icon="FileCheck"
                    icon-color="emerald"
                />
                <StatCard
                    title="Kesiapan Ruangan"
                    value="3 Ruangan"
                    subtitle="2 Tersedia • 1 Terpakai"
                    :icon="Building2"
                    icon-color="blue"
                />
                <StatCard
                    title="Berita Acara Dicetak"
                    value="28 Dokumen"
                    subtitle="SK & Lembar Pengesahan"
                    :icon="Printer"
                    icon-color="indigo"
                />
            </div>

            <!-- Quick Action Links -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Link
                    :href="verifikasi()"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-blue-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                        >
                            <FileCheck class="h-5 w-5" />
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 text-slate-400 group-hover:text-blue-600"
                        />
                    </div>
                    <h3
                        class="mt-3 text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Verifikasi Berkas
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Cek kelengkapan UKT, transkrip, TOEFL, dan syarat TA.
                    </p>
                </Link>

                <Link
                    :href="ruanganRoute()"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-emerald-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300"
                        >
                            <Building2 class="h-5 w-5" />
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 text-slate-400 group-hover:text-emerald-600"
                        />
                    </div>
                    <h3
                        class="mt-3 text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Jadwal Ruangan
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Pantau ketersediaan ruang sidang FSTI & link Zoom.
                    </p>
                </Link>

                <Link
                    :href="arsip()"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-purple-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                        >
                            <Printer class="h-5 w-5" />
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 text-slate-400 group-hover:text-purple-600"
                        />
                    </div>
                    <h3
                        class="mt-3 text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Cetak Dokumen & SK
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Generate Berita Acara Sempro (TA-06) dan Sidang (TA-09).
                    </p>
                </Link>

                <Link
                    :href="mahasiswa()"
                    class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all hover:border-amber-500 hover:shadow-md dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center justify-between">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                        >
                            <KeyRound class="h-5 w-5" />
                        </div>
                        <ArrowUpRight
                            class="h-4 w-4 text-slate-400 group-hover:text-amber-600"
                        />
                    </div>
                    <h3
                        class="mt-3 text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Manajemen Akun
                    </h3>
                    <p class="mt-1 text-xs text-slate-500">
                        Reset password default NIM & sinkronisasi data
                        mahasiswa.
                    </p>
                </Link>
            </div>

            <!-- Queue of Verification & Room Schedule -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left: Verification Queue -->
                <div class="space-y-4 lg:col-span-7">
                    <Card class="p-6">
                        <div
                            class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    Antrean Pendaftaran Menunggu Verifikasi
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Dokumen mahasiswa yang baru dikirimkan
                                    melalui portal pendaftaran
                                </p>
                            </div>
                            <Link
                                :href="verifikasi()"
                                class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                            >
                                Buka Semua →
                            </Link>
                        </div>

                        <div
                            class="mt-4 divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <div
                                v-for="item in pendingVerifications"
                                :key="item.id"
                                class="flex items-center justify-between py-3.5 first:pt-0 last:pb-0"
                            >
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ item.nama }}
                                        </p>
                                        <span
                                            class="rounded-md bg-blue-100 px-1.5 py-0.5 text-[9px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                        >
                                            {{ item.tipe }}
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ item.nim }} •
                                        {{ item.tanggalDaftar }}
                                    </p>
                                    <p
                                        class="mt-1 text-[11px] text-slate-600 dark:text-slate-300"
                                    >
                                        Berkas: {{ item.syarat.length }} dokumen
                                    </p>
                                </div>

                                <Link
                                    :href="verifikasi()"
                                    class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    Verifikasi
                                </Link>
                            </div>
                        </div>
                    </Card>
                </div>

                <!-- Right: Room Status Today -->
                <div class="space-y-4 lg:col-span-5">
                    <Card class="p-6">
                        <div
                            class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    Kondisi Ruangan Sidang Hari Ini
                                </h3>
                                <p class="text-xs text-slate-500">
                                    Gedung Kuliah Terpadu (GKT) Lantai 3
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="(room, idx) in roomsStatus"
                                :key="idx"
                                class="rounded-xl border border-slate-200 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-xs font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ room.nama }}
                                    </p>
                                    <span
                                        class="rounded-md px-1.5 py-0.5 text-[9px] font-bold"
                                        :class="[
                                            room.status === 'Digunakan'
                                                ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                                : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                                        ]"
                                    >
                                        {{ room.status }}
                                    </span>
                                </div>
                                <p
                                    class="mt-1 text-xs text-slate-600 dark:text-slate-300"
                                >
                                    {{ room.sesi }}
                                </p>
                                <p class="mt-1 text-[10px] text-slate-400">
                                    Petugas: {{ room.operator }}
                                </p>
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
