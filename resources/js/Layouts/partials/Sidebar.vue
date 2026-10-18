<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Building2,
    Calendar,
    ChevronDown,
    ChevronLeft,
    Clock,
    Cpu,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Globe,
    GraduationCap,
    Home,
    KeyRound,
    Layers,
    Printer,
    ShieldCheck,
    UserCheck,
    Users,
} from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';

const props = defineProps<{
    collapsed: boolean;
}>();

const emit = defineEmits<{
    (e: 'toggle-collapse'): void;
}>();

const page = usePage();
const { role: userRole, roleInfo } = useAuth();
const pengajuanDropdownOpen = ref(true);
const dosenPengajuanOpen = ref(true);
const kaprodiPengajuanOpen = ref(true);
const kaprodiJadwalOpen = ref(true);

const currentUrl = computed(() => page.url);

const isPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/pendaftaran') &&
        !currentUrl.value.startsWith('/pendaftaran/jadwal'),
);

const isDosenPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/dosen/persetujuan') ||
        currentUrl.value.startsWith('/dosen/bimbingan') ||
        currentUrl.value.startsWith('/dosen/sempro') ||
        currentUrl.value.startsWith('/dosen/sidang'),
);

const isKaprodiPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/kaprodi/persetujuan') ||
        currentUrl.value.startsWith('/dosen/bimbingan') ||
        currentUrl.value.startsWith('/kaprodi/bimbingan'),
);

const isKaprodiJadwalActive = computed(
    () =>
        currentUrl.value.startsWith('/kaprodi/penjadwalan') ||
        currentUrl.value.startsWith('/koordinator/penjadwalan') ||
        currentUrl.value.startsWith('/kaprodi/monitoring') ||
        currentUrl.value.startsWith('/koordinator/monitoring'),
);
</script>

<template>
    <aside
        class="z-30 hidden h-screen shrink-0 flex-col justify-between border-r border-[#1E293B] bg-[#0E1626] text-slate-300 transition-all duration-300 lg:flex"
        :class="[collapsed ? 'w-20' : 'w-64']"
    >
        <div class="flex min-h-0 flex-1 flex-col">
            <!-- Brand Header -->
            <div
                class="flex h-16 shrink-0 items-center justify-between border-b border-slate-800/80 px-4"
            >
                <Link
                    :href="roleInfo.homePath"
                    class="flex items-center gap-3 overflow-hidden"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30"
                    >
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div v-if="!collapsed" class="min-w-0">
                        <h1
                            class="truncate text-sm font-bold tracking-tight text-white"
                        >
                            SIPTA IF
                        </h1>
                        <p
                            class="truncate text-[10px] font-medium text-slate-400"
                        >
                            Informatika ITK
                        </p>
                    </div>
                </Link>

                <button
                    type="button"
                    class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                    :title="collapsed ? 'Buka Sidebar' : 'Tutup Sidebar'"
                    :aria-label="
                        collapsed ? 'Perluas navigasi' : 'Ringkas navigasi'
                    "
                    @click="emit('toggle-collapse')"
                >
                    <ChevronLeft
                        class="h-4 w-4 transition-transform"
                        :class="{ 'rotate-180': collapsed }"
                    />
                </button>
            </div>

            <!-- Role Badge -->
            <div v-if="!collapsed" class="shrink-0 px-4 pt-3 pb-1">
                <div
                    class="rounded-xl border px-3 py-1.5 text-center text-[10px] font-bold"
                    :class="roleInfo.color"
                >
                    {{ roleInfo.title }}
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto p-3">
                <!-- 1. MAHASISWA -->
                <template v-if="userRole === 'mahasiswa'">
                    <Link
                        href="/dashboard"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl === '/dashboard' || currentUrl === '/'
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Home class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Dashboard</span>
                    </Link>

                    <div>
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                isPengajuanActive
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            @click="
                                pengajuanDropdownOpen = !pengajuanDropdownOpen
                            "
                        >
                            <div class="flex items-center gap-3">
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Pengajuan TA</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{
                                    '-rotate-90': !pengajuanDropdownOpen,
                                }"
                            />
                        </button>

                        <div
                            v-if="pengajuanDropdownOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/pendaftaran/judul"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/judul'
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Pengajuan Judul
                            </Link>
                            <Link
                                href="/pendaftaran/bimbingan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/bimbingan'
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Bimbingan (TA-04)
                            </Link>
                            <Link
                                href="/pendaftaran/sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/sempro'
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Seminar Proposal
                            </Link>
                            <Link
                                href="/pendaftaran/sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/sidang'
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Sidang Tugas Akhir
                            </Link>
                        </div>
                    </div>

                    <Link
                        href="/pendaftaran/jadwal"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/pendaftaran/jadwal')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Calendar class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Pendaftar & Jadwal</span>
                    </Link>

                    <Link
                        href="/prosedur"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/prosedur')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Layers class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Prosedur TA</span>
                    </Link>

                    <Link
                        href="/katalog"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/katalog')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <BookOpen class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Katalog TA</span>
                    </Link>

                    <Link
                        href="/panduan"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/panduan')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Globe class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Panduan Website</span>
                    </Link>
                </template>

                <!-- 2. DOSEN -->
                <template v-else-if="userRole === 'dosen'">
                    <!-- SECTION 1: MANAJEMEN UTAMA -->
                    <div class="space-y-1">
                        <p
                            v-if="!collapsed"
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Manajemen Utama
                        </p>

                        <Link
                            href="/dosen/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl === '/dosen/dashboard'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Dashboard' : ''"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Dashboard</span>
                        </Link>
                    </div>

                    <!-- SECTION 2: DATA PENGAJUAN (ACCORDION) -->
                    <div class="pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                isDosenPengajuanActive
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            @click="dosenPengajuanOpen = !dosenPengajuanOpen"
                        >
                            <div class="flex items-center gap-3">
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Pengajuan</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !dosenPengajuanOpen }"
                            />
                        </button>

                        <div
                            v-if="dosenPengajuanOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/dosen/persetujuan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/persetujuan') &&
                                    !currentUrl.includes('section=sempro') &&
                                    !currentUrl.includes('section=sidang')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Daftar Judul
                            </Link>

                            <Link
                                href="/dosen/bimbingan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/bimbingan')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Bimbingan TA
                            </Link>

                            <Link
                                href="/dosen/sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/sempro') ||
                                    currentUrl.includes('section=sempro')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Seminar Proposal
                            </Link>

                            <Link
                                href="/dosen/sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/sidang') ||
                                    currentUrl.includes('section=sidang')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Sidang TA
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 3: PANDUAN & LAINNYA -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Panduan &amp; Lainnya
                        </p>

                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Katalog TA' : ''"
                        >
                            <BookOpen class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Katalog TA</span>
                        </Link>

                        <Link
                            href="/panduan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/panduan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Panduan Website' : ''"
                        >
                            <Globe class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Panduan Website</span>
                        </Link>
                    </div>
                </template>

                <!-- 3. KAPRODI / KOORPRODI -->
                <template v-else-if="userRole === 'kaprodi'">
                    <!-- SECTION 1: MANAJEMEN UTAMA -->
                    <div class="space-y-1">
                        <p
                            v-if="!collapsed"
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Manajemen Utama
                        </p>

                        <Link
                            href="/kaprodi/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl === '/kaprodi/dashboard'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Dashboard' : ''"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Dashboard</span>
                        </Link>
                    </div>

                    <!-- SECTION 2: DATA PENGAJUAN (ACCORDION) -->
                    <div class="pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                isKaprodiPengajuanActive
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            @click="
                                kaprodiPengajuanOpen = !kaprodiPengajuanOpen
                            "
                        >
                            <div class="flex items-center gap-3">
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Pengajuan</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !kaprodiPengajuanOpen }"
                            />
                        </button>

                        <div
                            v-if="kaprodiPengajuanOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/kaprodi/persetujuan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith(
                                        '/kaprodi/persetujuan',
                                    ) &&
                                    !currentUrl.includes('section=sempro') &&
                                    !currentUrl.includes('section=sidang')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Daftar Judul
                            </Link>

                            <Link
                                href="/dosen/bimbingan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/bimbingan') ||
                                    currentUrl.startsWith('/kaprodi/bimbingan')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Bimbingan TA
                            </Link>

                            <Link
                                href="/kaprodi/persetujuan?section=sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.includes('section=sempro')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Seminar Proposal
                            </Link>

                            <Link
                                href="/kaprodi/persetujuan?section=sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.includes('section=sidang')
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Sidang TA
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 3: PENJADWALAN & PLOTTING PENGUJI (ACCORDION) -->
                    <div class="pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                isKaprodiJadwalActive
                                    ? 'text-white'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            @click="kaprodiJadwalOpen = !kaprodiJadwalOpen"
                        >
                            <div class="flex items-center gap-3">
                                <Cpu class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Penjadwalan &amp; Plotting</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !kaprodiJadwalOpen }"
                            />
                        </button>

                        <div
                            v-if="kaprodiJadwalOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/kaprodi/penjadwalan"
                                class="flex items-center justify-between rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith(
                                        '/kaprodi/penjadwalan',
                                    ) ||
                                    currentUrl.startsWith(
                                        '/koordinator/penjadwalan',
                                    )
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                <span class="truncate"
                                    >Plotting Jadwal (GA)</span
                                >
                                <span
                                    class="py-0.2 rounded-full border border-blue-500/30 bg-blue-500/20 px-1.5 text-[9px] font-extrabold text-blue-400"
                                >
                                    GA
                                </span>
                            </Link>

                            <Link
                                href="/kaprodi/penjadwalan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith(
                                        '/kaprodi/penjadwalan',
                                    )
                                        ? 'text-slate-300 hover:text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Periode Gelombang
                            </Link>

                            <Link
                                href="/kaprodi/monitoring"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith(
                                        '/kaprodi/monitoring',
                                    ) ||
                                    currentUrl.startsWith(
                                        '/koordinator/monitoring',
                                    )
                                        ? 'font-bold text-white'
                                        : 'text-slate-400 hover:text-slate-200',
                                ]"
                            >
                                Monitoring Kemajuan TA
                            </Link>
                        </div>
                    </div>

                    <!-- SECTION 4: DATA MASTER -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Data Master
                        </p>

                        <Link
                            href="/kaprodi/dosen"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/dosen')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Data Dosen' : ''"
                        >
                            <GraduationCap class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Data Dosen</span>
                        </Link>

                        <Link
                            href="/kaprodi/mahasiswa"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/mahasiswa') ||
                                currentUrl.startsWith('/koordinator/mahasiswa')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Data Mahasiswa' : ''"
                        >
                            <Users class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Data Mahasiswa</span>
                        </Link>
                    </div>

                    <!-- SECTION 5: PROSEDUR & KATALOG -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Prosedur &amp; Katalog
                        </p>

                        <Link
                            href="/katalog"
                            class="flex items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Katalog TA' : ''"
                        >
                            <div class="flex items-center gap-3">
                                <FileSpreadsheet class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Katalog TA</span>
                            </div>
                            <span
                                v-if="!collapsed"
                                class="py-0.2 rounded-full border border-emerald-500/30 bg-emerald-500/20 px-1.5 text-[9px] font-extrabold text-emerald-400"
                            >
                                Arsip
                            </span>
                        </Link>

                        <Link
                            href="/prosedur"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/prosedur')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Prosedur TA' : ''"
                        >
                            <Layers class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Prosedur TA</span>
                        </Link>

                        <Link
                            href="/panduan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/panduan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Panduan Website' : ''"
                        >
                            <Globe class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Panduan Website</span>
                        </Link>
                    </div>
                </template>

                <!-- 4. TENDIK -->
                <template v-else-if="userRole === 'tendik'">
                    <Link
                        href="/tendik/dashboard"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl === '/tendik/dashboard'
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Home class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Dashboard Tendik</span>
                    </Link>

                    <Link
                        href="/tendik/verifikasi"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/tendik/verifikasi')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <FileCheck class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Verifikasi Berkas</span>
                    </Link>

                    <Link
                        href="/tendik/ruangan"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/tendik/ruangan')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Building2 class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Jadwal Ruangan</span>
                    </Link>

                    <Link
                        href="/tendik/arsip"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/tendik/arsip')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Printer class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Berita Acara & SK</span>
                    </Link>

                    <Link
                        href="/tendik/mahasiswa"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/tendik/mahasiswa')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <KeyRound class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Kelola Mahasiswa</span>
                    </Link>

                    <Link
                        href="/panduan"
                        class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                        :class="[
                            currentUrl.startsWith('/panduan')
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                        ]"
                    >
                        <Globe class="h-4 w-4 shrink-0" />
                        <span v-if="!collapsed">Panduan Website</span>
                    </Link>
                </template>
            </nav>
        </div>
    </aside>
</template>
