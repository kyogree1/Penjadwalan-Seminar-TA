<script setup lang="ts">
import { AlertTriangle, Trash2 } from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

interface Props {
    show: boolean;
    title?: string;
    message?: string;
    itemName?: string;
    itemType?: string;
    warningText?: string;
    confirmText?: string;
    cancelText?: string;
    processing?: boolean;
}

withDefaults(defineProps<Props>(), {
    title: 'Konfirmasi Hapus',
    message: '',
    itemName: '',
    itemType: '',
    warningText: 'Data yang telah dihapus tidak dapat dikembalikan.',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
    processing: false,
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'confirm'): void;
}>();
</script>

<template>
    <Modal
        :show="show"
        max-width="md"
        :closeable="!processing"
        @close="emit('close')"
    >
        <div class="flex items-start gap-4">
            <div
                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-rose-50 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400"
            >
                <Trash2 class="h-5 w-5" />
            </div>
            <div class="flex-1 space-y-1.5 pt-0.5 text-left">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ title }}
                </h3>
                <p class="text-xs leading-relaxed text-slate-600 dark:text-slate-400">
                    <template v-if="message">
                        {{ message }}
                    </template>
                    <template v-else-if="itemName">
                        Apakah Anda yakin ingin menghapus
                        <span v-if="itemType">{{ itemType }} </span>
                        <strong class="font-bold text-slate-900 dark:text-white">
                            "{{ itemName }}"
                        </strong>?
                    </template>
                    <template v-else>
                        Apakah Anda yakin ingin menghapus data ini?
                    </template>
                </p>
                <div
                    v-if="warningText"
                    class="mt-2 flex items-center gap-1.5 rounded-lg bg-rose-50/80 px-2.5 py-1.5 text-[11px] font-semibold text-rose-600 dark:bg-rose-950/40 dark:text-rose-400"
                >
                    <AlertTriangle class="h-3.5 w-3.5 shrink-0" />
                    <span>{{ warningText }}</span>
                </div>
            </div>
        </div>

        <template #footer>
            <div class="flex w-full items-center justify-end gap-2.5">
                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    :disabled="processing"
                    @click="emit('close')"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    type="button"
                    variant="danger"
                    size="sm"
                    :loading="processing"
                    :disabled="processing"
                    @click="emit('confirm')"
                >
                    {{ confirmText }}
                </Button>
            </div>
        </template>
    </Modal>
</template>
