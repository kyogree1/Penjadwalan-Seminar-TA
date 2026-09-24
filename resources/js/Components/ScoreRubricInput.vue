<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    id: string;
    title: string;
    description?: string;
    weight: number; // in percentage e.g. 25
    modelValue: number;
    minScore?: number;
    maxScore?: number;
    disabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    minScore: 0,
    maxScore: 100,
    disabled: false,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

const weightedScore = computed(() => {
    const val = Number(props.modelValue) || 0;
    return ((val * props.weight) / 100).toFixed(1);
});

const gradeLabel = computed(() => {
    const val = Number(props.modelValue) || 0;
    if (val >= 80)
        return {
            text: 'A (Sangat Baik)',
            bg: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-800',
        };
    if (val >= 75)
        return {
            text: 'AB (Baik)',
            bg: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-800',
        };
    if (val >= 70)
        return {
            text: 'B (Cukup Baik)',
            bg: 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-950/40 dark:text-cyan-400 dark:border-cyan-800',
        };
    if (val >= 65)
        return {
            text: 'BC (Cukup)',
            bg: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-400 dark:border-amber-800',
        };
    if (val >= 60)
        return {
            text: 'C (Kurang)',
            bg: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-400 dark:border-orange-800',
        };
    return {
        text: 'D/E (Tidak Memenuhi)',
        bg: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-400 dark:border-rose-800',
    };
});

const onInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let num = Number(target.value);
    if (isNaN(num)) num = 0;
    if (num > props.maxScore) num = props.maxScore;
    if (num < props.minScore) num = props.minScore;
    emit('update:modelValue', num);
};
</script>

<template>
    <div
        class="rounded-2xl border border-slate-200/80 bg-white p-5 transition-all hover:border-slate-300 dark:border-slate-800/90 dark:bg-[#0E1626]"
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div class="space-y-1">
                <div class="flex items-center gap-2.5">
                    <h4
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        {{ title }}
                    </h4>
                    <span
                        class="inline-flex items-center rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        Bobot {{ weight }}%
                    </span>
                </div>
                <p
                    v-if="description"
                    class="text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                >
                    {{ description }}
                </p>
            </div>

            <!-- Calculated Weighted Score Display -->
            <div
                class="flex items-center gap-3 sm:flex-col sm:items-end sm:gap-1"
            >
                <div class="text-right">
                    <span class="text-[11px] font-medium text-slate-400"
                        >Kontribusi Nilai:</span
                    >
                    <p
                        class="text-lg font-black text-blue-600 dark:text-blue-400"
                    >
                        {{ weightedScore }}
                        <span class="text-xs font-normal text-slate-400"
                            >/ {{ weight }}</span
                        >
                    </p>
                </div>
                <span
                    class="rounded-full border px-2.5 py-0.5 text-[11px] font-semibold"
                    :class="gradeLabel.bg"
                >
                    {{ gradeLabel.text }}
                </span>
            </div>
        </div>

        <!-- Slider & Numeric Input Control -->
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <input
                    :id="id + '-slider'"
                    type="range"
                    :min="minScore"
                    :max="maxScore"
                    step="1"
                    :value="modelValue"
                    :disabled="disabled"
                    class="h-2 w-full cursor-pointer appearance-none rounded-lg bg-slate-200 accent-blue-600 dark:bg-slate-700"
                    @input="onInput"
                />
                <div
                    class="mt-1 flex justify-between text-[10px] font-medium text-slate-400"
                >
                    <span>0</span>
                    <span>50</span>
                    <span>75</span>
                    <span>100</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <label
                    :for="id"
                    class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                    >Skor:</label
                >
                <div class="relative w-20">
                    <input
                        :id="id"
                        type="number"
                        :min="minScore"
                        :max="maxScore"
                        :value="modelValue"
                        :disabled="disabled"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-1.5 text-center text-sm font-bold text-slate-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800/80 dark:text-white"
                        @input="onInput"
                    />
                </div>
                <span class="text-xs font-medium text-slate-400">/ 100</span>
            </div>
        </div>
    </div>
</template>
