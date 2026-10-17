<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import type { ScheduleRecord } from '@/types/r2-read-contracts';

import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import PageTabs from '@/Components/PageTabs.vue';
import ScheduleDetailModal, {
    type ScheduleDetail,
} from '@/Components/ScheduleDetailModal.vue';

const activeTab = ref('registrants');
const search = ref('');
const selectedSchedule = ref<ScheduleDetail | null>(null);
const showDetail = ref(false);

const tabs = [
    { id: 'registrants', label: 'Pendaftar Sempro' },
    { id: 'sempro', label: 'Jadwal Sempro' },
    { id: 'sidang', label: 'Jadwal Sidang TA' },
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

interface Props {
    registrantsData?: Registrant[];
    jadwalSempro: ScheduleRecord[];
    jadwalSidang: ScheduleRecord[];
}

const props = defineProps<Props>();

type ScheduleList = (ScheduleDetail & {
    type: 'sempro' | 'sidang';
    period: string;
    wave: string;
    own?: boolean;
    name?: string;
    registered?: string;
})[];

const activeRegistrants = computed<Registrant[]>(
    () => props.registrantsData ?? [],
);

const schedules = computed(() => {
    const list: ScheduleList = [];
    if (props.jadwalSempro && props.jadwalSempro.length > 0) {
        for (const item of props.jadwalSempro) {
            list.push({
                type: 'sempro',
                period: 'Periode belum tersedia',
                wave: String(item.gelombang),
                studentName: item.mahasiswa?.name || 'Mahasiswa',
                nim: item.mahasiswa?.nim_nip || '-',
                title: 'Seminar Proposal Tugas Akhir',
                date: item.tanggal || 'TBA',
                time: item.jam_mulai
                    ? `${item.jam_mulai} - ${item.jam_selesai}`
                    : 'Waktu belum tersedia',
                supervisors: [
                    item.pembimbing1?.name,
                    item.pembimbing2?.name,
                ].filter((name): name is string => Boolean(name)),
                examiners: [item.penguji1?.name, item.penguji2?.name].filter(
                    (name): name is string => Boolean(name),
                ),
                status: 'Terjadwal',
            });
        }
    }
    if (props.jadwalSidang && props.jadwalSidang.length > 0) {
        for (const item of props.jadwalSidang) {
            list.push({
                type: 'sidang',
                period: 'Periode belum tersedia',
                wave: String(item.gelombang),
                studentName: item.mahasiswa?.name || 'Mahasiswa',
                nim: item.mahasiswa?.nim_nip || '-',
                title: 'Sidang Tugas Akhir',
                date: item.tanggal || 'TBA',
                time: item.jam_mulai
                    ? `${item.jam_mulai} - ${item.jam_selesai}`
                    : 'Waktu belum tersedia',
                supervisors: [
                    item.pembimbing1?.name,
                    item.pembimbing2?.name,
                ].filter((name): name is string => Boolean(name)),
                examiners: [item.penguji1?.name, item.penguji2?.name].filter(
                    (name): name is string => Boolean(name),
                ),
                status: 'Terjadwal',
            });
        }
    }
    return list;
});

const filteredRegistrants = computed(() => {
    const query = search.value.toLowerCase().trim();
    return activeRegistrants.value.filter(
        (item) =>
            !query || `${item.name} ${item.nim}`.toLowerCase().includes(query),
    );
});
const filteredSchedules = computed(() => {
    const query = search.value.toLowerCase().trim();
    return schedules.value.filter((item) => {
        return (
            item.type === activeTab.value &&
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
                    placeholder="Cari nama, NIM, atau judul tugas akhir..."
                    class="w-full rounded-xl border border-slate-300 py-2.5 pr-3 pl-9 text-sm dark:border-slate-700 dark:bg-slate-900"
                />
            </div>
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
                                activeTab === 'registrants'
                                    ? 'Tanggal Daftar'
                                    : 'Judul & Jadwal'
                            }}
                        </th>
                        <th class="p-4">
                            {{
                                activeTab === 'registrants'
                                    ? 'Gelombang'
                                    : 'Pembimbing & Penguji'
                            }}
                        </th>
                        <th class="p-4">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    <tr
                        v-for="item in activeTab === 'registrants'
                            ? filteredRegistrants
                            : filteredSchedules"
                        :key="item.nim"
                        :class="[
                            'transition-colors',
                            activeTab !== 'registrants'
                                ? 'cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/50'
                                : '',
                            'own' in item && item.own
                                ? 'bg-blue-50/50 dark:bg-blue-950/20'
                                : '',
                        ]"
                        @click="
                            activeTab !== 'registrants'
                                ? openDetail(item as ScheduleDetail)
                                : undefined
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
                        </template>
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
                        </template>
                    </tr>
                    <tr
                        v-if="
                            (activeTab === 'registrants'
                                ? filteredRegistrants
                                : filteredSchedules
                            ).length === 0
                        "
                    >
                        <td colspan="4" class="p-10 text-center text-slate-500">
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
</template>
