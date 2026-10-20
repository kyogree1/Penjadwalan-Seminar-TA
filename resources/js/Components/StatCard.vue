<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';

interface Props {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: Component;
    iconColor?: 'blue' | 'indigo' | 'emerald' | 'amber' | 'rose' | 'slate';
    trendText?: string;
    trendType?: 'up' | 'down' | 'neutral';
}

const props = withDefaults(defineProps<Props>(), {
    subtitle: '',
    iconColor: 'blue',
    trendText: '',
    trendType: 'neutral',
});

const colorClasses = computed(() => {
    switch (props.iconColor) {
        case 'indigo':
            return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400';
        case 'emerald':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400';
        case 'amber':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400';
        case 'rose':
            return 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-400';
        case 'slate':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        case 'blue':
        default:
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-400';
    }
});
</script>

<template>
    <div
        class="dark:bg-navy-surface flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-3 sm:p-4 dark:border-slate-800"
    >
        <div class="flex items-center justify-between">
            <span
                class="min-h-10 text-sm leading-snug font-medium text-slate-600 dark:text-slate-400"
            >
                {{ title }}
            </span>
            <div
                v-if="icon"
                class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg"
                :class="colorClasses"
            >
                <component :is="icon" class="h-4 w-4" aria-hidden="true" />
            </div>
        </div>

        <div class="mt-2">
            <p
                class="text-xl font-semibold text-slate-900 tabular-nums dark:text-white"
            >
                {{ value }}
            </p>
            <div
                v-if="subtitle || trendText"
                class="mt-1 flex items-center gap-2 text-xs"
            >
                <span
                    v-if="subtitle"
                    class="text-slate-500 dark:text-slate-400"
                >
                    {{ subtitle }}
                </span>
                <span
                    v-if="trendText"
                    class="font-semibold"
                    :class="[
                        trendType === 'up'
                            ? 'text-emerald-600 dark:text-emerald-400'
                            : trendType === 'down'
                              ? 'text-rose-600 dark:text-rose-400'
                              : 'text-slate-500 dark:text-slate-400',
                    ]"
                >
                    {{ trendText }}
                </span>
            </div>
        </div>
    </div>
</template>
