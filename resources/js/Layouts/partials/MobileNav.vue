<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Building2,
    Calendar,
    Cpu,
    FileCheck,
    FileText,
    Globe,
    GraduationCap,
    Home,
    KeyRound,
    Layers,
    LogOut,
    Moon,
    PenTool,
    Printer,
    ShieldCheck,
    Sun,
    UserCheck,
    Users,
    X,
} from 'lucide-vue-next';

import { useAuth } from '@/composables/useAuth';
import { useTheme } from '@/composables/useTheme';

defineProps<{
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
</script>

<template>
    <div
        v-if="open"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs lg:hidden"
        @click="emit('close')"
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
                        class="cursor-pointer p-1 text-slate-400 hover:text-white"
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
                        <Link
                            href="/dosen/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard Dosen
                        </Link>
                        <Link
                            href="/dosen/bimbingan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <UserCheck class="h-4 w-4" /> Bimbingan Mahasiswa
                        </Link>
                        <Link
                            href="/dosen/penilaian"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <PenTool class="h-4 w-4" /> Lembar Penilaian
                        </Link>
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <BookOpen class="h-4 w-4" /> Katalog TA
                        </Link>
                    </template>

                    <!-- Kaprodi -->
                    <template v-else-if="userRole === 'kaprodi'">
                        <Link
                            href="/kaprodi/dashboard"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Home class="h-4 w-4" /> Dashboard Kaprodi
                        </Link>
                        <Link
                            href="/kaprodi/penjadwalan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Cpu class="h-4 w-4" /> Jadwal Sempro & Sidang
                        </Link>
                        <Link
                            href="/kaprodi/monitoring"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Users class="h-4 w-4" /> Monitoring Prodi
                        </Link>
                        <Link
                            href="/kaprodi/dosen"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <GraduationCap class="h-4 w-4" /> Data Dosen & Kuota
                        </Link>
                        <Link
                            href="/kaprodi/persetujuan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <ShieldCheck class="h-4 w-4" /> Persetujuan Akademik
                        </Link>
                        <Link
                            href="/katalog"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <BookOpen class="h-4 w-4" /> Katalog TA
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
                            href="/tendik/ruangan"
                            class="flex items-center gap-3 rounded-xl px-3 py-2 font-semibold text-slate-300 hover:bg-slate-800"
                            @click="emit('close')"
                        >
                            <Building2 class="h-4 w-4" /> Jadwal Ruangan
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
            <div class="space-y-2 border-t border-slate-800 pt-4">
                <button
                    type="button"
                    class="flex w-full cursor-pointer items-center justify-between rounded-xl px-3 py-2 text-xs font-semibold text-slate-300 transition-colors hover:bg-slate-800"
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
                    class="flex w-full cursor-pointer items-center gap-3 rounded-xl px-3 py-2 text-xs font-semibold text-rose-400 transition-colors hover:bg-rose-950/30"
                    @click="handleLogout"
                >
                    <LogOut class="h-4 w-4" /> Log Out
                </button>
            </div>
        </div>
    </div>
</template>
