<script setup lang="ts">
import { computed } from "vue";
import { AlertCircle, CheckCircle2, Clock3, Info } from "lucide-vue-next";

export interface ReminderItem {
    id: string;
    title: string;
    detail: string;
    due?: string;
    priority: "urgent" | "soon" | "info";
    href?: string;
}

const props = defineProps<{ items: ReminderItem[]; compact?: boolean }>();
const visibleItems = computed(() => props.items.filter((item) => item.title));
const priorityClasses = {
    urgent: "border-rose-200 bg-rose-50 dark:border-rose-900/60 dark:bg-rose-950/20",
    soon: "border-amber-200 bg-amber-50 dark:border-amber-900/60 dark:bg-amber-950/20",
    info: "border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0E1626]",
};
</script>

<template>
    <div v-if="visibleItems.length" class="space-y-2.5">
        <component
            :is="item.href ? 'a' : 'div'"
            v-for="item in visibleItems"
            :key="item.id"
            :href="item.href"
            class="flex items-center gap-3 rounded-xl border p-3 transition-colors hover:border-blue-400 dark:hover:border-blue-500"
            :class="[
                priorityClasses[item.priority],
                compact && 'min-h-16 py-2.5',
            ]"
        >
            <AlertCircle
                v-if="item.priority === 'urgent'"
                class="h-4 w-4 shrink-0 text-rose-600"
            />
            <Clock3
                v-else-if="item.priority === 'soon'"
                class="h-4 w-4 shrink-0 text-amber-600"
            />
            <Info v-else class="h-4 w-4 shrink-0 text-blue-600" />
            <div
                class="min-w-0 flex-1"
                :class="
                    compact &&
                    'xl:flex xl:flex-wrap xl:items-baseline xl:gap-x-2'
                "
            >
                <p class="text-xs font-bold text-slate-900 dark:text-white">
                    <span v-if="compact" class="mr-1 font-medium text-slate-500"
                        >Reminder:</span
                    >{{ item.title }}
                </p>
                <p
                    class="text-[11px] leading-relaxed text-slate-500 dark:text-slate-400"
                    :class="compact ? 'break-words' : 'truncate'"
                >
                    {{ item.detail }}
                </p>
            </div>
            <span
                v-if="item.due"
                class="shrink-0 text-[10px] font-bold text-slate-500"
                >{{ item.due }}</span
            >
            <span
                v-if="compact && item.href"
                class="shrink-0 text-xs font-semibold text-blue-600 dark:text-blue-400"
                >Detail ›</span
            >
        </component>
    </div>
    <div
        v-else
        class="flex items-center gap-2 rounded-xl border border-slate-200 p-3 text-xs text-slate-500 dark:border-slate-800"
    >
        <CheckCircle2 class="h-4 w-4 text-emerald-500" /> Tidak ada reminder
        aktif.
    </div>
</template>
