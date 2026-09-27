<script setup lang="ts">
import { computed, ref } from "vue";
import { Head, useForm, usePage } from "@inertiajs/vue3";
import { AlertCircle, Send, X } from "lucide-vue-next";

import CollapsibleCard from "@/Components/CollapsibleCard.vue";
import DosenTeamSection from "@/Components/DosenTeamSection.vue";
import PageHeaderBox from "@/Components/PageHeaderBox.vue";
import ProfileHeaderCard from "@/Components/ProfileHeaderCard.vue";
import RiwayatTimelineCard from "@/Components/RiwayatTimelineCard.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import TemplateDownloadCard from "@/Components/TemplateDownloadCard.vue";
import AppLayout from "@/Layouts/AppLayout.vue";

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user);
const studentName = computed(
    () => authUser.value?.name || "Akmal Falah Maulana",
);
const studentNim = computed(
    () =>
        authUser.value?.username ||
        authUser.value?.nim_nip?.replace("NIM: ", "") ||
        "11231006",
);
const studentProdi = computed(() => authUser.value?.prodi || "Informatika");
const studentInitials = computed(() => {
    if (!authUser.value?.name) return "AF";
    return authUser.value.name
        .split(" ")
        .filter(Boolean)
        .slice(0, 2)
        .map((word: string) => word[0]?.toUpperCase())
        .join("");
});

type SemproStatus =
    | "draft"
    | "diajukan"
    | "revisi"
    | "diverifikasi"
    | "siap_jadwal"
    | "selesai"
    | "ditolak";

type FileField = "lembar_kehadiran_file" | "proposal_file" | "turnitin_file";

const status = ref<SemproStatus>("draft");
const revisionNote = ref("");
const showRevisionNote = ref(false);
const maxFileSize = 10 * 1024 * 1024;
const fileErrors = ref<Partial<Record<FileField, string>>>({});

const form = useForm({
    judul_ta:
        "Sistem Penjadwalan Seminar dan Chatbot Layanan Akademik Menggunakan Algoritma Genetika dan Large Language Model",
    bentuk_ta: "Skripsi Reguler (Pengembangan Perangkat Lunak & AI)",
    lembar_kehadiran_file: null as File | null,
    proposal_file: null as File | null,
    turnitin_file: null as File | null,
    lokasi_mitra: "",
    skor_iaet: "" as number | "",
});

const statusLabel = computed(() => {
    const labels: Record<SemproStatus, string> = {
        draft: "Belum Mengisi",
        diajukan: "Diajukan",
        revisi: "Perlu Revisi",
        diverifikasi: "Berkas Diverifikasi",
        siap_jadwal: "Siap Jadwal",
        selesai: "Selesai",
        ditolak: "Ditolak",
    };
    return labels[status.value];
});

const canEdit = computed(
    () => status.value === "draft" || status.value === "revisi",
);
const requiredFiles = computed(
    () =>
        !!form.lembar_kehadiran_file &&
        !!form.proposal_file &&
        !!form.turnitin_file,
);
const isIaetValid = computed(
    () =>
        form.skor_iaet === "" ||
        (typeof form.skor_iaet === "number" &&
            Number.isFinite(form.skor_iaet) &&
            form.skor_iaet >= 0),
);

const isFormValid = computed(
    () =>
        requiredFiles.value &&
        isIaetValid.value &&
        Object.keys(fileErrors.value).length === 0,
);

const formatFileSize = (bytes: number) => {
    if (bytes < 1024 * 1024) return `${Math.ceil(bytes / 1024)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const fileName = (file: File | null) => file?.name || "";

const setFile = (field: FileField, event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] || null;
    fileErrors.value[field] = "";

    if (!file) {
        form[field] = null;
        delete fileErrors.value[field];
        return;
    }

    if (
        file.type !== "application/pdf" &&
        !file.name.toLowerCase().endsWith(".pdf")
    ) {
        fileErrors.value[field] = "File harus berformat PDF.";
        form[field] = null;
        input.value = "";
        return;
    }

    if (file.size > maxFileSize) {
        fileErrors.value[field] = "Ukuran file maksimal 10 MB.";
        form[field] = null;
        input.value = "";
        return;
    }

    form[field] = file;
    delete fileErrors.value[field];
};

const removeFile = (field: FileField, inputId: string) => {
    form[field] = null;
    delete fileErrors.value[field];
    const input = document.getElementById(inputId) as HTMLInputElement | null;
    if (input) input.value = "";
};

const submitSempro = () => {
    if (!canEdit.value || !isFormValid.value) return;
    status.value = "diajukan";
    showRevisionNote.value = false;
};
</script>

<template>
    <AppLayout title="Seminar Proposal TA">
        <Head title="Seminar Proposal TA - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <PageHeaderBox title="Seminar Proposal TA" />

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
                <StatusBadge
                    :status="status === 'siap_jadwal' ? 'diajukan' : status"
                    :text="statusLabel"
                />
            </ProfileHeaderCard>

            <div
                v-if="status === 'revisi' && revisionNote"
                class="flex items-start gap-3 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-sm text-orange-800 dark:border-orange-900 dark:bg-orange-950/30 dark:text-orange-200"
            >
                <AlertCircle class="mt-0.5 h-5 w-5 shrink-0" />
                <div>
                    <p class="font-bold">Catatan revisi</p>
                    <p class="mt-1">{{ revisionNote }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <DosenTeamSection />

                    <TemplateDownloadCard
                        :templates="[
                            { code: 'TA-01A', label: 'Kesediaan Membimbing' },
                            {
                                code: 'TA-02',
                                label: 'Persetujuan Seminar Proposal',
                            },
                            { code: 'TA-04', label: 'Monitoring Bimbingan' },
                        ]"
                    />

                    <CollapsibleCard
                        title="Formulir Pendaftaran Seminar Proposal"
                        :default-open="true"
                    >
                        <form class="space-y-6" @submit.prevent="submitSempro">
                            <div
                                class="space-y-3 border-b border-slate-100 pb-4 text-sm dark:border-slate-800"
                            >
                                <div>
                                    <span
                                        class="font-bold text-slate-800 dark:text-slate-200"
                                        >Judul TA:</span
                                    >
                                    <span
                                        class="ml-1 text-slate-600 dark:text-slate-400"
                                        >{{ form.judul_ta }}</span
                                    >
                                </div>
                                <div>
                                    <span
                                        class="font-bold text-slate-800 dark:text-slate-200"
                                        >Bentuk TA:</span
                                    >
                                    <span
                                        class="ml-1 text-slate-600 dark:text-slate-400"
                                        >{{ form.bentuk_ta }}</span
                                    >
                                </div>
                            </div>

                            <div class="grid gap-5">
                                <div
                                    v-for="document in [
                                        {
                                            field: 'lembar_kehadiran_file',
                                            id: 'file-kehadiran',
                                            label: 'Lembar Bukti Kehadiran Seminar Proposal TA',
                                            code: 'Form TA-03D',
                                            model: form.lembar_kehadiran_file,
                                        },
                                        {
                                            field: 'proposal_file',
                                            id: 'file-proposal',
                                            label: 'Proposal Tugas Akhir',
                                            code: '',
                                            model: form.proposal_file,
                                        },
                                        {
                                            field: 'turnitin_file',
                                            id: 'file-turnitin',
                                            label: 'Bukti Plagiasi (Turnitin)',
                                            code: '',
                                            model: form.turnitin_file,
                                        },
                                    ]"
                                    :key="document.field"
                                    class="space-y-2"
                                >
                                    <label
                                        :for="document.id"
                                        class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ document.label }}
                                        <span
                                            v-if="document.code"
                                            class="font-normal text-slate-500"
                                            >({{ document.code }})</span
                                        >
                                        <span class="text-rose-500">*</span>
                                    </label>
                                    <div
                                        class="rounded-xl border border-slate-300 bg-slate-50/50 p-3 dark:border-slate-700 dark:bg-slate-900"
                                    >
                                        <div
                                            v-if="document.model"
                                            class="flex flex-wrap items-center justify-between gap-3"
                                        >
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-xs font-semibold text-slate-800 dark:text-slate-200"
                                                >
                                                    {{
                                                        fileName(document.model)
                                                    }}
                                                </p>
                                                <p
                                                    class="mt-1 text-[11px] text-slate-500"
                                                >
                                                    {{
                                                        formatFileSize(
                                                            document.model.size,
                                                        )
                                                    }}
                                                </p>
                                            </div>
                                            <button
                                                v-if="canEdit"
                                                type="button"
                                                class="inline-flex items-center gap-1 text-xs font-semibold text-rose-600 dark:text-rose-400"
                                                @click="
                                                    removeFile(
                                                        document.field as FileField,
                                                        document.id,
                                                    )
                                                "
                                            >
                                                <X class="h-3.5 w-3.5" /> Hapus
                                            </button>
                                        </div>
                                        <div
                                            v-else
                                            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <input
                                                :id="document.id"
                                                type="file"
                                                accept="application/pdf,.pdf"
                                                :disabled="!canEdit"
                                                class="max-w-full text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-slate-700 hover:file:bg-slate-100 disabled:opacity-50"
                                                @change="
                                                    setFile(
                                                        document.field as FileField,
                                                        $event,
                                                    )
                                                "
                                            />
                                            <span
                                                class="text-[11px] text-slate-400"
                                                >PDF, maks. 10 MB</span
                                            >
                                        </div>
                                    </div>
                                    <p
                                        v-if="
                                            fileErrors[
                                                document.field as FileField
                                            ]
                                        "
                                        class="text-xs text-rose-600 dark:text-rose-400"
                                    >
                                        {{
                                            fileErrors[
                                                document.field as FileField
                                            ]
                                        }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <label
                                    for="lokasi_mitra"
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Lokasi/Mitra Penelitian
                                    <span
                                        class="text-xs font-normal text-slate-500"
                                        >(Opsional)</span
                                    ></label
                                >
                                <input
                                    id="lokasi_mitra"
                                    v-model="form.lokasi_mitra"
                                    :readonly="!canEdit"
                                    type="text"
                                    placeholder="Contoh: PT Lorem Ipsum / Laboratorium Universitas / Balikpapan"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                    :class="{
                                        'cursor-not-allowed opacity-60':
                                            !canEdit,
                                    }"
                                />
                            </div>

                            <div class="space-y-2">
                                <label
                                    for="skor_iaet"
                                    class="block text-sm font-bold text-slate-800 dark:text-slate-200"
                                    >Skor IAET
                                    <span
                                        class="text-xs font-normal text-slate-500"
                                        >(Opsional)</span
                                    ></label
                                >
                                <input
                                    id="skor_iaet"
                                    v-model.number="form.skor_iaet"
                                    type="number"
                                    min="0"
                                    step="any"
                                    :readonly="!canEdit"
                                    placeholder="Masukkan skor IAET"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50/50 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                />
                                <p
                                    v-if="!isIaetValid"
                                    class="text-xs text-rose-600"
                                    role="alert"
                                >
                                    Skor IAET harus berupa angka nol atau lebih.
                                </p>
                            </div>

                            <div
                                v-if="canEdit && !isFormValid"
                                class="flex items-start gap-2 rounded-xl bg-blue-50/60 p-3 text-xs text-blue-700 dark:bg-blue-950/30 dark:text-blue-300"
                            >
                                <AlertCircle class="mt-0.5 h-4 w-4 shrink-0" />
                                <span
                                    >Lengkapi tiga dokumen wajib dalam format
                                    PDF sebelum mengajukan Seminar
                                    Proposal.</span
                                >
                            </div>

                            <div
                                class="flex flex-col justify-between gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center dark:border-slate-800"
                            >
                                <span
                                    v-if="!canEdit"
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                    >Form terkunci karena status
                                    {{ statusLabel }}.</span
                                >
                                <span
                                    v-else
                                    class="text-xs text-slate-500 dark:text-slate-400"
                                    >Dokumen wajib bertanda
                                    <span class="font-bold text-rose-500"
                                        >*</span
                                    >.</span
                                >
                                <button
                                    type="submit"
                                    :disabled="!canEdit || !isFormValid"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#8CE79B] px-6 py-2.5 text-xs font-bold text-slate-900 transition-all hover:bg-[#7BD68A] active:scale-95 disabled:pointer-events-none disabled:opacity-40"
                                >
                                    <Send class="h-3.5 w-3.5" />
                                    {{
                                        status === "revisi"
                                            ? "Ajukan Ulang"
                                            : "Ajukan Seminar Proposal"
                                    }}
                                </button>
                            </div>
                        </form>
                    </CollapsibleCard>
                </div>

                <div class="space-y-6">
                    <RiwayatTimelineCard
                        title="Riwayat Pendaftaran"
                        empty-text="Belum ada riwayat pendaftaran"
                        empty-subtext="Riwayat akan muncul setelah pengajuan dikirim."
                    />
                    <RiwayatTimelineCard
                        title="Riwayat Pelaksanaan & Hasil"
                        empty-text="Belum ada pelaksanaan"
                        empty-subtext="Jadwal dan hasil Seminar Proposal akan muncul di sini."
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
