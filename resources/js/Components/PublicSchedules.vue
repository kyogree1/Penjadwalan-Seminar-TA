<script setup lang="ts">
import { computed, ref } from 'vue';
import CompactCalendar from '@/Components/CompactCalendar.vue';
import WeekSelector from '@/Components/WeekSelector.vue';
import WeeklyAgendaList from '@/Components/WeeklyAgendaList.vue';
import {
    Calendar as CalendarIcon,
    ChevronLeft,
    ChevronRight,
    Clock,
    FileCheck,
    GraduationCap,
    MapPin,
    User,
    X,
} from 'lucide-vue-next';

interface ScheduleItem {
    student_name: string | null;
    title: string | null;
    type: 'sempro' | 'sidang';
    date: string;
    start_time: string;
    end_time: string;
    room: string | null;
    mode: 'offline' | 'online' | 'hybrid';
    supervisors: string[];
    examiners: string[];
}

interface PeriodItem {
    id: number | string;
    name: string;
    type: 'sempro' | 'sidang';
    gelombang: number;
    start_date: string;
    end_date: string;
    is_open: boolean;
}

const props = withDefaults(
    defineProps<{
        schedules: ScheduleItem[];
        periods?: PeriodItem[];
    }>(),
    {
        periods: () => [],
    },
);

const today = new Date();
const currentMonthDate = ref(
    new Date(today.getFullYear(), today.getMonth(), 1),
);

// Modal state matching origin/main
const isModalOpen = ref(false);
const activeModalView = ref<'agenda' | 'detail'>('agenda');
const selectedCell = ref<{
    day: number;
    dateKey: string;
    dateLabel: string;
    schedules: ScheduleItem[];
    periods?: PeriodItem[];
} | null>(null);
const selectedSchedule = ref<ScheduleItem | null>(null);

// Mobile calendar view mode
const mobileCalendarView = ref<'list' | 'grid'>('list');
const selectedMobileWeek = ref(0);

const dateKey = (date: Date) =>
    `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

const monthLabel = computed(() =>
    currentMonthDate.value.toLocaleDateString('id-ID', {
        month: 'long',
        year: 'numeric',
    }),
);

const days = computed(() => {
    const first = new Date(currentMonthDate.value);
    first.setDate(1 - ((first.getDay() + 6) % 7));
    return Array.from({ length: 35 }, (_, index) => {
        const date = new Date(first);
        date.setDate(first.getDate() + index);
        const k = dateKey(date);
        const dateLabel = date.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
        const isToday =
            date.getDate() === today.getDate() &&
            date.getMonth() === today.getMonth() &&
            date.getFullYear() === today.getFullYear();

        return {
            key: k,
            dateString: dateLabel,
            day: date.getDate(),
            dateLabel,
            current: date.getMonth() === currentMonthDate.value.getMonth(),
            currentMonth: date.getMonth() === currentMonthDate.value.getMonth(),
            isToday,
            events: props.schedules
                .filter((s) => s.date === k)
                .map((s, sIdx) => ({
                    id: `${k}-${sIdx}`,
                    title: `${s.type === 'sempro' ? 'Sempro' : 'Sidang'}: ${s.student_name ?? 'Mahasiswa'}`,
                    type: s.type === 'sempro' ? 'primary' : 'warning',
                    detail: {
                        time: `${s.start_time.slice(0, 5)} - ${s.end_time.slice(0, 5)} WITA`,
                    },
                    raw: s,
                })),
            schedules: props.schedules.filter((s) => s.date === k),
            periods: props.periods.filter(
                (p) => k >= p.start_date && k <= p.end_date,
            ),
        };
    });
});

const mobileWeeks = computed(() => {
    return Array.from(
        { length: Math.ceil(days.value.length / 7) },
        (_, index) => {
            const weekDays = days.value.slice(index * 7, index * 7 + 7);
            const first = weekDays[0]?.dateLabel?.replace(/^[^,]+, /, '') || '';
            const last =
                weekDays[weekDays.length - 1]?.dateLabel?.replace(
                    /^[^,]+, /,
                    '',
                ) || '';
            return {
                index,
                days: weekDays,
                label: `Minggu ${index + 1}`,
                range: `${first} - ${last}`,
            };
        },
    );
});

const selectedMobileWeekDays = computed(() => {
    return (
        mobileWeeks.value[selectedMobileWeek.value]?.days.filter(
            (day) => day.current && day.schedules.length > 0,
        ) ?? []
    );
});

const getEventDotClass = (type: string) => {
    switch (type) {
        case 'primary':
            return 'bg-blue-500';
        case 'warning':
            return 'bg-purple-500';
        default:
            return 'bg-blue-500';
    }
};

const getAgendaCount = (day: any) => day.schedules?.length ?? 0;

function moveMonth(offset: number) {
    currentMonthDate.value = new Date(
        currentMonthDate.value.getFullYear(),
        currentMonthDate.value.getMonth() + offset,
        1,
    );
}

function resetMonth() {
    currentMonthDate.value = new Date(today.getFullYear(), today.getMonth(), 1);
}

const openDayModal = (cell: any) => {
    selectedCell.value = {
        day: cell.day,
        dateKey: cell.key ?? cell.dateString,
        dateLabel: cell.dateLabel ?? cell.dateString,
        schedules: cell.schedules ?? [],
        periods: cell.periods ?? [],
    };
    activeModalView.value = 'agenda';
    selectedSchedule.value = null;
    isModalOpen.value = true;
};

function openScheduleDetail(schedule: ScheduleItem) {
    selectedSchedule.value = schedule;
    activeModalView.value = 'detail';
}

function backToAgenda() {
    selectedSchedule.value = null;
    activeModalView.value = 'agenda';
}

function closeModal() {
    isModalOpen.value = false;
    selectedCell.value = null;
    selectedSchedule.value = null;
}
</script>

<template>
    <section
        class="mx-auto max-w-[1536px] px-4 pb-16 sm:px-6 lg:px-8"
        aria-labelledby="public-schedules-heading"
    >
        <div class="mt-8 space-y-4">
            <!-- Header Section matching origin/main -->
            <div
                class="flex flex-col justify-between gap-2 border-t border-slate-200 pt-8 sm:flex-row sm:items-center dark:border-slate-800"
            >
                <div>
                    <h2
                        id="public-schedules-heading"
                        class="text-xl font-extrabold tracking-tight text-slate-900 uppercase dark:text-white"
                    >
                        JADWAL SEMINAR &amp; SIDANG TERBIT (WITA)
                    </h2>
                    <p
                        class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                    >
                        Pantau jadwal pelaksanaan seminar proposal dan sidang
                        skripsi terkini yang telah disinkronkan oleh sistem
                        penjadwalan.
                    </p>
                </div>

                <!-- Controls: Month Navigator & Mobile View Toggle -->
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <!-- View Toggle for Mobile (List vs Grid) -->
                    <div
                        class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 lg:hidden dark:border-slate-800 dark:bg-slate-900"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors"
                            :class="
                                mobileCalendarView === 'list'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="mobileCalendarView = 'list'"
                        >
                            Daftar Agenda
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors"
                            :class="
                                mobileCalendarView === 'grid'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="mobileCalendarView = 'grid'"
                        >
                            Kalender
                        </button>
                    </div>

                    <!-- Month Navigator -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            aria-label="Bulan sebelumnya"
                            class="rounded-lg border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="moveMonth(-1)"
                        >
                            ‹
                        </button>
                        <span
                            class="font-bold text-slate-800 dark:text-slate-200"
                            aria-live="polite"
                        >
                            {{ monthLabel }}
                        </span>
                        <button
                            type="button"
                            aria-label="Bulan berikutnya"
                            class="rounded-lg border border-slate-200 px-2.5 py-1 text-slate-600 hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                            @click="moveMonth(1)"
                        >
                            ›
                        </button>
                        <button
                            type="button"
                            class="ml-1 font-medium text-blue-600 hover:underline dark:text-blue-400"
                            @click="resetMonth"
                        >
                            Bulan ini
                        </button>
                    </div>
                </div>
            </div>

            <!-- Compact Calendar (Mobile Grid Mode Only) -->
            <div v-if="mobileCalendarView === 'grid'" class="lg:hidden">
                <CompactCalendar
                    :days="days"
                    :current-month="monthLabel"
                    :get-event-dot-class="getEventDotClass"
                    @day-click="openDayModal"
                    @prev-month="moveMonth(-1)"
                    @next-month="moveMonth(1)"
                />
            </div>

            <!-- Mobile Agenda List View -->
            <div
                v-if="mobileCalendarView === 'list'"
                class="space-y-4 lg:hidden"
            >
                <WeekSelector
                    :weeks="mobileWeeks"
                    :selected-week="selectedMobileWeek"
                    @select-week="selectedMobileWeek = $event"
                />
                <WeeklyAgendaList
                    :days="selectedMobileWeekDays"
                    :max-events="2"
                    :get-event-dot-class="getEventDotClass"
                    :get-agenda-count="getAgendaCount"
                    @open-day="openDayModal"
                />
            </div>

            <!-- Large Calendar Grid (Desktop Only) -->
            <div
                class="hidden overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs lg:block dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <!-- Calendar Days Header -->
                <div
                    class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                >
                    <div class="py-3">SENIN / MON</div>
                    <div class="py-3">SELASA / TUE</div>
                    <div class="py-3">RABU / WED</div>
                    <div class="py-3">KAMIS / THU</div>
                    <div class="py-3">JUMAT / FRI</div>
                    <div class="py-3">SABTU / SAT</div>
                    <div class="py-3">MINGGU / SUN</div>
                </div>

                <!-- Calendar Cells Grid -->
                <div
                    class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800"
                >
                    <div
                        v-for="(item, idx) in days"
                        :key="idx"
                        class="group relative flex min-h-40 cursor-pointer flex-col justify-between p-3 transition-all select-none hover:bg-blue-50/40 sm:min-h-48 sm:p-3.5 md:min-h-52 dark:hover:bg-slate-900/60"
                        :class="[
                            { 'opacity-40': !item.current },
                            item.isToday
                                ? 'bg-blue-50/20 dark:bg-blue-950/10'
                                : '',
                        ]"
                        @click="openDayModal(item)"
                    >
                        <!-- Day Number Header -->
                        <div class="flex items-start justify-between">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-transform group-hover:scale-110 sm:h-8 sm:w-8 sm:text-sm"
                                :class="
                                    item.isToday
                                        ? 'bg-blue-600 text-white shadow-xs'
                                        : 'text-slate-700 dark:text-slate-300'
                                "
                            >
                                {{ item.day }}
                            </span>

                            <div class="flex items-center gap-1">
                                <span
                                    v-if="
                                        item.periods && item.periods.length > 0
                                    "
                                    class="rounded-md bg-emerald-50 px-1.5 py-0.5 text-[9.5px] font-bold text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300"
                                    title="Ada periode pendaftaran aktif pada tanggal ini"
                                >
                                    Pendaftaran
                                </span>

                                <span
                                    v-if="item.schedules.length > 0"
                                    class="flex items-center gap-1 rounded-md bg-blue-50/80 px-1.5 py-0.5 text-[10px] font-bold text-blue-600 opacity-0 transition-all group-hover:opacity-100 dark:bg-blue-950/60 dark:text-blue-400"
                                >
                                    <span>Buka</span>
                                    <ChevronRight class="h-3 w-3" />
                                </span>
                            </div>
                        </div>

                        <!-- Events Badges inside Day Cell: Google Calendar Style (Max 3) -->
                        <div class="mt-3 space-y-2">
                            <!-- Registration Period Badge in Calendar Cell -->
                            <div
                                v-for="(p, pIdx) in item.periods"
                                :key="`period-${pIdx}`"
                                class="flex items-center gap-1.5 truncate rounded-lg border px-2 py-1 text-[10.5px] font-bold shadow-2xs"
                                :class="
                                    p.type === 'sempro'
                                        ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300'
                                        : 'border-purple-300 bg-purple-50 text-purple-800 dark:border-purple-800 dark:bg-purple-950/60 dark:text-purple-300'
                                "
                                :title="`Periode Pendaftaran Buka: ${p.name}`"
                            >
                                <span
                                    class="h-1.5 w-1.5 shrink-0 rounded-full"
                                    :class="
                                        p.type === 'sempro'
                                            ? 'bg-emerald-500'
                                            : 'bg-purple-500'
                                    "
                                />
                                <span class="truncate">
                                    Daftar
                                    {{
                                        p.type === 'sempro'
                                            ? 'Sempro'
                                            : 'Sidang'
                                    }}
                                    G{{ p.gelombang }}
                                </span>
                            </div>

                            <div
                                v-for="(ev, eIdx) in item.schedules.slice(0, 3)"
                                :key="eIdx"
                                class="flex items-center justify-between gap-2 truncate rounded-lg border px-2.5 py-1.5 text-[11px] font-semibold shadow-2xs transition-all hover:scale-[1.01]"
                                :class="
                                    ev.type === 'sempro'
                                        ? 'border-blue-200 bg-blue-50 text-blue-700 hover:border-blue-300 hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300'
                                        : 'border-purple-200 bg-purple-50 text-purple-700 hover:border-purple-300 hover:bg-purple-100 dark:border-purple-800 dark:bg-purple-950/60 dark:text-purple-300'
                                "
                                :title="ev.title ?? ''"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <span
                                        class="h-2 w-2 shrink-0 rounded-full"
                                        :class="
                                            ev.type === 'sempro'
                                                ? 'bg-blue-500'
                                                : 'bg-purple-500'
                                        "
                                    />
                                    <span class="truncate font-medium">
                                        {{
                                            ev.type === 'sempro'
                                                ? 'Sempro'
                                                : 'Sidang'
                                        }}:
                                        {{ ev.student_name ?? 'Mahasiswa' }} ({{
                                            ev.start_time.slice(0, 5)
                                        }})
                                    </span>
                                </div>
                                <ChevronRight
                                    class="h-3.5 w-3.5 shrink-0 opacity-40 transition-opacity group-hover:opacity-100"
                                />
                            </div>

                            <!-- "+X more" if > 3 events -->
                            <div
                                v-if="item.schedules.length > 3"
                                class="pt-0.5 pl-1.5 text-[10.5px] font-bold text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400"
                            >
                                +{{ item.schedules.length - 3 }} agenda lainnya
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty banner if database has 0 schedules -->
            <p
                v-if="schedules.length === 0"
                class="rounded-xl border border-slate-200 bg-white p-4 text-center text-xs text-slate-500 dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-400"
            >
                Belum ada jadwal terpublikasi di sistem.
            </p>
        </div>

        <!-- ============================================================== -->
        <!-- MODAL DETAIL AGENDA & JADWAL SIDANG/SEMPRO (Matching origin/main) -->
        <!-- ============================================================== -->
        <div
            v-if="isModalOpen && selectedCell"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm transition-all sm:p-6"
            @click.self="closeModal"
        >
            <div
                class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-200/90 bg-white text-slate-800 shadow-2xl transition-all sm:max-w-3xl lg:max-w-4xl dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-100"
            >
                <!-- ============================================== -->
                <!-- VIEW 1: DAFTAR AGENDA PADA HARI TERPILIH      -->
                <!-- ============================================== -->
                <template v-if="activeModalView === 'agenda'">
                    <!-- Header Modal View 1 -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800/80"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3
                                    class="text-base font-bold text-slate-900 sm:text-lg dark:text-white"
                                >
                                    Agenda &amp; Jadwal Kegiatan
                                </h3>
                                <span
                                    class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-extrabold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ selectedCell.schedules.length }} Agenda
                                </span>
                            </div>
                            <p
                                class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <CalendarIcon
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                />
                                {{ selectedCell.dateLabel }}
                            </p>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            title="Tutup Modal (Esc)"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body List Sessions View 1 -->
                    <div class="flex-1 space-y-3.5 overflow-y-auto p-5 sm:p-6">
                        <template
                            v-if="
                                selectedCell.schedules.length > 0 ||
                                (selectedCell.periods &&
                                    selectedCell.periods.length > 0)
                            "
                        >
                            <!-- Active Registration Periods on this Day in Modal -->
                            <div
                                v-if="
                                    selectedCell.periods &&
                                    selectedCell.periods.length > 0
                                "
                                class="space-y-2.5"
                            >
                                <div
                                    v-for="(p, pIdx) in selectedCell.periods"
                                    :key="`modal-p-${pIdx}`"
                                    class="rounded-2xl border p-4 shadow-xs"
                                    :class="
                                        p.type === 'sempro'
                                            ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-800/80 dark:bg-emerald-950/20'
                                            : 'border-purple-200 bg-purple-50/70 dark:border-purple-800/80 dark:bg-purple-950/20'
                                    "
                                >
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="rounded-lg px-2.5 py-0.5 text-[10.5px] font-bold uppercase"
                                            :class="
                                                p.type === 'sempro'
                                                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-200'
                                                    : 'bg-purple-100 text-purple-800 dark:bg-purple-900/60 dark:text-purple-200'
                                            "
                                        >
                                            Periode Pendaftaran Buka • Gelombang
                                            {{ p.gelombang }}
                                        </span>
                                        <span
                                            class="text-[11px] font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            {{ p.start_date }} s.d.
                                            {{ p.end_date }}
                                        </span>
                                    </div>
                                    <h4
                                        class="mt-2 text-sm font-bold"
                                        :class="
                                            p.type === 'sempro'
                                                ? 'text-emerald-950 dark:text-emerald-100'
                                                : 'text-purple-950 dark:text-purple-100'
                                        "
                                    >
                                        {{ p.name }}
                                    </h4>
                                    <p
                                        class="mt-1 text-xs text-slate-600 dark:text-slate-300"
                                    >
                                        Pendaftaran online dibuka untuk
                                        mahasiswa aktif yang memenuhi syarat
                                        akademik.
                                    </p>
                                </div>
                            </div>

                            <div
                                v-for="(
                                    session, sIdx
                                ) in selectedCell.schedules"
                                :key="sIdx"
                                class="group cursor-pointer rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4.5 transition-all duration-150 hover:border-blue-500/80 hover:bg-blue-50/30 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-blue-500/50 dark:hover:bg-blue-950/20"
                                @click="openScheduleDetail(session)"
                            >
                                <!-- Row 1: Badges, Time & Detail Arrow -->
                                <div
                                    class="flex items-center justify-between gap-2"
                                >
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <span
                                            class="rounded-lg px-2.5 py-1 text-[10.5px] font-bold tracking-wider uppercase"
                                            :class="
                                                session.type === 'sidang'
                                                    ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                            "
                                        >
                                            {{
                                                session.type === 'sidang'
                                                    ? 'Sidang Tugas Akhir'
                                                    : 'Seminar Proposal TA'
                                            }}
                                        </span>
                                        <span
                                            class="flex items-center gap-1 text-xs font-semibold text-slate-600 dark:text-slate-300"
                                        >
                                            <Clock
                                                class="h-3.5 w-3.5 text-slate-400"
                                            />
                                            {{ session.start_time.slice(0, 5) }}
                                            –
                                            {{ session.end_time.slice(0, 5) }}
                                            WITA
                                        </span>
                                    </div>

                                    <div
                                        class="flex shrink-0 items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-950/60 dark:text-blue-300 dark:group-hover:bg-blue-600 dark:group-hover:text-white"
                                    >
                                        <span>Lihat Detail</span>
                                        <ChevronRight
                                            class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                                        />
                                    </div>
                                </div>

                                <!-- Row 2: Title & Student Name -->
                                <div class="mt-3">
                                    <h4
                                        class="line-clamp-2 text-sm leading-snug font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                                    >
                                        {{
                                            session.title ??
                                            'Judul belum dicatat'
                                        }}
                                    </h4>
                                    <div
                                        class="mt-1.5 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                                    >
                                        <span
                                            class="font-bold text-slate-800 dark:text-slate-200"
                                        >
                                            {{
                                                session.student_name ??
                                                'Mahasiswa'
                                            }}
                                        </span>
                                        <span
                                            class="text-slate-300 dark:text-slate-700"
                                            >•</span
                                        >
                                        <span
                                            class="text-slate-500 dark:text-slate-400"
                                        >
                                            S1 Informatika
                                        </span>
                                    </div>
                                </div>

                                <!-- Row 3: Meta Footer (Ruangan & Pembimbing) -->
                                <div
                                    class="mt-3.5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/70 pt-3 text-xs text-slate-500 dark:border-slate-800/80 dark:text-slate-400"
                                >
                                    <div
                                        class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300"
                                    >
                                        <MapPin
                                            class="h-3.5 w-3.5 shrink-0 text-rose-500"
                                        />
                                        <span class="font-medium">
                                            {{
                                                session.room ??
                                                (session.mode === 'online'
                                                    ? 'Online'
                                                    : 'TBA')
                                            }}
                                        </span>
                                        <span class="text-slate-400 capitalize"
                                            >({{ session.mode }})</span
                                        >
                                    </div>

                                    <div
                                        v-if="session.supervisors.length > 0"
                                        class="truncate text-[11px]"
                                    >
                                        <span
                                            class="font-semibold text-slate-700 dark:text-slate-300"
                                        >
                                            Pembimbing:
                                        </span>
                                        {{ session.supervisors[0] }}
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State jika tidak ada sesi -->
                        <div v-else class="py-12 text-center text-slate-400">
                            <CalendarIcon
                                class="mx-auto mb-3 h-12 w-12 stroke-[1.5] text-slate-300 dark:text-slate-600"
                            />
                            <p
                                class="text-sm font-bold text-slate-700 dark:text-slate-300"
                            >
                                Tidak ada jadwal pada tanggal ini
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Belum ada sesi seminar atau sidang yang
                                diterbitkan pada hari ini.
                            </p>
                        </div>
                    </div>

                    <!-- Footer Modal View 1 -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 sm:px-6 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <span
                            class="hidden text-[11px] text-slate-500 sm:inline dark:text-slate-400"
                        >
                            Tip: Klik salah satu kartu seminar/sidang untuk
                            melihat informasi penguji dan judul lengkap.
                        </span>
                        <span
                            class="text-[11px] text-slate-500 sm:hidden dark:text-slate-400"
                        >
                            Klik kartu untuk melihat detail lengkap.
                        </span>

                        <button
                            type="button"
                            class="ml-auto rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="closeModal"
                        >
                            Tutup
                        </button>
                    </div>
                </template>

                <!-- ============================================== -->
                <!-- VIEW 2: DETAIL LENGKAP SIDANG / SEMPRO        -->
                <!-- ============================================== -->
                <template
                    v-else-if="activeModalView === 'detail' && selectedSchedule"
                >
                    <!-- Header Modal View 2 -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800/80"
                    >
                        <div class="flex items-center gap-2 overflow-hidden">
                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200/90 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 active:scale-95 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                @click="backToAgenda"
                            >
                                <ChevronLeft class="h-4 w-4" />
                                <span>Kembali</span>
                            </button>

                            <ChevronRight
                                class="h-4 w-4 shrink-0 text-slate-400 dark:text-slate-600"
                            />

                            <div
                                class="flex items-center gap-1.5 truncate text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                <span class="hidden sm:inline">
                                    Agenda {{ selectedCell?.day }}
                                    {{ monthLabel.split(' ')[0] }}
                                </span>
                                <ChevronRight
                                    class="hidden h-3.5 w-3.5 shrink-0 text-slate-300 sm:inline dark:text-slate-600"
                                />
                                <span
                                    class="truncate font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Detail Sesi
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            title="Tutup Modal (Esc)"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body Detail View 2 -->
                    <div class="flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">
                        <!-- Category Badge & Format -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-xl px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-2xs"
                                    :class="
                                        selectedSchedule.type === 'sidang'
                                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                    "
                                >
                                    {{
                                        selectedSchedule.type === 'sidang'
                                            ? 'Sidang Tugas Akhir (Skripsi)'
                                            : 'Seminar Proposal Tugas Akhir'
                                    }}
                                </span>

                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 capitalize dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{ selectedSchedule.mode }}
                                </span>
                            </div>

                            <span
                                class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                <FileCheck class="h-4 w-4" /> Terjadwal Resmi di
                                Portal ITK
                            </span>
                        </div>

                        <!-- Judul Lengkap Tugas Akhir (Card Utama) -->
                        <div
                            class="rounded-2xl border border-blue-200/80 bg-blue-50/40 p-5 dark:border-blue-900/50 dark:bg-blue-950/30"
                        >
                            <span
                                class="text-[10px] font-bold tracking-wider text-blue-700 uppercase dark:text-blue-300"
                            >
                                Judul Tugas Akhir
                            </span>
                            <h3
                                class="mt-1.5 text-base leading-snug font-extrabold text-slate-900 sm:text-lg dark:text-white"
                            >
                                "{{
                                    selectedSchedule.title ??
                                    'Judul belum dicatat'
                                }}"
                            </h3>
                        </div>

                        <!-- 2 Kolom: Data Mahasiswa & Waktu/Lokasi -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Kolom 1: Mahasiswa -->
                            <div
                                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4.5 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <h5
                                    class="mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                                >
                                    <User
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                    />
                                    Data Mahasiswa
                                </h5>
                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >Nama Mahasiswa</span
                                        >
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                selectedSchedule.student_name ??
                                                '-'
                                            }}
                                        </span>
                                    </div>
                                    <div class="pt-1">
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >Program Studi</span
                                        >
                                        <span
                                            class="font-semibold text-slate-800 dark:text-slate-200"
                                        >
                                            S1 Informatika
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 2: Waktu & Lokasi -->
                            <div
                                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4.5 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <h5
                                    class="mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                                >
                                    <MapPin class="h-3.5 w-3.5 text-rose-500" />
                                    Waktu &amp; Ruangan
                                </h5>
                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >Hari &amp; Tanggal</span
                                        >
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ selectedCell?.dateLabel }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >Jam Pelaksanaan</span
                                            >
                                            <span
                                                class="font-semibold text-blue-600 dark:text-blue-400"
                                            >
                                                {{
                                                    selectedSchedule.start_time.slice(
                                                        0,
                                                        5,
                                                    )
                                                }}
                                                –
                                                {{
                                                    selectedSchedule.end_time.slice(
                                                        0,
                                                        5,
                                                    )
                                                }}
                                                WITA
                                            </span>
                                        </div>
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >Ruangan</span
                                            >
                                            <span
                                                class="font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    selectedSchedule.room ??
                                                    (selectedSchedule.mode ===
                                                    'online'
                                                        ? 'Online'
                                                        : 'TBA')
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tim Dosen Lengkap: Pembimbing & Penguji -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <h5
                                class="mb-4 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                            >
                                <GraduationCap
                                    class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
                                />
                                Tim Dosen Pembimbing &amp; Penguji
                            </h5>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Tim Dosen Pembimbing -->
                                <div class="space-y-2.5">
                                    <span
                                        class="block text-[11px] font-bold tracking-wide text-indigo-700 uppercase dark:text-indigo-300"
                                    >
                                        Dosen Pembimbing
                                    </span>
                                    <div
                                        v-if="
                                            selectedSchedule.supervisors
                                                .length === 0
                                        "
                                        class="text-xs text-slate-400"
                                    >
                                        Belum ditetapkan
                                    </div>
                                    <div
                                        v-for="(
                                            name, pIdx
                                        ) in selectedSchedule.supervisors"
                                        :key="pIdx"
                                        class="rounded-xl border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-[#0E1626]"
                                    >
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ name }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] text-indigo-600 dark:text-indigo-400"
                                        >
                                            Pembimbing {{ pIdx + 1 }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Tim Dosen Penguji -->
                                <div class="space-y-2.5">
                                    <span
                                        class="block text-[11px] font-bold tracking-wide text-purple-700 uppercase dark:text-purple-300"
                                    >
                                        Dosen Penguji
                                    </span>
                                    <div
                                        v-if="
                                            selectedSchedule.examiners
                                                .length === 0
                                        "
                                        class="text-xs text-slate-400"
                                    >
                                        Belum ditetapkan
                                    </div>
                                    <div
                                        v-for="(
                                            name, uIdx
                                        ) in selectedSchedule.examiners"
                                        :key="uIdx"
                                        class="rounded-xl border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-[#0E1626]"
                                    >
                                        <p
                                            class="text-xs font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ name }}
                                        </p>
                                        <p
                                            class="mt-0.5 text-[10px] text-purple-600 dark:text-purple-400"
                                        >
                                            Penguji {{ uIdx + 1 }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Modal View 2 -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 sm:px-6 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <button
                            type="button"
                            class="ml-auto rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="closeModal"
                        >
                            Tutup
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </section>
</template>
