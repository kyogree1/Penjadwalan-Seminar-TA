<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    Laptop,
    MapPin,
    Plus,
    Tv,
    Users,
    Video,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';

const rooms = ref([
    {
        id: 1,
        nama: 'Ruang Sidang FSTI A (GKT 304)',
        gedung: 'Gedung Kuliah Terpadu Lantai 3',
        kapasitas: '25 Orang',
        fasilitas: [
            'Proyektor HDMI 4K',
            'Sound & Mic Wireless',
            'Webcam Hybrid Meeting',
            'AC Sentral',
        ],
        status: 'Digunakan',
        sesiHariIni: [
            {
                waktu: '09:00 - 10:30 WITA',
                kegiatan: 'Sidang Akhir TA (Akmal Falah)',
                status: 'Berlangsung',
            },
            {
                waktu: '13:00 - 14:30 WITA',
                kegiatan: 'Seminar Proposal (Bagus Pratama)',
                status: 'Terjadwal',
            },
        ],
    },
    {
        id: 2,
        nama: 'Ruang Sidang FSTI B (GKT 305)',
        gedung: 'Gedung Kuliah Terpadu Lantai 3',
        kapasitas: '20 Orang',
        fasilitas: [
            'Smart TV 65 Inch',
            'Sound System',
            'Webcam Logitech MeetUp',
            'AC',
        ],
        status: 'Tersedia',
        sesiHariIni: [
            {
                waktu: '10:45 - 12:15 WITA',
                kegiatan: 'Sidang Akhir TA (Siti Nurhaliza)',
                status: 'Selesai',
            },
        ],
    },
    {
        id: 3,
        nama: 'Lab Software Engineering (GKT 208)',
        gedung: 'Gedung Kuliah Terpadu Lantai 2',
        kapasitas: '35 Komputer',
        fasilitas: [
            'Dual Monitor PC',
            'Koneksi LAN Gigabit',
            'Proyektor HD',
            'Whiteboard',
        ],
        status: 'Tersedia',
        sesiHariIni: [],
    },
    {
        id: 4,
        nama: 'Ruang Sidang Virtual Zoom FSTI',
        gedung: 'Cloud Meeting Server ITK',
        kapasitas: '300 Peserta',
        fasilitas: [
            'Breakout Rooms',
            'Cloud Recording Otomatis',
            'Live Stream YouTube',
        ],
        status: 'Tersedia',
        sesiHariIni: [
            {
                waktu: '15:00 - 16:30 WITA',
                kegiatan: 'Bimbingan Hybrid Terpadu',
                status: 'Terjadwal',
            },
        ],
    },
]);
</script>

<template>
    <AppLayout title="Manajemen Ruangan Sidang">
        <Head title="Manajemen Ruangan - Portal Tendik" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Sarana & Prasarana Sidang"
                title="Kelola Ruang Sidang Fisik & Fasilitas Hybrid"
                description="Pantau ketersediaan ruang ujian, perangkat multimedia, proyektor, dan koordinasi teknis operasional seminar Informatika."
            />

            <!-- Room Cards Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <Card
                    v-for="room in rooms"
                    :key="room.id"
                    class="p-6 transition-all hover:shadow-md"
                >
                    <div
                        class="flex items-start justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <Building2
                                    class="h-5 w-5 text-blue-600 dark:text-blue-400"
                                />
                                <h3
                                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                                >
                                    {{ room.nama }}
                                </h3>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                {{ room.gedung }} • Kapasitas
                                {{ room.kapasitas }}
                            </p>
                        </div>

                        <span
                            class="rounded-full px-2.5 py-1 text-[10px] font-bold"
                            :class="[
                                room.status === 'Digunakan'
                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                            ]"
                        >
                            {{ room.status }}
                        </span>
                    </div>

                    <!-- Facilities -->
                    <div class="mt-4">
                        <p
                            class="text-[11px] font-bold text-slate-700 dark:text-slate-300"
                        >
                            Fasilitas Ruangan:
                        </p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span
                                v-for="(f, fIdx) in room.fasilitas"
                                :key="fIdx"
                                class="rounded-lg bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ f }}
                            </span>
                        </div>
                    </div>

                    <!-- Schedule Today -->
                    <div
                        class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <p
                            class="text-[11px] font-bold text-slate-700 dark:text-slate-300"
                        >
                            Agenda Pemakaian Hari Ini:
                        </p>
                        <div
                            v-if="room.sesiHariIni.length > 0"
                            class="mt-2 space-y-2"
                        >
                            <div
                                v-for="(sesi, sIdx) in room.sesiHariIni"
                                :key="sIdx"
                                class="flex items-center justify-between rounded-xl bg-slate-50 p-2.5 text-xs dark:bg-slate-900"
                            >
                                <div>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ sesi.kegiatan }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        {{ sesi.waktu }}
                                    </p>
                                </div>
                                <span
                                    class="rounded-md px-2 py-0.5 text-[9px] font-bold"
                                    :class="[
                                        sesi.status === 'Berlangsung'
                                            ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                            : sesi.status === 'Selesai'
                                              ? 'bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300'
                                              : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                    ]"
                                >
                                    {{ sesi.status }}
                                </span>
                            </div>
                        </div>
                        <div
                            v-else
                            class="mt-2 rounded-xl bg-slate-50 p-3 text-center text-xs text-slate-400 dark:bg-slate-900"
                        >
                            Tidak ada agenda terjadwal hari ini (Ruangan Bebas)
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
