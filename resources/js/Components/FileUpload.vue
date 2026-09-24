<script setup lang="ts">
import { computed, ref } from 'vue';
import {
    AlertCircle,
    CheckCircle2,
    FileText,
    UploadCloud,
    X,
} from 'lucide-vue-next';

interface Props {
    id: string;
    label: string;
    hint?: string;
    modelValue?: File | null;
    accept?: string;
    maxSizeMb?: number;
    required?: boolean;
    disabled?: boolean;
    error?: string;
    progress?: { percentage?: number } | number | null;
}

const props = withDefaults(defineProps<Props>(), {
    hint: '',
    modelValue: null,
    accept: '.pdf',
    maxSizeMb: 5,
    required: false,
    disabled: false,
    error: '',
    progress: null,
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: File | null): void;
    (e: 'change', value: File | null): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const clientError = ref('');

const activeError = computed(() => props.error || clientError.value);

const progressPercent = computed(() => {
    if (props.progress === null || props.progress === undefined) return null;
    if (typeof props.progress === 'number') return props.progress;
    return props.progress.percentage ?? null;
});

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

const triggerFileInput = () => {
    if (props.disabled) return;
    fileInput.value?.click();
};

const validateAndSetFile = (file: File | null) => {
    clientError.value = '';

    if (!file) {
        emit('update:modelValue', null);
        emit('change', null);
        return;
    }

    // Validasi ukuran
    const maxSizeBytes = props.maxSizeMb * 1024 * 1024;
    if (file.size > maxSizeBytes) {
        clientError.value = `Ukuran berkas (${formatBytes(file.size)}) melebihi batas maksimal ${props.maxSizeMb} MB`;
        if (fileInput.value) fileInput.value.value = '';
        return;
    }

    // Validasi ekstensi
    if (props.accept) {
        const acceptedExtensions = props.accept
            .split(',')
            .map((ext) => ext.trim().toLowerCase());
        const fileName = file.name.toLowerCase();
        const fileType = file.type.toLowerCase();

        const matches = acceptedExtensions.some((ext) => {
            if (ext.startsWith('.')) {
                return fileName.endsWith(ext);
            }
            return fileType === ext;
        });

        if (!matches) {
            clientError.value = `Format berkas tidak sesuai. Hanya menerima ${props.accept}`;
            if (fileInput.value) fileInput.value.value = '';
            return;
        }
    }

    emit('update:modelValue', file);
    emit('change', file);
};

const handleFileInputChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0] || null;
    validateAndSetFile(file);
};

const handleDragOver = (event: DragEvent) => {
    if (props.disabled) return;
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
};

const handleDrop = (event: DragEvent) => {
    if (props.disabled) return;
    event.preventDefault();
    isDragging.value = false;

    const file = event.dataTransfer?.files?.[0] || null;
    validateAndSetFile(file);
};

const clearFile = (event?: MouseEvent) => {
    if (event) event.stopPropagation();
    clientError.value = '';
    if (fileInput.value) fileInput.value.value = '';
    emit('update:modelValue', null);
    emit('change', null);
};
</script>

<template>
    <div class="w-full space-y-1.5">
        <!-- Label & Indicator -->
        <div class="flex items-center justify-between">
            <label
                :for="id"
                class="block text-sm font-semibold text-slate-800 dark:text-slate-200"
            >
                {{ label }}
                <span v-if="required" class="text-rose-500">*</span>
            </label>
            <span
                v-if="hint"
                class="text-xs text-slate-500 dark:text-slate-400"
            >
                {{ hint }}
            </span>
        </div>

        <!-- Hidden Native File Input -->
        <input
            :id="id"
            ref="fileInput"
            type="file"
            :accept="accept"
            class="hidden"
            :disabled="disabled"
            @change="handleFileInputChange"
        />

        <!-- Dropzone / Selected File State -->
        <div
            class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-5 transition-all duration-200"
            :class="[
                disabled
                    ? 'cursor-not-allowed border-slate-200 bg-slate-100 opacity-60 dark:border-slate-800 dark:bg-slate-900'
                    : 'cursor-pointer hover:border-blue-500 hover:bg-blue-50/40 dark:hover:border-blue-400 dark:hover:bg-blue-950/20',
                isDragging
                    ? 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-400/30 dark:border-blue-400 dark:bg-blue-950/40'
                    : activeError
                      ? 'border-rose-400 bg-rose-50/30 dark:border-rose-700 dark:bg-rose-950/20'
                      : modelValue
                        ? 'border-emerald-400 bg-emerald-50/30 dark:border-emerald-700 dark:bg-emerald-950/20'
                        : 'border-slate-300 bg-slate-50/60 dark:border-slate-700 dark:bg-slate-900/50',
            ]"
            @click="triggerFileInput"
            @dragover="handleDragOver"
            @dragleave="handleDragLeave"
            @drop="handleDrop"
        >
            <!-- Case 1: File Selected -->
            <div
                v-if="modelValue"
                class="flex w-full items-center justify-between gap-3"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700 shadow-xs dark:bg-emerald-900/50 dark:text-emerald-300"
                    >
                        <FileText class="h-6 w-6" />
                    </div>
                    <div class="min-w-0">
                        <p
                            class="truncate text-sm font-medium text-slate-800 dark:text-slate-200"
                        >
                            {{ modelValue.name }}
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            {{ formatBytes(modelValue.size) }} •
                            <span
                                class="font-medium text-emerald-600 dark:text-emerald-400"
                            >
                                Berkas siap diunggah
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium text-slate-600 hover:bg-slate-200 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click.stop="triggerFileInput"
                    >
                        Ganti
                    </button>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 hover:bg-rose-100 hover:text-rose-600 dark:hover:bg-rose-950/50 dark:hover:text-rose-400"
                        title="Hapus Berkas"
                        @click="clearFile"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </div>

            <!-- Case 2: Idle Empty State (Drag & Drop area) -->
            <div v-else class="text-center">
                <div
                    class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 text-blue-600 shadow-xs dark:bg-blue-950 dark:text-blue-400"
                >
                    <UploadCloud class="h-6 w-6 animate-pulse" />
                </div>
                <p
                    class="text-sm font-medium text-slate-700 dark:text-slate-300"
                >
                    <span
                        class="font-semibold text-blue-600 underline underline-offset-2 hover:text-blue-700 dark:text-blue-400"
                    >
                        Pilih berkas
                    </span>
                    atau seret dan lepas di sini
                </p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Maksimal {{ maxSizeMb }} MB ({{ accept }})
                </p>
            </div>

            <!-- Progress Bar (Inertia useForm upload progress) -->
            <div
                v-if="progressPercent !== null"
                class="mt-3 w-full space-y-1.5"
            >
                <div class="flex justify-between text-xs font-medium">
                    <span class="text-blue-600 dark:text-blue-400">
                        Mengunggah berkas...
                    </span>
                    <span class="text-slate-600 dark:text-slate-300">
                        {{ Math.round(progressPercent) }}%
                    </span>
                </div>
                <div
                    class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
                >
                    <div
                        class="h-full rounded-full bg-blue-600 transition-all duration-300"
                        :style="{ width: `${progressPercent}%` }"
                    />
                </div>
            </div>
        </div>

        <!-- Inline Error Message -->
        <div
            v-if="activeError"
            class="flex items-center gap-1.5 pt-0.5 text-xs font-medium text-rose-600 dark:text-rose-400"
        >
            <AlertCircle class="h-3.5 w-3.5 shrink-0" />
            <span>{{ activeError }}</span>
        </div>
    </div>
</template>
