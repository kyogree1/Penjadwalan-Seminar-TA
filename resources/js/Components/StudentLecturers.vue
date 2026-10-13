<script setup lang="ts">
import { computed } from 'vue';
import type { ScheduleRecord } from '@/types/r2-read-contracts';

const props = defineProps<{
    approvedTitle: {
        status: string;
        pembimbing1?: { name: string } | null;
        pembimbing2?: { name: string } | null;
    } | null;
    schedules: ScheduleRecord[];
}>();
const supervisors = computed(() =>
    props.approvedTitle?.status === 'disetujui' ? props.approvedTitle : null,
);
const exams = computed(() =>
    props.schedules.filter((row) => row.status === 'terpublikasi'),
);
</script>

<template>
    <section aria-label="Dosen Pembimbing & Penguji" class="space-y-3">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white">
            Dosen Pembimbing & Penguji
        </h3>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2.5">
                <h4
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                >
                    Pembimbing
                </h4>
                <p
                    v-if="
                        !supervisors?.pembimbing1 && !supervisors?.pembimbing2
                    "
                    class="text-xs text-slate-500"
                >
                    Pembimbing belum ditetapkan.
                </p>
                <div
                    v-for="(lecturer, index) in [
                        supervisors?.pembimbing1,
                        supervisors?.pembimbing2,
                    ]"
                    :key="index"
                    class="dark:bg-navy-surface flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-2xs dark:border-slate-800"
                >
                    <span
                        aria-hidden="true"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-xs font-bold text-rose-700 dark:bg-rose-950 dark:text-rose-300"
                        >P{{ index + 1 }}</span
                    >
                    <div class="min-w-0">
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ lecturer?.name ?? 'Belum ditetapkan' }}
                        </p>
                        <p
                            class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            Pembimbing {{ index + 1 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="space-y-2.5">
                <h4
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                >
                    Penguji
                </h4>
                <p v-if="!exams.length" class="text-xs text-slate-500">
                    Penguji belum ditetapkan pada jadwal terpublikasi.
                </p>
                <template v-for="exam in exams" :key="exam.id">
                    <div
                        v-for="(lecturer, index) in [
                            exam.penguji1,
                            exam.penguji2,
                        ]"
                        :key="index"
                        class="dark:bg-navy-surface flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-3 shadow-2xs dark:border-slate-800"
                    >
                        <span
                            aria-hidden="true"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                            >P{{ index + 1 }}</span
                        >
                        <div class="min-w-0">
                            <p
                                class="text-xs font-bold text-slate-900 dark:text-white"
                            >
                                {{ lecturer?.name ?? 'Belum ditetapkan' }}
                            </p>
                            <p
                                class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                Penguji {{ index + 1 }} •
                                {{
                                    exam.tipe === 'sempro'
                                        ? 'Seminar Proposal'
                                        : 'Sidang Tugas Akhir'
                                }}
                            </p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>
