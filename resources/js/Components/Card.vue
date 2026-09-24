<script setup lang="ts">
interface Props {
    title?: string;
    subtitle?: string;
    noPadding?: boolean;
}

withDefaults(defineProps<Props>(), {
    title: '',
    subtitle: '',
    noPadding: false,
});
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-shadow duration-200 dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Card Header -->
        <div
            v-if="title || subtitle || $slots.header || $slots.action"
            class="flex items-center justify-between border-b border-slate-100 px-6 py-4 dark:border-slate-800"
        >
            <slot name="header">
                <div>
                    <h3
                        v-if="title"
                        class="text-base font-semibold text-slate-900 dark:text-white"
                    >
                        {{ title }}
                    </h3>
                    <p
                        v-if="subtitle"
                        class="text-xs text-slate-500 dark:text-slate-400"
                    >
                        {{ subtitle }}
                    </p>
                </div>
            </slot>

            <div v-if="$slots.action" class="flex items-center gap-2">
                <slot name="action" />
            </div>
        </div>

        <!-- Card Body -->
        <div :class="[noPadding ? '' : 'p-6']">
            <slot />
        </div>

        <!-- Card Footer -->
        <div
            v-if="$slots.footer"
            class="border-t border-slate-100 bg-slate-50/50 px-6 py-3.5 dark:border-slate-800 dark:bg-slate-950/40"
        >
            <slot name="footer" />
        </div>
    </div>
</template>
