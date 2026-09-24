<script setup lang="ts">
import { computed } from 'vue';

export type BadgeStatus =
    | 'draft'
    | 'diajukan'
    | 'verifikasi'
    | 'disetujui'
    | 'ditolak'
    | 'revisi'
    | 'selesai';

export type BadgeVariant =
    | 'blue'
    | 'emerald'
    | 'amber'
    | 'purple'
    | 'slate'
    | 'rose'
    | 'orange';

interface Props {
    status?: BadgeStatus | string;
    variant?: BadgeVariant;
    text?: string;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    status: 'disetujui',
    variant: undefined,
    text: '',
    size: 'md',
});

const config = computed(() => {
    if (props.variant) {
        switch (props.variant) {
            case 'blue':
                return {
                    bg: 'bg-blue-50 dark:bg-blue-950/50',
                    text: 'text-blue-700 dark:text-blue-300',
                    border: 'border-blue-200 dark:border-blue-900',
                    dot: 'bg-blue-500 animate-pulse',
                    defaultLabel: 'Info',
                };
            case 'emerald':
                return {
                    bg: 'bg-emerald-50 dark:bg-emerald-950/50',
                    text: 'text-emerald-700 dark:text-emerald-300',
                    border: 'border-emerald-200 dark:border-emerald-900',
                    dot: 'bg-emerald-500',
                    defaultLabel: 'Disetujui',
                };
            case 'amber':
                return {
                    bg: 'bg-amber-50 dark:bg-amber-950/50',
                    text: 'text-amber-700 dark:text-amber-300',
                    border: 'border-amber-200 dark:border-amber-900',
                    dot: 'bg-amber-500',
                    defaultLabel: 'Proses',
                };
            case 'purple':
                return {
                    bg: 'bg-purple-50 dark:bg-purple-950/50',
                    text: 'text-purple-700 dark:text-purple-300',
                    border: 'border-purple-200 dark:border-purple-900',
                    dot: 'bg-purple-500',
                    defaultLabel: 'Sidang',
                };
            case 'rose':
                return {
                    bg: 'bg-rose-50 dark:bg-rose-950/50',
                    text: 'text-rose-700 dark:text-rose-300',
                    border: 'border-rose-200 dark:border-rose-900',
                    dot: 'bg-rose-500',
                    defaultLabel: 'Revisi',
                };
            case 'orange':
                return {
                    bg: 'bg-orange-50 dark:bg-orange-950/50',
                    text: 'text-orange-700 dark:text-orange-300',
                    border: 'border-orange-200 dark:border-orange-900',
                    dot: 'bg-orange-500',
                    defaultLabel: 'Perlu Revisi',
                };
            case 'slate':
            default:
                return {
                    bg: 'bg-slate-100 dark:bg-slate-800',
                    text: 'text-slate-700 dark:text-slate-300',
                    border: 'border-slate-200 dark:border-slate-700',
                    dot: 'bg-slate-400',
                    defaultLabel: 'Draft',
                };
        }
    }

    switch (props.status) {
        case 'draft':
            return {
                bg: 'bg-slate-100 dark:bg-slate-800',
                text: 'text-slate-700 dark:text-slate-300',
                border: 'border-slate-200 dark:border-slate-700',
                dot: 'bg-slate-400',
                defaultLabel: 'Draft',
            };
        case 'diajukan':
            return {
                bg: 'bg-blue-50 dark:bg-blue-950/50',
                text: 'text-blue-700 dark:text-blue-300',
                border: 'border-blue-200 dark:border-blue-900',
                dot: 'bg-blue-500 animate-pulse',
                defaultLabel: 'Diajukan',
            };
        case 'verifikasi':
            return {
                bg: 'bg-amber-50 dark:bg-amber-950/50',
                text: 'text-amber-700 dark:text-amber-300',
                border: 'border-amber-200 dark:border-amber-900',
                dot: 'bg-amber-500 animate-ping',
                defaultLabel: 'Verifikasi Berkas',
            };
        case 'disetujui':
        case 'selesai':
            return {
                bg: 'bg-emerald-50 dark:bg-emerald-950/50',
                text: 'text-emerald-700 dark:text-emerald-300',
                border: 'border-emerald-200 dark:border-emerald-900',
                dot: 'bg-emerald-500',
                defaultLabel: 'Disetujui (ACC)',
            };
        case 'ditolak':
            return {
                bg: 'bg-rose-50 dark:bg-rose-950/50',
                text: 'text-rose-700 dark:text-rose-300',
                border: 'border-rose-200 dark:border-rose-900',
                dot: 'bg-rose-500',
                defaultLabel: 'Ditolak',
            };
        case 'revisi':
            return {
                bg: 'bg-orange-50 dark:bg-orange-950/50',
                text: 'text-orange-700 dark:text-orange-300',
                border: 'border-orange-200 dark:border-orange-900',
                dot: 'bg-orange-500',
                defaultLabel: 'Perlu Revisi',
            };
        default:
            return {
                bg: 'bg-slate-100 dark:bg-slate-800',
                text: 'text-slate-700 dark:text-slate-300',
                border: 'border-slate-200 dark:border-slate-700',
                dot: 'bg-slate-400',
                defaultLabel: props.status,
            };
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border font-medium"
        :class="[
            config.bg,
            config.text,
            config.border,
            size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-1 text-xs',
        ]"
    >
        <span class="h-1.5 w-1.5 rounded-full" :class="config.dot" />
        {{ text || config.defaultLabel }}
    </span>
</template>
