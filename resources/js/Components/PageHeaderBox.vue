<script setup lang="ts">
interface Props {
    title: string;
    subtitle?: string;
}

defineProps<Props>();
</script>

<template>
    <div
        class="flex flex-col gap-3 rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
    >
        <div>
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

        <div v-if="$slots.action" class="flex items-center gap-3">
            <slot name="action" />
        </div>
    </div>
</template>
