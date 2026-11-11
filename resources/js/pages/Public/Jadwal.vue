<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Calendar as CalendarIcon,
    GraduationCap,
    LayoutDashboard,
    LogIn,
    Moon,
    Search,
    Sun,
} from 'lucide-vue-next';

import { useTheme } from '@/composables/useTheme';
import PublicSchedules, {
    type PeriodItem,
    type ScheduleItem,
} from '@/Components/PublicSchedules.vue';

const props = withDefaults(
    defineProps<{
        publicSchedules: ScheduleItem[];
        publicPeriods?: PeriodItem[];
    }>(),
    {
        publicPeriods: () => [],
    },
);

const { isDarkMode, toggleDarkMode, initializeTheme } = useTheme();

const page = usePage();
const authUser = computed(
    () =>
        (
            page.props.auth as
                | { user?: { name?: string; role?: string } }
                | undefined
        )?.user,
);

const portalLink = computed(() => {
    if (!authUser.value) return '/login';
    const r = authUser.value.role;
    if (r === 'kaprodi' || r === 'koordinator') return '/kaprodi/jadwal';
    if (r === 'dosen') return '/dosen/dashboard';
    if (r === 'tendik') return '/tendik/dashboard';
    return '/dashboard';
});

onMounted(() => {
    initializeTheme();
});

const selectedType = ref<'all' | 'sempro' | 'sidang'>('all');
const searchQuery = ref('');

const filteredSchedules = computed(() => {
    let list = props.publicSchedules;

    if (selectedType.value !== 'all') {
        list = list.filter((s) => s.type === selectedType.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter((s) => {
            const studentMatch = s.student_name?.toLowerCase().includes(q);
            const titleMatch = s.title?.toLowerCase().includes(q);
            const supervisorMatch = s.supervisors.some((sup) =>
                sup.toLowerCase().includes(q),
            );
            const examinerMatch = s.examiners.some((ex) =>
                ex.toLowerCase().includes(q),
            );
            return (
                studentMatch || titleMatch || supervisorMatch || examinerMatch
            );
        });
    }

    return list;
});
</script>

<template>
    <div
        class="min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased dark:bg-[#080D1A] dark:text-slate-100"
    >
        <Head title="Jadwal Seminar & Sidang - SIPTA IF" />

        <!-- Top Header Bar -->
        <header
            class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/90 backdrop-blur-md dark:border-slate-800 dark:bg-[#0E1626]/90"
        >
            <div
                class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-xs"
                    >
                        <GraduationCap class="h-6 w-6" />
                    </div>
                    <div>
                        <Link
                            href="/"
                            class="text-base font-extrabold tracking-tight text-slate-900 hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                        >
                            SIPTA IF
                        </Link>
                        <p
                            class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            Institut Teknologi Kalimantan
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                        @click="toggleDarkMode"
                        :title="
                            isDarkMode
                                ? 'Beralih ke mode terang'
                                : 'Beralih ke mode gelap'
                        "
                    >
                        <Sun
                            v-if="!isDarkMode"
                            class="h-3.5 w-3.5 text-amber-500"
                        />
                        <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                        <span class="hidden sm:inline">{{
                            isDarkMode ? 'Dark' : 'Light'
                        }}</span>
                    </button>

                    <Link
                        :href="portalLink"
                        class="flex items-center gap-1.5 rounded-xl bg-[#0C1A40] px-3.5 py-1.5 text-xs font-bold text-white shadow-xs transition-all hover:bg-slate-900 dark:bg-blue-600 dark:hover:bg-blue-500"
                    >
                        <LayoutDashboard v-if="authUser" class="h-3.5 w-3.5" />
                        <LogIn v-else class="h-3.5 w-3.5" />
                        <span>{{
                            authUser ? 'Kembali ke Portal' : 'Masuk Portal'
                        }}</span>
                    </Link>
                </div>
            </div>
        </header>

        <!-- Page Hero -->
        <div class="mx-auto max-w-7xl px-4 pt-8 pb-4 sm:px-6 lg:px-8">
            <div
                class="flex flex-col justify-between gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end dark:border-slate-800"
            >
                <div class="space-y-1">
                    <div
                        class="inline-flex items-center gap-1.5 rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                    >
                        <CalendarIcon class="h-3.5 w-3.5" />
                        <span>Jadwal Terbit Resmi</span>
                    </div>
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                    >
                        Jadwal Seminar Proposal & Sidang TA
                    </h1>
                    <p
                        class="text-xs text-slate-500 sm:text-sm dark:text-slate-400"
                    >
                        Daftar jadwal seminar proposal dan sidang tugas akhir
                        mahasiswa Informatika ITK.
                    </p>
                </div>

                <!-- Filters: Type & Search -->
                <div class="flex flex-wrap items-center gap-2">
                    <div
                        class="inline-flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-2xs dark:border-slate-800 dark:bg-slate-900"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                selectedType === 'all'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                            "
                            @click="selectedType = 'all'"
                        >
                            Semua ({{ publicSchedules.length }})
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                selectedType === 'sempro'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                            "
                            @click="selectedType = 'sempro'"
                        >
                            Sempro ({{
                                publicSchedules.filter(
                                    (s) => s.type === 'sempro',
                                ).length
                            }})
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 transition-colors"
                            :class="
                                selectedType === 'sidang'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                            "
                            @click="selectedType = 'sidang'"
                        >
                            Sidang TA ({{
                                publicSchedules.filter(
                                    (s) => s.type === 'sidang',
                                ).length
                            }})
                        </button>
                    </div>

                    <div class="relative min-w-[200px]">
                        <Search
                            class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                        />
                        <input
                            v-model="searchQuery"
                            type="search"
                            placeholder="Cari mahasiswa / dosen..."
                            class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-800 shadow-2xs placeholder:text-slate-400 focus:border-blue-500 focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Board Component -->
        <PublicSchedules
            :schedules="filteredSchedules"
            :periods="publicPeriods"
        />

        <!-- Footer -->
        <footer
            class="mt-12 border-t border-slate-200/80 bg-white px-4 py-6 text-center text-xs text-slate-500 sm:px-8 dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-400"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 sm:flex-row"
            >
                <div class="flex items-center gap-4">
                    <Link href="/panduan" class="hover:text-blue-600">
                        Panduan Layanan
                    </Link>
                    <span>|</span>
                    <Link href="/prosedur" class="hover:text-blue-600">
                        SOP & Prosedur TA
                    </Link>
                    <span>|</span>
                    <Link href="/login" class="hover:text-blue-600">
                        Masuk Portal
                    </Link>
                </div>
                <p>
                    &copy; 2026 SIPTA IF - Institut Teknologi Kalimantan. Hak
                    Cipta Dilindungi.
                </p>
            </div>
        </footer>
    </div>
</template>
