<script setup lang="ts">
import type { Component } from 'vue';

interface Props {
    title: string;
    value: string | number;
    subtitle?: string;
    icon?: Component;
    trendText?: string;
    trendType?: 'up' | 'down' | 'neutral';
}

withDefaults(defineProps<Props>(), {
    subtitle: '',
    trendText: '',
    trendType: 'neutral',
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
            <component
                v-if="icon"
                :is="icon"
                class="h-4 w-4 shrink-0 text-slate-500 dark:text-slate-400"
                aria-hidden="true"
            />
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
