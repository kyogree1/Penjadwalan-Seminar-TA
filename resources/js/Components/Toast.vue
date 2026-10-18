<script setup lang="ts">
interface Props {
    show: boolean;
    message: string;
    isError?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isError: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out transform"
        enter-from-class="opacity-0 translate-y-[-10px] scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition duration-200 ease-in transform"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 translate-y-[-10px] scale-95"
    >
        <div
            v-if="show"
            role="status"
            class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md"
            :class="[
                isError
                    ? 'border-rose-300 bg-rose-50/95 text-rose-800 dark:border-rose-800 dark:bg-rose-950/90 dark:text-rose-200'
                    : 'border-emerald-300 bg-emerald-50/95 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-200',
            ]"
        >
            <span class="text-xs font-bold sm:text-sm">{{ message }}</span>
            <button
                type="button"
                class="ml-2 text-xs font-bold opacity-60 hover:opacity-100 cursor-pointer"
                @click="emit('close')"
            >
                ✕
            </button>
        </div>
    </Transition>
</template>
