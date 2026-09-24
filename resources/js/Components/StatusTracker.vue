<script setup lang="ts">
import { computed } from 'vue';
import {
    AlertTriangle,
    Check,
    Clock,
    FileCheck2,
    FileEdit,
    Send,
    XCircle,
} from 'lucide-vue-next';

export type StageStatus =
    | 'draft'
    | 'diajukan'
    | 'verifikasi'
    | 'disetujui'
    | 'ditolak'
    | 'revisi';

export interface StepItem {
    id: string;
    title: string;
    description: string;
    timestamp?: string;
}

interface Props {
    currentStatus: StageStatus;
    feedbackNotes?: string;
    customSteps?: StepItem[];
}

const props = withDefaults(defineProps<Props>(), {
    currentStatus: 'draft',
    feedbackNotes: '',
    customSteps: undefined,
});

const defaultSteps: StepItem[] = [
    {
        id: 'draft',
        title: 'Draft Berkas',
        description: 'Pengisian data & unggah berkas persyaratan',
    },
    {
        id: 'diajukan',
        title: 'Diajukan',
        description: 'Berkas dikirim ke tim koordinator',
    },
    {
        id: 'verifikasi',
        title: 'Verifikasi Berkas',
        description: 'Pemeriksaan kelengkapan & keabsahan dokumen',
    },
    {
        id: 'disetujui',
        title: 'Disetujui / ACC',
        description: 'Jadwal & penguji telah ditetapkan',
    },
];

const steps = computed(() => props.customSteps || defaultSteps);

// Menghitung indeks aktif berdasarkan currentStatus
const statusIndexMap: Record<StageStatus, number> = {
    draft: 0,
    diajukan: 1,
    verifikasi: 2,
    disetujui: 3,
    ditolak: 2, // Biasanya ditolak saat verifikasi
    revisi: 2,
};

const currentStepIndex = computed(
    () => statusIndexMap[props.currentStatus] ?? 0,
);

const getStepState = (index: number) => {
    if (props.currentStatus === 'ditolak' && index === currentStepIndex.value) {
        return 'rejected';
    }
    if (props.currentStatus === 'revisi' && index === currentStepIndex.value) {
        return 'revision';
    }
    if (index < currentStepIndex.value) {
        return 'completed';
    }
    if (index === currentStepIndex.value) {
        return props.currentStatus === 'disetujui' ? 'completed' : 'active';
    }
    return 'upcoming';
};
</script>

<template>
    <div
        class="w-full rounded-2xl border border-slate-200 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Header Tracker -->
        <div
            class="mb-6 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
        >
            <div>
                <h3
                    class="text-base font-semibold text-slate-900 dark:text-white"
                >
                    Alur & Status Pendaftaran
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Pantau proses verifikasi berkas Tugas Akhir Anda
                </p>
            </div>

            <!-- Current Status Badge -->
            <div>
                <span
                    v-if="currentStatus === 'draft'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                    <FileEdit class="h-3.5 w-3.5" />
                    Status: Draft
                </span>
                <span
                    v-else-if="currentStatus === 'diajukan'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                >
                    <Send class="h-3.5 w-3.5" />
                    Status: Menunggu Antrian
                </span>
                <span
                    v-else-if="currentStatus === 'verifikasi'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-950/60 dark:text-amber-300"
                >
                    <Clock class="h-3.5 w-3.5" />
                    Status: Sedang Diverifikasi
                </span>
                <span
                    v-else-if="currentStatus === 'disetujui'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                >
                    <FileCheck2 class="h-3.5 w-3.5" />
                    Status: Disetujui (ACC)
                </span>
                <span
                    v-else-if="currentStatus === 'ditolak'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700 dark:bg-rose-950/60 dark:text-rose-300"
                >
                    <XCircle class="h-3.5 w-3.5" />
                    Status: Ditolak
                </span>
                <span
                    v-else-if="currentStatus === 'revisi'"
                    class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-700 dark:bg-orange-950/60 dark:text-orange-300"
                >
                    <AlertTriangle class="h-3.5 w-3.5" />
                    Status: Perlu Revisi Berkas
                </span>
            </div>
        </div>

        <!-- Step Wizard Bar -->
        <div class="relative">
            <!-- Progress Line Desktop -->
            <div
                class="absolute top-5 left-6 hidden h-0.5 w-[calc(100%-3rem)] bg-slate-200 md:block dark:bg-slate-800"
                aria-hidden="true"
            >
                <div
                    class="h-full bg-blue-600 transition-all duration-500 dark:bg-blue-500"
                    :style="{
                        width: `${(currentStepIndex / (steps.length - 1)) * 100}%`,
                    }"
                />
            </div>

            <!-- Steps List -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 md:gap-4">
                <div
                    v-for="(step, index) in steps"
                    :key="step.id"
                    class="relative flex items-start gap-4 md:flex-col md:items-center md:text-center"
                >
                    <!-- Step Indicator Circle -->
                    <div
                        class="relative z-10 flex shrink-0 items-center justify-center"
                    >
                        <!-- State: Completed -->
                        <div
                            v-if="getStepState(index) === 'completed'"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-600 text-white shadow-md ring-4 shadow-emerald-500/20 ring-white dark:ring-slate-900"
                        >
                            <Check class="h-5 w-5 stroke-[2.5]" />
                        </div>

                        <!-- State: Active / In-Progress -->
                        <div
                            v-else-if="getStepState(index) === 'active'"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-600 text-white shadow-md ring-4 shadow-blue-500/30 ring-blue-100 dark:ring-blue-950"
                        >
                            <span class="text-sm font-bold">{{
                                index + 1
                            }}</span>
                        </div>

                        <!-- State: Rejected -->
                        <div
                            v-else-if="getStepState(index) === 'rejected'"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-600 text-white shadow-md ring-4 shadow-rose-500/30 ring-rose-100 dark:ring-rose-950"
                        >
                            <XCircle class="h-5 w-5 stroke-[2.5]" />
                        </div>

                        <!-- State: Revision -->
                        <div
                            v-else-if="getStepState(index) === 'revision'"
                            class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-600 text-white shadow-md ring-4 shadow-orange-500/30 ring-orange-100 dark:ring-orange-950"
                        >
                            <AlertTriangle class="h-5 w-5 stroke-[2.5]" />
                        </div>

                        <!-- State: Upcoming / Pending -->
                        <div
                            v-else
                            class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-slate-300 bg-white text-slate-400 ring-4 ring-white dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500 dark:ring-slate-900"
                        >
                            <span class="text-sm font-semibold">{{
                                index + 1
                            }}</span>
                        </div>
                    </div>

                    <!-- Step Content -->
                    <div class="min-w-0 flex-1 md:mt-2">
                        <p
                            class="text-sm font-semibold"
                            :class="[
                                getStepState(index) === 'completed'
                                    ? 'text-emerald-700 dark:text-emerald-400'
                                    : getStepState(index) === 'active'
                                      ? 'text-blue-600 dark:text-blue-400'
                                      : getStepState(index) === 'rejected'
                                        ? 'text-rose-600 dark:text-rose-400'
                                        : getStepState(index) === 'revision'
                                          ? 'text-orange-600 dark:text-orange-400'
                                          : 'text-slate-500 dark:text-slate-400',
                            ]"
                        >
                            {{ step.title }}
                        </p>
                        <p
                            class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ step.description }}
                        </p>
                        <p
                            v-if="step.timestamp"
                            class="mt-1 text-[11px] font-medium text-slate-400 dark:text-slate-500"
                        >
                            {{ step.timestamp }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Catatan Feedback / Revisi Box (Jika ada) -->
        <div
            v-if="feedbackNotes"
            class="mt-6 rounded-xl border p-4"
            :class="[
                currentStatus === 'ditolak'
                    ? 'border-rose-200 bg-rose-50/70 text-rose-900 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-200'
                    : currentStatus === 'revisi'
                      ? 'border-orange-200 bg-orange-50/70 text-orange-900 dark:border-orange-900/50 dark:bg-orange-950/30 dark:text-orange-200'
                      : 'border-blue-200 bg-blue-50/70 text-blue-900 dark:border-blue-900/50 dark:bg-blue-950/30 dark:text-blue-200',
            ]"
        >
            <div class="flex items-start gap-3">
                <AlertTriangle
                    v-if="
                        currentStatus === 'ditolak' ||
                        currentStatus === 'revisi'
                    "
                    class="mt-0.5 h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400"
                />
                <Clock
                    v-else
                    class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400"
                />
                <div>
                    <h4 class="text-sm font-semibold">
                        Catatan dari Koordinator / Tim Akademik:
                    </h4>
                    <p class="mt-1 text-xs leading-relaxed whitespace-pre-line">
                        {{ feedbackNotes }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
