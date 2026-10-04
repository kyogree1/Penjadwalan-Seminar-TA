<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Award,
    Bell,
    BookOpen,
    Building2,
    Calendar,
    ChevronDown,
    ChevronLeft,
    ChevronRight,
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
    LogOut,
    Menu,
    Moon,
    PenTool,
    Printer,
    ShieldCheck,
    Sun,
    Trash2,
    User,
    UserCheck,
    Users,
    X,
} from 'lucide-vue-next';

import AcademicChatbot from '@/Components/AcademicChatbot.vue';
import { useTheme } from '@/composables/useTheme';

interface Props {
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Dashboard',
});

const page = usePage();
const mobileMenuOpen = ref(false);
const sidebarCollapsed = ref(false);
const pengajuanDropdownOpen = ref(true);
const { isDarkMode, toggleDarkMode, initializeTheme } = useTheme();

onMounted(() => {
    initializeTheme();
});

const currentUrl = computed(() => page.url);

const isPengajuanActive = computed(
    () =>
        currentUrl.value.startsWith('/pendaftaran') &&
        !currentUrl.value.startsWith('/pendaftaran/jadwal'),
);

// Authenticated User & Role Detection
const authUser = computed(() => (page.props.auth as any)?.user);
const userRole = computed<'mahasiswa' | 'dosen' | 'kaprodi' | 'tendik'>(() => {
    const r = authUser.value?.role;
    if (r === 'koordinator') return 'kaprodi';
    if (r === 'dosen') return 'dosen';
    if (r === 'kaprodi') return 'kaprodi';
    if (r === 'tendik') return 'tendik';
    return 'mahasiswa';
});

// Role Badge and Labels
const roleInfo = computed(() => {
    switch (userRole.value) {
        case 'dosen':
            return {
                title: 'Portal Dosen',
                badge: 'Dosen Pembimbing & Penguji',
                color: 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                homePath: '/dosen/dashboard',
            };
        case 'kaprodi':
            return {
                title: 'Portal Kaprodi',
                badge: 'Koordinator Program Studi',
                color: 'bg-purple-500/10 text-purple-400 border-purple-500/30',
                homePath: '/kaprodi/dashboard',
            };
        case 'tendik':
            return {
                title: 'Portal Tendik',
                badge: 'Tenaga Kependidikan & Admin',
                color: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                homePath: '/tendik/dashboard',
            };
        case 'mahasiswa':
        default:
            return {
                title: 'Portal Mahasiswa',
                badge: 'Mahasiswa S1 Informatika',
                color: 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                homePath: '/dashboard',
            };
    }
});

const profileData = computed(() => {
    const u = authUser.value;
    if (!u) {
        return {
            name: 'Akmal Falah Maulana',
            idNumber: 'NIM: 11231006',
            initials: 'AF',
            roleLabel: 'Mahasiswa S1 Informatika',
        };
    }

    const initials = u.name
        ? u.name
              .split(' ')
              .filter(Boolean)
              .slice(0, 2)
              .map((w: string) => w[0]?.toUpperCase())
              .join('')
        : 'US';

    return {
        name: u.name,
        idNumber: u.nim_nip || u.username || u.email,
        initials: initials || 'US',
        roleLabel: u.jabatan || roleInfo.value.badge,
    };
});

// Notifications State & Role-specific Mock Data
interface NotificationItem {
    id: number;
    title: string;
    message: string;
    time: string;
    read: boolean;
    type: 'success' | 'info' | 'warning' | 'calendar';
    link?: string;
}

const showNotificationModal = ref(false);

const notifications = ref<Record<string, NotificationItem[]>>({
    mahasiswa: [
        {
            id: 1,
            title: 'Verifikasi Berkas Sempro Diterima',
            message:
                'Tendik telah memverifikasi kelengkapan berkas pendaftaran Seminar Proposal TA Anda. Status: Siap Jadwal.',
            time: '15 menit yang lalu',
            read: false,
            type: 'success',
            link: '/pendaftaran/sempro',
        },
        {
            id: 2,
            title: 'Validasi Logbook Bimbingan',
            message:
                'Dr. Ir. Hendra Wijaya, M.Kom. menyetujui catatan bimbingan Bab 4 Anda.',
            time: '2 jam yang lalu',
            read: false,
            type: 'info',
            link: '/pendaftaran/bimbingan',
        },
        {
            id: 3,
            title: 'Jadwal Seminar Proposal Ditetapkan',
            message:
                'Seminar Proposal dijadwalkan pada Senin, 30 September 2026, 10.00 WITA.',
            time: '1 hari yang lalu',
            read: false,
            type: 'calendar',
            link: '/pendaftaran/sempro',
        },
        {
            id: 4,
            title: 'Pengingat Periode Sidang Gelombang 2',
            message:
                'Pendaftaran Sidang Tugas Akhir dibuka hingga akhir bulan Oktober 2026.',
            time: '3 hari yang lalu',
            read: true,
            type: 'warning',
            link: '/pendaftaran/sidang',
        },
    ],
    dosen: [
        {
            id: 101,
            title: 'Pengajuan Bimbingan Baru',
            message:
                'Akmal Falah Maulana mengajukan logbook bimbingan Bab 4 untuk ditinjau.',
            time: '10 menit yang lalu',
            read: false,
            type: 'info',
            link: '/dosen/bimbingan',
        },
        {
            id: 102,
            title: 'Penugasan Penguji Sidang TA',
            message:
                'Anda ditugaskan sebagai Penguji 1 Sidang TA Mahasiswa Bagus Pratama.',
            time: '3 jam yang lalu',
            read: false,
            type: 'calendar',
            link: '/dosen/penilaian',
        },
        {
            id: 103,
            title: 'Input Nilai Belum Lengkap',
            message:
                'Terdapat 2 mahasiswa seminar proposal yang belum diinputkan nilai akhirnya.',
            time: 'Kemarin',
            read: true,
            type: 'warning',
            link: '/dosen/penilaian',
        },
    ],
    kaprodi: [
        {
            id: 201,
            title: 'Validasi Judul Menunggu ACC',
            message:
                'Ada 4 pengajuan judul Tugas Akhir mahasiswa baru yang memerlukan persetujuan Koordinator.',
            time: '30 menit yang lalu',
            read: false,
            type: 'info',
            link: '/kaprodi/persetujuan',
        },
        {
            id: 202,
            title: 'Optimasi Penjadwalan Selesai',
            message:
                'Algoritma Genetika menyelesaikan penjadwalan 18 sesi seminar tanpa konflik (Fitness 98.4%).',
            time: '2 jam yang lalu',
            read: false,
            type: 'success',
            link: '/koordinator/penjadwalan',
        },
        {
            id: 203,
            title: 'Penerbitan SK Tim Penguji',
            message:
                'Draft SK Penguji periode Gasal 2026/2027 telah siap diterbitkan dan ditandatangani.',
            time: '1 hari yang lalu',
            read: true,
            type: 'calendar',
            link: '/kaprodi/persetujuan',
        },
    ],
    tendik: [
        {
            id: 301,
            title: 'Berkas Sempro Baru Masuk',
            message:
                '3 berkas pendaftaran Seminar Proposal mahasiswa baru membutuhkan verifikasi keabsahan.',
            time: '5 menit yang lalu',
            read: false,
            type: 'warning',
            link: '/tendik/verifikasi',
        },
        {
            id: 302,
            title: 'Konfirmasi Ruangan Sidang',
            message:
                'Ruangan Lab Riset A telah dikonfirmasi untuk sesi seminar besok.',
            time: '1 jam yang lalu',
            read: false,
            type: 'success',
            link: '/tendik/ruangan',
        },
        {
            id: 303,
            title: 'Berita Acara Siap Cetak',
            message:
                'Berita Acara Sidang TA tanggal 18 September telah selesai diarsipkan.',
            time: 'Kemarin',
            read: true,
            type: 'info',
            link: '/tendik/arsip',
        },
    ],
});

const currentNotifications = computed(() => {
    return notifications.value[userRole.value] || notifications.value.mahasiswa;
});

const unreadCount = computed(() => {
    return currentNotifications.value.filter((n) => !n.read).length;
});

const markAllAsRead = () => {
    currentNotifications.value.forEach((n) => (n.read = true));
};

const markAsRead = (item: NotificationItem) => {
    item.read = true;
    if (item.link) {
        showNotificationModal.value = false;
        router.visit(item.link);
    }
};

const clearAllNotifications = () => {
    const roleKey = userRole.value;
    if (notifications.value[roleKey]) {
        notifications.value[roleKey] = [];
    }
};

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div
        class="flex h-screen w-full overflow-hidden bg-[#F4F6FA] font-sans text-slate-800 antialiased transition-colors duration-200 dark:bg-[#080D1A] dark:text-slate-100"
    >
        <!-- 1. DESKTOP SIDEBAR: Terkunci h-screen (tidak molor sepanjang web & tidak scroll keluar) -->
        <aside
            class="z-30 hidden h-screen shrink-0 flex-col justify-between border-r border-[#1E293B] bg-[#0E1626] text-slate-300 transition-all duration-300 lg:flex"
            :class="[sidebarCollapsed ? 'w-20' : 'w-64']"
        >
            <!-- Top Section: Header + Portal Title + Scrollable Navigation -->
            <div class="flex min-h-0 flex-1 flex-col">
                <!-- Brand Header (Tinggi tetap h-16) -->
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
                        <div v-if="!sidebarCollapsed" class="min-w-0">
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
                        class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                        :title="
                            sidebarCollapsed ? 'Buka Sidebar' : 'Tutup Sidebar'
                        "
                        @click="sidebarCollapsed = !sidebarCollapsed"
                    >
                        <ChevronLeft
                            class="h-4 w-4 transition-transform"
                            :class="{ 'rotate-180': sidebarCollapsed }"
                        />
                    </button>
                </div>

                <!-- Role Portal Title Banner in Sidebar -->
                <div v-if="!sidebarCollapsed" class="shrink-0 px-4 pt-3 pb-1">
                    <div
                        class="rounded-xl border px-3 py-1.5 text-center text-[10px] font-bold"
                        :class="roleInfo.color"
                    >
                        {{ roleInfo.title }}
                    </div>
                </div>

                <!-- Navigation Items: STRICTLY BY ROLE -->
                <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto p-3">
                    <!-- ===================================== -->
                    <!-- 1. MAHASISWA MENU                     -->
                    <!-- ===================================== -->
                    <template v-if="userRole === 'mahasiswa'">
                        <!-- Dashboard -->
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl === '/dashboard' ||
                                currentUrl === '/'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">Dashboard</span>
                        </Link>

                        <!-- Pengajuan TA (Accordion Dropdown) -->
                        <div>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                                :class="[
                                    isPengajuanActive
                                        ? 'text-white'
                                        : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                                ]"
                                @click="
                                    pengajuanDropdownOpen =
                                        !pengajuanDropdownOpen
                                "
                            >
                                <div class="flex items-center gap-3">
                                    <FileText class="h-4 w-4 shrink-0" />
                                    <span v-if="!sidebarCollapsed"
                                        >Pengajuan TA</span
                                    >
                                </div>
                                <ChevronDown
                                    v-if="!sidebarCollapsed"
                                    class="h-3.5 w-3.5 transition-transform duration-200"
                                    :class="{
                                        '-rotate-90': !pengajuanDropdownOpen,
                                    }"
                                />
                            </button>

                            <!-- Submenu Items -->
                            <div
                                v-if="
                                    !sidebarCollapsed && pengajuanDropdownOpen
                                "
                                class="mt-1 space-y-0.5 pl-7 text-xs font-medium"
                            >
                                <Link
                                    href="/pendaftaran/judul"
                                    class="block rounded-lg px-3 py-1.5 transition-colors"
                                    :class="[
                                        currentUrl.startsWith(
                                            '/pendaftaran/judul',
                                        )
                                            ? 'bg-blue-950/40 font-semibold text-blue-400'
                                            : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200',
                                    ]"
                                >
                                    Pengajuan Judul
                                </Link>
                                <Link
                                    href="/pendaftaran/bimbingan"
                                    class="block rounded-lg px-3 py-1.5 transition-colors"
                                    :class="[
                                        currentUrl.startsWith(
                                            '/pendaftaran/bimbingan',
                                        )
                                            ? 'bg-blue-950/40 font-semibold text-blue-400'
                                            : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200',
                                    ]"
                                >
                                    Bimbingan TA
                                </Link>
                                <Link
                                    href="/pendaftaran/sempro"
                                    class="block rounded-lg px-3 py-1.5 transition-colors"
                                    :class="[
                                        currentUrl.startsWith(
                                            '/pendaftaran/sempro',
                                        )
                                            ? 'bg-blue-950/40 font-semibold text-blue-400'
                                            : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200',
                                    ]"
                                >
                                    Seminar Proposal
                                </Link>
                                <Link
                                    href="/pendaftaran/sidang"
                                    class="block rounded-lg px-3 py-1.5 transition-colors"
                                    :class="[
                                        currentUrl.startsWith(
                                            '/pendaftaran/sidang',
                                        )
                                            ? 'bg-blue-950/40 font-semibold text-blue-400'
                                            : 'text-slate-400 hover:bg-slate-800/40 hover:text-slate-200',
                                    ]"
                                >
                                    Sidang TA
                                </Link>
                            </div>
                        </div>

                        <!-- Prosedur TA -->
                        <Link
                            href="/prosedur"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/prosedur')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <BookOpen class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">Prosedur TA</span>
                        </Link>

                        <!-- Pendaftar & Jadwal (Standalone top-level menu) -->
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
                            <span v-if="!sidebarCollapsed"
                                >Pendaftar & Jadwal</span
                            >
                        </Link>

                        <!-- Katalog TA -->
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <FileSpreadsheet class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">Katalog TA</span>
                        </Link>

                        <!-- Panduan Website -->
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
                            <span v-if="!sidebarCollapsed"
                                >Panduan Website</span
                            >
                        </Link>
                    </template>

                    <!-- ===================================== -->
                    <!-- 2. DOSEN (PEMBIMBING & PENGUJI) MENU  -->
                    <!-- ===================================== -->
                    <template v-else-if="userRole === 'dosen'">
                        <!-- Dashboard Dosen -->
                        <Link
                            href="/dosen/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl === '/dosen/dashboard'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Dashboard Dosen</span
                            >
                        </Link>

                        <!-- Bimbingan Mahasiswa -->
                        <Link
                            href="/dosen/bimbingan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/dosen/bimbingan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <UserCheck class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Bimbingan (TA-04)</span
                            >
                        </Link>

                        <!-- Lembar Penilaian -->
                        <Link
                            href="/dosen/penilaian"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/dosen/penilaian')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <PenTool class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Lembar Penilaian</span
                            >
                        </Link>

                        <!-- Katalog TA -->
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <FileSpreadsheet class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">Katalog TA</span>
                        </Link>

                        <!-- Panduan Website -->
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
                            <span v-if="!sidebarCollapsed"
                                >Panduan Website</span
                            >
                        </Link>
                    </template>

                    <!-- ===================================== -->
                    <!-- 3. KAPRODI / KOORDINATOR MENU         -->
                    <!-- ===================================== -->
                    <template v-else-if="userRole === 'kaprodi'">
                        <!-- Dashboard Kaprodi -->
                        <Link
                            href="/kaprodi/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl === '/kaprodi/dashboard'
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <Home class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Dashboard Kaprodi</span
                            >
                        </Link>

                        <!-- Penjadwalan GA -->
                        <Link
                            href="/kaprodi/penjadwalan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/penjadwalan') ||
                                currentUrl.startsWith(
                                    '/koordinator/penjadwalan',
                                )
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <Cpu class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Jadwal Sempro & Sidang</span
                            >
                        </Link>

                        <!-- Monitoring Prodi -->
                        <Link
                            href="/kaprodi/monitoring"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/monitoring') ||
                                currentUrl.startsWith('/koordinator/monitoring')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <Users class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Monitoring Prodi</span
                            >
                        </Link>

                        <!-- Data Dosen & Kuota -->
                        <Link
                            href="/kaprodi/dosen"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/dosen')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <GraduationCap class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Data Dosen & Kuota</span
                            >
                        </Link>

                        <!-- Persetujuan Akademik -->
                        <Link
                            href="/kaprodi/persetujuan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/kaprodi/persetujuan')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <ShieldCheck class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed"
                                >Persetujuan Akademik</span
                            >
                        </Link>

                        <!-- Katalog TA -->
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold transition-all"
                            :class="[
                                currentUrl.startsWith('/katalog')
                                    ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30'
                                    : 'text-slate-400 hover:bg-slate-800/70 hover:text-slate-200',
                            ]"
                        >
                            <FileSpreadsheet class="h-4 w-4 shrink-0" />
                            <span v-if="!sidebarCollapsed">Katalog TA</span>
                        </Link>

                        <!-- Panduan Website -->
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
                            <span v-if="!sidebarCollapsed"
                                >Panduan Website</span
                            >
                        </Link>
                    </template>

                    <!-- ===================================== -->
                    <!-- 4. TENDIK (ADMIN AKADEMIK) MENU       -->
                    <!-- ===================================== -->
                    <template v-else-if="userRole === 'tendik'">
                        <!-- Dashboard Tendik -->
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
                            <span v-if="!sidebarCollapsed"
                                >Dashboard Tendik</span
                            >
                        </Link>

                        <!-- Verifikasi Berkas -->
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
                            <span v-if="!sidebarCollapsed"
                                >Verifikasi Berkas</span
                            >
                        </Link>

                        <!-- Jadwal Ruangan -->
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
                            <span v-if="!sidebarCollapsed">Jadwal Ruangan</span>
                        </Link>

                        <!-- Arsip & SK -->
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
                            <span v-if="!sidebarCollapsed"
                                >Berita Acara & SK</span
                            >
                        </Link>

                        <!-- Manajemen Akun -->
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
                            <span v-if="!sidebarCollapsed"
                                >Kelola Mahasiswa</span
                            >
                        </Link>

                        <!-- Panduan Website -->
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
                            <span v-if="!sidebarCollapsed"
                                >Panduan Website</span
                            >
                        </Link>
                    </template>
                </nav>
            </div>
        </aside>

        <!-- 2. MAIN APP CONTENT CONTAINER: Terkunci h-screen, header fixed di atas -->
        <div class="flex h-screen min-w-0 flex-1 flex-col overflow-hidden">
            <!-- TOP NAVBAR: Tinggi tetap h-16, tidak ikut ter-scroll ke mana-mana -->
            <header
                class="z-40 flex h-16 w-full shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-md lg:px-8 dark:border-slate-800 dark:bg-[#0E1626]/95"
            >
                <!-- Left: Hamburger mobile toggle + Page Title + Active Role Pill -->
                <div class="flex items-center gap-3 sm:gap-4">
                    <button
                        type="button"
                        class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800"
                        @click="mobileMenuOpen = !mobileMenuOpen"
                    >
                        <Menu class="h-5 w-5" />
                    </button>

                    <div>
                        <h2
                            class="text-base font-bold tracking-tight text-slate-900 sm:text-lg dark:text-white"
                        >
                            {{ title }}
                        </h2>
                    </div>

                    <!-- Active Role Chip Badge (No Toggle Switcher!) -->
                    <span
                        class="hidden rounded-full border px-2.5 py-0.5 text-[10px] font-bold sm:inline-block"
                        :class="roleInfo.color"
                    >
                        {{ roleInfo.badge }}
                    </span>
                </div>

                <!-- Right: Actions (Theme, Notification, Profile, Logout) -->
                <div class="flex items-center gap-3">
                    <!-- Light / Dark Mode Toggle Pill -->
                    <button
                        type="button"
                        class="flex items-center gap-2 rounded-full border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                        @click="toggleDarkMode"
                    >
                        <Sun
                            v-if="!isDarkMode"
                            class="h-3.5 w-3.5 text-amber-500"
                        />
                        <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                        <span class="hidden sm:inline">{{
                            isDarkMode ? 'Dark mode' : 'Light mode'
                        }}</span>
                    </button>

                    <!-- Notification Bell & Popup Modal -->
                    <div class="relative">
                        <button
                            type="button"
                            class="relative flex h-9 w-9 items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            :class="{
                                'bg-blue-50 text-blue-600 ring-2 ring-blue-500/20 dark:bg-slate-800 dark:text-blue-400':
                                    showNotificationModal,
                            }"
                            title="Notifikasi"
                            @click="
                                showNotificationModal = !showNotificationModal
                            "
                        >
                            <Bell class="h-4 w-4" />
                            <span
                                v-if="unreadCount > 0"
                                class="absolute top-2 right-2 h-2 w-2 rounded-full bg-rose-500 ring-2 ring-white dark:ring-slate-900"
                            />
                        </button>

                        <!-- Backdrop Overlay for Click Outside -->
                        <div
                            v-if="showNotificationModal"
                            class="fixed inset-0 z-40"
                            @click="showNotificationModal = false"
                        />

                        <!-- Popup Modal Dropdown -->
                        <div
                            v-if="showNotificationModal"
                            class="absolute right-0 z-50 mt-3 w-80 rounded-2xl border border-slate-200/90 bg-white shadow-2xl transition-all duration-200 sm:w-96 dark:border-slate-800 dark:bg-[#0E1626]"
                        >
                            <!-- Modal Header -->
                            <div
                                class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5 dark:border-slate-800/80"
                            >
                                <div class="flex items-center gap-2.5">
                                    <span
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        Notifikasi
                                    </span>
                                    <span
                                        v-if="unreadCount > 0"
                                        class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-extrabold text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                                    >
                                        {{ unreadCount }} Baru
                                    </span>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button
                                        v-if="unreadCount > 0"
                                        type="button"
                                        class="text-[11px] font-semibold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400"
                                        @click="markAllAsRead"
                                    >
                                        Tandai dibaca
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
                                        @click="showNotificationModal = false"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Notification List -->
                            <div
                                class="max-h-80 divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800/60"
                            >
                                <div
                                    v-for="item in currentNotifications"
                                    :key="item.id"
                                    class="flex cursor-pointer items-start gap-3 p-3.5 transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                                    :class="{
                                        'bg-blue-50/30 dark:bg-blue-950/20':
                                            !item.read,
                                    }"
                                    @click="markAsRead(item)"
                                >
                                    <!-- Type Icon -->
                                    <div
                                        class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                                        :class="[
                                            item.type === 'success'
                                                ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400'
                                                : item.type === 'info'
                                                  ? 'bg-blue-100 text-blue-600 dark:bg-blue-950 dark:text-blue-400'
                                                  : item.type === 'warning'
                                                    ? 'bg-amber-100 text-amber-600 dark:bg-amber-950 dark:text-amber-400'
                                                    : 'bg-purple-100 text-purple-600 dark:bg-purple-950 dark:text-purple-400',
                                        ]"
                                    >
                                        <FileCheck
                                            v-if="item.type === 'success'"
                                            class="h-4 w-4"
                                        />
                                        <FileText
                                            v-else-if="item.type === 'info'"
                                            class="h-4 w-4"
                                        />
                                        <Clock
                                            v-else-if="item.type === 'warning'"
                                            class="h-4 w-4"
                                        />
                                        <Calendar v-else class="h-4 w-4" />
                                    </div>

                                    <!-- Text Content -->
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex items-center justify-between gap-1"
                                        >
                                            <p
                                                class="truncate text-xs font-bold"
                                                :class="
                                                    item.read
                                                        ? 'text-slate-700 dark:text-slate-300'
                                                        : 'text-slate-900 dark:text-white'
                                                "
                                            >
                                                {{ item.title }}
                                            </p>
                                            <span
                                                v-if="!item.read"
                                                class="h-2 w-2 shrink-0 rounded-full bg-blue-600"
                                            />
                                        </div>
                                        <p
                                            class="mt-0.5 line-clamp-2 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400"
                                        >
                                            {{ item.message }}
                                        </p>
                                        <span
                                            class="mt-1 block text-[10px] text-slate-400 dark:text-slate-500"
                                        >
                                            {{ item.time }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Empty State -->
                                <div
                                    v-if="currentNotifications.length === 0"
                                    class="flex flex-col items-center justify-center p-8 text-center text-slate-400"
                                >
                                    <div
                                        class="mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800"
                                    >
                                        <Bell class="h-6 w-6" />
                                    </div>
                                    <p
                                        class="text-xs font-bold text-slate-700 dark:text-slate-300"
                                    >
                                        Tidak ada notifikasi
                                    </p>
                                    <p
                                        class="mt-0.5 text-[11px] text-slate-400"
                                    >
                                        Semua pemberitahuan aktivitas Anda telah
                                        bersih.
                                    </p>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div
                                v-if="currentNotifications.length > 0"
                                class="flex items-center justify-between rounded-b-2xl border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 dark:border-slate-800/80 dark:bg-slate-900/40"
                            >
                                <button
                                    type="button"
                                    class="flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400"
                                    @click="clearAllNotifications"
                                >
                                    <Trash2 class="h-3 w-3" /> Bersihkan
                                </button>
                                <button
                                    type="button"
                                    class="text-[11px] font-bold text-blue-600 hover:underline dark:text-blue-400"
                                    @click="showNotificationModal = false"
                                >
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Pill -->
                    <Link
                        href="/profile"
                        class="flex items-center gap-2.5 pl-1 transition-opacity hover:opacity-90"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700 dark:bg-slate-700 dark:text-slate-200"
                        >
                            {{ profileData.initials }}
                        </div>
                        <div class="hidden text-left sm:block">
                            <span
                                class="block text-xs font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ profileData.name }}
                            </span>
                            <span class="block text-[10px] text-slate-400">
                                {{ profileData.idNumber }}
                            </span>
                        </div>
                    </Link>

                    <!-- Top-Right Logout Button (Satu-satunya tombol logout) -->
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-bold text-slate-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:border-rose-900/50 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                        title="Keluar (Log Out)"
                        @click="handleLogout"
                    >
                        <LogOut class="h-3.5 w-3.5" />
                        <span class="hidden sm:inline">Log Out</span>
                    </button>
                </div>
            </header>

            <!-- Mobile Menu Drawer -->
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs lg:hidden"
                @click="mobileMenuOpen = false"
            >
                <div
                    class="flex h-full w-72 flex-col justify-between overflow-y-auto bg-[#0E1626] p-5 shadow-2xl"
                    @click.stop
                >
                    <div>
                        <div
                            class="flex items-center justify-between border-b border-slate-800 pb-4"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-600 text-white"
                                >
                                    <GraduationCap class="h-4 w-4" />
                                </div>
                                <span class="text-sm font-bold text-white"
                                    >SIPTA IF</span
                                >
                            </div>
                            <button
                                type="button"
                                class="p-1 text-slate-400 hover:text-white"
                                @click="mobileMenuOpen = false"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>

                        <!-- Mobile Navigation Links BY ROLE -->
                        <nav class="mt-4 space-y-2 text-xs">
                            <p
                                class="text-[10px] font-bold tracking-wider uppercase"
                                :class="roleInfo.color"
                            >
                                {{ roleInfo.title }}
                            </p>

                            <!-- Mahasiswa Mobile -->
                            <template v-if="userRole === 'mahasiswa'">
                                <Link
                                    href="/dashboard"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Home class="h-4 w-4" /> Dashboard
                                </Link>
                                <Link
                                    href="/pendaftaran/judul"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <FileText class="h-4 w-4" /> Pengajuan Judul
                                </Link>
                                <Link
                                    href="/pendaftaran/bimbingan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <FileCheck class="h-4 w-4" /> Bimbingan TA
                                </Link>
                                <Link
                                    href="/pendaftaran/sempro"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <FileSpreadsheet class="h-4 w-4" /> Seminar
                                    Proposal
                                </Link>
                                <Link
                                    href="/pendaftaran/sidang"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Award class="h-4 w-4" /> Sidang TA
                                </Link>
                                <Link
                                    href="/pendaftaran/jadwal"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Calendar class="h-4 w-4" /> Pendaftar &
                                    Jadwal
                                </Link>
                                <Link
                                    href="/katalog"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <BookOpen class="h-4 w-4" /> Katalog TA
                                </Link>
                                <Link
                                    href="/panduan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Globe class="h-4 w-4" /> Panduan Website
                                </Link>
                            </template>

                            <!-- Dosen Mobile -->
                            <template v-else-if="userRole === 'dosen'">
                                <Link
                                    href="/dosen/dashboard"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Home class="h-4 w-4" /> Dashboard Dosen
                                </Link>
                                <Link
                                    href="/dosen/bimbingan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <UserCheck class="h-4 w-4" /> Bimbingan
                                    Mahasiswa
                                </Link>
                                <Link
                                    href="/dosen/penilaian"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <PenTool class="h-4 w-4" /> Lembar Penilaian
                                </Link>
                                <Link
                                    href="/katalog"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <BookOpen class="h-4 w-4" /> Katalog TA
                                </Link>
                            </template>

                            <!-- Kaprodi Mobile -->
                            <template v-else-if="userRole === 'kaprodi'">
                                <Link
                                    href="/kaprodi/dashboard"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Home class="h-4 w-4" /> Dashboard Kaprodi
                                </Link>
                                <Link
                                    href="/kaprodi/penjadwalan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Cpu class="h-4 w-4" /> Jadwal Sempro &
                                    Sidang
                                </Link>
                                <Link
                                    href="/kaprodi/monitoring"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Users class="h-4 w-4" /> Monitoring Prodi
                                </Link>
                                <Link
                                    href="/kaprodi/persetujuan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <ShieldCheck class="h-4 w-4" /> Persetujuan
                                    Akademik
                                </Link>
                                <Link
                                    href="/katalog"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <BookOpen class="h-4 w-4" /> Katalog TA
                                </Link>
                            </template>

                            <!-- Tendik Mobile -->
                            <template v-else-if="userRole === 'tendik'">
                                <Link
                                    href="/tendik/dashboard"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Home class="h-4 w-4" /> Dashboard Tendik
                                </Link>
                                <Link
                                    href="/tendik/verifikasi"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <FileCheck class="h-4 w-4" /> Verifikasi
                                    Berkas
                                </Link>
                                <Link
                                    href="/tendik/ruangan"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Building2 class="h-4 w-4" /> Jadwal Ruangan
                                </Link>
                                <Link
                                    href="/tendik/arsip"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <Printer class="h-4 w-4" /> Berita Acara &
                                    SK
                                </Link>
                                <Link
                                    href="/tendik/mahasiswa"
                                    class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                                    @click="mobileMenuOpen = false"
                                >
                                    <KeyRound class="h-4 w-4" /> Kelola
                                    Mahasiswa
                                </Link>
                            </template>
                        </nav>
                    </div>

                    <!-- Mobile Theme Toggle & Logout -->
                    <div class="space-y-2 border-t border-slate-800 pt-4">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 transition-colors hover:bg-slate-800"
                            @click="toggleDarkMode"
                        >
                            <span class="flex items-center gap-3">
                                <Sun
                                    v-if="!isDarkMode"
                                    class="h-4 w-4 text-amber-500"
                                />
                                <Moon v-else class="h-4 w-4 text-blue-400" />
                                Mode Tampilan
                            </span>
                            <span
                                class="rounded-md bg-slate-800 px-2 py-0.5 text-[10px] font-bold text-slate-400 uppercase"
                            >
                                {{ isDarkMode ? 'Dark' : 'Light' }}
                            </span>
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold text-rose-400 transition-colors hover:bg-slate-800"
                            @click="handleLogout"
                        >
                            <LogOut class="h-4 w-4" /> Log Out
                        </button>
                    </div>
                </div>
            </div>

            <!-- 3. SCROLLABLE CONTENT WRAPPER: Hanya area ini yang bergulir saat konten panjang -->
            <div
                class="flex flex-1 flex-col justify-between overflow-x-hidden overflow-y-auto"
            >
                <!-- MAIN PAGE CONTENT BODY -->
                <main class="flex-1 p-4 lg:p-8">
                    <slot />
                </main>

                <!-- FOOTER -->
                <footer
                    class="shrink-0 border-t border-slate-200/80 bg-white/50 px-4 py-4 text-center text-xs text-slate-500 sm:px-8 dark:border-slate-800 dark:bg-[#0E1626]/50 dark:text-slate-400"
                >
                    <div
                        class="flex flex-col items-center justify-between gap-2 sm:flex-row"
                    >
                        <div class="flex items-center gap-4">
                            <Link
                                href="/panduan"
                                class="transition-colors hover:text-blue-600"
                                >Panduan Layanan</Link
                            >
                            <span>•</span>
                            <Link
                                href="/prosedur"
                                class="transition-colors hover:text-blue-600"
                                >SOP & Prosedur TA</Link
                            >
                        </div>
                        <p>© 2026 Program Studi Informatika ITK • SIPTA IF</p>
                    </div>
                </footer>
            </div>
        </div>

        <!-- 3. FLOATING ACADEMIC AI CHATBOT WIDGET -->
        <AcademicChatbot />
    </div>
</template>
