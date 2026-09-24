<script setup lang="ts">
interface Props {
    name?: string;
    nim?: string;
    angkatan?: string;
    prodi?: string;
    avatarInitials?: string;
}

withDefaults(defineProps<Props>(), {
    name: 'Akmal Falah Maulana',
    nim: '11231006',
    angkatan: 'Akt 2023',
    prodi: 'Informatika',
    avatarInitials: 'AF',
});
</script>

<template>
    <div
        class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
    >
        <!-- Info Mahasiswa -->
        <div class="flex items-center gap-4">
            <div
                class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-slate-200 text-lg font-bold text-slate-700 shadow-inner dark:bg-slate-700 dark:text-slate-200"
            >
                {{ avatarInitials }}
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">
                    {{ name }}
                </h3>
                <div class="mt-1.5 flex flex-wrap gap-2 text-xs font-semibold">
                    <span
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ angkatan }}
                    </span>
                    <span
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        NIM: {{ nim }}
                    </span>
                    <span
                        class="rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ prodi }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Custom Slot Action (Status Pill atau Tombol Unduh) -->
        <div
            v-if="$slots.default"
            class="flex flex-col items-start sm:items-end"
        >
            <slot />
        </div>
    </div>
</template>
