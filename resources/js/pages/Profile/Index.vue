<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
    CheckCircle2,
    GraduationCap,
    Info,
    Mail,
    PenTool,
    Phone,
    ShieldCheck,
    User,
} from 'lucide-vue-next';

import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import SignaturePadModal from '@/Components/SignaturePadModal.vue';

const showSignatureModal = ref(false);
const signaturePreview = ref<string | null>(
    'data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="200" height="80"><path d="M20 50 Q 60 10, 90 45 T 160 30" fill="none" stroke="%231E40AF" stroke-width="3"/></svg>',
);

const form = useForm({
    name: 'Akmal Falah Maulana',
    nim: '11231006',
    email: '11231006@student.itk.ac.id',
    prodi: 'S1 Teknik Informatika',
    angkatan: '2023',
    phone: '081234567890',
});

const onSaveSignature = (dataUrl: string) => {
    signaturePreview.value = dataUrl;
};
</script>

<template>
    <Head title="Profil Pengguna & e-TTD - SIPTA IF" />

    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header Box (Reusable Component) -->
        <PageHeaderBox
            title="Profil Pengguna & e-TTD"
            subtitle="Data diri akademik dan tanda tangan digital resmi (e-TTD)"
        />

        <!-- Warning PENTING SOP SIPTA IF -->
        <div
            class="rounded-2xl border border-yellow-200 bg-[#FCFDE1] p-5 shadow-xs dark:border-yellow-900/50 dark:bg-yellow-950/30"
        >
            <div class="flex items-start gap-3">
                <Info class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                <div
                    class="space-y-1 text-xs leading-relaxed text-slate-800 dark:text-yellow-200"
                >
                    <p class="font-bold">
                        Ketentuan e-TTD & Data Akademik (SIPTA IF):
                    </p>
                    <p>
                        • Pastikan e-TTD Anda sudah tersimpan. Tanda tangan ini
                        secara otomatis dibubuhkan pada lembar bimbingan (Form
                        TA-04) dan berita acara seminar/sidang.
                    </p>
                    <p>
                        • Password akun mahasiswa menggunakan NIM dan tidak
                        dapat diubah mandiri. Jika terkendala login, hubungi
                        staf Tendik FSTI.
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Data Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Left: Avatar Card & Signature Status -->
            <div
                class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-6 text-center shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div>
                    <div
                        class="mx-auto flex h-24 w-24 items-center justify-center rounded-full bg-blue-100 text-2xl font-bold text-blue-700 shadow-md dark:bg-blue-950 dark:text-blue-300"
                    >
                        AF
                    </div>
                    <h3
                        class="mt-4 text-base font-bold text-slate-900 dark:text-white"
                    >
                        {{ form.name }}
                    </h3>
                    <p class="text-xs font-semibold text-slate-400">
                        {{ form.nim }}
                    </p>
                    <span
                        class="mt-2 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                    >
                        <ShieldCheck class="h-3.5 w-3.5" /> Akun Mahasiswa Aktif
                    </span>
                </div>

                <!-- e-TTD Preview Container -->
                <div
                    class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <span
                            class="text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            e-TTD Terdaftar:
                        </span>
                        <span
                            class="text-[10px] font-semibold text-emerald-600 dark:text-emerald-400"
                        >
                            Terverifikasi
                        </span>
                    </div>

                    <div
                        class="flex h-24 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 p-2 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <img
                            v-if="signaturePreview"
                            :src="signaturePreview"
                            alt="Tanda Tangan Digital"
                            class="max-h-full max-w-full object-contain"
                        />
                        <span v-else class="text-xs text-slate-400"
                            >Belum ada e-TTD</span
                        >
                    </div>

                    <button
                        type="button"
                        class="mt-3 flex w-full items-center justify-center gap-2 rounded-xl bg-blue-50 py-2 text-xs font-bold text-blue-600 transition-colors hover:bg-blue-100 dark:bg-blue-950/60 dark:text-blue-300"
                        @click="showSignatureModal = true"
                    >
                        <PenTool class="h-3.5 w-3.5" />
                        <span>Perbarui e-TTD</span>
                    </button>
                </div>
            </div>

            <!-- Right: Biodata Detail Form (Span 2) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs md:col-span-2 dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3
                    class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    Biodata Akademik Mahasiswa
                </h3>

                <div class="mt-5 grid grid-cols-1 gap-4 text-xs sm:grid-cols-2">
                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Nama Lengkap</label
                        >
                        <input
                            :value="form.name"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Nomor Induk Mahasiswa (NIM)</label
                        >
                        <input
                            :value="form.nim"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Program Studi</label
                        >
                        <input
                            :value="form.prodi"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Tahun Angkatan</label
                        >
                        <input
                            :value="form.angkatan"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Email Kampus ITK</label
                        >
                        <input
                            :value="form.email"
                            readonly
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-semibold text-slate-800 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="font-bold text-slate-500 dark:text-slate-400"
                            >Nomor WhatsApp / HP Aktif</label
                        >
                        <input
                            v-model="form.phone"
                            class="w-full rounded-xl border border-slate-300 bg-white p-2.5 font-semibold text-slate-800 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        type="button"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-xs hover:bg-blue-700"
                    >
                        Simpan Perubahan Kontak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reusable Signature Pad Modal -->
    <SignaturePadModal
        :show="showSignatureModal"
        @close="showSignatureModal = false"
        @save="onSaveSignature"
    />
</template>
