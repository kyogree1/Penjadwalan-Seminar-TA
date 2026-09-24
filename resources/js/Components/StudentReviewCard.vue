<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowRight, BookOpen, Clock, FileCheck, User } from 'lucide-vue-next';
import StatusBadge from '@/Components/StatusBadge.vue';

interface Props {
    name: string;
    nim: string;
    avatar?: string;
    angkatan?: string | number;
    judul: string;
    statusText: string;
    statusVariant?: 'blue' | 'emerald' | 'amber' | 'purple' | 'slate' | 'rose';
    bidang?: string;
    bimbinganCount?: number;
    turnitinScore?: number;
    roleAs?: string; // e.g. "Pembimbing 1", "Pembimbing 2", "Penguji 1", "Penguji 2"
    actionText?: string;
    actionHref?: string;
}

const props = withDefaults(defineProps<Props>(), {
    avatar: '',
    angkatan: '2023',
    statusVariant: 'blue',
    bidang: 'Informatika',
    bimbinganCount: 0,
    turnitinScore: undefined,
    roleAs: 'Pembimbing 1',
    actionText: 'Review Logbook',
    actionHref: '',
});

const initials = computed(() => {
    if (props.avatar) return props.avatar;
    return props.name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
});
</script>

<template>
    <div
        class="group relative rounded-2xl border border-slate-200/80 bg-white p-5 transition-all duration-200 hover:border-blue-300 hover:shadow-md hover:shadow-blue-500/5 dark:border-slate-800 dark:bg-[#0E1626] dark:hover:border-slate-700"
    >
        <!-- Header: Avatar, Name, Role Badge, Status Badge -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="flex items-start gap-3.5">
                <div
                    class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-500 text-sm font-bold text-white shadow-md shadow-blue-500/20"
                >
                    {{ initials }}
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            {{ name }}
                        </h4>
                        <span
                            class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                        >
                            {{ nim }}
                        </span>
                        <span
                            v-if="roleAs"
                            class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-400"
                        >
                            {{ roleAs }}
                        </span>
                    </div>
                    <p
                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Informatika ITK • Angkatan {{ angkatan }}
                    </p>
                </div>
            </div>

            <div>
                <StatusBadge :text="statusText" :variant="statusVariant" />
            </div>
        </div>

        <!-- Judul Tugas Akhir -->
        <div class="mt-3.5 rounded-xl bg-slate-50/80 p-3 dark:bg-slate-800/40">
            <p
                class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
            >
                Judul Tugas Akhir:
            </p>
            <p
                class="mt-1 text-xs leading-relaxed font-semibold text-slate-800 dark:text-slate-200"
            >
                "{{ judul }}"
            </p>
        </div>

        <!-- Quick Metatags & Action -->
        <div
            class="mt-4 flex flex-col gap-3 border-t border-slate-100 pt-3.5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
        >
            <div
                class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400"
            >
                <span class="inline-flex items-center gap-1.5 font-medium">
                    <BookOpen class="h-3.5 w-3.5 text-blue-500" />
                    {{ bidang }}
                </span>
                <span
                    v-if="bimbinganCount !== undefined"
                    class="inline-flex items-center gap-1.5 font-medium"
                >
                    <FileCheck class="h-3.5 w-3.5 text-emerald-500" />
                    {{ bimbinganCount }} Sesi Bimbingan
                </span>
                <span
                    v-if="turnitinScore !== undefined"
                    class="inline-flex items-center gap-1.5 font-medium"
                    :class="
                        turnitinScore <= 20
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : 'text-amber-600 dark:text-amber-400'
                    "
                >
                    Turnitin: {{ turnitinScore }}%
                </span>
            </div>

            <!-- Actions slot or Default Link -->
            <div class="flex items-center gap-2">
                <slot name="actions">
                    <Link
                        v-if="actionHref"
                        :href="actionHref"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20"
                    >
                        <span>{{ actionText }}</span>
                        <ArrowRight class="h-3.5 w-3.5" />
                    </Link>
                </slot>
            </div>
        </div>
    </div>
</template>
