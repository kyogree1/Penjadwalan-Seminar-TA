<script setup lang="ts">
defineProps<{
    tabs: { id: string; label: string }[];
    modelValue: string;
}>();

defineEmits<{ (e: "update:modelValue", value: string): void }>();
</script>

<template>
    <div class="flex w-fit gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800">
        <button
            v-for="tab in tabs"
            :key="tab.id"
            type="button"
            class="rounded-lg px-4 py-2 text-xs font-bold transition-colors"
            :class="
                modelValue === tab.id
                    ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-900 dark:text-blue-300'
                    : 'text-slate-500 dark:text-slate-400'
            "
            @click="$emit('update:modelValue', tab.id)"
        >
            {{ tab.label }}
        </button>
    </div>
</template>
