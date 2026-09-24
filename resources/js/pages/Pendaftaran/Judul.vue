<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    FileText,
    Info,
    Send,
} from 'lucide-vue-next';

import AssessmentResultCard from '@/Components/AssessmentResultCard.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    judul_ta: '',
    bidang_penelitian: '',
    pembimbing_1: '',
    pembimbing_2: '',
    telah_konsultasi: false,
});

const dosenList = [
    { id: '1', name: 'Dr. Ir. Hendra Wijaya, M.Kom.' },
    { id: '2', name: 'Rina Agustina, S.T., M.Kom.' },
    { id: '3', name: 'Dr. Dian Indah Permatasari, M.Kom.' },
    { id: '4', name: 'Ahmad Fauzi, S.Kom., M.T.' },
];

const submitForm = () => {
    form.post('/pendaftaran/judul', {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Pengajuan Judul Tugas Akhir - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- 1. Header Judul & Status Pill (Reusable Component) -->
            <PageHeaderBox title="Pengajuan Judul Tugas Akhir">
                <template #action>
                    <div
                        class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white px-4 py-2 shadow-2xs dark:border-slate-800 dark:bg-[#0E1626]"
                    >
                        <span
                            class="text-xs font-bold text-slate-700 dark:text-slate-300"
                            >Status:</span
                        >
                        <span
                            class="rounded-xl bg-[#F6ED78] px-3 py-1 text-xs font-bold text-slate-900 shadow-2xs"
                        >
                            Belum Selesai
                        </span>
                    </div>
                </template>
            </PageHeaderBox>

            <!-- 2. Card Perhatian! (Sesuai Mockup) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    Perhatian!
                </h3>
                <p class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                    Sebelum mengajukan judul, pastikan:
                </p>

                <ul
                    class="mt-2 list-disc space-y-1 pl-5 text-xs text-slate-600 dark:text-slate-300"
                >
                    <li>
                        Lengkapi e-TTD terlebih dahulu melalui menu Profil
                        (pojok kanan atas)
                    </li>
                    <li>
                        Sudah konsultasi dengan dosen pembimbing 1 & 2 dan
                        mereka menyetujui judul
                    </li>
                    <li>
                        Dosen pembimbing sudah siap dan bersedia membimbing
                        penelitian Anda
                    </li>
                    <li>Judul dan bidang penelitian sudah FINAL</li>
                </ul>

                <div
                    class="mt-4 rounded-xl border border-slate-200 bg-slate-50/80 p-3.5 text-xs font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300"
                >
                    Setelah pengajuan dikirim, pembimbing 1 & 2 TIDAK DAPAT
                    DIUBAH. Jika judul ditolak dosen, Anda tidak bisa mengubah
                    apapun lagi dan tidak bisa melakukan pengajuan lagi.
                </div>
            </div>

            <!-- 3. Card Data Pengajuan TA Form (Sesuai Mockup) -->
            <form
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
                @submit.prevent="submitForm"
            >
                <h3
                    class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    Data Pengajuan TA
                </h3>

                <div class="mt-5 space-y-5">
                    <!-- Judul TA -->
                    <div class="space-y-1.5">
                        <label
                            for="judul_ta"
                            class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                        >
                            Judul TA
                        </label>
                        <textarea
                            id="judul_ta"
                            v-model="form.judul_ta"
                            rows="3"
                            placeholder="Masukkan judul..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50/50 p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        />
                    </div>

                    <!-- Bidang Penelitian -->
                    <div class="space-y-1.5">
                        <label
                            for="bidang_penelitian"
                            class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                        >
                            Bidang Penelitian
                        </label>
                        <input
                            id="bidang_penelitian"
                            v-model="form.bidang_penelitian"
                            type="text"
                            placeholder="Masukkan bidang penelitian, seperti Computer vision, Internet of Things (IoT), Web Development, Data mining, dll..."
                            class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        />
                    </div>

                    <!-- Dosen Pembimbing 1 & 2 -->
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <label
                                for="pembimbing_1"
                                class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                            >
                                Pembimbing 1
                            </label>
                            <select
                                id="pembimbing_1"
                                v-model="form.pembimbing_1"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            >
                                <option value="" disabled>
                                    Pilih Pembimbing
                                </option>
                                <option
                                    v-for="dosen in dosenList"
                                    :key="dosen.id"
                                    :value="dosen.name"
                                >
                                    {{ dosen.name }}
                                </option>
                            </select>
                        </div>

                        <div class="space-y-1.5">
                            <label
                                for="pembimbing_2"
                                class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                            >
                                Pembimbing 2
                            </label>
                            <select
                                id="pembimbing_2"
                                v-model="form.pembimbing_2"
                                class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                            >
                                <option value="" disabled>
                                    Pilih Pembimbing
                                </option>
                                <option
                                    v-for="dosen in dosenList"
                                    :key="dosen.id"
                                    :value="dosen.name"
                                >
                                    {{ dosen.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Checkbox Konfirmasi -->
                    <div
                        class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <input
                            id="konfirmasi"
                            v-model="form.telah_konsultasi"
                            type="checkbox"
                            class="h-4 w-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500"
                        />
                        <label
                            for="konfirmasi"
                            class="cursor-pointer text-xs font-medium text-slate-700 dark:text-slate-300"
                        >
                            Saya sudah berkonsultasi dengan kedua dosen
                            pembimbing dan mereka menyetujui judul serta
                            bersedia membimbing penelitian saya.
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-2">
                        <button
                            type="submit"
                            :disabled="
                                !form.telah_konsultasi || form.processing
                            "
                            class="rounded-xl bg-[#8CE79B] px-6 py-2.5 text-xs font-bold text-slate-900 transition-all hover:bg-[#7BD68A] active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                        >
                            Simpan Pengajuan
                        </button>
                    </div>
                </div>
            </form>

            <!-- 4. Card Riwayat Pengajuan (Reusable AssessmentResultCard) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3
                    class="mb-4 text-base font-bold text-slate-900 dark:text-white"
                >
                    Riwayat Pengajuan
                </h3>

                <div class="space-y-3">
                    <AssessmentResultCard
                        title="Sidang Tugas Akhir"
                        pembimbing="Pembimbing 1 • Dr. Ir. Hendra Wijaya, M.Kom."
                        result="Lulus dengan Revisi"
                    />
                    <AssessmentResultCard
                        title="Seminar Proposal Tugas Akhir"
                        pembimbing="Pembimbing 1 • Dr. Ir. Hendra Wijaya, M.Kom."
                        result="Lulus dengan Revisi"
                    />
                    <AssessmentResultCard
                        title="Seminar Proposal Tugas Akhir"
                        pembimbing="Pembimbing 1 • Dr. Ir. Hendra Wijaya, M.Kom."
                        result="Tidak Lulus"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
