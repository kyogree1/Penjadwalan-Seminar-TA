<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import Card from '@/Components/Card.vue';
import StatCard from '@/Components/StatCard.vue';
import {
    ArrowUpRight,
    Award,
    Calendar,
    CheckCircle2,
    Clock,
    FileCheck,
    FileEdit,
    FileText,
    GraduationCap,
    Layers,
    UserCheck,
    Users,
} from 'lucide-vue-next';

const props = defineProps<{
    stats: { pendingBimbingan: number; jadwalUjiCount: number };
}>();

const stats = computed(() => props.stats);
const page = usePage();
const lecturer = computed(
    () =>
        (
            page.props.auth as {
                user?: {
                    name: string;
                    nim_nip?: string | null;
                    jabatan?: string | null;
                };
            }
        )?.user,
);

// Mahasiswa bimbingan tugas akhir milik dosen ini
const mySupervisedStudents = [
    {
        id: 1,
        nama: 'Anisa Rahmadani',
        nim: '11231010',
        judul: 'Analisis Perbandingan Kinerja Arsitektur Microservices pada Sistem Akademik',
        tahap: 'Bimbingan Bab 4',
        progress: 80,
        hasPendingLogbook: true,
        logbookPendingText: '1 Logbook Menunggu Validasi',
        terakhirBimbingan: 'Kemarin, 14:30',
    },
    {
        id: 2,
        nama: 'Bayu Aditya Saputra',
        nim: '11231089',
        judul: 'Rancang Bangun Sistem Lokalisasi Indoor Menggunakan WiFi Fingerprinting',
        tahap: 'Persiapan Sempro',
        progress: 65,
        hasPendingLogbook: false,
        logbookPendingText: 'Terverifikasi (8 Sesi)',
        terakhirBimbingan: '3 hari lalu',
    },
    {
        id: 3,
        nama: 'Muhammad Raihan',
        nim: '11211030',
        judul: 'Analisis Kerentanan Aplikasi Web Portal Kampus dengan Metode OWASP Top 10',
        tahap: 'Revisi Pasca Sidang',
        progress: 95,
        hasPendingLogbook: false,
        logbookPendingText: '14 Sesi Terverifikasi',
        terakhirBimbingan: '5 hari lalu',
    },
];

// Jadwal menguji seminar proposal & sidang mendatang
const upcomingExams = [
    {
        id: 1,
        tipe: 'Seminar Proposal',
        tipeBadge: 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300',
        mahasiswa: 'Dimas Wahyu Ramadhan',
        nim: '11231024',
        judul: 'Rancang Bangun Sistem Timegraph dan Monitoring Berkas Terdistribusi',
        peran: 'Penguji 1',
        tanggal: 'Rabu, 22 Okt 2026',
        jam: '09:00 - 10:30 WITA',
        ruang: 'Lab Rekayasa Perangkat Lunak',
        href: '/dosen/sempro',
    },
    {
        id: 2,
        tipe: 'Sidang Tugas Akhir',
        tipeBadge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
        mahasiswa: 'Siti Rahmawati',
        nim: '11221038',
        judul: 'Peramalan Kuantitas Penjualan Harian Produk Retail Menggunakan Hybrid ARIMA-LSTM',
        peran: 'Penguji 2',
        tanggal: 'Jumat, 24 Okt 2026',
        jam: '13:30 - 15:00 WITA',
        ruang: 'Ruang Sidang IF 204',
        href: '/dosen/sidang',
    },
];

// Pipeline tahapan mahasiswa bimbingan dosen
const adviseeStages = [
    { name: 'Usulan Judul', count: 1, pct: 20, barColor: 'bg-amber-500', dotColor: 'bg-amber-500', href: '/dosen/persetujuan' },
    { name: 'Bimbingan Berjalan', count: 2, pct: 40, barColor: 'bg-indigo-600', dotColor: 'bg-indigo-600', href: '/dosen/bimbingan' },
    { name: 'Seminar Proposal', count: 1, pct: 20, barColor: 'bg-blue-600', dotColor: 'bg-blue-600', href: '/dosen/sempro' },
    { name: 'Sidang Tugas Akhir', count: 1, pct: 20, barColor: 'bg-emerald-600', dotColor: 'bg-emerald-600', href: '/dosen/sidang' },
];
</script>

<template>
    <Head title="Dashboard Dosen • SIPTA IF" />

    <div class="space-y-6">
        <!-- 1. HERO BANNER PORTAL DOSEN (MATCHING GRADIENT KATALOG & KOORDINATOR) -->
        <div
            class="relative overflow-hidden rounded-2xl border border-blue-700/40 bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 p-6 text-white shadow-xl shadow-blue-950/20 md:p-8"
        >
            <div
                class="pointer-events-none absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"
            />
            <div
                class="pointer-events-none absolute top-4 right-20 h-32 w-32 rounded-full bg-blue-400/10 blur-2xl"
            />

            <div class="relative z-10 space-y-2.5">
                <h1
                    class="text-xl font-extrabold tracking-tight !text-white sm:text-2xl lg:text-3xl"
                >
                    Selamat Datang, {{ lecturer?.name ?? 'Bapak/Ibu Dosen' }}
                </h1>
                <p class="text-xs font-semibold tracking-wide !text-white/90">
                    NIP: {{ lecturer?.nim_nip ?? 'Belum tersedia' }} • {{ lecturer?.jabatan ?? 'Dosen Pembimbing & Penguji TA' }}
                </p>
                <p
                    class="w-full text-xs leading-relaxed !text-white/80 sm:text-sm"
                >
                    Kelola bimbingan tugas akhir mahasiswa (TA-04), validasi logbook bimbingan,
                    serta pantau jadwal penugasan penguji seminar proposal dan sidang secara terpadu.
                </p>
            </div>
        </div>

        <!-- 2. KPI STAT CARDS (PRESERVING EXACT KEYS & SUBTITLES FOR TEST SUITE COMPATIBILITY) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Antrean Logbook"
                :value="stats.pendingBimbingan"
                subtitle="Sesi milik Anda berstatus process"
                :icon="FileCheck"
                icon-color="amber"
            />
            <StatCard
                title="Jadwal Menguji Mendatang"
                :value="stats.jadwalUjiCount"
                subtitle="Jadwal terpublikasi mulai hari ini; posisi penguji"
                :icon="Calendar"
                icon-color="blue"
            />
            <StatCard
                title="Mahasiswa Bimbingan Aktif"
                :value="mySupervisedStudents.length"
                subtitle="Mahasiswa TA dalam proses bimbingan Anda"
                :icon="Users"
                icon-color="emerald"
            />
            <StatCard
                title="Kapasitas Kuota Dosen"
                value="5 / 10"
                subtitle="Tersedia 5 slot bimbingan semester ini"
                :icon="GraduationCap"
                icon-color="indigo"
            />
        </div>

        <!-- 3. PIPELINE KEMAJUAN BIMBINGAN MAHASISWA DOSEN -->
        <div
            class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex flex-col justify-between gap-2 sm:flex-row sm:items-center"
            >
                <div>
                    <h3
                        class="text-sm font-bold text-slate-900 sm:text-base dark:text-white"
                    >
                        Distribusi Tahapan Mahasiswa Bimbingan Anda
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Sebaran tahapan mahasiswa tugas akhir yang sedang Anda bimbing saat ini
                    </p>
                </div>
                <span
                    class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                >
                    Total {{ mySupervisedStudents.length }} Mahasiswa Bimbingan
                </span>
            </div>

            <!-- Progress Bar Multi-Segment -->
            <div
                class="flex h-3 w-full overflow-hidden rounded-full bg-slate-100 shadow-inner dark:bg-slate-800"
            >
                <div
                    v-for="stage in adviseeStages"
                    :key="stage.name"
                    :class="[
                        stage.barColor,
                        'h-full transition-all duration-500',
                    ]"
                    :style="{ width: stage.pct + '%' }"
                    :title="stage.name + ': ' + stage.count + ' mhs (' + stage.pct + '%)'"
                />
            </div>

            <!-- Segment Legend Cards -->
            <div
                class="grid grid-cols-2 gap-3 pt-2 sm:grid-cols-4"
            >
                <Link
                    v-for="stage in adviseeStages"
                    :key="stage.name"
                    :href="stage.href"
                    class="block space-y-1 rounded-xl border border-slate-200/70 bg-slate-50/70 p-3 transition-all hover:border-blue-500/50 hover:shadow-xs dark:border-slate-800/80 dark:bg-slate-900/50"
                >
                    <div class="flex items-center gap-2">
                        <span
                            class="h-2.5 w-2.5 rounded-full"
                            :class="stage.dotColor"
                        />
                        <span
                            class="truncate text-xs font-semibold text-slate-700 dark:text-slate-300"
                        >
                            {{ stage.name }}
                        </span>
                    </div>
                    <div class="flex items-baseline justify-between">
                        <span
                            class="text-base font-bold text-slate-900 dark:text-white"
                        >
                            {{ stage.count }} Mhs
                        </span>
                        <span class="text-[10px] font-medium text-slate-400">
                            {{ stage.pct }}%
                        </span>
                    </div>
                </Link>
            </div>
        </div>

        <!-- 4. MAIN 2-COLUMN SECTION -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 items-stretch">
            <!-- LEFT COLUMN: MAHASISWA BIMBINGAN & LOGBOOK (7 COLS) -->
            <div class="flex flex-col justify-between space-y-6 lg:col-span-7">
                <!-- CARD: MAHASISWA BIMBINGAN & LOGBOOK TA-04 -->
                <Card class="flex flex-col justify-between p-5 sm:p-6 flex-1">
                    <div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-3 sm:pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h2
                                    class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white"
                                >
                                    Mahasiswa Bimbingan &amp; Logbook TA-04
                                </h2>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Pantau progres penulisan skripsi dan validasi lembar konsultasi mahasiswa
                                </p>
                            </div>
                            <Link
                                href="/dosen/bimbingan"
                                class="text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 shrink-0"
                            >
                                Semua →
                            </Link>
                        </div>

                        <div class="mt-4 space-y-3.5">
                            <div
                                v-for="student in mySupervisedStudents"
                                :key="student.id"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 shadow-2xs transition-all hover:border-blue-400/50 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ student.nama }}
                                        </span>
                                        <span
                                            class="font-mono text-xs text-slate-400"
                                        >
                                            ({{ student.nim }})
                                        </span>
                                        <span
                                            class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-800 dark:bg-blue-950 dark:text-blue-300"
                                        >
                                            {{ student.tahap }}
                                        </span>
                                        <span
                                            v-if="student.hasPendingLogbook"
                                            class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-950 dark:text-amber-300"
                                        >
                                            <span
                                                class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"
                                            />
                                            {{ student.logbookPendingText }}
                                        </span>
                                    </div>

                                    <p
                                        class="text-xs text-slate-600 line-clamp-1 dark:text-slate-300"
                                    >
                                        {{ student.judul }}
                                    </p>

                                    <!-- Progress Bar -->
                                    <div class="flex items-center gap-3 pt-1">
                                        <div
                                            class="h-1.5 flex-1 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
                                        >
                                            <div
                                                class="h-full rounded-full bg-blue-600 transition-all duration-500"
                                                :style="{
                                                    width: student.progress + '%',
                                                }"
                                            />
                                        </div>
                                        <span
                                            class="text-[11px] font-bold text-slate-700 dark:text-slate-300"
                                        >
                                            {{ student.progress }}%
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400">
                                        Konsultasi terakhir: {{ student.terakhirBimbingan }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- CARD: USULAN JUDUL CALON MAHASISWA BIMBINGAN -->
                <Card class="p-5 sm:p-6">
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-3 sm:pb-4 dark:border-slate-800"
                    >
                        <div>
                            <h3
                                class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white"
                            >
                                Usulan Judul
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                Mahasiswa yang memilih Anda sebagai calon Pembimbing 1 atau 2
                            </p>
                        </div>
                        <Link
                            href="/dosen/persetujuan"
                            class="text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 shrink-0"
                        >
                            Semua →
                        </Link>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 dark:border-slate-800 dark:bg-slate-900/50"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-700 dark:border-amber-900/50 dark:bg-amber-950/40 dark:text-amber-400"
                                >
                                    Menunggu review
                                </span>
                                <span class="text-[11px] text-slate-400">18 Okt 2026</span>
                            </div>
                            <h4
                                class="mt-2 text-xs font-bold text-slate-900 dark:text-white"
                            >
                                Fajar Nugraha (NIM: 11231045)
                            </h4>
                            <p class="mt-1 text-xs text-slate-600 line-clamp-1 dark:text-slate-300">
                                Implementasi Federative Learning pada Sistem Deteksi Penyakit Tanaman Padi
                            </p>
                            <div class="mt-2 flex items-center justify-between pt-1">
                                <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Posisi: <strong class="text-slate-700 dark:text-slate-200">Pembimbing 1</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- RIGHT COLUMN: JADWAL MENGUJI & AGENDA (5 COLS) -->
            <div class="flex flex-col justify-between space-y-6 lg:col-span-5">
                <!-- CARD: JADWAL MENGUJI TERDEKAT -->
                <Card class="flex flex-col justify-between p-5 sm:p-6 flex-1">
                    <div>
                        <div
                            class="flex items-center justify-between border-b border-slate-100 pb-3 sm:pb-4 dark:border-slate-800"
                        >
                            <div>
                                <h3
                                    class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white"
                                >
                                    Jadwal Menguji Terdekat
                                </h3>
                                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    Penugasan seminar &amp; sidang terdaftar
                                </p>
                            </div>
                            <Link
                                href="/dosen/sempro"
                                class="text-xs font-bold text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 shrink-0"
                            >
                                Semua →
                            </Link>
                        </div>

                        <div class="mt-4 space-y-3">
                            <div
                                v-for="exam in upcomingExams"
                                :key="exam.id"
                                class="rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5 transition-all hover:border-blue-400/50 dark:border-slate-800 dark:bg-slate-900/50"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span
                                        class="rounded-full px-2.5 py-0.5 text-[10px] font-bold"
                                        :class="exam.tipeBadge"
                                    >
                                        {{ exam.tipe }}
                                    </span>
                                    <span
                                        class="rounded-md border border-slate-200 bg-white px-2 py-0.5 text-[10px] font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                    >
                                        {{ exam.peran }}
                                    </span>
                                </div>

                                <p
                                    class="mt-2 text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ exam.mahasiswa }}
                                    <span class="font-mono text-[11px] font-normal text-slate-400">
                                        ({{ exam.nim }})
                                    </span>
                                </p>
                                <p class="mt-1 text-xs text-slate-600 line-clamp-1 dark:text-slate-300">
                                    {{ exam.judul }}
                                </p>

                                <div class="mt-2.5 space-y-1 border-t border-slate-200/60 pt-2 text-[11px] text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
                                    <div class="flex items-center gap-1.5">
                                        <Clock class="h-3.5 w-3.5 text-slate-400" />
                                        <span>{{ exam.tanggal }} • {{ exam.jam }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <Layers class="h-3.5 w-3.5 text-slate-400" />
                                        <span>{{ exam.ruang }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- CARD: BATAS WAKTU AKADEMIK PERIODE AKTIF -->
                <Card class="p-5 sm:p-6">
                    <div
                        class="flex items-start justify-between border-b border-slate-100 pb-3 sm:pb-4 dark:border-slate-800"
                    >
                        <div>
                            <h3
                                class="text-sm font-extrabold text-slate-900 sm:text-base dark:text-white"
                            >
                                Batas Waktu Periode Aktif
                            </h3>
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                Ketetapan batas waktu ujian dan entri nilai prodi
                            </p>
                        </div>
                    </div>

                    <div class="mt-3.5 space-y-2 text-xs">
                        <div
                            class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/50"
                        >
                            <span class="text-slate-700 dark:text-slate-300">
                                Batas Nilai Sempro Gel. 2
                            </span>
                            <span class="font-bold text-rose-600 dark:text-rose-400">
                                25 Okt 2026
                            </span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/50"
                        >
                            <span class="text-slate-700 dark:text-slate-300">
                                Penutupan Pendaftaran Sidang
                            </span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">
                                30 Okt 2026
                            </span>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </div>
</template>
