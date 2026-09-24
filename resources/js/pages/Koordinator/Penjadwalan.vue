<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    CalendarDays,
    CheckCircle2,
    Edit3,
    Lock,
    LockOpen,
    SlidersHorizontal,
    Users,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';

type ExamType = 'Sempro' | 'Sidang';
type Semester = 'Gasal' | 'Genap';
type Period = {
    id: number;
    type: ExamType;
    academicYear: string;
    semester: Semester;
    wave: number;
    quota: number;
    startDate: string;
    endDate: string;
    isOpen: boolean;
};
type PeriodDraft = Omit<Period, 'id' | 'isOpen'>;
type ScheduleSession = {
    id: number;
    periodId: number;
    studentName: string;
    studentNim: string;
    supervisors: string[];
    examiners: string[];
    date: string;
    time: string;
    room: string;
    status: 'Terjadwal' | 'Selesai' | 'Ditunda';
};

// ponytail: page-local demo state; replace with persisted data when an API exists.
const isDate = (value: string) =>
    /^\d{4}-\d{2}-\d{2}$/.test(value) &&
    !Number.isNaN(Date.parse(value)) &&
    new Date(value).toISOString().slice(0, 10) === value;
const isConsecutiveAcademicYear = (value: string) => {
    const match = /^(\d{4})\/(\d{4})$/.exec(value.trim());
    return Boolean(match && Number(match[2]) === Number(match[1]) + 1);
};

const validatePeriodInput = (
    draft: PeriodDraft,
    periods: Period[],
    editingId: number | null = null,
) => {
    if (!isConsecutiveAcademicYear(draft.academicYear))
        return 'Tahun akademik harus berurutan, contoh 2026/2027.';
    if (!Number.isInteger(draft.wave) || draft.wave < 1)
        return 'Gelombang harus berupa bilangan bulat positif.';
    if (!Number.isInteger(draft.quota) || draft.quota < 1)
        return 'Kuota harus berupa bilangan bulat positif.';
    if (
        !isDate(draft.startDate) ||
        !isDate(draft.endDate) ||
        draft.startDate > draft.endDate
    )
        return 'Tanggal mulai harus sama dengan atau sebelum tanggal selesai.';

    const duplicate = periods.some(
        (period) =>
            period.id !== editingId &&
            period.type === draft.type &&
            period.academicYear === draft.academicYear.trim() &&
            period.semester === draft.semester &&
            period.wave === draft.wave,
    );
    return duplicate
        ? 'Periode dengan jenis, tahun, semester, dan gelombang yang sama sudah ada.'
        : '';
};

const validateExaminerOverride = (
    examiners: string[],
    supervisors: string[],
) => {
    if (examiners.some((name) => !name)) return 'Pilih dua dosen penguji.';
    if (new Set(examiners).size !== examiners.length)
        return 'Dosen penguji harus berbeda.';
    if (examiners.some((name) => supervisors.includes(name)))
        return 'Dosen pembimbing tidak dapat dipilih sebagai penguji.';
    return '';
};
const periods = ref<Period[]>([
    {
        id: 1,
        type: 'Sempro',
        academicYear: '2026/2027',
        semester: 'Gasal',
        wave: 1,
        quota: 20,
        startDate: '2026-09-01',
        endDate: '2026-09-12',
        isOpen: false,
    },
    {
        id: 2,
        type: 'Sempro',
        academicYear: '2026/2027',
        semester: 'Gasal',
        wave: 2,
        quota: 24,
        startDate: '2026-09-21',
        endDate: '2026-09-30',
        isOpen: true,
    },
    {
        id: 3,
        type: 'Sempro',
        academicYear: '2026/2027',
        semester: 'Gasal',
        wave: 3,
        quota: 20,
        startDate: '2026-10-12',
        endDate: '2026-10-23',
        isOpen: false,
    },
    {
        id: 4,
        type: 'Sidang',
        academicYear: '2026/2027',
        semester: 'Gasal',
        wave: 1,
        quota: 16,
        startDate: '2026-09-21',
        endDate: '2026-09-30',
        isOpen: true,
    },
]);

const scheduleSessions = ref<ScheduleSession[]>([
    {
        id: 1,
        periodId: 2,
        studentName: 'Akmal Falah Maulana',
        studentNim: '11231006',
        supervisors: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        ],
        examiners: [
            'Prof. Dr. Agus Tri Haryanto, M.T.',
            'Sri Wahyuni, S.Kom., M.T.',
        ],
        date: '28 September 2026',
        time: '08:30 - 10:00 WITA',
        room: 'Ruang Sidang FSTI A (GKT 304)',
        status: 'Terjadwal',
    },
    {
        id: 2,
        periodId: 2,
        studentName: 'Bagus Pratama Hendrawan',
        studentNim: '11221089',
        supervisors: [
            'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        ],
        examiners: [
            'Dr. Muhammad Rizal, M.Kom.',
            'Hendra Pratama, S.Kom., M.Sc.',
        ],
        date: '28 September 2026',
        time: '10:15 - 11:45 WITA',
        room: 'Ruang Sidang FSTI A (GKT 304)',
        status: 'Selesai',
    },
    {
        id: 3,
        periodId: 4,
        studentName: 'Siti Nurhaliza Putri',
        studentNim: '11211045',
        supervisors: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            'Dewi Ratnasari, S.T., M.T.',
        ],
        examiners: [
            'Dr. Muhammad Rizal, M.Kom.',
            'Hendra Pratama, S.Kom., M.Sc.',
        ],
        date: '29 September 2026',
        time: '09:00 - 10:30 WITA',
        room: 'Ruang Sidang FSTI B (GKT 305)',
        status: 'Terjadwal',
    },
    {
        id: 4,
        periodId: 4,
        studentName: 'Dinda Rahmadani',
        studentNim: '11221012',
        supervisors: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            'Sri Wahyuni, S.Kom., M.T.',
        ],
        examiners: [
            'Prof. Dr. Agus Tri Haryanto, M.T.',
            'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        ],
        date: '29 September 2026',
        time: '13:30 - 15:00 WITA',
        room: 'Lab Software Engineering (GKT 208)',
        status: 'Ditunda',
    },
    {
        id: 5,
        periodId: 2,
        studentName: 'Fajar Hidayatullah',
        studentNim: '11221034',
        supervisors: [
            'Hendra Pratama, S.Kom., M.Sc.',
            'Dr. Muhammad Rizal, M.Kom.',
        ],
        examiners: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            'Dewi Ratnasari, S.T., M.T.',
        ],
        date: '30 September 2026',
        time: '09:00 - 10:30 WITA',
        room: 'Hybrid (Google Meet)',
        status: 'Terjadwal',
    },
]);

const lecturerOptions = [
    'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
    'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
    'Prof. Dr. Agus Tri Haryanto, M.T.',
    'Sri Wahyuni, S.Kom., M.T.',
    'Dr. Muhammad Rizal, M.Kom.',
    'Hendra Pratama, S.Kom., M.Sc.',
    'Dewi Ratnasari, S.T., M.T.',
];

const activeTab = ref<'periods' | 'results'>('periods');
const selectedPeriodId = ref(2);
const showPeriodModal = ref(false);
const editingPeriodId = ref<number | null>(null);
const periodError = ref('');
const blankPeriod = (): PeriodDraft => ({
    type: 'Sempro',
    academicYear: '2026/2027',
    semester: 'Gasal',
    wave: 1,
    quota: 20,
    startDate: '',
    endDate: '',
});
const periodDraft = ref<PeriodDraft>(blankPeriod());

const filteredSessions = computed(() =>
    scheduleSessions.value.filter(
        (session) => session.periodId === selectedPeriodId.value,
    ),
);
const selectedPeriod = computed(() =>
    periods.value.find((period) => period.id === selectedPeriodId.value),
);

const openCreatePeriod = () => {
    editingPeriodId.value = null;
    periodDraft.value = blankPeriod();
    periodError.value = '';
    showPeriodModal.value = true;
};

const openEditPeriod = (period: Period) => {
    editingPeriodId.value = period.id;
    periodDraft.value = {
        type: period.type,
        academicYear: period.academicYear,
        semester: period.semester,
        wave: period.wave,
        quota: period.quota,
        startDate: period.startDate,
        endDate: period.endDate,
    };
    periodError.value = '';
    showPeriodModal.value = true;
};

const savePeriod = () => {
    periodDraft.value.academicYear = periodDraft.value.academicYear.trim();
    periodError.value = validatePeriodInput(
        periodDraft.value,
        periods.value,
        editingPeriodId.value,
    );
    if (periodError.value) return;

    if (editingPeriodId.value === null) {
        periods.value.push({
            ...periodDraft.value,
            id: Math.max(0, ...periods.value.map(({ id }) => id)) + 1,
            isOpen: false,
        });
    } else {
        const index = periods.value.findIndex(
            ({ id }) => id === editingPeriodId.value,
        );
        if (index !== -1)
            periods.value[index] = {
                ...periods.value[index],
                ...periodDraft.value,
            };
    }
    showPeriodModal.value = false;
};

const togglePeriod = (period: Period) => {
    period.isOpen = !period.isOpen;
};

const viewResults = (period: Period) => {
    selectedPeriodId.value = period.id;
    activeTab.value = 'results';
};

const showExaminerModal = ref(false);
const editingSessionId = ref<number | null>(null);
const examinerDraft = ref(['', '']);
const examinerError = ref('');
const editingSession = computed(() =>
    scheduleSessions.value.find(
        (session) => session.id === editingSessionId.value,
    ),
);

const openExaminerOverride = (session: ScheduleSession) => {
    editingSessionId.value = session.id;
    examinerDraft.value = [...session.examiners];
    examinerError.value = '';
    showExaminerModal.value = true;
};

const saveExaminerOverride = () => {
    if (!editingSession.value) return;
    examinerError.value = validateExaminerOverride(
        examinerDraft.value,
        editingSession.value.supervisors,
    );
    if (examinerError.value) return;
    editingSession.value.examiners = [...examinerDraft.value];
    showExaminerModal.value = false;
};
</script>

<template>
    <AppLayout title="Penjadwalan Ujian">
        <Head title="Penjadwalan Ujian • Koordinator TA ITK" />

        <div class="space-y-6">
            <PageHeaderBox
                title="Periode & Hasil Penjadwalan"
                subtitle="Kelola periode Sempro dan Sidang, lalu tinjau jadwal yang tersedia untuk setiap periode."
            >
                <template #action>
                    <Button
                        v-if="activeTab === 'periods'"
                        variant="primary"
                        size="sm"
                        @click="openCreatePeriod"
                    >
                        <CalendarDays class="mr-1.5 h-4 w-4" />
                        Tambah Periode
                    </Button>
                </template>
            </PageHeaderBox>

            <p class="text-xs text-amber-600 dark:text-amber-400">
                Demo frontend: perubahan periode dan penguji hanya tersimpan
                selama halaman ini dibuka.
            </p>

            <div class="flex gap-2" role="tablist" aria-label="Penjadwalan">
                <Button
                    variant="outline"
                    role="tab"
                    :aria-selected="activeTab === 'periods'"
                    @click="activeTab = 'periods'"
                >
                    Kelola Periode
                </Button>
                <Button
                    variant="outline"
                    role="tab"
                    :aria-selected="activeTab === 'results'"
                    @click="activeTab = 'results'"
                >
                    Hasil Jadwal
                </Button>
            </div>

            <Card v-if="activeTab === 'periods'" class="overflow-hidden">
                <div
                    class="flex items-center gap-3 border-b border-slate-200 p-5 dark:border-slate-800"
                >
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                    >
                        <CalendarDays class="h-5 w-5" />
                    </div>
                    <div>
                        <h3
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Periode Sempro & Sidang
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Periode dibedakan berdasarkan jenis ujian, semester,
                            tahun akademik, dan gelombang.
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-xs">
                        <thead
                            class="bg-slate-50 text-slate-500 dark:bg-slate-900/50 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-3">Jenis</th>
                                <th class="p-3">Tahun Akademik</th>
                                <th class="p-3">Semester</th>
                                <th class="p-3">Gelombang</th>
                                <th class="p-3">Kuota</th>
                                <th class="p-3">Rentang Tanggal</th>
                                <th class="p-3">Status</th>
                                <th class="p-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="period in periods"
                                :key="period.id"
                                class="border-t border-slate-200 dark:border-slate-800"
                            >
                                <td class="p-3 font-semibold">
                                    {{ period.type }}
                                </td>
                                <td class="p-3">{{ period.academicYear }}</td>
                                <td class="p-3">{{ period.semester }}</td>
                                <td class="p-3">{{ period.wave }}</td>
                                <td class="p-3">{{ period.quota }} peserta</td>
                                <td class="p-3">
                                    {{ period.startDate }} –
                                    {{ period.endDate }}
                                </td>
                                <td class="p-3">
                                    <span
                                        class="rounded-full px-2 py-1 font-semibold"
                                        :class="
                                            period.isOpen
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                                        "
                                    >
                                        {{
                                            period.isOpen ? 'Dibuka' : 'Ditutup'
                                        }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="viewResults(period)"
                                        >
                                            Lihat Hasil
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            :aria-label="`Edit periode ${period.type} gelombang ${period.wave}`"
                                            @click="openEditPeriod(period)"
                                        >
                                            <Edit3 class="h-3.5 w-3.5" />
                                        </Button>
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="togglePeriod(period)"
                                        >
                                            <Lock
                                                v-if="period.isOpen"
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            <LockOpen
                                                v-else
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            {{
                                                period.isOpen ? 'Tutup' : 'Buka'
                                            }}
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>

            <div v-else class="space-y-4">
                <Card class="p-5">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between"
                    >
                        <div>
                            <label
                                for="period-filter"
                                class="mb-1 block text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                Periode hasil
                            </label>
                            <select
                                id="period-filter"
                                v-model="selectedPeriodId"
                                class="min-w-72 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-200"
                            >
                                <option
                                    v-for="period in periods"
                                    :key="period.id"
                                    :value="period.id"
                                >
                                    {{ period.type }} ·
                                    {{ period.academicYear }} ·
                                    {{ period.semester }} · Gelombang
                                    {{ period.wave }}
                                </option>
                            </select>
                        </div>
                        <p
                            v-if="selectedPeriod"
                            class="text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ filteredSessions.length }} sesi dari kuota
                            {{ selectedPeriod.quota }} peserta
                        </p>
                    </div>
                </Card>

                <Card class="overflow-hidden">
                    <div
                        class="flex items-center gap-3 border-b border-slate-200 p-5 dark:border-slate-800"
                    >
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400"
                        >
                            <Users class="h-5 w-5" />
                        </div>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                Hasil Jadwal per Periode
                            </h3>
                            <p
                                class="text-xs text-slate-500 dark:text-slate-400"
                            >
                                Penyesuaian penguji tidak mengubah tanggal,
                                waktu, ruangan, atau status sesi.
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[1200px] text-left text-xs">
                            <thead
                                class="bg-slate-50 text-slate-500 dark:bg-slate-900/50 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="p-3">Mahasiswa</th>
                                    <th class="p-3">Pembimbing</th>
                                    <th class="p-3">Penguji</th>
                                    <th class="p-3">Tanggal</th>
                                    <th class="p-3">Waktu</th>
                                    <th class="p-3">Ruangan</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="session in filteredSessions"
                                    :key="session.id"
                                    class="border-t border-slate-200 align-top dark:border-slate-800"
                                >
                                    <td class="p-3">
                                        <p class="font-semibold">
                                            {{ session.studentName }}
                                        </p>
                                        <p class="text-slate-500">
                                            {{ session.studentNim }}
                                        </p>
                                    </td>
                                    <td class="p-3">
                                        <ol class="space-y-1">
                                            <li
                                                v-for="(
                                                    supervisor, index
                                                ) in session.supervisors"
                                                :key="supervisor"
                                            >
                                                {{ index + 1 }}.
                                                {{ supervisor }}
                                            </li>
                                        </ol>
                                    </td>
                                    <td class="p-3">
                                        <ol class="space-y-1">
                                            <li
                                                v-for="(
                                                    examiner, index
                                                ) in session.examiners"
                                                :key="examiner"
                                            >
                                                {{ index + 1 }}. {{ examiner }}
                                            </li>
                                        </ol>
                                    </td>
                                    <td class="p-3">{{ session.date }}</td>
                                    <td class="p-3">{{ session.time }}</td>
                                    <td class="p-3">{{ session.room }}</td>
                                    <td class="p-3">
                                        <span
                                            class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-1 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                        >
                                            <CheckCircle2
                                                v-if="
                                                    session.status === 'Selesai'
                                                "
                                                class="h-3.5 w-3.5 text-emerald-500"
                                            />
                                            {{ session.status }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-right">
                                        <Button
                                            size="sm"
                                            variant="outline"
                                            @click="
                                                openExaminerOverride(session)
                                            "
                                        >
                                            <SlidersHorizontal
                                                class="mr-1 h-3.5 w-3.5"
                                            />
                                            Ubah Penguji
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="filteredSessions.length === 0">
                                    <td
                                        colspan="8"
                                        class="p-8 text-center text-slate-500"
                                    >
                                        Belum ada hasil jadwal untuk periode
                                        ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </Card>
            </div>
        </div>

        <Modal
            :show="showPeriodModal"
            max-width="lg"
            @close="showPeriodModal = false"
        >
            <form class="space-y-4 p-6" @submit.prevent="savePeriod">
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        {{ editingPeriodId === null ? 'Tambah' : 'Edit' }}
                        Periode
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Semua kolom wajib diisi.
                    </p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="text-xs font-semibold">
                        Jenis ujian
                        <select
                            v-model="periodDraft.type"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <option value="Sempro">Sempro</option>
                            <option value="Sidang">Sidang</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold">
                        Tahun akademik
                        <input
                            v-model="periodDraft.academicYear"
                            required
                            placeholder="2026/2027"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Semester
                        <select
                            v-model="periodDraft.semester"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        >
                            <option value="Gasal">Gasal</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </label>
                    <label class="text-xs font-semibold">
                        Gelombang
                        <input
                            v-model.number="periodDraft.wave"
                            required
                            type="number"
                            min="1"
                            step="1"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Kuota peserta
                        <input
                            v-model.number="periodDraft.quota"
                            required
                            type="number"
                            min="1"
                            step="1"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <div></div>
                    <label class="text-xs font-semibold">
                        Tanggal mulai
                        <input
                            v-model="periodDraft.startDate"
                            required
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                    <label class="text-xs font-semibold">
                        Tanggal selesai
                        <input
                            v-model="periodDraft.endDate"
                            required
                            type="date"
                            class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                        />
                    </label>
                </div>
                <p
                    v-if="periodError"
                    role="alert"
                    class="text-xs font-semibold text-rose-600"
                >
                    {{ periodError }}
                </p>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showPeriodModal = false"
                    >
                        Batal
                    </Button>
                    <Button type="submit" variant="primary">Simpan</Button>
                </div>
            </form>
        </Modal>

        <Modal
            :show="showExaminerModal"
            max-width="lg"
            @close="showExaminerModal = false"
        >
            <form class="space-y-4 p-6" @submit.prevent="saveExaminerOverride">
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Ubah Penguji Manual
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ editingSession?.studentName }} · Jadwal sesi tetap
                        dipertahankan.
                    </p>
                </div>
                <label class="block text-xs font-semibold">
                    Penguji 1
                    <select
                        v-model="examinerDraft[0]"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Pilih dosen</option>
                        <option
                            v-for="lecturer in lecturerOptions"
                            :key="lecturer"
                            :value="lecturer"
                        >
                            {{ lecturer }}
                        </option>
                    </select>
                </label>
                <label class="block text-xs font-semibold">
                    Penguji 2
                    <select
                        v-model="examinerDraft[1]"
                        class="mt-1 w-full rounded-lg border border-slate-200 bg-white p-2 dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option value="">Pilih dosen</option>
                        <option
                            v-for="lecturer in lecturerOptions"
                            :key="lecturer"
                            :value="lecturer"
                        >
                            {{ lecturer }}
                        </option>
                    </select>
                </label>
                <p
                    v-if="examinerError"
                    role="alert"
                    class="text-xs font-semibold text-rose-600"
                >
                    {{ examinerError }}
                </p>
                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="showExaminerModal = false"
                    >
                        Batal
                    </Button>
                    <Button type="submit" variant="primary"
                        >Simpan Penguji</Button
                    >
                </div>
            </form>
        </Modal>
    </AppLayout>
</template>
