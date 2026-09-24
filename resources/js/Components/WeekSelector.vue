<script setup lang="ts">
interface Week {
    index: number;
    label: string;
    range: string;
}

interface Props {
    weeks: Week[];
    selectedWeek: number;
}

defineProps<Props>();
const emit = defineEmits<{
    selectWeek: [index: number];
}>();
</script>

<template>
    <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
        <button
            v-for="week in weeks"
            :key="week.index"
            type="button"
            class="shrink-0 rounded-xl border px-4 py-2.5 text-xs font-semibold transition-colors"
            :class="
                selectedWeek === week.index
                    ? 'border-blue-600 bg-blue-600 text-white shadow-sm'
                    : 'border-slate-200 bg-white text-slate-600 hover:border-blue-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'
            "
            @click="emit('selectWeek', week.index)"
        >
            <div class="flex flex-col items-start gap-0.5">
                <span>{{ week.label }}</span>
                <span class="text-[10px] font-normal opacity-80">
                    {{ week.range }}
                </span>
            </div>
        </button>
    </div>
</template>
