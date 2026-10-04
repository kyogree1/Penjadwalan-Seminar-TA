<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';

import CollapsibleCard from '@/Components/CollapsibleCard.vue';
import DosenTeamSection from '@/Components/DosenTeamSection.vue';
import TemplateDownloadCard from '@/Components/TemplateDownloadCard.vue';
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
    skor_iaet: '' as number | string,
    lokasi_mitra: '',
    draft_laporan_file: null as File | null,
    turnitin_file: null as File | null,
});

const fileError = (file: File | null) => {
    if (!file) return 'Berkas wajib diunggah.';
    if (
        !/\.pdf$/i.test(file.name) ||
        (file.type && file.type !== 'application/pdf')
    )
        return 'Berkas harus berupa PDF.';
    if (file.size === 0) return 'Berkas tidak boleh kosong.';
    if (file.size > 10 * 1024 * 1024) return 'Ukuran berkas maksimal 10 MB.';
    return '';
};
const validationErrors = computed(() => ({
    draft_laporan_file: fileError(form.draft_laporan_file),
    turnitin_file: fileError(form.turnitin_file),
    skor_iaet:
        form.skor_iaet !== '' &&
        (!Number.isFinite(Number(form.skor_iaet)) || Number(form.skor_iaet) < 0)
            ? 'Skor IAET harus berupa angka nol atau lebih.'
            : '',
}));
const isFormValid = computed(() =>
    Object.values(validationErrors.value).every((error) => !error),
);
const submitSidang = () => {
    if (form.processing) return;
    form.clearErrors();
    if (!isFormValid.value) {
        for (const field of [
            'draft_laporan_file',
            'turnitin_file',
            'skor_iaet',
        ] as const) {
            if (validationErrors.value[field])
                form.setError(field, validationErrors.value[field]);
        }
        return;
    }
    if (
        form.skor_iaet !== '' &&
        (!Number.isFinite(Number(form.skor_iaet)) || Number(form.skor_iaet) < 0)
    ) {
        form.setError(
            'skor_iaet',
            'Skor IAET harus berupa angka nol atau lebih.',
        );
        return;
    }
    form.post('/pendaftaran/sidang', {
        forceFormData: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Sidang Tugas Akhir">
        <Head title="Sidang Tugas Akhir - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- 1. Header Box (Reusable Component) -->
            <PageHeaderBox title="Sidang Tugas Akhir" />

            <!-- 2. Profile Card (Reusable Component) -->
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
                    Status Sidang
                </span>
                <span
                    class="rounded-xl bg-[#FF7575] px-4 py-2 text-xs font-bold text-white shadow-2xs"
                >
                    Belum Isi Formulir
                </span>
            </ProfileHeaderCard>

            <!-- 3. Two Column Layout -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Dosen Pembimbing & Penguji (Reusable Component) -->
                    <DosenTeamSection />

                    <TemplateDownloadCard
                        :templates="[
                            { label: 'Lembar Persetujuan Revisi Proposal TA' },
                            { code: 'TA-04', label: 'Monitoring Bimbingan' },
                            { code: 'TA-05', label: 'Persetujuan Sidang TA' },
                        ]"
                    />

                    <!-- Formulir Pendaftaran Sidang TA (Reusable CollapsibleCard) -->
                    <CollapsibleCard
                        title="Formulir Pendaftaran Sidang TA"
                        :default-open="true"
                    >
                        <form class="space-y-6" @submit.prevent="submitSidang">
                            <!-- Judul TA -->
                            <div
                                class="border-b border-slate-100 pb-4 text-sm leading-relaxed dark:border-slate-800"
                            >
                                <span
                                    class="font-bold text-slate-800 dark:text-slate-200"
                                    >Judul TA:
                                </span>
                                <span
                                    class="text-slate-600 dark:text-slate-400"
                                    >{{ form.judul_ta }}</span
                                >
                            </div>

                            <!-- Input Skor IAET Terbaru -->
                            <div class="space-y-2">
                                <label
                                    for="skor-iaet"
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Skor IAET Terbaru (Opsional)
                                </label>
                                <input
                                    id="skor-iaet"
                                    v-model.number="form.skor_iaet"
                                    type="number"
                                    min="0"
                                    step="any"
                                    placeholder="Masukkan skor IAET"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                    :aria-invalid="!!form.errors.skor_iaet"
                                />
                                <p
                                    v-if="form.errors.skor_iaet"
                                    class="text-xs text-rose-600"
                                >
                                    {{ form.errors.skor_iaet }}
                                </p>
                            </div>

                            <!-- Input Lokasi/Mitra Penelitian -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Lokasi/Mitra Penelitian (Opsional)
                                </label>
                                <input
                                    v-model="form.lokasi_mitra"
                                    type="text"
                                    placeholder="Contoh: PT Lorem Ipsum / Laboratorium Universitas / Balikpapan"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-sm text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </div>

                            <!-- Input Draft Laporan TA -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Draft Laporan Tugas Akhir
                                    <span class="text-rose-500">*</span>
                                </label>
                                <div
                                    class="flex flex-col items-stretch gap-2 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        required
                                        accept=".pdf"
                                        class="w-full min-w-0 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.draft_laporan_file =
                                                    e.target.files?.[0] ?? null)
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.draft_laporan_file &&
                                            validationErrors.draft_laporan_file
                                        "
                                        class="text-xs text-rose-600"
                                        role="alert"
                                    >
                                        {{
                                            validationErrors.draft_laporan_file
                                        }}
                                    </p>
                                    <span
                                        class="shrink-0 text-xs text-slate-500"
                                        >PDF, maks. 10 MB</span
                                    >
                                </div>
                            </div>

                            <!-- Input Bukti Plagiasi Turnitin -->
                            <div class="space-y-2">
                                <label
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Bukti Plagiasi (Turnitin)
                                    <span class="text-rose-500">*</span>
                                </label>
                                <div
                                    class="flex flex-col items-stretch gap-2 rounded-xl border border-slate-300 bg-slate-50/50 p-2.5 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <input
                                        type="file"
                                        required
                                        accept=".pdf"
                                        class="w-full min-w-0 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100"
                                        @change="
                                            (e: any) =>
                                                (form.turnitin_file =
                                                    e.target.files?.[0] ?? null)
                                        "
                                    />
                                    <p
                                        v-if="
                                            form.turnitin_file &&
                                            validationErrors.turnitin_file
                                        "
                                        class="text-xs text-rose-600"
                                        role="alert"
                                    >
                                        {{ validationErrors.turnitin_file }}
                                    </p>
                                    <span
                                        class="shrink-0 text-xs text-slate-500"
                                        >PDF, maks. 10 MB</span
                                    >
                                </div>
                            </div>

                            <p
                                v-if="!isFormValid"
                                class="rounded-xl bg-blue-50 p-3 text-sm text-blue-800 dark:bg-blue-950/30 dark:text-blue-200"
                                role="status"
                            >
                                Lengkapi berkas bertanda * dengan PDF yang valid
                                (maksimal 10 MB) sebelum mengajukan. Skor IAET
                                dan lokasi penelitian opsional.
                            </p>
                            <!-- Submit Button -->
                            <div class="flex justify-end pt-2">
                                <button
                                    type="submit"
                                    :disabled="!isFormValid || form.processing"
                                    class="w-full rounded-xl bg-[#8CE79B] px-6 py-3 text-sm font-bold text-slate-900 transition-all hover:bg-[#7BD68A] active:scale-95 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                                >
                                    Ajukan Pendaftaran Sidang
                                </button>
                            </div>
                        </form>
                    </CollapsibleCard>
                </div>

                <!-- Right Column: Riwayat Pendaftaran -->
                <div class="space-y-6">
                    <RiwayatTimelineCard title="Riwayat Pendaftaran" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
