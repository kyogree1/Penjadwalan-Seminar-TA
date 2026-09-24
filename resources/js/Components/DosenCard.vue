<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    nama: string;
    role: string;
    initials?: string;
    variant?: 'rose' | 'blue' | 'indigo' | 'emerald';
}

const props = withDefaults(defineProps<Props>(), {
    initials: '',
    variant: 'rose',
});

const calculatedInitials = computed(() => {
    if (props.initials) return props.initials;
    const parts = props.nama
        .replace(/(Dr\.|Ir\.|S\.Kom\.|M\.Kom\.|S\.T\.|M\.T\.)/g, '')
        .trim()
        .split(' ');
    if (parts.length >= 2) {
        return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return props.nama.substring(0, 2).toUpperCase();
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'blue':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400';
        case 'indigo':
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400';
        case 'emerald':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400';
        case 'rose':
        default:
            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400';
    }
});
</script>

<template>
    <div
        class="flex items-center gap-3 rounded-xl border border-slate-200/90 bg-white p-3.5 shadow-2xs transition-colors hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700"
    >
        <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-2xs"
            :class="variantClasses"
        >
            {{ calculatedInitials }}
        </div>
        <div class="min-w-0">
            <p
                class="truncate text-xs font-bold text-slate-900 dark:text-white"
            >
                {{ nama }}
            </p>
            <p
                class="text-[11px] font-medium text-slate-500 dark:text-slate-400"
            >
                {{ role }}
            </p>
        </div>
    </div>
</template>
