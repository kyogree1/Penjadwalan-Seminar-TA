<script setup lang="ts">
import { computed, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { Search } from "lucide-vue-next";

import AppLayout from "@/Layouts/AppLayout.vue";
import PageHeaderBox from "@/Components/PageHeaderBox.vue";
import PageTabs from "@/Components/PageTabs.vue";
import ScheduleDetailModal, {
    type ScheduleDetail,
} from "@/Components/ScheduleDetailModal.vue";

const activeTab = ref("registrants");
const search = ref("");
const period = ref("Semua");
const wave = ref("Semua");
const selectedSchedule = ref<ScheduleDetail | null>(null);
const showDetail = ref(false);

const tabs = [
    { id: "registrants", label: "Pendaftar Sempro" },
    { id: "sempro", label: "Jadwal Sempro" },
    { id: "sidang", label: "Jadwal Sidang TA" },
];

type Registrant = {
    name: string;
    nim: string;
    registered: string;
    wave: string;
    status: string;
    studentName?: string;
    title?: string;
    date?: string;
    time?: string;
    room?: string;
    supervisors?: string[];
    examiners?: string[];
    own?: boolean;
};
const registrants: Registrant[] = [
    {
        name: "Akmal Falah Maulana",
        nim: "11231006",
        registered: "16 Sep 2026",
        wave: "2",
        status: "Diterima",
    },
    {
        name: "Anisa Rahmadani",
        nim: "11231010",
        registered: "15 Sep 2026",
        wave: "2",
        status: "Diterima",
    },
    {
        name: "Bayu Aditya Saputra",
        nim: "11231089",
        registered: "14 Sep 2026",
        wave: "2",
        status: "Diterima",
    },
];

const schedules = ref<
    (ScheduleDetail & {
        type: "sempro" | "sidang";
        period: string;
        wave: string;
        own?: boolean;
        name?: string;
        registered?: string;
    })[]
>([
    {
        type: "sempro",
        period: "Gasal 2026/2027",
        wave: "2",
        own: true,
        studentName: "Akmal Falah Maulana",
        nim: "11231006",
        title: "Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3",
        date: "02 Oktober 2026",
        time: "13.30 - 15.00 WITA",
        room: "Ruang Lab JSTI 2 / Gedung A",
        supervisors: ["Dr. Ir. Hendra Wijaya, M.Kom."],
        examiners: [
            "Prof. Dr. Agus Susanto, M.T.",
            "Siti Nurhaliza, S.Kom., M.Cs.",
        ],
        status: "Terjadwal",
    },
    {
        type: "sempro",
        period: "Gasal 2026/2027",
        wave: "2",
        studentName: "Anisa Rahmadani",
        nim: "11231010",
        title: "Analisis Perbandingan Kinerja Model Computer Vision pada Edge Device",
        date: "02 Oktober 2026",
        time: "09.00 - 10.30 WITA",
        room: "Ruang B-207",
        supervisors: ["Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom."],
        examiners: [
            "Sri Wahyuni, S.Kom., M.T.",
            "Gusti Ahmad Fanshuri, S.Kom., M.Cs.",
        ],
        status: "Terjadwal",
    },
    {
        type: "sidang",
        period: "Gasal 2026/2027",
        wave: "1",
        studentName: "Siti Nurhaliza Putri",
        nim: "11211045",
        title: "Sistem Deteksi Retinopati Diabetik Menggunakan Vision Transformer",
        date: "20 Oktober 2026",
        time: "09.00 - 11.00 WITA",
        room: "Ruang Sidang Utama Informatika Lt. 3",
        supervisors: ["Dr. Ir. Hendra Wijaya, M.Kom."],
        examiners: [
            "Ir. Budi Santoso, M.Eng.",
            "Siti Nurhaliza, S.Kom., M.Cs.",
        ],
        status: "Terjadwal",
    },
]);

const filteredRegistrants = computed(() => {
    const query = search.value.toLowerCase().trim();
    return registrants.filter(
        (item) =>
            !query || `${item.name} ${item.nim}`.toLowerCase().includes(query),
    );
});
const filteredSchedules = computed(() => {
    const query = search.value.toLowerCase().trim();
    return schedules.value.filter((item) => {
        return (
            item.type === activeTab.value &&
            (period.value === "Semua" || item.period === period.value) &&
            (wave.value === "Semua" || item.wave === wave.value) &&
            (!query ||
                `${item.studentName} ${item.nim} ${item.title}`
                    .toLowerCase()
                    .includes(query))
        );
    });
});

const openDetail = (item: ScheduleDetail) => {
    selectedSchedule.value = item;
    showDetail.value = true;
};
</script>

<template>
    <AppLayout title="Pendaftar & Jadwal">
        <Head title="Pendaftar & Jadwal Tugas Akhir - SIPTA IF" />
        <div class="mx-auto max-w-7xl space-y-6">
            <PageHeaderBox
                title="Pendaftar & Jadwal Tugas Akhir"
                subtitle="Informasi pendaftar, jadwal Seminar Proposal, dan jadwal Sidang TA."
            />
            <PageTabs v-model="activeTab" :tabs="tabs" />
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search
                        class="absolute top-2.5 left-3 h-4 w-4 text-slate-400"
                    /><input
                        v-model="search"
                        placeholder="Cari nama, NIM, atau judul"
                        class="w-full rounded-xl border border-slate-300 py-2 pl-9 pr-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                    />
                </div>
                <select
                    v-if="activeTab !== 'registrants'"
                    v-model="period"
                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <option>Semua</option>
                    <option>Gasal 2026/2027</option>
                </select>
                <select
                    v-if="activeTab !== 'registrants'"
                    v-model="wave"
                    class="rounded-xl border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                >
                    <option>Semua</option>
                    <option>1</option>
                    <option>2</option>
                </select>
            </div>
            <div
                class="overflow-x-auto rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <table class="w-full text-left text-xs">
                    <thead
                        class="bg-slate-50 text-[11px] font-bold uppercase dark:bg-slate-900 dark:text-slate-400"
                    >
                        <tr>
                            <th class="p-4">Mahasiswa</th>
                            <th class="p-4">
                                {{
                                    activeTab === "registrants"
                                        ? "Tanggal Daftar"
                                        : "Judul & Jadwal"
                                }}
                            </th>
                            <th class="p-4">
                                {{
                                    activeTab === "registrants"
                                        ? "Gelombang"
                                        : "Pembimbing & Penguji"
                                }}
                            </th>
                            <th class="p-4">Status</th>
                            <th class="p-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="item in activeTab === 'registrants'
                                ? filteredRegistrants
                                : filteredSchedules"
                            :key="item.nim"
                            :class="
                                'own' in item && item.own
                                    ? 'bg-blue-50/50 dark:bg-blue-950/20'
                                    : ''
                            "
                        >
                            <template v-if="activeTab === 'registrants'"
                                ><td class="p-4">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-slate-500">{{ item.nim }}</p>
                                </td>
                                <td class="p-4">{{ item.registered }}</td>
                                <td class="p-4">Gelombang {{ item.wave }}</td>
                                <td class="p-4">
                                    <span
                                        class="rounded-md bg-emerald-100 px-2 py-1 text-[10px] font-bold text-emerald-700"
                                        >{{ item.status }}</span
                                    >
                                </td>
                                <td class="p-4 text-slate-500">
                                    Data pendaftaran
                                </td></template
                            >
                            <template v-else
                                ><td class="p-4">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.studentName }}
                                        <span
                                            v-if="item.own"
                                            class="text-[10px] text-blue-600"
                                            >(Anda)</span
                                        >
                                    </p>
                                    <p class="text-slate-500">{{ item.nim }}</p>
                                </td>
                                <td class="max-w-sm p-4">
                                    <p class="line-clamp-2">{{ item.title }}</p>
                                    <p class="mt-1 text-slate-500">
                                        {{ item.date }} · {{ item.time }} ·
                                        {{ item.room }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    <p
                                        v-for="person in [
                                            ...(item.supervisors || []),
                                            ...(item.examiners || []),
                                        ]"
                                        :key="person"
                                        class="truncate text-[11px]"
                                    >
                                        {{ person }}
                                    </p>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="rounded-md bg-blue-100 px-2 py-1 text-[10px] font-bold text-blue-700"
                                        >{{ item.status }}</span
                                    >
                                </td>
                                <td class="p-4">
                                    <button
                                        type="button"
                                        class="font-semibold text-blue-600 hover:underline"
                                        @click="
                                            openDetail(item as ScheduleDetail)
                                        "
                                    >
                                        Detail
                                    </button>
                                </td></template
                            >
                        </tr>
                        <tr
                            v-if="
                                (activeTab === 'registrants'
                                    ? filteredRegistrants
                                    : filteredSchedules
                                ).length === 0
                            "
                        >
                            <td
                                colspan="5"
                                class="p-10 text-center text-slate-500"
                            >
                                Tidak ada data yang sesuai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <ScheduleDetailModal
            :show="showDetail"
            :detail="selectedSchedule"
            @close="showDetail = false"
        />
    </AppLayout>
</template>
