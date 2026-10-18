<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    Calendar,
    ChevronDown,
    ChevronLeft,
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
const dosenJadwalOpen = ref(true);
const kaprodiPengajuanOpen = ref(true);
const kaprodiJadwalOpen = ref(true);
const kaprodiPeriodeOpen = ref(true);

const currentUrl = computed(() => page.url);

const isPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/pendaftaran') &&
        !currentUrl.value.startsWith('/pendaftaran/jadwal'),
);

const isDosenPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/dosen/persetujuan') ||
        currentUrl.value.startsWith('/dosen/bimbingan'),
);

const isDosenJadwalActive = computed(
    () =>
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
        currentUrl.value.startsWith('/kaprodi/penjadwalan/plotting') ||
        currentUrl.value.startsWith('/koordinator/penjadwalan/plotting') ||
        ((currentUrl.value.startsWith('/kaprodi/penjadwalan') ||
            currentUrl.value.startsWith('/koordinator/penjadwalan')) &&
            !currentUrl.value.includes('/penjadwalan/sempro') &&
            !currentUrl.value.includes('/penjadwalan/ta')),
);

const isKaprodiPeriodeActive = computed(
    () =>
        currentUrl.value.startsWith('/kaprodi/penjadwalan/sempro') ||
        currentUrl.value.startsWith('/kaprodi/penjadwalan/ta') ||
        currentUrl.value.startsWith('/koordinator/penjadwalan/sempro') ||
        currentUrl.value.startsWith('/koordinator/penjadwalan/ta'),
);

const handleAccordionClick = (
    type:
        | 'pengajuan'
        | 'dosenPengajuan'
        | 'dosenJadwal'
        | 'kaprodiPengajuan'
        | 'kaprodiPeriode'
        | 'kaprodiJadwal',
) => {
    if (props.collapsed) {
        emit('toggle-collapse');
        return;
    }
    switch (type) {
        case 'pengajuan':
            pengajuanDropdownOpen.value = !pengajuanDropdownOpen.value;
            break;
        case 'dosenPengajuan':
            dosenPengajuanOpen.value = !dosenPengajuanOpen.value;
            break;
        case 'dosenJadwal':
            dosenJadwalOpen.value = !dosenJadwalOpen.value;
            break;
        case 'kaprodiPengajuan':
            kaprodiPengajuanOpen.value = !kaprodiPengajuanOpen.value;
            break;
        case 'kaprodiPeriode':
            kaprodiPeriodeOpen.value = !kaprodiPeriodeOpen.value;
            break;
        case 'kaprodiJadwal':
            kaprodiJadwalOpen.value = !kaprodiJadwalOpen.value;
            break;
    }
};
</script>

<template>
    <aside
        class="z-30 hidden h-screen shrink-0 flex-col justify-between border-r border-slate-200/80 bg-white text-slate-700 transition-all duration-300 lg:flex dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-300"
        :class="[collapsed ? 'w-20' : 'w-64']"
    >
        <div class="flex min-h-0 flex-1 flex-col">
            <!-- Brand Header -->
            <div
                class="relative flex h-16 shrink-0 items-center border-b border-slate-200/80 dark:border-slate-800/80"
                :class="collapsed ? 'justify-center px-2' : 'justify-between px-4'"
            >
                <Link
                    :href="roleInfo.homePath"
                    class="flex items-center gap-3 overflow-hidden"
                    :title="collapsed ? 'SIPTA IF - Informatika ITK' : ''"
                >
                    <div
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md shadow-blue-600/30"
                    >
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div v-if="!collapsed" class="min-w-0">
                        <h1
                            class="truncate text-sm font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            SIPTA IF
                        </h1>
                        <p
                            class="truncate text-[10px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            Informatika ITK
                        </p>
                    </div>
                </Link>

                <button
                    type="button"
                    class="flex cursor-pointer items-center justify-center text-slate-400 transition-colors hover:text-slate-700 dark:hover:text-white"
                    :class="
                        collapsed
                            ? 'absolute -right-3 top-5 z-40 h-6 w-6 rounded-full border border-slate-200 bg-white shadow-sm hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700'
                            : 'h-7 w-7 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800'
                    "
                    :title="collapsed ? 'Buka Sidebar' : 'Tutup Sidebar'"
                    :aria-label="
                        collapsed ? 'Perluas navigasi' : 'Ringkas navigasi'
                    "
                    @click="emit('toggle-collapse')"
                >
                    <ChevronLeft
                        class="h-3.5 w-3.5 transition-transform"
                        :class="{ 'rotate-180': collapsed }"
                    />
                </button>
            </div>

            <!-- Role Badge (Only shown when expanded) -->
            <div v-if="!collapsed" class="shrink-0 px-4 pt-3 pb-1">
                <div
                    class="rounded-xl border px-3 py-1.5 text-center text-[10px] font-bold"
                    :class="roleInfo.color"
                >
                    {{ roleInfo.title }}
                </div>
            </div>

            <!-- Navigation Links -->
            <nav
                class="min-h-0 flex-1 space-y-1 p-3"
                :class="collapsed ? 'overflow-visible' : 'overflow-y-auto'"
            >
                <!-- ============================================== -->
                <!-- 1. MAHASISWA                                   -->
                <!-- ============================================== -->
                <template v-if="userRole === 'mahasiswa'">
                    <!-- Dashboard -->
                    <div class="relative group">
                        <Link
                            href="/dashboard"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl === '/dashboard' || currentUrl === '/'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Dashboard</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Dashboard
                        </div>
                    </div>

                    <!-- Pengajuan TA (Accordion & Flyout) -->
                    <div class="relative group pt-1">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 justify-between',
                                isPengajuanActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Pengajuan TA (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('pengajuan')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
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

                        <!-- Submenu inline saat sidebar terbuka -->
                        <div
                            v-if="pengajuanDropdownOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/pendaftaran/judul"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/judul'
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Pengajuan Judul
                            </Link>
                            <Link
                                href="/pendaftaran/bimbingan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/bimbingan'
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Bimbingan (TA-04)
                            </Link>
                            <Link
                                href="/pendaftaran/sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/sempro'
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Seminar Proposal
                            </Link>
                            <Link
                                href="/pendaftaran/sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl === '/pendaftaran/sidang'
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Sidang Tugas Akhir
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Pengajuan TA
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/pendaftaran/judul"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl === '/pendaftaran/judul'
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Pengajuan Judul</span>
                                </Link>
                                <Link
                                    href="/pendaftaran/bimbingan"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl === '/pendaftaran/bimbingan'
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Bimbingan (TA-04)</span>
                                </Link>
                                <Link
                                    href="/pendaftaran/sempro"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl === '/pendaftaran/sempro'
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Seminar Proposal</span>
                                </Link>
                                <Link
                                    href="/pendaftaran/sidang"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl === '/pendaftaran/sidang'
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Sidang Tugas Akhir</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Pendaftar & Jadwal -->
                    <div class="relative group pt-1">
                        <Link
                            href="/pendaftaran/jadwal"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/pendaftaran/jadwal')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Calendar class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Pendaftar & Jadwal</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Pendaftar & Jadwal
                        </div>
                    </div>

                    <!-- Prosedur TA -->
                    <div class="relative group pt-1">
                        <Link
                            href="/prosedur"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/prosedur')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Layers class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Prosedur TA</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Prosedur TA
                        </div>
                    </div>

                    <!-- Katalog TA -->
                    <div class="relative group pt-1">
                        <Link
                            href="/katalog"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <BookOpen class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Katalog TA</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Katalog TA
                        </div>
                    </div>

                    <!-- Panduan Website -->
                    <div class="relative group pt-1">
                        <Link
                            href="/panduan"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/panduan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Globe class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Panduan Website</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Panduan Website
                        </div>
                    </div>
                </template>

                <!-- ============================================== -->
                <!-- 2. DOSEN                                       -->
                <!-- ============================================== -->
                <template v-else-if="userRole === 'dosen'">
                    <!-- SECTION 1: MANAJEMEN UTAMA -->
                    <div class="space-y-1">
                        <p
                            v-if="!collapsed"
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Manajemen Utama
                        </p>
                        <div
                            v-else
                            class="mx-2 my-2 border-t border-slate-100 dark:border-slate-800/80"
                        ></div>

                        <div class="relative group">
                            <Link
                                href="/dosen/dashboard"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl === '/dosen/dashboard'
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Home class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Dashboard</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Dashboard
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: DATA PENGAJUAN (ACCORDION & FLYOUT) -->
                    <div class="relative group pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-1.5 justify-between',
                                isDosenPengajuanActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Data Pengajuan (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('dosenPengajuan')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Pengajuan</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !dosenPengajuanOpen }"
                            />
                        </button>

                        <!-- Submenu inline saat sidebar terbuka -->
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
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Daftar Judul
                            </Link>
                            <Link
                                href="/dosen/bimbingan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/bimbingan')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Bimbingan TA
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Data Pengajuan
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/dosen/persetujuan"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/dosen/persetujuan') &&
                                        !currentUrl.includes('section=sempro') &&
                                        !currentUrl.includes('section=sidang')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Daftar Judul</span>
                                </Link>
                                <Link
                                    href="/dosen/bimbingan"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/dosen/bimbingan')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Bimbingan TA</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION: PENJADWALAN (ACCORDION & FLYOUT) -->
                    <div class="relative group pt-1">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-1.5 justify-between',
                                isDosenJadwalActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Penjadwalan (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('dosenJadwal')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
                                <Calendar class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Penjadwalan</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !dosenJadwalOpen }"
                            />
                        </button>

                        <!-- Submenu inline saat sidebar terbuka -->
                        <div
                            v-if="dosenJadwalOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/dosen/sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/sempro')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Seminar Proposal
                            </Link>
                            <Link
                                href="/dosen/sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/dosen/sidang')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Sidang TA
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Penjadwalan
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/dosen/sempro"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/dosen/sempro')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Seminar Proposal</span>
                                </Link>
                                <Link
                                    href="/dosen/sidang"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/dosen/sidang')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Sidang TA</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PANDUAN & LAINNYA -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Panduan &amp; Lainnya
                        </p>
                        <div
                            v-else
                            class="mx-2 my-2 border-t border-slate-100 dark:border-slate-800/80"
                        ></div>

                        <!-- Katalog TA -->
                        <div class="relative group">
                            <Link
                                href="/katalog"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/katalog')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Katalog TA</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Katalog TA
                            </div>
                        </div>

                        <!-- Panduan Website -->
                        <div class="relative group pt-1">
                            <Link
                                href="/panduan"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/panduan')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Globe class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Panduan Website</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Panduan Website
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ============================================== -->
                <!-- 3. KAPRODI / KOORPRODI                         -->
                <!-- ============================================== -->
                <template v-else-if="userRole === 'kaprodi'">
                    <!-- SECTION 1: MANAJEMEN UTAMA -->
                    <div class="space-y-1">
                        <p
                            v-if="!collapsed"
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Manajemen Utama
                        </p>
                        <div
                            v-else
                            class="mx-2 my-2 border-t border-slate-100 dark:border-slate-800/80"
                        ></div>

                        <div class="relative group">
                            <Link
                                href="/kaprodi/dashboard"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl === '/kaprodi/dashboard'
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Home class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Dashboard</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Dashboard
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: DATA PENGAJUAN (ACCORDION & FLYOUT) -->
                    <div class="relative group pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-1.5 justify-between',
                                isKaprodiPengajuanActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Data Pengajuan (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('kaprodiPengajuan')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
                                <FileText class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Pengajuan</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{
                                    '-rotate-90': !kaprodiPengajuanOpen,
                                }"
                            />
                        </button>

                        <!-- Submenu inline saat sidebar terbuka -->
                        <div
                            v-if="kaprodiPengajuanOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/kaprodi/persetujuan"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/kaprodi/persetujuan') &&
                                    !currentUrl.includes('section=sempro') &&
                                    !currentUrl.includes('section=sidang')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
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
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Bimbingan TA
                            </Link>
                            <Link
                                href="/kaprodi/persetujuan?section=sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.includes('section=sempro')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Seminar Proposal
                            </Link>
                            <Link
                                href="/kaprodi/persetujuan?section=sidang"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.includes('section=sidang')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Sidang TA
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Data Pengajuan
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/kaprodi/persetujuan"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/kaprodi/persetujuan') &&
                                        !currentUrl.includes('section=sempro') &&
                                        !currentUrl.includes('section=sidang')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Daftar Judul</span>
                                </Link>
                                <Link
                                    href="/dosen/bimbingan"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/dosen/bimbingan') ||
                                        currentUrl.startsWith('/kaprodi/bimbingan')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Bimbingan TA</span>
                                </Link>
                                <Link
                                    href="/kaprodi/persetujuan?section=sempro"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.includes('section=sempro')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Seminar Proposal</span>
                                </Link>
                                <Link
                                    href="/kaprodi/persetujuan?section=sidang"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.includes('section=sidang')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Sidang TA</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: PERIODE GELOMBANG (ACCORDION & FLYOUT) -->
                    <div class="relative group pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-1.5 justify-between',
                                isKaprodiPeriodeActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Periode Gelombang (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('kaprodiPeriode')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
                                <Calendar class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Periode Gelombang</span>
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !kaprodiPeriodeOpen }"
                            />
                        </button>

                        <!-- Submenu inline saat sidebar terbuka -->
                        <div
                            v-if="kaprodiPeriodeOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/kaprodi/penjadwalan/sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/kaprodi/penjadwalan/sempro') ||
                                    currentUrl.startsWith('/koordinator/penjadwalan/sempro')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Periode Sempro
                            </Link>
                            <Link
                                href="/kaprodi/penjadwalan/ta"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/kaprodi/penjadwalan/ta') ||
                                    currentUrl.startsWith('/koordinator/penjadwalan/ta')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Periode TA
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-56 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Periode Gelombang
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/kaprodi/penjadwalan/sempro"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/kaprodi/penjadwalan/sempro') ||
                                        currentUrl.startsWith('/koordinator/penjadwalan/sempro')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Periode Sempro</span>
                                </Link>
                                <Link
                                    href="/kaprodi/penjadwalan/ta"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/kaprodi/penjadwalan/ta') ||
                                        currentUrl.startsWith('/koordinator/penjadwalan/ta')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Periode TA</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: PENJADWALAN & PLOTTING PENGUJI (ACCORDION & FLYOUT) -->
                    <div class="relative group pt-2">
                        <button
                            type="button"
                            class="flex w-full cursor-pointer items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-1.5 justify-between',
                                isKaprodiJadwalActive
                                    ? 'text-blue-600 dark:text-white font-bold bg-blue-50/70 dark:bg-slate-800/50'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                            :title="collapsed ? 'Penjadwalan & Plotting (Klik untuk buka)' : ''"
                            @click="handleAccordionClick('kaprodiJadwal')"
                        >
                            <div
                                class="flex items-center"
                                :class="collapsed ? 'justify-center' : 'gap-3'"
                            >
                                <Cpu class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed"
                                    >Penjadwalan &amp; Plotting</span
                                >
                            </div>
                            <ChevronDown
                                v-if="!collapsed"
                                class="h-3.5 w-3.5 transition-transform"
                                :class="{ '-rotate-90': !kaprodiJadwalOpen }"
                            />
                        </button>

                        <!-- Submenu inline saat sidebar terbuka -->
                        <div
                            v-if="kaprodiJadwalOpen && !collapsed"
                            class="mt-1 space-y-1 pl-9"
                        >
                            <Link
                                href="/kaprodi/penjadwalan/plotting-sempro"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/kaprodi/penjadwalan/plotting-sempro') ||
                                    currentUrl.startsWith('/koordinator/penjadwalan/plotting-sempro') ||
                                    currentUrl === '/kaprodi/penjadwalan'
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Plotting Jadwal Sempro
                            </Link>
                            <Link
                                href="/kaprodi/penjadwalan/plotting-ta"
                                class="block rounded-lg px-2.5 py-1.5 text-xs transition-colors"
                                :class="[
                                    currentUrl.startsWith('/kaprodi/penjadwalan/plotting-ta') ||
                                    currentUrl.startsWith('/koordinator/penjadwalan/plotting-ta')
                                        ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-white'
                                        : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-800/50',
                                ]"
                            >
                                Plotting Jadwal Sidang TA
                            </Link>
                        </div>

                        <!-- Flyout Floating Menu saat sidebar collapsed -->
                        <div
                            v-if="collapsed"
                            class="pointer-events-none group-hover:pointer-events-auto opacity-0 group-hover:opacity-100 transition-all duration-150 absolute left-full top-0 ml-2.5 z-50 w-60 rounded-2xl border border-slate-200 bg-white p-2 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <div
                                class="border-b border-slate-100 px-2.5 py-1.5 dark:border-slate-800/80"
                            >
                                <span
                                    class="text-[11px] font-bold uppercase tracking-wider text-slate-900 dark:text-white"
                                >
                                    Penjadwalan &amp; Plotting
                                </span>
                            </div>
                            <div class="space-y-0.5 pt-1.5">
                                <Link
                                    href="/kaprodi/penjadwalan/plotting-sempro"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/kaprodi/penjadwalan/plotting-sempro') ||
                                        currentUrl.startsWith('/koordinator/penjadwalan/plotting-sempro') ||
                                        currentUrl === '/kaprodi/penjadwalan'
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Plotting Jadwal Sempro</span>
                                </Link>
                                <Link
                                    href="/kaprodi/penjadwalan/plotting-ta"
                                    class="flex items-center gap-2 rounded-xl px-2.5 py-1.5 text-xs transition-colors"
                                    :class="
                                        currentUrl.startsWith('/kaprodi/penjadwalan/plotting-ta') ||
                                        currentUrl.startsWith('/koordinator/penjadwalan/plotting-ta')
                                            ? 'font-bold bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/60 dark:hover:text-white'
                                    "
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                                    <span>Plotting Jadwal Sidang TA</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: DATA MASTER -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Data Master
                        </p>
                        <div
                            v-else
                            class="mx-2 my-2 border-t border-slate-100 dark:border-slate-800/80"
                        ></div>

                        <!-- Data Dosen -->
                        <div class="relative group">
                            <Link
                                href="/kaprodi/dosen"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/kaprodi/dosen')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <GraduationCap class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Dosen</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Data Dosen
                            </div>
                        </div>

                        <!-- Data Mahasiswa -->
                        <div class="relative group pt-1">
                            <Link
                                href="/kaprodi/mahasiswa"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/kaprodi/mahasiswa') ||
                                    currentUrl.startsWith('/koordinator/mahasiswa')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Users class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Data Mahasiswa</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Data Mahasiswa
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: PROSEDUR & KATALOG -->
                    <div class="space-y-1 pt-2">
                        <p
                            v-if="!collapsed"
                            class="px-3 text-[10px] font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                        >
                            Prosedur &amp; Katalog
                        </p>
                        <div
                            v-else
                            class="mx-2 my-2 border-t border-slate-100 dark:border-slate-800/80"
                        ></div>

                        <!-- Katalog TA -->
                        <div class="relative group">
                            <Link
                                href="/katalog"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/katalog')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <BookOpen class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Katalog TA</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Katalog TA
                            </div>
                        </div>

                        <!-- Prosedur TA -->
                        <div class="relative group pt-1">
                            <Link
                                href="/prosedur"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/prosedur')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Layers class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Prosedur TA</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Prosedur TA
                            </div>
                        </div>

                        <!-- Panduan Website -->
                        <div class="relative group pt-1">
                            <Link
                                href="/panduan"
                                class="flex items-center rounded-xl text-xs font-semibold transition-all"
                                :class="[
                                    collapsed
                                        ? 'h-10 w-10 mx-auto justify-center p-0'
                                        : 'px-3 py-2 gap-3',
                                    currentUrl.startsWith('/panduan')
                                        ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                                ]"
                            >
                                <Globe class="h-4 w-4 shrink-0" />
                                <span v-if="!collapsed">Panduan Website</span>
                            </Link>
                            <div
                                v-if="collapsed"
                                class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                            >
                                Panduan Website
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ============================================== -->
                <!-- 4. TENDIK                                      -->
                <!-- ============================================== -->
                <template v-else-if="userRole === 'tendik'">
                    <!-- Dashboard Tendik -->
                    <div class="relative group">
                        <Link
                            href="/tendik/dashboard"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl === '/tendik/dashboard'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Dashboard Tendik</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Dashboard Tendik
                        </div>
                    </div>

                    <!-- Verifikasi Berkas -->
                    <div class="relative group pt-1">
                        <Link
                            href="/tendik/verifikasi"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/tendik/verifikasi')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <FileCheck class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Verifikasi Berkas</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Verifikasi Berkas
                        </div>
                    </div>

                    <!-- Jadwal Ruangan -->
                    <div class="relative group pt-1">
                        <Link
                            href="/tendik/ruangan"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/tendik/ruangan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Building2 class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Jadwal Ruangan</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Jadwal Ruangan
                        </div>
                    </div>

                    <!-- Berita Acara & SK -->
                    <div class="relative group pt-1">
                        <Link
                            href="/tendik/arsip"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/tendik/arsip')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Printer class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Berita Acara & SK</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Berita Acara & SK
                        </div>
                    </div>

                    <!-- Kelola Mahasiswa -->
                    <div class="relative group pt-1">
                        <Link
                            href="/tendik/mahasiswa"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/tendik/mahasiswa')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <KeyRound class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Kelola Mahasiswa</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Kelola Mahasiswa
                        </div>
                    </div>

                    <!-- Panduan Website -->
                    <div class="relative group pt-1">
                        <Link
                            href="/panduan"
                            class="flex items-center rounded-xl text-xs font-semibold transition-all"
                            :class="[
                                collapsed
                                    ? 'h-10 w-10 mx-auto justify-center p-0'
                                    : 'px-3 py-2 gap-3',
                                currentUrl.startsWith('/panduan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/70 dark:hover:text-slate-200',
                            ]"
                        >
                            <Globe class="h-4 w-4 shrink-0" />
                            <span v-if="!collapsed">Panduan Website</span>
                        </Link>
                        <div
                            v-if="collapsed"
                            class="pointer-events-none absolute left-full top-1/2 -translate-y-1/2 ml-2.5 z-50 hidden group-hover:flex items-center rounded-lg bg-slate-900 px-2.5 py-1 text-xs font-medium text-white shadow-lg whitespace-nowrap dark:bg-slate-800 dark:border dark:border-slate-700"
                        >
                            Panduan Website
                        </div>
                    </div>
                </template>
            </nav>
        </div>
    </aside>
</template>
