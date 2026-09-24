<script setup lang="ts">
import { ref } from 'vue';
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Clock,
    FileText,
    MapPin,
    PenTool,
    User,
    X,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';

interface LogbookItem {
    id: number | string;
    studentName: string;
    studentNim: string;
    date: string;
    bab: string;
    topik: string;
    rangkuman: string;
    metode: 'Tatap Muka (Offline)' | 'Daring (Online / GMeet)';
    lampiranUrl?: string;
    status: 'Menunggu ACC' | 'Disetujui' | 'Perlu Revisi';
}

interface Props {
    show: boolean;
    item: LogbookItem | null;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    (e: 'close'): void;
    (
        e: 'approve',
        payload: {
            id: number | string;
            notes: string;
            status: 'Disetujui' | 'Perlu Revisi';
        },
    ): void;
}>();

const dosenNotes = ref('');
const approvalDecision = ref<'Disetujui' | 'Perlu Revisi'>('Disetujui');
const isSubmitting = ref(false);

const handleClose = () => {
    dosenNotes.value = '';
    emit('close');
};

const handleSubmit = () => {
    if (!props.item) return;
    isSubmitting.value = true;
    setTimeout(() => {
        isSubmitting.value = false;
        emit('approve', {
            id: props.item!.id,
            notes: dosenNotes.value,
            status: approvalDecision.value,
        });
        handleClose();
    }, 400);
};
</script>

<template>
    <Modal :show="show" max-width="2xl" @close="handleClose">
        <div v-if="item" class="p-6">
            <!-- Header -->
            <div
                class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800"
            >
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Review & Validasi Logbook Bimbingan
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Form TA-04 • Logbook Monitoring Skripsi Mahasiswa
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    @click="handleClose"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Student Summary -->
            <div
                class="mt-4 flex items-center justify-between rounded-xl bg-blue-50/70 p-3.5 dark:bg-blue-950/40"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 font-bold text-white shadow-xs"
                    >
                        {{ item.studentName.slice(0, 2).toUpperCase() }}
                    </div>
                    <div>
                        <h4
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ item.studentName }}
                        </h4>
                        <p
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            NIM: {{ item.studentNim }} • Informatika ITK
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-lg bg-white px-2.5 py-1 text-xs font-semibold text-blue-700 shadow-xs dark:bg-slate-800 dark:text-blue-300"
                >
                    {{ item.bab }}
                </span>
            </div>

            <!-- Logbook Details -->
            <div class="mt-4 space-y-3">
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div
                        class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-800"
                    >
                        <span class="text-[10px] font-semibold text-slate-400"
                            >Tanggal Bimbingan:</span
                        >
                        <p
                            class="mt-1 flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200"
                        >
                            <Calendar class="h-3.5 w-3.5 text-blue-600" />
                            {{ item.date }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-200/80 p-3 dark:border-slate-800"
                    >
                        <span class="text-[10px] font-semibold text-slate-400"
                            >Metode Pertemuan:</span
                        >
                        <p
                            class="mt-1 flex items-center gap-1.5 font-bold text-slate-800 dark:text-slate-200"
                        >
                            <MapPin class="h-3.5 w-3.5 text-emerald-600" />
                            {{ item.metode }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-slate-200/80 p-3.5 dark:border-slate-800"
                >
                    <span
                        class="text-[10px] font-semibold tracking-wider text-slate-400 uppercase"
                    >
                        Topik & Rangkuman Bimbingan:
                    </span>
                    <p
                        class="mt-1 text-xs font-bold text-slate-900 dark:text-white"
                    >
                        {{ item.topik }}
                    </p>
                    <p
                        class="mt-2 text-xs leading-relaxed text-slate-600 dark:text-slate-300"
                    >
                        {{ item.rangkuman }}
                    </p>
                </div>
            </div>

            <!-- Dosen Decision & Notes Form -->
            <div
                class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800"
            >
                <label class="text-xs font-bold text-slate-900 dark:text-white">
                    Keputusan Validasi Bimbingan:
                </label>

                <div class="mt-2 grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-bold transition-all"
                        :class="[
                            approvalDecision === 'Disetujui'
                                ? 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-2 ring-emerald-500/20 dark:bg-emerald-950/40 dark:text-emerald-300'
                                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                        ]"
                        @click="approvalDecision = 'Disetujui'"
                    >
                        <CheckCircle2 class="h-4 w-4" />
                        <span>Setujui & ACC (Form TA-04)</span>
                    </button>

                    <button
                        type="button"
                        class="flex items-center justify-center gap-2 rounded-xl border p-3 text-xs font-bold transition-all"
                        :class="[
                            approvalDecision === 'Perlu Revisi'
                                ? 'border-amber-500 bg-amber-50 text-amber-700 ring-2 ring-amber-500/20 dark:bg-amber-950/40 dark:text-amber-300'
                                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                        ]"
                        @click="approvalDecision = 'Perlu Revisi'"
                    >
                        <AlertCircle class="h-4 w-4" />
                        <span>Perlu Revisi / Tambahan</span>
                    </button>
                </div>

                <div class="mt-3.5">
                    <label
                        class="text-xs font-semibold text-slate-700 dark:text-slate-300"
                    >
                        Catatan & Arahan Pembimbing untuk Mahasiswa:
                    </label>
                    <textarea
                        v-model="dosenNotes"
                        rows="3"
                        placeholder="Tuliskan arahan perbaikan, revisi metodologi, atau catatan progres bab berikutnya..."
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800/80 dark:text-white"
                    ></textarea>
                </div>

                <div
                    class="mt-3 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400"
                >
                    <PenTool class="h-3.5 w-3.5 text-blue-600" />
                    <span
                        >e-TTD Digital Anda akan otomatis dibubuhkan pada
                        logbook setelah disetujui.</span
                    >
                </div>
            </div>

            <!-- Action Buttons -->
            <div
                class="mt-6 flex items-center justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-800"
            >
                <Button variant="outline" size="sm" @click="handleClose">
                    Batal
                </Button>
                <Button
                    :variant="
                        approvalDecision === 'Disetujui'
                            ? 'primary'
                            : 'secondary'
                    "
                    size="sm"
                    :loading="isSubmitting"
                    @click="handleSubmit"
                >
                    {{
                        approvalDecision === 'Disetujui'
                            ? 'ACC Sesi Bimbingan'
                            : 'Kirim Catatan Revisi'
                    }}
                </Button>
            </div>
        </div>
    </Modal>
</template>
