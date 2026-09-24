<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';
import { X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
    maxWidth?: 'sm' | 'md' | 'lg' | 'xl' | '2xl';
    closeable?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: '',
    maxWidth: 'md',
    closeable: true,
});

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const handleKeyDown = (event: KeyboardEvent) => {
    if (event.key === 'Escape' && props.show && props.closeable) {
        close();
    }
};

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }
    },
);

onMounted(() => {
    window.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyDown);
    document.body.style.overflow = '';
});

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth];
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto px-4 py-6 sm:px-0"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs transition-opacity"
                    @click="close"
                />

                <!-- Dialog Box -->
                <div
                    class="relative w-full transform overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 text-left shadow-xl transition-all sm:w-full dark:border-slate-800 dark:bg-slate-900"
                    :class="maxWidthClass"
                >
                    <!-- Header -->
                    <div
                        v-if="title || $slots.header || closeable"
                        class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                    >
                        <slot name="header">
                            <h3
                                class="text-base font-semibold text-slate-900 dark:text-white"
                            >
                                {{ title }}
                            </h3>
                        </slot>

                        <button
                            v-if="closeable"
                            type="button"
                            class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            @click="close"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Content -->
                    <div
                        class="mt-2 text-sm text-slate-600 dark:text-slate-300"
                    >
                        <slot />
                    </div>

                    <!-- Actions -->
                    <div
                        v-if="$slots.footer"
                        class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
