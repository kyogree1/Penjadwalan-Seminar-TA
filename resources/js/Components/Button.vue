<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Loader2 } from 'lucide-vue-next';

interface Props {
    type?: 'button' | 'submit' | 'reset';
    variant?:
        | 'primary'
        | 'secondary'
        | 'outline'
        | 'danger'
        | 'ghost'
        | 'success';
    size?: 'sm' | 'md' | 'lg';
    href?: string;
    loading?: boolean;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'button',
    variant: 'primary',
    size: 'md',
    href: '',
    loading: false,
    disabled: false,
});

const variantClasses = computed(() => {
    switch (props.variant) {
        case 'primary':
            return 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus-visible:ring-blue-500 shadow-sm shadow-blue-500/20';
        case 'secondary':
            return 'bg-slate-100 text-slate-700 hover:bg-slate-200 active:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700';
        case 'outline':
            return 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 active:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800';
        case 'danger':
            return 'bg-rose-600 text-white hover:bg-rose-700 active:bg-rose-800 focus-visible:ring-rose-500 shadow-sm shadow-rose-500/20';
        case 'success':
            return 'bg-emerald-600 text-white hover:bg-emerald-700 active:bg-emerald-800 focus-visible:ring-emerald-500 shadow-sm shadow-emerald-500/20';
        case 'ghost':
            return 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200';
        default:
            return '';
    }
});

const sizeClasses = computed(() => {
    switch (props.size) {
        case 'sm':
            return 'px-3 py-1.5 text-xs rounded-lg gap-1.5';
        case 'lg':
            return 'px-6 py-3 text-base rounded-xl gap-2.5 font-semibold';
        case 'md':
        default:
            return 'px-4 py-2 text-sm rounded-xl gap-2';
    }
});
</script>

<template>
    <component
        :is="href ? Link : 'button'"
        :href="href || undefined"
        :type="!href ? type : undefined"
        :disabled="disabled || loading"
        class="inline-flex items-center justify-center font-medium transition-all duration-150 select-none focus:outline-hidden focus-visible:ring-2 focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50"
        :class="[variantClasses, sizeClasses]"
    >
        <Loader2 v-if="loading" class="h-4 w-4 shrink-0 animate-spin" />
        <slot />
    </component>
</template>
