<script setup lang="ts">
interface Props {
    title: string;
    subtitle?: string;
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex flex-col gap-3 border-b border-slate-200/80 pb-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
    >
        <div class="min-w-0">
            <h2
                class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
            >
                {{ title }}
            </h2>
            <p
                v-if="subtitle"
                class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
            >
                {{ subtitle }}
            </p>
        </div>

        <div v-if="$slots.action" class="flex flex-wrap items-center gap-3">
            <slot name="action" />
        </div>
    </div>
</template>
