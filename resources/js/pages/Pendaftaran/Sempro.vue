<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { AlertCircle, Download, FileCheck, Send } from 'lucide-vue-next';

import CollapsibleCard from '@/Components/CollapsibleCard.vue';
import DosenTeamSection from '@/Components/DosenTeamSection.vue';
import FileTemplateLink from '@/Components/FileTemplateLink.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import ProfileHeaderCard from '@/Components/ProfileHeaderCard.vue';
import RiwayatTimelineCard from '@/Components/RiwayatTimelineCard.vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);
const studentName = computed(
    () => authUser.value?.name || 'Akmal Falah Maulana',
);
const studentNim = computed(
    () =>
        authUser.value?.username ||
        authUser.value?.nim_nip?.replace('NIM: ', '') ||
        '11231006',
);
const studentProdi = computed(() => authUser.value?.prodi || 'Informatika');
const studentInitials = computed(() => {
    if (!authUser.value?.name) return 'AF';
    return authUser.value.name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w: string) => w[0]?.toUpperCase())
        .join('');
});

const form = useForm({
    judul_ta:
        'Sistem Penjadwalan Seminar dan Chatbot Layanan Akademik Menggunakan Algoritma Genetika dan Large Language Model',
    bentuk_ta: 'Skripsi Reguler (Pengembangan Perangkat Lunak & AI)',
    lembar_kehadiran_file: null as File | null,
    proposal_file: null as File | null,
    turnitin_file: null as File | null,
    lokasi_mitra: '',
    iaet_file: null as File | null,
});

const submitSempro = () => {
    form.post('/pendaftaran/sempro', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Seminar Proposal TA - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- 1. Header Box (Reusable Component) -->
            <PageHeaderBox title="Seminar Proposal TA" />

            <!-- 2. Profile Card & Status Sempro (Reusable Component) -->
            <ProfileHeaderCard
                :name="studentName"
                :nim="studentNim"
                angkatan="Akt 2023"
                :prodi="studentProdi"
                :avatar-initials="studentInitials"
            >
                <span
                    class="mb-1 text-xs font-bold text-slate-500 dark:text-slate-400"
                >
                    Status Sempro
                </span>
                <span
                    class="rounded-xl bg-[#FF7575] px-4 py-2 text-xs font-bold text-white shadow-2xs"
                >
                    Belum Isi Formulir
                </span>
            </ProfileHeaderCard>

            <!-- 3. Two Column Layout -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column (Span 2): Dosen & Formulir Pendaftaran -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Tim Pembimbing & Penguji (Reusable Component) -->
                    <DosenTeamSection />

                    <!-- Formulir Pendaftaran Sempro (Reusable CollapsibleCard) -->
                    <CollapsibleCard
                        title="Formulir Pendaftaran Sempro"
                        status-text="Belum Selesai"
                        status-variant="yellow"
                        :default-open="true"
                    >
                        <form class="space-y-6" @submit.prevent="submitSempro">
                            <!-- Judul & Bentuk TA Summary -->
                            <div
                                class="space-y-3 border-b border-slate-100 pb-4 text-xs dark:border-slate-800"
                            >
                                <div>
                                    <span
                                        class="font-bold text-slate-800 dark:text-slate-200"
                                        >Judul TA:
                                    </span>
                                    <span
                                        class="text-slate-600 dark:text-slate-400"
                                        >{{ form.judul_ta }}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="font-bold text-slate-800 dark:text-slate-200"
                                        >Bentuk TA:
                                    </span>
                                    <span
                                        class="text-slate-600 dark:text-slate-400"
                                        >{{ form.bentuk_ta }}</span
                                    >
                                </div>
                            </div>

                            <!-- File Pendukung Links (Reusable FileTemplateLink) -->
                            <div class="space-y-2 text-xs">
                                <p
                                    class="font-bold text-slate-800 dark:text-slate-200"
                                >
                                    File Pendukung Template:
                                </p>
                                <div class="space-y-1.5 pl-1">
                                    <FileTemplateLink
                                        label="Formulir Kesediaan Membimbing"
                                        code="Form TA-01A"
                                    />
                                    <FileTemplateLink
                                        label="Formulir Persetujuan Seminar Proposal"
                                        code="Form TA-02"
                                    />
                                    <FileTemplateLink
                                        label="Lembar Monitoring Bimbingan"
                                        code="Form TA-04"
                                    />
                                </div>
                            </div>

                            <!-- Upload Berkas 1: Lembar Bukti Kehadiran (Form TA-03D) -->
                            <div class="space-y-1.5">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Lembar Bukti Kehadiran Seminar Proposal TA
                                    (Form. TA-03D)
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        class="text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.lembar_kehadiran_file =
                                                    e.target.files[0])
                                        "
                                    />
                                    <span
                                        class="ml-auto text-[11px] text-slate-400"
                                        >Max 10 MB</span
                                    >
                                </div>
                            </div>

                            <!-- Upload Berkas 2: Proposal Tugas Akhir -->
                            <div class="space-y-1.5">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Proposal Tugas Akhir
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        class="text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.proposal_file =
                                                    e.target.files[0])
                                        "
                                    />
                                    <span
                                        class="ml-auto text-[11px] text-slate-400"
                                        >Max 10 MB</span
                                    >
                                </div>
                            </div>

                            <!-- Upload Berkas 3: Bukti Plagiasi (Turnitin) -->
                            <div class="space-y-1.5">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Bukti Plagiasi (Turnitin)
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        class="text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.turnitin_file =
                                                    e.target.files[0])
                                        "
                                    />
                                    <span
                                        class="ml-auto text-[11px] text-slate-400"
                                        >Max 10 MB</span
                                    >
                                </div>
                            </div>

                            <!-- Input 4: Lokasi/Mitra Penelitian (Opsional) -->
                            <div class="space-y-1.5">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Lokasi/Mitra Penelitian (Opsional)
                                </label>
                                <input
                                    v-model="form.lokasi_mitra"
                                    type="text"
                                    placeholder="Contoh: PT Lorem Ipsum / Laboratorium Universitas / Balikpapan"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <!-- Input 5: Skor IAET (Opsional) -->
                            <div class="space-y-1.5">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Skor IAET (opsional)
                                </label>
                                <div
                                    class="flex items-center gap-3 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        accept=".pdf"
                                        class="text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.iaet_file =
                                                    e.target.files[0])
                                        "
                                    />
                                    <span
                                        class="ml-auto text-[11px] text-slate-400"
                                        >Max 10 MB</span
                                    >
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    class="rounded-xl bg-[#8CE79B] px-6 py-2.5 text-xs font-bold text-slate-900 transition-all hover:bg-[#7BD68A] active:scale-95"
                                >
                                    Submit
                                </button>
                            </div>
                        </form>
                    </CollapsibleCard>
                </div>

                <!-- Right Column: Riwayat Pendaftaran & Pelaksanaan (Reusable RiwayatTimelineCard) -->
                <div class="space-y-6">
                    <RiwayatTimelineCard title="Riwayat Pendaftaran" />
                    <RiwayatTimelineCard title="Riwayat Pelaksanaan & Hasil" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
