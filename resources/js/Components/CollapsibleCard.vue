<script setup lang="ts">
import { computed, ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

interface Props {
    title: string;
    statusText?: string;
    statusVariant?: 'yellow' | 'red' | 'green' | 'blue' | 'neutral';
    defaultOpen?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    statusText: '',
    statusVariant: 'yellow',
    defaultOpen: true,
});

const isOpen = ref(props.defaultOpen);

const toggle = () => {
    isOpen.value = !isOpen.value;
};

const badgeClasses = computed(() => {
    switch (props.statusVariant) {
        case 'red':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-200';
        case 'green':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200';
        case 'blue':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-200';
        case 'neutral':
            return 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
        case 'yellow':
        default:
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/80 dark:text-amber-200';
    }
});
</script>

<template>
    <div
        class="dark:bg-navy-surface rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800"
    >
        <!-- Header Toggle Bar -->
        <div
            class="flex cursor-pointer items-center justify-between select-none"
            :class="{
                'border-b border-slate-100 pb-4 dark:border-slate-800': isOpen,
            }"
            @click="toggle"
        >
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                {{ title }}
            </h3>

            <div class="flex items-center gap-3">
                <span
                    v-if="statusText"
                    class="rounded-xl px-3 py-1 text-xs font-bold shadow-2xs"
                    :class="badgeClasses"
                >
                    {{ statusText }}
                </span>

                <ChevronDown
                    class="h-4 w-4 text-slate-500 transition-transform duration-200"
                    :class="{ 'rotate-180': isOpen }"
                />
            </div>
        </div>

        <!-- Content Body -->
        <div v-if="isOpen" class="mt-5">
            <slot />
        </div>
    </div>
</template>
