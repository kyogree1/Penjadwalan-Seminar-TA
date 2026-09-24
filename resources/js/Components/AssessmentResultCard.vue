<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title: string;
    pembimbing?: string;
    result: string;
    variant?: 'neutral' | 'danger' | 'success' | 'warning';
}

const props = withDefaults(defineProps<Props>(), {
    pembimbing: '',
    variant: 'neutral',
});

const calculatedVariant = computed(() => {
    if (props.variant !== 'neutral') return props.variant;
    const lower = props.result.toLowerCase();
    if (lower.includes('tidak lulus') || lower.includes('ditolak'))
        return 'danger';
    if (lower.includes('revisi')) return 'neutral';
    if (lower.includes('lulus') || lower.includes('disetujui'))
        return 'success';
    return 'neutral';
});

const badgeClasses = computed(() => {
    switch (calculatedVariant.value) {
        case 'danger':
            return 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300';
        case 'success':
            return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300';
        case 'warning':
            return 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300';
        case 'neutral':
        default:
            return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
    }
});
</script>

<template>
    <div
        class="flex flex-col gap-2 rounded-xl border border-slate-200/80 p-4 transition-colors hover:border-slate-300 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:hover:border-slate-700"
    >
        <div>
            <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                {{ title }}
            </h4>
            <p
                v-if="pembimbing"
                class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400"
            >
                {{ pembimbing }}
            </p>
        </div>
        <span
            class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-bold shadow-2xs"
            :class="badgeClasses"
        >
            {{ result }}
        </span>
    </div>
</template>
