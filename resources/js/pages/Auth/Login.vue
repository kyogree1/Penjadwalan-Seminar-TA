<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, GraduationCap, Moon, Sun } from 'lucide-vue-next';

import { useTheme } from '@/composables/useTheme';
import PublicSchedules from '@/Components/PublicSchedules.vue';

const { isDarkMode, toggleDarkMode, initializeTheme } = useTheme();

onMounted(() => {
    initializeTheme();
});

const showPassword = ref(false);
defineProps<{
    flash?: { success?: string };
    publicSchedules: {
        student_name: string | null;
        title: string | null;
        type: 'sempro' | 'sidang';
        date: string;
        start_time: string;
        end_time: string;
        room: string | null;
        mode: 'offline' | 'online' | 'hybrid';
        supervisors: string[];
        examiners: string[];
    }[];
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submitLogin = () => {
    form.post('/login', {
        preserveScroll: true,
        onError: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <div
        class="min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased dark:bg-[#080D1A] dark:text-slate-100"
    >
        <Head title="Masuk - SIPTA IF" />

        <!-- Top Header: Theme Toggle -->
        <div
            class="mx-auto flex max-w-7xl justify-end px-4 pt-6 sm:px-6 lg:px-8"
        >
            <button
                type="button"
                class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs backdrop-blur-xs transition-all hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="toggleDarkMode"
                :title="
                    isDarkMode
                        ? 'Beralih ke mode terang (Light mode)'
                        : 'Beralih ke mode gelap (Dark mode)'
                "
            >
                <Sun v-if="!isDarkMode" class="h-3.5 w-3.5 text-amber-500" />
                <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                <span>{{ isDarkMode ? 'Dark mode' : 'Light mode' }}</span>
            </button>
        </div>

        <!-- Header / Hero Section (Sesuai Mockup) -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-6 lg:grid-cols-2">
                <!-- Left: Branding SIPTA IF -->
                <div class="space-y-4 text-center lg:text-left">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white lg:mx-0"
                    >
                        <GraduationCap class="h-9 w-9" />
                    </div>
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                    >
                        SIPTA IF
                    </h1>
                    <p
                        class="text-sm font-semibold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                    >
                        Sistem Informasi Tugas Akhir & Penjadwalan Seminar
                    </p>
                    <p
                        class="max-w-md text-xs leading-relaxed text-slate-500 sm:text-sm dark:text-slate-400"
                    >
                        Program Studi Informatika, Jurusan Sains dan Teknologi
                        Informasi, Institut Teknologi Kalimantan. Kelola seluruh
                        proses pendaftaran Sempro, Sidang, hingga logbook
                        bimbingan secara terpadu.
                    </p>
                </div>

                <!-- Right: Sign In Box (Sesuai Mockup) -->
                <div class="mx-auto w-full max-w-md">
                    <div
                        class="dark:bg-navy-surface rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 dark:border-slate-800"
                    >
                        <h2
                            class="text-xl font-extrabold text-slate-900 dark:text-white"
                        >
                            Sign in
                        </h2>
                        <p
                            class="mt-1 text-xs text-slate-700 dark:text-slate-300"
                        >
                            Masuk sesuai hak akses: Mahasiswa, Dosen, Kaprodi,
                            atau Tendik
                        </p>

                        <p
                            v-if="flash?.success"
                            role="status"
                            class="mt-4 text-sm"
                        >
                            {{ flash.success }}
                        </p>
                        <!-- Error alert -->
                        <div
                            v-if="form.errors.email"
                            role="alert"
                            class="mt-4 rounded-xl border border-rose-300 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/50 dark:text-rose-300"
                        >
                            {{ form.errors.email }}
                        </div>

                        <form
                            class="mt-4 space-y-4"
                            @submit.prevent="submitLogin"
                        >
                            <!-- Username -->
                            <div class="space-y-1">
                                <label
                                    for="login-identifier"
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Username / NIM / NIP / Email
                                </label>
                                <div class="relative">
                                    <input
                                        id="login-identifier"
                                        autocomplete="username"
                                        v-model="form.email"
                                        type="text"
                                        placeholder="Contoh: 11231006 atau 19850412..."
                                        required
                                        class="w-full rounded-xl border border-transparent bg-white/90 px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-hidden dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="space-y-1">
                                <label
                                    for="login-password"
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        id="login-password"
                                        autocomplete="current-password"
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        placeholder="Masukkan password"
                                        required
                                        class="w-full rounded-xl border border-transparent bg-white/90 py-2.5 pr-10 pl-3.5 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-hidden dark:bg-slate-900 dark:text-white"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-2.5 right-3 text-slate-400 hover:text-slate-600"
                                        :aria-label="
                                            showPassword
                                                ? 'Sembunyikan password'
                                                : 'Tampilkan password'
                                        "
                                        :aria-pressed="showPassword"
                                        @click="showPassword = !showPassword"
                                    >
                                        <Eye
                                            v-if="!showPassword"
                                            class="h-4 w-4"
                                        />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Forgot Password Link -->
                            <div
                                class="flex items-center justify-between text-[11px]"
                            >
                                <label
                                    class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300"
                                >
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-blue-600 shadow-xs focus:ring-blue-500"
                                    />
                                    Ingat saya
                                </label>
                                <Link
                                    href="/forgot-password"
                                    class="font-medium text-slate-600 hover:text-blue-700 dark:text-slate-400"
                                >
                                    Akses pertama / Lupa password?
                                </Link>
                            </div>

                            <!-- Login Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-xl bg-[#0C1A40] py-3 text-xs font-bold text-white shadow-lg transition-all hover:bg-slate-900 active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Memproses...</span>
                                <span v-else>Masuk ke Portal</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <PublicSchedules :schedules="publicSchedules" />

        <!-- Footer -->
        <footer
            class="mt-12 border-t border-slate-200/80 bg-white px-4 py-6 text-center text-xs text-slate-500 sm:px-8 dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-400"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 sm:flex-row"
            >
                <div class="flex items-center gap-4">
                    <Link href="/panduan" class="hover:text-blue-600"
                        >Panduan Layanan</Link
                    >
                    <span>|</span>
                    <Link href="/prosedur" class="hover:text-blue-600"
                        >SOP & Prosedur TA</Link
                    >
                    <span>|</span>
                    <Link href="/katalog" class="hover:text-blue-600"
                        >Katalog Publik</Link
                    >
                </div>
                <p>© 2026 Informatika ITK • SIPTA IF</p>
            </div>
        </footer>
    </div>
</template>
