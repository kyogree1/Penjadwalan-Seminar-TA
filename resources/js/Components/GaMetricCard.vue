<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title: string;
    value: string | number;
    subtext?: string;
    badge?: string;
    variant?: 'blue' | 'emerald' | 'amber' | 'rose' | 'purple';
}

const props = withDefaults(defineProps<Props>(), {
    subtext: '',
    badge: '',
    variant: 'blue',
});

const variantStyles = computed(() => {
    switch (props.variant) {
        case 'emerald':
            return {
                bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                border: 'border-emerald-200 dark:border-emerald-900/50',
                badgeBg:
                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300',
            };
        case 'amber':
            return {
                bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                border: 'border-amber-200 dark:border-amber-900/50',
                badgeBg:
                    'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300',
            };
        case 'rose':
            return {
                bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                border: 'border-rose-200 dark:border-rose-900/50',
                badgeBg:
                    'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300',
            };
        case 'purple':
            return {
                bg: 'bg-purple-500/10 text-purple-600 dark:text-purple-400',
                border: 'border-purple-200 dark:border-purple-900/50',
                badgeBg:
                    'bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300',
            };
        default:
            return {
                bg: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
                border: 'border-blue-200 dark:border-blue-900/50',
                badgeBg:
                    'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300',
            };
    }
});
</script>

<template>
    <div
        class="relative overflow-hidden rounded-2xl border bg-white p-5 shadow-xs transition-all dark:bg-[#0E1626]"
        :class="variantStyles.border"
    >
        <div class="flex items-start justify-between gap-2">
            <p
                class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400"
            >
                {{ title }}
            </p>
            <span
                v-if="badge"
                class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                :class="variantStyles.badgeBg"
            >
                {{ badge }}
            </span>
        </div>

        <div class="mt-2 flex items-baseline gap-2">
            <h3 class="text-2xl font-black text-slate-900 dark:text-white">
                {{ value }}
            </h3>
        </div>

        <p
            v-if="subtext"
            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
        >
            {{ subtext }}
        </p>

        <!-- Slot for icon or mini sparkline / progress -->
        <slot />
    </div>
</template>
