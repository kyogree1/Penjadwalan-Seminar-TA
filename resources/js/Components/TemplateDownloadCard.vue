<script setup lang="ts">
import { useId } from 'vue';
import { Download } from 'lucide-vue-next';

const availabilityId = useId();

export interface TemplateDocument {
    code?: string;
    label: string;
}

interface Props {
    templates: TemplateDocument[];
    title?: string;
    description?: string;
    availabilityNote?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Template Dokumen Pendukung',
    description:
        'Unduh template yang diperlukan sebelum melengkapi berkas pendaftaran.',
    availabilityNote:
        'Unduhan belum tersedia. Menunggu file resmi dari program studi.',
});
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
    >
        <div class="flex items-start gap-3">
            <Download
                class="mt-0.5 h-5 w-5 shrink-0 text-blue-600 dark:text-blue-400"
                aria-hidden="true"
            />
            <div class="min-w-0">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ title }}
                </h3>
                <p
                    class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                >
                    {{ description }}
                </p>
            </div>
        </div>

        <div class="mt-4 space-y-2">
            <button
                v-for="template in templates"
                :key="template.code || template.label"
                type="button"
                disabled
                class="flex min-h-12 w-full cursor-not-allowed items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400"
                :aria-describedby="availabilityId"
            >
                <Download class="h-4 w-4 shrink-0" aria-hidden="true" />
                <span class="min-w-0 flex-1 break-words">{{
                    template.label
                }}</span>
                <span
                    v-if="template.code"
                    class="shrink-0 text-xs font-semibold"
                >
                    {{ template.code }}
                </span>
            </button>
        </div>

        <p
            :id="availabilityId"
            class="mt-3 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
        >
            {{ availabilityNote }}
        </p>
    </div>
</template>
