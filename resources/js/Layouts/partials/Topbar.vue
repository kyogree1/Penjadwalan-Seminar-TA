<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Bell, CheckCheck, ExternalLink, LogOut, Menu, Moon, Sun } from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';

const props = defineProps<{
    title?: string;
}>();

const emit = defineEmits<{
    (e: 'toggle-mobile-menu'): void;
}>();

const { profileData } = useAuth();
const { isDarkMode, toggleDarkMode } = useTheme();

const page = usePage();
const showNotificationDropdown = ref(false);
const notifications = computed(() => (page.props.notifications as any[]) || []);
const unreadCount = computed(() => (page.props.unreadNotificationsCount as number) || 0);

const markAsRead = (id: number) => {
    router.patch(`/notifications/${id}/read`, {}, { preserveScroll: true });
};

const markAllRead = () => {
    router.post('/notifications/read-all', {}, { preserveScroll: true });
};

const pageTitle = computed(() => {
    if (props.title && props.title !== 'Dashboard') {
        return props.title;
    }

    let path = '';
    try {
        if (typeof usePage === 'function') {
            const page = usePage();
            path = page?.url ?? '';
        }
    } catch {
        path = '';
    }

    if (!path && typeof window !== 'undefined') {
        path = window.location.pathname;
    }
    path = path.split('?')[0];

    // Rute Mahasiswa
    if (path === '/dashboard') return 'Dashboard Mahasiswa';
    if (path.startsWith('/pendaftaran/judul')) return 'Pengajuan Judul';
    if (path.startsWith('/pendaftaran/bimbingan')) return 'Logbook Bimbingan';
    if (path.startsWith('/pendaftaran/jadwal')) return 'Pendaftaran & Jadwal';
    if (path.startsWith('/pendaftaran/sempro')) return 'Seminar Proposal';
    if (path.startsWith('/pendaftaran/sidang')) return 'Sidang Tugas Akhir';

    // Rute Tendik
    if (path === '/tendik/dashboard') return 'Dashboard Tendik';
    if (path.startsWith('/tendik/verifikasi')) return 'Verifikasi Berkas';
    if (path.startsWith('/tendik/arsip')) return 'Arsip Dokumen';
    if (path.startsWith('/tendik/mahasiswa')) return 'Data Mahasiswa';

    // Rute Dosen
    if (path === '/dosen/dashboard') return 'Dashboard Dosen';
    if (path.startsWith('/dosen/bimbingan')) return 'Bimbingan Mahasiswa';
    if (path.startsWith('/dosen/penilaian'))
        return 'Penilaian Seminar & Sidang';

    // Rute Kaprodi / Koordinator
    if (path === '/kaprodi/dashboard') return 'Dashboard Kaprodi';
    if (path.startsWith('/kaprodi/persetujuan')) return 'Persetujuan Pengajuan';
    if (path.startsWith('/kaprodi/penjadwalan')) return 'Penjadwalan Ujian';
    if (path.startsWith('/kaprodi/dosen')) return 'Master Data Dosen';
    if (
        path.startsWith('/kaprodi/monitoring') ||
        path.startsWith('/koordinator/monitoring')
    )
        return 'Monitoring Kemajuan TA';

    // Rute Umum
    if (path.startsWith('/profile')) return 'Profil Akun';
    if (path.startsWith('/katalog')) return 'Katalog Publik';
    if (path.startsWith('/panduan')) return 'Panduan Layanan';
    if (path.startsWith('/prosedur')) return 'SOP & Prosedur TA';

    return props.title || 'Dashboard';
});

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <header
        class="z-40 flex h-16 w-full shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-md lg:px-8 dark:border-slate-800 dark:bg-[#0E1626]/95"
    >
        <!-- Left: Mobile navigation and page title -->
        <div class="flex min-w-0 items-center gap-2 sm:gap-4">
            <button
                type="button"
                class="cursor-pointer rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800"
                aria-label="Buka navigasi"
                @click="emit('toggle-mobile-menu')"
            >
                <Menu class="h-5 w-5" />
            </button>

            <div>
                <h2
                    class="truncate text-base font-bold tracking-tight text-slate-900 sm:text-lg dark:text-white"
                >
                    {{ pageTitle }}
                </h2>
            </div>
        </div>

        <!-- Right: Actions (Theme, Notification, Profile, Logout) -->
        <div class="flex shrink-0 items-center gap-1 sm:gap-3">
            <!-- Light / Dark Mode Toggle Pill -->
            <button
                type="button"
                class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                :aria-label="
                    isDarkMode
                        ? 'Beralih ke mode terang'
                        : 'Beralih ke mode gelap'
                "
                @click="toggleDarkMode"
            >
                <Sun v-if="!isDarkMode" class="h-3.5 w-3.5 text-amber-500" />
                <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                <span class="hidden sm:inline">{{
                    isDarkMode ? 'Dark mode' : 'Light mode'
                }}</span>
            </button>

            <!-- Notification Bell with Dropdown -->
            <div class="relative">
                <button
                    type="button"
                    class="relative flex h-8 w-8 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-slate-100/80 text-slate-600 transition-colors hover:bg-slate-200 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-700"
                    aria-label="Notifikasi"
                    title="Notifikasi"
                    @click="showNotificationDropdown = !showNotificationDropdown"
                >
                    <Bell class="h-4 w-4" />
                    <span
                        v-if="unreadCount > 0"
                        class="absolute -top-1 -right-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow-xs"
                    >
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </button>

                <!-- Dropdown Panel -->
                <div
                    v-if="showNotificationDropdown"
                    class="absolute right-0 z-50 mt-2 w-80 rounded-2xl border border-slate-200 bg-white p-3 shadow-xl dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="mb-2 flex items-center justify-between border-b border-slate-100 pb-2 dark:border-slate-800">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-100">Notifikasi</span>
                            <span
                                v-if="unreadCount > 0"
                                class="rounded-full bg-blue-100 px-1.5 py-0.2 text-[10px] font-semibold text-blue-600 dark:bg-blue-900/40 dark:text-blue-300"
                            >
                                {{ unreadCount }} baru
                            </span>
                        </div>
                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            class="text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400"
                            @click="markAllRead"
                        >
                            Tandai semua dibaca
                        </button>
                    </div>

                    <div v-if="notifications.length === 0" class="py-6 text-center text-xs text-slate-400">
                        Tidak ada notifikasi baru
                    </div>

                    <div v-else class="max-h-72 space-y-2 overflow-y-auto pr-1 text-left">
                        <div
                            v-for="notif in notifications"
                            :key="notif.id"
                            class="rounded-xl border p-2.5 transition-colors"
                            :class="
                                notif.read
                                    ? 'border-slate-100 bg-slate-50/50 dark:border-slate-800/60 dark:bg-slate-900/30'
                                    : 'border-blue-100 bg-blue-50/50 dark:border-blue-900/40 dark:bg-blue-950/20'
                            "
                        >
                            <div class="flex items-start justify-between gap-2">
                                <p class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                    {{ notif.title }}
                                </p>
                                <span class="text-[10px] text-slate-400">{{ notif.time }}</span>
                            </div>
                            <p class="mt-1 text-[11px] text-slate-600 dark:text-slate-400">
                                {{ notif.message }}
                            </p>
                            <div class="mt-2 flex items-center justify-between">
                                <Link
                                    v-if="notif.link"
                                    :href="notif.link"
                                    class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 hover:underline dark:text-blue-400"
                                    @click="showNotificationDropdown = false"
                                >
                                    Buka <ExternalLink class="h-3 w-3" />
                                </Link>
                                <button
                                    v-if="!notif.read"
                                    type="button"
                                    class="ml-auto inline-flex items-center gap-1 text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                    @click="markAsRead(notif.id)"
                                >
                                    <CheckCheck class="h-3 w-3" /> Tandai dibaca
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Profile Pill -->
            <Link
                href="/profile"
                aria-label="Profil akun"
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

            <!-- Logout Button -->
            <button
                type="button"
                class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-bold text-slate-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:border-rose-900/50 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                aria-label="Keluar (Log Out)"
                title="Keluar (Log Out)"
                @click="handleLogout"
            >
                <LogOut class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Log Out</span>
            </button>
        </div>
    </header>
</template>
