<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { Check, Eraser, PenTool, Upload, X } from 'lucide-vue-next';

interface Props {
    show: boolean;
    title?: string;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Bubuhkan Tanda Tangan Digital (e-TTD)',
});

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'save', signatureDataUrl: string): void;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const isDrawing = ref(false);
const hasDrawn = ref(false);
const activeTab = ref<'draw' | 'upload'>('draw');

let ctx: CanvasRenderingContext2D | null = null;

const initCanvas = () => {
    if (!canvasRef.value) return;
    const canvas = canvasRef.value;
    canvas.width = 460;
    canvas.height = 200;
    ctx = canvas.getContext('2d');
    if (ctx) {
        ctx.strokeStyle = '#0F172A';
        ctx.lineWidth = 2.5;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
    }
};

watch(
    () => props.show,
    (isOpen) => {
        if (isOpen) {
            setTimeout(() => {
                initCanvas();
            }, 100);
        }
    },
);

const getCanvasPos = (e: MouseEvent | TouchEvent) => {
    if (!canvasRef.value) return { x: 0, y: 0 };
    const rect = canvasRef.value.getBoundingClientRect();
    if ('touches' in e && e.touches.length > 0) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top,
        };
    }
    const mouseEvent = e as MouseEvent;
    return {
        x: mouseEvent.clientX - rect.left,
        y: mouseEvent.clientY - rect.top,
    };
};

const startDrawing = (e: MouseEvent | TouchEvent) => {
    if (!ctx) return;
    isDrawing.value = true;
    hasDrawn.value = true;
    const pos = getCanvasPos(e);
    ctx.beginPath();
    ctx.moveTo(pos.x, pos.y);
};

const draw = (e: MouseEvent | TouchEvent) => {
    if (!isDrawing.value || !ctx) return;
    e.preventDefault();
    const pos = getCanvasPos(e);
    ctx.lineTo(pos.x, pos.y);
    ctx.stroke();
};

const stopDrawing = () => {
    if (!isDrawing.value || !ctx) return;
    isDrawing.value = false;
    ctx.closePath();
};

const clearCanvas = () => {
    if (!ctx || !canvasRef.value) return;
    ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
    hasDrawn.value = false;
};

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        const img = new Image();
        img.onload = () => {
            if (!ctx || !canvasRef.value) return;
            ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
            // Draw centered
            const hRatio = canvasRef.value.width / img.width;
            const vRatio = canvasRef.value.height / img.height;
            const ratio = Math.min(hRatio, vRatio, 1);
            const centerShiftX =
                (canvasRef.value.width - img.width * ratio) / 2;
            const centerShiftY =
                (canvasRef.value.height - img.height * ratio) / 2;
            ctx.drawImage(
                img,
                0,
                0,
                img.width,
                img.height,
                centerShiftX,
                centerShiftY,
                img.width * ratio,
                img.height * ratio,
            );
            hasDrawn.value = true;
        };
        img.src = e.target?.result as string;
    };
    reader.readAsDataURL(file);
};

const saveSignature = () => {
    if (!canvasRef.value || !hasDrawn.value) return;
    const dataUrl = canvasRef.value.toDataURL('image/png');
    emit('save', dataUrl);
    emit('close');
};
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
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
            >
                <div
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"
                    @click="emit('close')"
                />

                <div
                    class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div
                        class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                    >
                        <div class="flex items-center gap-2">
                            <PenTool
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ title }}
                            </h3>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                            @click="emit('close')"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Mode Switcher Tabs -->
                    <div
                        class="mt-4 flex items-center gap-2 border-b border-slate-100 pb-2 text-xs font-bold dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="border-b-2 pb-1 transition-colors"
                            :class="[
                                activeTab === 'draw'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-slate-400 hover:text-slate-600',
                            ]"
                            @click="activeTab = 'draw'"
                        >
                            Gores Tanda Tangan
                        </button>
                        <button
                            type="button"
                            class="border-b-2 pb-1 transition-colors"
                            :class="[
                                activeTab === 'upload'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-slate-400 hover:text-slate-600',
                            ]"
                            @click="activeTab = 'upload'"
                        >
                            Unggah Gambar TTD
                        </button>
                    </div>

                    <!-- Canvas Area -->
                    <div class="mt-4 space-y-3">
                        <div
                            class="relative flex items-center justify-center overflow-hidden rounded-xl border-2 border-dashed border-slate-300 bg-slate-50 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <canvas
                                ref="canvasRef"
                                class="cursor-crosshair touch-none"
                                @mousedown="startDrawing"
                                @mousemove="draw"
                                @mouseup="stopDrawing"
                                @mouseleave="stopDrawing"
                                @touchstart="startDrawing"
                                @touchmove="draw"
                                @touchend="stopDrawing"
                            />
                            <div
                                v-if="!hasDrawn"
                                class="pointer-events-none absolute text-center text-xs text-slate-400 select-none"
                            >
                                <span v-if="activeTab === 'draw'"
                                    >Goreskan tanda tangan di sini</span
                                >
                                <span v-else
                                    >Pilih berkas gambar untuk melihat
                                    preview</span
                                >
                            </div>
                        </div>

                        <!-- Upload Input if activeTab === 'upload' -->
                        <div
                            v-if="activeTab === 'upload'"
                            class="flex items-center gap-2"
                        >
                            <input
                                type="file"
                                accept="image/png,image/jpeg"
                                class="text-xs file:mr-3 file:rounded-lg file:border-0 file:bg-blue-50 file:px-3 file:py-1 file:text-xs file:font-semibold file:text-blue-700 hover:file:bg-blue-100"
                                @change="handleFileUpload"
                            />
                        </div>

                        <p class="text-[11px] text-slate-400">
                            * Tanda tangan digital ini akan digunakan untuk
                            paraf otomatis pada lembar bimbingan (Form TA-04)
                            dan berita acara.
                        </p>
                    </div>

                    <!-- Actions -->
                    <div
                        class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            @click="clearCanvas"
                        >
                            <Eraser class="h-3.5 w-3.5" />
                            <span>Hapus / Bersihkan</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                class="rounded-xl px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                @click="emit('close')"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                :disabled="!hasDrawn"
                                class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-5 py-2 text-xs font-bold text-white shadow-xs hover:bg-blue-700 disabled:pointer-events-none disabled:opacity-40"
                                @click="saveSignature"
                            >
                                <Check class="h-4 w-4" />
                                <span>Simpan e-TTD</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
