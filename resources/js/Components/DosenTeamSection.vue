<script setup lang="ts">
import DosenCard from '@/Components/DosenCard.vue';

interface DosenItem {
    nama: string;
    role: string;
    variant?: 'rose' | 'blue' | 'indigo' | 'emerald';
}

interface Props {
    pembimbingList?: DosenItem[];
    pengujiList?: DosenItem[];
}

withDefaults(defineProps<Props>(), {
    pembimbingList: () => [
        {
            nama: 'Dr. Ir. Hendra Wijaya, M.Kom.',
            role: 'Pembimbing 1',
            variant: 'rose',
        },
        {
            nama: 'Rina Agustina, S.T., M.Kom.',
            role: 'Pembimbing 2',
            variant: 'rose',
        },
    ],
    pengujiList: () => [
        {
            nama: 'Dr. Dian Indah Permatasari, M.Kom.',
            role: 'Penguji 1',
            variant: 'rose',
        },
        {
            nama: 'Ahmad Fauzi, S.Kom., M.T.',
            role: 'Penguji 2',
            variant: 'rose',
        },
    ],
});
</script>

<template>
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
        <!-- Pembimbing Box -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
        >
            <h3 class="mb-3 text-xs font-bold text-slate-900 dark:text-white">
                Pembimbing
            </h3>
            <div class="space-y-2.5">
                <DosenCard
                    v-for="(dosen, idx) in pembimbingList"
                    :key="idx"
                    :nama="dosen.nama"
                    :role="dosen.role"
                    :variant="dosen.variant"
                />
            </div>
        </div>

        <!-- Penguji Box -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-slate-50/70 p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900/60"
        >
            <h3 class="mb-3 text-xs font-bold text-slate-900 dark:text-white">
                Penguji
            </h3>
            <div class="space-y-2.5">
                <DosenCard
                    v-for="(dosen, idx) in pengujiList"
                    :key="idx"
                    :nama="dosen.nama"
                    :role="dosen.role"
                    :variant="dosen.variant"
                />
            </div>
        </div>
    </div>
</template>
