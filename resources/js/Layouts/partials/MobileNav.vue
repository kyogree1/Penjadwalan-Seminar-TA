<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue';
import { dialog as vDialog } from '@/lib/dialog';
import { Link, router } from '@inertiajs/vue3';
import {
    Activity,
    Award,
    BookOpen,
    Building2,
    Calendar,
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
    Moon,
    Printer,
    ShieldCheck,
    Sun,
    UserCheck,
    Users,
    X,
} from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';

const props = defineProps<{
    open: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { role: userRole } = useAuth();
const { isDarkMode, toggleDarkMode } = useTheme();

const handleLogout = () => {
    emit('close');
    router.post('/logout');
};
const onKey = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.open) emit('close');
};
onMounted(() => window.addEventListener('keydown', onKey));
onUnmounted(() => window.removeEventListener('keydown', onKey));
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs lg:hidden"
        @click="emit('close')"
    >
        <div
            v-dialog="'Navigasi portal'"
            class="flex h-full w-72 max-w-[calc(100vw-2rem)] flex-col justify-between overflow-y-auto bg-white p-5 text-slate-700 shadow-2xl dark:bg-[#0E1626] dark:text-slate-300"
            @click.stop
        >
            <div>
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200/80 bg-white p-1 shadow-xs dark:border-slate-700/80 dark:bg-slate-800"
                        >
                            <img
                                src="/images/logo_if.png"
                                alt="Logo Informatika ITK"
                                class="h-5 w-5 object-contain"
                            />
                        </div>
                        <span
                            class="text-sm font-bold text-slate-900 dark:text-white"
                            >SIPTA IF</span
                        >
                    </div>
                    <button
                        type="button"
                        aria-label="Tutup navigasi"
                        class="cursor-pointer p-1 text-slate-400 hover:text-slate-700 dark:hover:text-white"
                        @click="emit('close')"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Navigation Links strictly by role -->
                <nav class="mt-4 space-y-2 text-xs">
                    <!-- Mahasiswa -->
                    <template v-if="userRole === 'mahasiswa'">
                        <Link
                            href="/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard
                        </Link>
                        <Link
                            href="/pendaftaran/judul"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <FileText class="h-4 w-4" /> Pengajuan Judul
                        </Link>
                        <Link
                            href="/pendaftaran/bimbingan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <UserCheck class="h-4 w-4" /> Bimbingan TA
                        </Link>
                        <Link
                            href="/pendaftaran/sempro"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <FileCheck class="h-4 w-4" /> Seminar Proposal
                        </Link>
                        <Link
                            href="/pendaftaran/sidang"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Award class="h-4 w-4" /> Sidang TA
                        </Link>
                        <Link
                            href="/pendaftaran/jadwal"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Pendaftar & Jadwal
                        </Link>
                        <Link
                            href="/prosedur"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Layers class="h-4 w-4" /> Prosedur TA
                        </Link>
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <BookOpen class="h-4 w-4" /> Katalog TA
                        </Link>
                        <Link
                            href="/panduan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Globe class="h-4 w-4" /> Panduan Website
                        </Link>
                    </template>

                    <!-- Dosen -->
                    <template v-else-if="userRole === 'dosen'">
                        <p
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Manajemen Utama
                        </p>
                        <Link
                            href="/dosen/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Data Pengajuan
                        </p>
                        <Link
                            href="/dosen/persetujuan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <FileText class="h-4 w-4" /> Daftar Judul
                        </Link>
                        <Link
                            href="/dosen/bimbingan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <UserCheck class="h-4 w-4" /> Bimbingan TA
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Penjadwalan
                        </p>
                        <Link
                            href="/dosen/sempro"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Seminar Proposal
                        </Link>
                        <Link
                            href="/dosen/sidang"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <GraduationCap class="h-4 w-4" /> Sidang TA
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Panduan &amp; Lainnya
                        </p>
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <BookOpen class="h-4 w-4" /> Katalog TA
                        </Link>
                        <Link
                            href="/panduan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Globe class="h-4 w-4" /> Panduan Website
                        </Link>
                    </template>

                    <!-- Kaprodi / Koorprodi -->
                    <template v-else-if="userRole === 'kaprodi'">
                        <p
                            class="px-3 pt-1 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Manajemen Utama
                        </p>
                        <Link
                            href="/kaprodi/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Data Pengajuan
                        </p>
                        <Link
                            href="/kaprodi/persetujuan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <FileText class="h-4 w-4" /> Daftar Judul
                        </Link>
                        <Link
                            href="/dosen/bimbingan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <UserCheck class="h-4 w-4" /> Bimbingan TA
                        </Link>
                        <Link
                            href="/kaprodi/persetujuan?section=sempro"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Seminar Proposal
                        </Link>
                        <Link
                            href="/kaprodi/persetujuan?section=sidang"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Award class="h-4 w-4" /> Sidang TA
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Periode Gelombang
                        </p>
                        <Link
                            href="/kaprodi/penjadwalan/sempro"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Periode Sempro
                        </Link>
                        <Link
                            href="/kaprodi/penjadwalan/ta"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Periode TA
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Penjadwalan &amp; Plotting
                        </p>
                        <Link
                            href="/kaprodi/penjadwalan/plotting-sempro"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Cpu class="h-4 w-4" /> Plotting Jadwal Sempro
                        </Link>
                        <Link
                            href="/kaprodi/penjadwalan/plotting-ta"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Calendar class="h-4 w-4" /> Plotting Jadwal Sidang
                            TA
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Data Master
                        </p>
                        <Link
                            href="/kaprodi/dosen"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <GraduationCap class="h-4 w-4" /> Data Dosen
                        </Link>
                        <Link
                            href="/kaprodi/mahasiswa"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Users class="h-4 w-4" /> Data Mahasiswa
                        </Link>

                        <p
                            class="px-3 pt-2 text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                        >
                            Prosedur &amp; Katalog
                        </p>
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-700 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <BookOpen class="h-4 w-4" /> Katalog TA
                        </Link>
                        <Link
                            href="/prosedur"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Layers class="h-4 w-4" /> Prosedur TA
                        </Link>
                        <Link
                            href="/panduan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Globe class="h-4 w-4" /> Panduan Website
                        </Link>
                    </template>

                    <!-- Tendik -->
                    <template v-else-if="userRole === 'tendik'">
                        <Link
                            href="/tendik/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard Tendik
                        </Link>
                        <Link
                            href="/tendik/verifikasi"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <FileCheck class="h-4 w-4" /> Verifikasi Berkas
                        </Link>
                        <Link
                            href="/tendik/arsip"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Printer class="h-4 w-4" /> Berita Acara & SK
                        </Link>
                        <Link
                            href="/tendik/mahasiswa"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <KeyRound class="h-4 w-4" /> Kelola Mahasiswa
                        </Link>
                    </template>
                </nav>
            </div>

            <!-- Footer: Theme Toggle & Logout -->
            <div
                class="space-y-2 border-t border-slate-200 pt-4 dark:border-slate-800"
            >
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
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
                    <span class="text-[10px] text-slate-500">
                        {{ isDarkMode ? 'Gelap' : 'Terang' }}
                    </span>
                </button>

                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30"
                    @click="handleLogout"
                >
                    <LogOut class="h-4 w-4" /> Log Out
                </button>
            </div>
        </div>
    </div>
</template>
