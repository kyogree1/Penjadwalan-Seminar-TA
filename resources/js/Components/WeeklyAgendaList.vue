<script setup lang="ts">
import { ChevronRight } from "lucide-vue-next";

interface CalendarEventLike {
    id: string | number;
    title: string;
    type: any;
    detail?: any;
}

interface CalendarDayLike {
    day: number;
    dateString: string;
    currentMonth: boolean;
    isToday?: boolean;
    events: CalendarEventLike[];
}

interface Props {
    days: any[];
    maxEvents?: number;
    getEventDotClass: (type: string) => string;
    getAgendaCount?: (day: any) => number;
}

withDefaults(defineProps<Props>(), {
    maxEvents: 2,
    getAgendaCount: undefined,
});

const emit = defineEmits<{
    openDay: [day: any];
}>();
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="day in days"
            :key="day.dateString"
            class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <button
                type="button"
                class="flex w-full items-center justify-between bg-slate-50 px-4 py-3.5 text-left transition-colors hover:bg-slate-100 dark:bg-slate-900 dark:hover:bg-slate-800"
                @click="emit('openDay', day)"
            >
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold"
                        :class="
                            day.isToday
                                ? 'bg-blue-600 text-white'
                                : 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                        "
                    >
                        {{ day.day }}
                    </span>
                    <div class="text-left">
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ day.dateString }}
                        </p>
                        <p
                            class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400"
                        >
                            {{
                                getAgendaCount
                                    ? getAgendaCount(day)
                                    : day.events.length
                            }}
                            agenda
                        </p>
                    </div>
                </div>
                <span
                    class="flex items-center gap-1 text-[11px] font-semibold text-blue-600 dark:text-blue-400"
                >
                    Lihat <ChevronRight class="h-3.5 w-3.5" />
                </span>
            </button>

            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                <div
                    v-for="(ev, eIdx) in day.events.slice(0, maxEvents)"
                    :key="eIdx"
                    class="flex items-start gap-3 px-4 py-4"
                >
                    <span
                        class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full"
                        :class="getEventDotClass(ev.type)"
                    />
                    <div class="min-w-0 flex-1">
                        <p
                            class="text-sm font-semibold leading-normal text-slate-800 dark:text-slate-200"
                        >
                            {{ ev.title }}
                        </p>
                        <p
                            v-if="ev.detail?.time"
                            class="mt-1.5 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ ev.detail.time }}
                        </p>
                    </div>
                </div>

                <div
                    v-if="day.events.length > maxEvents"
                    class="bg-slate-50/60 px-4 py-3 text-center dark:bg-slate-900/40"
                >
                    <button
                        type="button"
                        class="text-[11px] font-semibold text-blue-600 hover:underline dark:text-blue-400"
                        @click="emit('openDay', day)"
                    >
                        dan {{ day.events.length - maxEvents }} agenda lainnya
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
