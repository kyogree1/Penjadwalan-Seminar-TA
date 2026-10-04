<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    Bell,
    Calendar,
    Clock,
    FileCheck,
    FileText,
    LogOut,
    Menu,
    Moon,
    Sun,
    Trash2,
    X,
} from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';
import { useNotifications } from '@/composables/useNotifications';
import { useTheme } from '@/composables/useTheme';
import type { NotificationItem } from '@/types/models';

defineProps<{
    title?: string;
}>();

const emit = defineEmits<{
    (e: 'toggle-mobile-menu'): void;
}>();

const { role: userRole, roleInfo, profileData } = useAuth();
const { isDarkMode, toggleDarkMode } = useTheme();
const {
    getNotificationsForRole,
    getUnreadCount,
    markAsRead,
    markAllAsRead,
    notifications,
} = useNotifications();

const showNotificationModal = ref(false);

const currentNotifications = computed(() =>
    getNotificationsForRole(userRole.value),
);
const unreadCount = computed(() => getUnreadCount(userRole.value));

const handleMarkAsRead = (item: NotificationItem) => {
    markAsRead(userRole.value, item.id);
    if (item.link) {
        showNotificationModal.value = false;
        router.visit(item.link);
    }
};

const handleMarkAllAsRead = () => {
    markAllAsRead(userRole.value);
};

const clearAllNotifications = () => {
    notifications.value[userRole.value] = [];
    showNotificationModal.value = false;
};

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <header
        class="z-40 flex h-16 w-full shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-md lg:px-8 dark:border-slate-800 dark:bg-[#0E1626]/95"
    >
        <!-- Left: Hamburger mobile toggle + Page Title + Active Role Pill -->
        <div class="flex items-center gap-3 sm:gap-4">
            <button
                type="button"
                class="cursor-pointer rounded-lg p-2 text-slate-500 hover:bg-slate-100 lg:hidden dark:text-slate-400 dark:hover:bg-slate-800"
                @click="emit('toggle-mobile-menu')"
            >
                <Menu class="h-5 w-5" />
            </button>

            <div>
                <h2
                    class="text-base font-bold tracking-tight text-slate-900 sm:text-lg dark:text-white"
                >
                    {{ title || 'Dashboard' }}
                </h2>
            </div>

            <!-- Active Role Chip Badge -->
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
                class="flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-semibold text-slate-700 transition-all hover:bg-slate-200 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                @click="toggleDarkMode"
            >
                <Sun v-if="!isDarkMode" class="h-3.5 w-3.5 text-amber-500" />
                <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                <span class="hidden sm:inline">{{
                    isDarkMode ? 'Dark mode' : 'Light mode'
                }}</span>
            </button>

            <!-- Notification Bell & Popup Dropdown -->
            <div class="relative">
                <button
                    type="button"
                    class="relative flex h-9 w-9 cursor-pointer items-center justify-center rounded-full text-slate-600 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                    :class="{
                        'bg-blue-50 text-blue-600 ring-2 ring-blue-500/20 dark:bg-slate-800 dark:text-blue-400':
                            showNotificationModal,
                    }"
                    title="Notifikasi"
                    @click="showNotificationModal = !showNotificationModal"
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
                    <!-- Header -->
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
                                class="cursor-pointer text-[11px] font-semibold text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400"
                                @click="handleMarkAllAsRead"
                            >
                                Tandai dibaca
                            </button>
                            <button
                                type="button"
                                class="cursor-pointer rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-300"
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
                                'bg-blue-50/30 dark:bg-blue-950/20': !item.read,
                            }"
                            @click="handleMarkAsRead(item)"
                        >
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
                            <p class="mt-0.5 text-[11px] text-slate-400">
                                Semua pemberitahuan aktivitas Anda telah bersih.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div
                        v-if="currentNotifications.length > 0"
                        class="flex items-center justify-between rounded-b-2xl border-t border-slate-100 bg-slate-50/50 px-4 py-2.5 dark:border-slate-800/80 dark:bg-slate-900/40"
                    >
                        <button
                            type="button"
                            class="flex cursor-pointer items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400"
                            @click="clearAllNotifications"
                        >
                            <Trash2 class="h-3 w-3" /> Bersihkan
                        </button>
                        <button
                            type="button"
                            class="cursor-pointer text-[11px] font-bold text-blue-600 hover:underline dark:text-blue-400"
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

            <!-- Logout Button -->
            <button
                type="button"
                class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-100/80 px-3 py-1.5 text-xs font-bold text-slate-600 transition-all hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:border-rose-900/50 dark:hover:bg-rose-950/40 dark:hover:text-rose-400"
                title="Keluar (Log Out)"
                @click="handleLogout"
            >
                <LogOut class="h-3.5 w-3.5" />
                <span class="hidden sm:inline">Log Out</span>
            </button>
        </div>
    </header>
</template>
