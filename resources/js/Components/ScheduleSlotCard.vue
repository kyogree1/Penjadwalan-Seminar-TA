<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    Calendar,
    CheckCircle2,
    Clock,
    GraduationCap,
    MapPin,
    PenTool,
    UserCheck,
    Users,
} from 'lucide-vue-next';

interface Props {
    id?: string | number;
    date: string;
    time: string; // e.g. "09:00 - 10:30 WITA"
    room: string;
    type: 'Sempro' | 'Sidang';
    studentName: string;
    studentNim: string;
    judul: string;
    pembimbing: string[];
    penguji: string[];
    status?: 'Terjadwal' | 'Berlangsung' | 'Selesai' | 'Konflik';
    hasConflict?: boolean;
    conflictReason?: string;
    actionText?: string;
    actionHref?: string;
    isDosenView?: boolean;
    isCoordinatorView?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    status: 'Terjadwal',
    hasConflict: false,
    conflictReason: '',
    actionText: 'Beri Nilai',
    actionHref: '',
    isDosenView: false,
    isCoordinatorView: false,
});

const emit = defineEmits<{
    (e: 'reassign', id?: string | number): void;
    (e: 'clickAction', id?: string | number): void;
}>();

const typeBadgeClass = computed(() => {
    return props.type === 'Sidang'
        ? 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800'
        : 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800';
});
</script>

<template>
    <div
        class="relative rounded-2xl border bg-white p-5 transition-all duration-200 dark:bg-[#0E1626]"
        :class="[
            hasConflict
                ? 'border-rose-300 bg-rose-50/20 dark:border-rose-900/60 dark:bg-rose-950/10'
                : 'border-slate-200/80 hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:hover:border-slate-700',
        ]"
    >
        <!-- Top Bar: Date, WITA Time, Room, Type Badge -->
        <div
            class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-800"
        >
            <div class="flex flex-wrap items-center gap-3">
                <span
                    class="rounded-lg border px-2.5 py-0.5 text-xs font-bold tracking-wider uppercase"
                    :class="typeBadgeClass"
                >
                    {{
                        type === 'Sidang'
                            ? 'Sidang Akhir TA'
                            : 'Seminar Proposal'
                    }}
                </span>

                <span
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 dark:text-slate-300"
                >
                    <Calendar
                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                    />
                    {{ date }}
                </span>

                <span
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 dark:text-blue-400"
                >
                    <Clock class="h-3.5 w-3.5" />
                    {{ time }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                >
                    <MapPin class="h-3.5 w-3.5 text-rose-500" />
                    {{ room }}
                </span>

                <!-- Conflict Alert or Status -->
                <span
                    v-if="hasConflict"
                    class="inline-flex items-center gap-1 rounded-md bg-rose-100 px-2 py-0.5 text-[11px] font-bold text-rose-700 dark:bg-rose-950/50 dark:text-rose-400"
                >
                    <AlertTriangle class="h-3 w-3" /> Konflik Terdeteksi
                </span>
            </div>
        </div>

        <!-- Warning banner if conflict -->
        <div
            v-if="hasConflict && conflictReason"
            class="mt-3 flex items-start gap-2 rounded-xl border border-rose-200 bg-rose-50 p-2.5 text-xs text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300"
        >
            <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
            <p>{{ conflictReason }}</p>
        </div>

        <!-- Student & Thesis Info -->
        <div class="mt-3.5">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                    {{ studentName }}
                </h4>
                <span class="text-xs font-semibold text-slate-400"
                    >({{ studentNim }})</span
                >
            </div>
            <p
                class="mt-1 line-clamp-2 text-xs text-slate-600 dark:text-slate-300"
            >
                "{{ judul }}"
            </p>
        </div>

        <!-- Examiners & Advisors Grid -->
        <div
            class="mt-4 grid grid-cols-1 gap-2.5 rounded-xl bg-slate-50/70 p-3 text-xs sm:grid-cols-2 dark:bg-slate-800/40"
        >
            <!-- Dosen Pembimbing -->
            <div>
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                >
                    Pembimbing:
                </span>
                <ul
                    class="mt-1 space-y-0.5 font-medium text-slate-700 dark:text-slate-300"
                >
                    <li
                        v-for="(dos, idx) in pembimbing"
                        :key="'pemb-' + idx"
                        class="flex items-center gap-1.5 truncate"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-blue-500"
                        ></span>
                        <span class="truncate">{{ dos }}</span>
                    </li>
                </ul>
            </div>

            <!-- Dosen Penguji -->
            <div>
                <span
                    class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                >
                    Penguji:
                </span>
                <ul
                    class="mt-1 space-y-0.5 font-medium text-slate-700 dark:text-slate-300"
                >
                    <li
                        v-for="(dos, idx) in penguji"
                        :key="'peng-' + idx"
                        class="flex items-center gap-1.5 truncate"
                    >
                        <span
                            class="h-1.5 w-1.5 rounded-full bg-purple-500"
                        ></span>
                        <span class="truncate">{{ dos }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Footer Actions -->
        <div
            class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
        >
            <span class="text-[11px] font-medium text-slate-400">
                Zona Waktu:
                <strong class="text-slate-600 dark:text-slate-300"
                    >WITA (UTC+8)</strong
                >
            </span>

            <div class="flex items-center gap-2">
                <!-- Action slot or Buttons -->
                <slot name="action">
                    <button
                        v-if="isCoordinatorView"
                        type="button"
                        class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                        @click="emit('reassign', id)"
                    >
                        Ubah Jadwal / Penguji
                    </button>

                    <Link
                        v-if="actionHref"
                        :href="actionHref"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-md hover:shadow-blue-600/20"
                    >
                        <PenTool class="h-3.5 w-3.5" />
                        <span>{{ actionText }}</span>
                    </Link>
                </slot>
            </div>
        </div>
    </div>
</template>
