<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle,
    CheckCircle2,
    Clock,
    FileText,
    Info,
    Send,
} from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatusBadge, { type BadgeStatus } from '@/Components/StatusBadge.vue';

interface Props {
    pengajuan?: {
        id?: number;
        judul_ta?: string;
        bidang_penelitian?: string;
        pembimbing_1_id?: number | null;
        pembimbing_2_id?: number | null;
        pembimbing_1?: { id: number; name: string } | null;
        pembimbing_2?: { id: number; name: string } | null;
        telah_konsultasi?: boolean;
        status?: string;
        catatan_kaprodi?: string | null;
    } | null;
    dosenList?: Array<{ id: number | string; name: string }>;
}

const props = defineProps<Props>();
const feedback = computed(
    () => (page.props.flash as { success?: string; error?: string }) ?? {},
);
const page = usePage();

const availableDosenList = computed(() => props.dosenList ?? []);

const initialStatus = (): BadgeStatus => {
    if (!props.pengajuan) return 'draft';
    const s = props.pengajuan.status;
    if (s === 'menunggu') return 'diajukan';
    if (s === 'disetujui') return 'disetujui';
    if (s === 'revisi') return 'revisi';
    if (s === 'ditolak') return 'ditolak';
    return 'draft';
};

const applicationStatus = computed<BadgeStatus>(initialStatus);

const supervisorId = (id?: number | null, related?: { id: number } | null) =>
    id ?? related?.id;
const supervisorValue = (
    id?: number | null,
    related?: { id: number } | null,
) => {
    const value = supervisorId(id, related);
    return value == null ? '' : String(value);
};
const form = useForm({
    judul_ta: props.pengajuan?.judul_ta || '',
    bidang_penelitian: props.pengajuan?.bidang_penelitian || '',
    pembimbing_1: supervisorValue(
        props.pengajuan?.pembimbing_1_id,
        props.pengajuan?.pembimbing_1,
    ),
    pembimbing_2: supervisorValue(
        props.pengajuan?.pembimbing_2_id,
        props.pengajuan?.pembimbing_2,
    ),
    telah_konsultasi: Boolean(props.pengajuan?.telah_konsultasi),
});
watch(
    () => props.pengajuan,
    (record) => {
        form.judul_ta = record?.judul_ta ?? '';
        form.bidang_penelitian = record?.bidang_penelitian ?? '';
        form.pembimbing_1 = supervisorValue(
            record?.pembimbing_1_id,
            record?.pembimbing_1,
        );
        form.pembimbing_2 = supervisorValue(
            record?.pembimbing_2_id,
            record?.pembimbing_2,
        );
        form.telah_konsultasi = Boolean(record?.telah_konsultasi);
    },
);
const statusLabel = computed(() => {
    switch (applicationStatus.value) {
        case 'draft':
            return 'Belum Selesai';
        case 'diajukan':
            return 'Menunggu Persetujuan';
        case 'revisi':
            return 'Perlu Revisi';
        case 'disetujui':
            return 'Disetujui';
        case 'ditolak':
            return 'Tidak Disetujui';
        default:
            return 'Belum Selesai';
    }
});
const canEditForm = computed(
    () =>
        applicationStatus.value === 'draft' ||
        applicationStatus.value === 'revisi' ||
        applicationStatus.value === 'ditolak',
);

const isFormValid = computed(() => {
    return (
        form.judul_ta.trim() !== '' &&
        form.bidang_penelitian.trim() !== '' &&
        form.pembimbing_1 !== '' &&
        form.telah_konsultasi
    );
});

const submitForm = () => {
    if (!canEditForm.value || form.processing) return;
    form.post('/pendaftaran/judul', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pengajuan Judul Tugas Akhir - SIPTA IF" />

    <div class="mx-auto max-w-7xl space-y-6">
        <p
            v-if="feedback.success"
            role="status"
            class="text-sm text-emerald-700"
        >
            {{ feedback.success }}
        </p>
        <p v-if="feedback.error" role="alert" class="text-sm text-rose-700">
            {{ feedback.error }}
        </p>
        <p
            v-for="error in form.errors"
            :key="error"
            role="alert"
            class="text-sm text-rose-700"
        >
            {{ error }}
        </p>
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
                    <StatusBadge
                        :status="applicationStatus"
                        :text="statusLabel"
                    />
                </div>
            </template>
        </PageHeaderBox>

        <!-- Banner Notifikasi Status Disetujui -->
        <div
            v-if="applicationStatus === 'disetujui'"
            class="flex items-start gap-3.5 rounded-2xl border border-emerald-300 bg-emerald-50/90 p-5 shadow-xs dark:border-emerald-800 dark:bg-emerald-950/30"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-xs"
            >
                <CheckCircle2 class="h-6 w-6" />
            </div>
            <div class="flex-1">
                <h3
                    class="text-sm font-bold text-emerald-950 dark:text-emerald-100"
                >
                    Pengajuan Judul Telah Disetujui
                </h3>
                <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-300">
                    Judul tugas akhir Anda telah disetujui secara resmi oleh
                    Koordinator / Program Studi. Anda dapat melanjutkan ke
                    tahapan bimbingan logbook dan pendaftaran seminar proposal.
                </p>
            </div>
        </div>

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
                    Sudah konsultasi dengan dosen pembimbing yang dipilih dan
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
                Pengajuan menunggu persetujuan atau disetujui dikunci. Jika
                diminta revisi atau ditolak, perbaiki data dan ajukan kembali
                untuk peninjauan ulang.
            </div>
        </div>

        <!-- 2.5 Catatan Kaprodi Alert: Hanya tampil jika status revisi/ditolak DAN ada catatan -->
        <div
            v-if="
                (applicationStatus === 'revisi' ||
                    applicationStatus === 'ditolak') &&
                props.pengajuan?.catatan_kaprodi?.trim()
            "
            class="rounded-2xl border border-amber-200 bg-amber-50/80 p-5 shadow-xs dark:border-amber-900/60 dark:bg-amber-950/40"
        >
            <div class="flex items-start gap-3">
                <Info
                    class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400"
                />
                <div>
                    <h4
                        class="text-sm font-bold text-amber-900 dark:text-amber-200"
                    >
                        Catatan Revisi dari Koordinator / Kaprodi
                    </h4>
                    <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">
                        {{ props.pengajuan.catatan_kaprodi }}
                    </p>
                </div>
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

            <div class="mt-5 space-y-6">
                <!-- Judul TA -->
                <div class="space-y-1.5">
                    <label
                        for="judul_ta"
                        class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                    >
                        Judul TA
                        <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        id="judul_ta"
                        v-model="form.judul_ta"
                        rows="3"
                        placeholder="Masukkan judul..."
                        required
                        :readonly="!canEditForm"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50/50 p-3 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        :class="{
                            'cursor-not-allowed opacity-60': !canEditForm,
                        }"
                    />
                </div>

                <!-- Bidang Penelitian -->
                <div class="space-y-1.5">
                    <label
                        for="bidang_penelitian"
                        class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                    >
                        Bidang Penelitian
                        <span class="text-rose-500">*</span>
                    </label>
                    <input
                        id="bidang_penelitian"
                        v-model="form.bidang_penelitian"
                        type="text"
                        placeholder="Masukkan bidang penelitian, seperti Computer vision, Internet of Things (IoT), Web Development, Data mining, dll..."
                        required
                        :readonly="!canEditForm"
                        class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        :class="{
                            'cursor-not-allowed opacity-60': !canEditForm,
                        }"
                    />
                </div>

                <!-- Dosen Pembimbing 1 & 2 -->
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="space-y-1.5">
                        <label
                            for="pembimbing_1"
                            class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                        >
                            Pembimbing 1
                            <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="pembimbing_1"
                            v-model="form.pembimbing_1"
                            required
                            :disabled="!canEditForm"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                            <option value="" disabled>Pilih Pembimbing</option>
                            <option
                                v-for="dosen in availableDosenList"
                                :key="dosen.id"
                                :value="String(dosen.id)"
                            >
                                {{ dosen.name }}
                            </option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label
                            for="pembimbing_2"
                            class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                        >
                            Pembimbing 2
                            <span class="text-xs font-normal text-slate-500"
                                >(Opsional)</span
                            >
                        </label>
                        <select
                            id="pembimbing_2"
                            v-model="form.pembimbing_2"
                            :disabled="!canEditForm"
                            class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                        >
                            <option value="" disabled>Pilih Pembimbing</option>
                            <option
                                v-for="dosen in availableDosenList"
                                :key="dosen.id"
                                :value="String(dosen.id)"
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
                        :disabled="!canEditForm"
                        class="h-4 w-4 rounded-md border-slate-300 text-blue-600 focus:ring-blue-500 disabled:cursor-not-allowed disabled:opacity-60"
                    />
                    <label
                        for="konfirmasi"
                        class="cursor-pointer text-xs font-medium text-slate-700 dark:text-slate-300"
                        :class="{
                            'cursor-not-allowed opacity-60': !canEditForm,
                        }"
                    >
                        Saya sudah berkonsultasi dengan dosen pembimbing yang
                        dipilih dan mereka menyetujui judul serta bersedia
                        membimbing penelitian saya.
                    </label>
                </div>

                <!-- Helper Text untuk Validasi -->
                <div
                    v-if="!isFormValid"
                    class="flex items-start gap-2 rounded-lg bg-blue-50/50 p-3 text-xs text-blue-700 dark:bg-blue-950/30 dark:text-blue-300"
                >
                    <Info class="mt-0.5 h-4 w-4 flex-shrink-0" />
                    <span
                        >Lengkapi semua field bertanda
                        <span class="font-bold">*</span> dan centang persetujuan
                        untuk melanjutkan.</span
                    >
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-2">
                    <div
                        v-if="applicationStatus === 'disetujui'"
                        class="inline-flex items-center gap-2 rounded-xl border border-emerald-300 bg-emerald-100/80 px-5 py-2.5 text-xs font-bold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200"
                    >
                        <CheckCircle2
                            class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                        />
                        <span>Judul Disetujui</span>
                    </div>
                    <button
                        v-else
                        type="submit"
                        :disabled="
                            !canEditForm || !isFormValid || form.processing
                        "
                        class="rounded-xl bg-[#8CE79B] px-6 py-2.5 text-xs font-bold text-slate-900 transition-all hover:bg-[#7BD68A] active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                    >
                        Ajukan Judul
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>
