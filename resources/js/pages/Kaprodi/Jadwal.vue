<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Calendar as CalendarIcon,
    Cpu,
    ExternalLink,
    Search,
} from 'lucide-vue-next';

import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import PublicSchedules, {
    type PeriodItem,
    type ScheduleItem,
} from '@/Components/PublicSchedules.vue';

const props = withDefaults(
    defineProps<{
        publicSchedules: ScheduleItem[];
        publicPeriods?: PeriodItem[];
    }>(),
    {
        publicPeriods: () => [],
    },
);

const selectedType = ref<'all' | 'sempro' | 'sidang'>('all');
const searchQuery = ref('');

const filteredSchedules = computed(() => {
    let list = props.publicSchedules;

    if (selectedType.value !== 'all') {
        list = list.filter((s) => s.type === selectedType.value);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase().trim();
        list = list.filter((s) => {
            const studentMatch = s.student_name?.toLowerCase().includes(q);
            const titleMatch = s.title?.toLowerCase().includes(q);
            const supervisorMatch = s.supervisors.some((sup) =>
                sup.toLowerCase().includes(q),
            );
            const examinerMatch = s.examiners.some((ex) =>
                ex.toLowerCase().includes(q),
            );
            return (
                studentMatch || titleMatch || supervisorMatch || examinerMatch
            );
        });
    }

    return list;
});
</script>

<template>
    <div class="space-y-6">
        <Head title="Jadwal Seminar & Sidang - SIPTA IF" />

        <PageHeaderBox
            title="Jadwal Seminar & Sidang"
            subtitle="Daftar jadwal seminar proposal dan sidang tugas akhir mahasiswa Informatika ITK."
        >
            <template #action>
                <div class="flex flex-wrap items-center gap-2">
                    <Link
                        href="/kaprodi/penjadwalan/plotting-sempro"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        <Cpu class="h-3.5 w-3.5 text-blue-500" />
                        <span>Plotting Sempro</span>
                    </Link>
                    <Link
                        href="/kaprodi/penjadwalan/plotting-ta"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        <CalendarIcon class="h-3.5 w-3.5 text-emerald-500" />
                        <span>Plotting Sidang TA</span>
                    </Link>
                    <a
                        href="/jadwal"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-blue-500"
                    >
                        <ExternalLink class="h-3.5 w-3.5" />
                        <span>Tampilan Publik</span>
                    </a>
                </div>
            </template>
        </PageHeaderBox>

        <!-- Filters: Type & Search -->
        <div
            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
        >
            <div
                class="inline-flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold shadow-2xs dark:border-slate-800 dark:bg-slate-900"
            >
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 transition-colors"
                    :class="
                        selectedType === 'all'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                    "
                    @click="selectedType = 'all'"
                >
                    Semua ({{ publicSchedules.length }})
                </button>
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 transition-colors"
                    :class="
                        selectedType === 'sempro'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                    "
                    @click="selectedType = 'sempro'"
                >
                    Sempro ({{
                        publicSchedules.filter((s) => s.type === 'sempro')
                            .length
                    }})
                </button>
                <button
                    type="button"
                    class="rounded-lg px-3 py-1.5 transition-colors"
                    :class="
                        selectedType === 'sidang'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                    "
                    @click="selectedType = 'sidang'"
                >
                    Sidang TA ({{
                        publicSchedules.filter((s) => s.type === 'sidang')
                            .length
                    }})
                </button>
            </div>

            <div class="relative min-w-[240px]">
                <Search
                    class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                    v-model="searchQuery"
                    type="search"
                    placeholder="Cari mahasiswa / dosen..."
                    class="w-full rounded-xl border border-slate-200 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-800 shadow-2xs placeholder:text-slate-400 focus:border-blue-500 focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                />
            </div>
        </div>

        <!-- Schedule Board Component -->
        <PublicSchedules
            :schedules="filteredSchedules"
            :periods="publicPeriods"
        />
    </div>
</template>
