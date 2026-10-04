<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Building2 } from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import { mockRuangan } from '@/data/tendik';
import type { Ruangan } from '@/types/models';

const props = withDefaults(
    defineProps<{
        ruangan?: Ruangan[];
    }>(),
    {
        ruangan: () => mockRuangan,
    },
);

const rooms = ref<Ruangan[]>([...props.ruangan]);

const viewMode = ref<'cards' | 'timeline'>('cards');
</script>

<template>
    <Head title="Manajemen Ruangan - Portal Tendik" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Sarana & Prasarana Sidang"
            title="Kelola Ruang Sidang Fisik & Fasilitas Hybrid"
            description="Pantau ketersediaan ruang ujian, perangkat multimedia, proyektor, dan koordinasi teknis operasional seminar Informatika."
        />

        <!-- View Toggle -->
        <div class="flex justify-end">
            <div
                class="inline-flex rounded-xl border border-slate-200 bg-slate-100/70 p-1 text-xs font-bold dark:border-slate-800 dark:bg-slate-900"
            >
                <button
                    type="button"
                    class="rounded-lg px-3 py-1 transition-all"
                    :class="[
                        viewMode === 'cards'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400',
                    ]"
                    @click="viewMode = 'cards'"
                >
                    Grid View
                </button>
                <button
                    type="button"
                    class="rounded-lg px-3 py-1 transition-all"
                    :class="[
                        viewMode === 'timeline'
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'text-slate-600 hover:text-slate-900 dark:text-slate-400',
                    ]"
                    @click="viewMode = 'timeline'"
                >
                    Timeline View
                </button>
            </div>
        </div>

        <!-- Room Cards Grid -->
        <div
            v-if="viewMode === 'cards'"
            class="grid grid-cols-1 gap-6 md:grid-cols-2"
        >
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

        <!-- Timeline View -->
        <Card v-else class="p-6">
            <div class="border-b border-slate-200 pb-4 dark:border-slate-800">
                <h3
                    class="text-sm font-extrabold text-slate-900 dark:text-white"
                >
                    Timeline Jadwal Hari Ini
                </h3>
                <p class="mt-1 text-xs text-slate-500">
                    Semua ruangan dan sesi sidang dalam satu tampilan
                </p>
            </div>

            <div class="mt-4 space-y-4">
                <div
                    v-for="room in rooms"
                    :key="room.id"
                    class="rounded-xl border border-slate-200 p-4 dark:border-slate-800"
                >
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Building2
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
                            <h4
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ room.nama }}
                            </h4>
                        </div>
                        <span
                            class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                            :class="[
                                room.status === 'Digunakan'
                                    ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
                            ]"
                        >
                            {{ room.status }}
                        </span>
                    </div>

                    <div v-if="room.sesiHariIni.length" class="space-y-2">
                        <div
                            v-for="(sesi, sIdx) in room.sesiHariIni"
                            :key="sIdx"
                            class="flex items-center gap-3 rounded-lg bg-slate-50 p-3 dark:bg-slate-900"
                        >
                            <div class="flex-1">
                                <p
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ sesi.kegiatan }}
                                </p>
                                <p class="mt-0.5 text-[10px] text-slate-500">
                                    {{ sesi.waktu }}
                                </p>
                            </div>
                            <span
                                class="rounded-md px-2 py-0.5 text-[9px] font-bold whitespace-nowrap"
                                :class="[
                                    sesi.status === 'Berlangsung'
                                        ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300'
                                        : sesi.status === 'Selesai'
                                          ? 'bg-slate-200 text-slate-600 dark:bg-slate-800 dark:text-slate-400'
                                          : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                ]"
                            >
                                {{ sesi.status }}
                            </span>
                        </div>
                    </div>
                    <div
                        v-else
                        class="rounded-lg bg-slate-50 p-3 text-center text-xs text-slate-400 dark:bg-slate-900"
                    >
                        Tidak ada agenda terjadwal
                    </div>
                </div>
            </div>
        </Card>
    </div>
</template>
