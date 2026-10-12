<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { Bell, LogOut, Menu, Moon, Sun } from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';

defineProps<{
    title?: string;
}>();

const emit = defineEmits<{
    (e: 'toggle-mobile-menu'): void;
}>();

const { roleInfo, profileData } = useAuth();
const { isDarkMode, toggleDarkMode } = useTheme();
const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <header
        class="z-40 flex h-16 w-full shrink-0 items-center justify-between border-b border-slate-200/80 bg-white/95 px-4 backdrop-blur-md lg:px-8 dark:border-slate-800 dark:bg-[#0E1626]/95"
    >
        <!-- Left: Hamburger mobile toggle + Page Title + Active Role Pill -->
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

            <span
                aria-label="Notifikasi server belum tersedia"
                title="Notifikasi server belum tersedia"
                class="text-slate-400"
                ><Bell class="h-4 w-4"
            /></span>

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
