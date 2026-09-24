<script setup lang="ts">
interface TimelineItem {
    id?: string | number;
    title: string;
    subtitle?: string;
    date?: string;
    status?: string;
}

interface Props {
    title: string;
    items?: TimelineItem[];
    emptyText?: string;
    emptySubtext?: string;
}

withDefaults(defineProps<Props>(), {
    items: () => [],
    emptyText: 'Belum ada riwayat',
    emptySubtext: 'kosong',
});
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-6 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
    >
        <h3
            class="border-b border-slate-200 pb-3 text-xs font-bold text-slate-900 dark:border-slate-800 dark:text-white"
        >
            {{ title }}
        </h3>

        <!-- Case 1: Items Available -->
        <div v-if="items.length > 0" class="mt-4 space-y-4">
            <div
                v-for="(item, idx) in items"
                :key="item.id || idx"
                class="flex items-start gap-3 pl-2"
            >
                <span
                    class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-blue-600 ring-2 ring-blue-100 dark:ring-blue-950"
                />
                <div class="min-w-0">
                    <p class="text-xs font-bold text-slate-900 dark:text-white">
                        {{ item.title }}
                    </p>
                    <p
                        v-if="item.subtitle"
                        class="text-[11px] text-slate-500 dark:text-slate-400"
                    >
                        {{ item.subtitle }}
                    </p>
                    <p
                        v-if="item.date"
                        class="mt-0.5 text-[10px] text-slate-400"
                    >
                        {{ item.date }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Case 2: Empty State (Sesuai Mockup) -->
        <div v-else class="mt-4 flex items-start gap-3 pl-2">
            <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-slate-400" />
            <div>
                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">
                    {{ emptyText }}
                </p>
                <p class="text-[11px] text-slate-400">
                    {{ emptySubtext }}
                </p>
            </div>
        </div>
    </div>
</template>
