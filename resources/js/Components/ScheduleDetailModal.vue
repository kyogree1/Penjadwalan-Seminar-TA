<script setup lang="ts">
import {
    CalendarDays,
    Clock3,
    FileText,
    MapPin,
    UserRound,
    X,
} from 'lucide-vue-next';

export interface ScheduleDetail {
    studentName: string;
    nim: string;
    title: string;
    date: string;
    time: string;
    room: string;
    supervisors: string[];
    examiners: string[];
    status: string;
}

defineProps<{ show: boolean; detail: ScheduleDetail | null }>();
defineEmits<{ (e: 'close'): void }>();
</script>

<template>
    <div
        v-if="show"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4"
        @click.self="$emit('close')"
    >
        <div
            class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl dark:bg-[#0E1626]"
        >
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
            >
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">
                    Detail Jadwal Akademik
                </h2>
                <button
                    type="button"
                    aria-label="Tutup detail"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800"
                    @click="$emit('close')"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
            <div v-if="detail" class="mt-4 space-y-4 text-xs">
                <div>
                    <p class="font-bold text-slate-900 dark:text-white">
                        {{ detail.studentName }}
                    </p>
                    <p class="text-slate-500">{{ detail.nim }}</p>
                    <p
                        class="mt-2 flex gap-2 text-slate-700 dark:text-slate-300"
                    >
                        <FileText class="h-4 w-4 shrink-0 text-blue-500" />{{
                            detail.title
                        }}
                    </p>
                </div>
                <div class="grid gap-2 sm:grid-cols-2">
                    <p class="flex gap-2 text-slate-600 dark:text-slate-300">
                        <CalendarDays class="h-4 w-4 text-blue-500" />{{
                            detail.date
                        }}
                    </p>
                    <p class="flex gap-2 text-slate-600 dark:text-slate-300">
                        <Clock3 class="h-4 w-4 text-blue-500" />{{
                            detail.time
                        }}
                    </p>
                    <p class="flex gap-2 text-slate-600 dark:text-slate-300">
                        <MapPin class="h-4 w-4 text-blue-500" />{{
                            detail.room
                        }}
                    </p>
                    <p class="flex gap-2 text-slate-600 dark:text-slate-300">
                        <span class="h-4 w-4 rounded-full bg-emerald-500" />{{
                            detail.status
                        }}
                    </p>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <p
                            class="mb-1 font-bold text-slate-700 dark:text-slate-200"
                        >
                            Pembimbing
                        </p>
                        <p
                            v-for="item in detail.supervisors"
                            :key="item"
                            class="text-slate-500"
                        >
                            {{ item }}
                        </p>
                    </div>
                    <div>
                        <p
                            class="mb-1 font-bold text-slate-700 dark:text-slate-200"
                        >
                            Penguji
                        </p>
                        <p
                            v-for="item in detail.examiners"
                            :key="item"
                            class="text-slate-500"
                        >
                            {{ item }}
                        </p>
                    </div>
                </div>
                <div
                    class="flex justify-end border-t border-slate-200 pt-4 dark:border-slate-800"
                >
                    <button
                        type="button"
                        class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-300"
                        @click="$emit('close')"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
