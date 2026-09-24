<script setup lang="ts">
import { ChevronLeft, ChevronRight } from "lucide-vue-next";

interface CalendarDay {
    day: number;
    dateString: string;
    currentMonth: boolean;
    isToday?: boolean;
    events: any[];
}

interface Props {
    days: CalendarDay[];
    currentMonth: string;
    getEventDotClass: (type: string) => string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    dayClick: [day: CalendarDay];
    prevMonth: [];
    nextMonth: [];
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
    >
        <!-- Month Header -->
        <div
            class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-800 dark:bg-slate-900"
        >
            <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-200 dark:hover:bg-slate-800"
                @click="emit('prevMonth')"
            >
                <ChevronLeft class="h-4 w-4" />
            </button>
            <span class="text-sm font-bold text-slate-900 dark:text-white">
                {{ currentMonth }}
            </span>
            <button
                type="button"
                class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-slate-200 dark:hover:bg-slate-800"
                @click="emit('nextMonth')"
            >
                <ChevronRight class="h-4 w-4" />
            </button>
        </div>

        <!-- Day Names -->
        <div
            class="grid grid-cols-7 divide-x divide-slate-100 dark:divide-slate-800"
        >
            <div
                v-for="dayName in [
                    'Sen',
                    'Sel',
                    'Rab',
                    'Kam',
                    'Jum',
                    'Sab',
                    'Min',
                ]"
                :key="dayName"
                class="py-2.5 text-center text-[10px] font-bold text-slate-500 dark:text-slate-400"
            >
                {{ dayName }}
            </div>
        </div>

        <!-- Calendar Days -->
        <div
            class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800"
        >
            <button
                v-for="(item, idx) in days"
                :key="idx"
                type="button"
                class="relative flex min-h-14 flex-col items-center justify-center gap-1.5 p-2 text-xs transition-colors hover:bg-blue-50 dark:hover:bg-slate-900"
                :class="[
                    !item.currentMonth ? 'opacity-35' : '',
                    item.isToday ? 'bg-blue-50 dark:bg-blue-950/30' : '',
                ]"
                @click="emit('dayClick', item)"
            >
                <span
                    class="flex h-7 w-7 items-center justify-center rounded-full font-semibold transition-transform hover:scale-110"
                    :class="
                        item.isToday
                            ? 'bg-blue-600 text-white'
                            : 'text-slate-700 dark:text-slate-300'
                    "
                >
                    {{ item.day }}
                </span>
                <span
                    v-if="item.events.length"
                    class="h-1.5 w-1.5 rounded-full"
                    :class="getEventDotClass(item.events[0].type)"
                />
            </button>
        </div>
    </div>
</template>
