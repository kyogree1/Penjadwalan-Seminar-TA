<script setup lang="ts">
import { FileDown } from 'lucide-vue-next';

interface Props {
    label: string;
    code?: string;
    href?: string;
}

withDefaults(defineProps<Props>(), {
    code: '',
    href: '#',
});
</script>

<template>
    <a
        :href="href"
        class="group flex items-center gap-2 text-xs font-semibold text-blue-600 transition-colors hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300"
    >
        <FileDown
            class="h-3.5 w-3.5 shrink-0 transition-transform group-hover:scale-110"
        />
        <span>{{ label }}</span>
        <span
            v-if="code"
            class="rounded-md bg-blue-50 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
        >
            {{ code }}
        </span>
    </a>
</template>
