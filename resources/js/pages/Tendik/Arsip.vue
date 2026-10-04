<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import { Printer, Search } from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import { mockArsipDokumen, mockCetakTerbaru } from '@/data/tendik';
import type { ArsipDokumen, CetakTerbaru } from '@/types/models';

const props = withDefaults(
    defineProps<{
        arsip?: ArsipDokumen[];
        cetak?: CetakTerbaru[];
    }>(),
    {
        arsip: () => mockArsipDokumen,
        cetak: () => mockCetakTerbaru,
    },
);

const documents = ref<ArsipDokumen[]>([...props.arsip]);

const recentPrints = ref<CetakTerbaru[]>([...props.cetak]);

const searchRecent = ref('');

const filteredRecentPrints = computed(() => {
    const query = searchRecent.value.trim().toLowerCase();
    if (!query) return recentPrints.value;
    return recentPrints.value.filter((print) => {
        return (
            print.namaMhs.toLowerCase().includes(query) ||
            print.nim.includes(query) ||
            print.dokumen.toLowerCase().includes(query) ||
            print.petugas.toLowerCase().includes(query)
        );
    });
});
</script>

<template>
    <Head title="Arsip & Cetak Dokumen - Portal Tendik" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Layanan Dokumen Resmi"
            title="Pencetakan Berita Acara & Pengarsipan SK Tugas Akhir"
            description="Terbitkan formulir resmi TA-06, TA-09, Surat Tugas Dosen, dan Surat Keterangan Bebas TA untuk keperluan akademik mahasiswa."
        />

        <!-- Template Documents Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card
                v-for="doc in documents"
                :key="doc.id"
                class="flex flex-col justify-between p-5 transition-all hover:shadow-md"
            >
                <div>
                    <div class="flex items-center justify-between">
                        <span
                            class="rounded-lg bg-blue-100 px-2 py-1 text-xs font-extrabold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                        >
                            {{ doc.kode }}
                        </span>
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ doc.kategori }}
                        </span>
                    </div>
                    <h4
                        class="mt-3 text-xs font-bold text-slate-900 dark:text-white"
                    >
                        {{ doc.nama }}
                    </h4>
                    <p
                        class="mt-1 text-[11px] leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        {{ doc.deskripsi }}
                    </p>
                </div>

                <div
                    class="mt-4 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-[#0C1A40] py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-slate-900 active:scale-95"
                    >
                        <Printer class="h-3.5 w-3.5" /> Cetak Template Blanko
                    </button>
                </div>
            </Card>
        </div>

        <!-- Recent Generated Documents Table -->
        <Card class="p-6">
            <div
                class="flex items-center justify-between border-b border-slate-200 pb-4 dark:border-slate-800"
            >
                <div>
                    <h3
                        class="text-sm font-extrabold text-slate-900 dark:text-white"
                    >
                        Riwayat Pencetakan Dokumen Terbaru
                    </h3>
                    <p class="text-xs text-slate-500">
                        Dokumen resmi mahasiswa yang siap diserahkan ke ruang
                        sidang
                    </p>
                </div>

                <div class="relative">
                    <Search
                        class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                    />
                    <input
                        v-model="searchRecent"
                        type="text"
                        placeholder="Cari nama, NIM, dokumen..."
                        class="w-64 rounded-xl border border-slate-200 bg-slate-50 py-2 pr-4 pl-9 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                    />
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table
                    class="w-full text-left text-xs text-slate-600 dark:text-slate-300"
                >
                    <thead
                        class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                    >
                        <tr>
                            <th class="px-4 py-3">Nama Mahasiswa</th>
                            <th class="px-4 py-3">Jenis Dokumen</th>
                            <th class="px-4 py-3">Tanggal Terbit</th>
                            <th class="px-4 py-3">Petugas Tendik</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        v-if="filteredRecentPrints.length"
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="item in filteredRecentPrints"
                            :key="item.id"
                            class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40"
                        >
                            <td class="px-4 py-3.5">
                                <p
                                    class="font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.namaMhs }}
                                </p>
                                <p class="text-[10px] text-slate-500">
                                    NIM: {{ item.nim }}
                                </p>
                            </td>
                            <td
                                class="px-4 py-3.5 font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ item.dokumen }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-500">
                                {{ item.tanggal }}
                            </td>
                            <td
                                class="px-4 py-3.5 text-slate-600 dark:text-slate-400"
                            >
                                {{ item.petugas }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span
                                    class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                    :class="[
                                        item.status === 'Sudah Dicetak'
                                            ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300',
                                    ]"
                                >
                                    {{ item.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <Button size="sm" variant="outline">
                                    <Printer class="mr-1 h-3.5 w-3.5" />
                                    Cetak Ulang
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <Search
                                            class="h-8 w-8 text-slate-400"
                                        />
                                    </div>
                                    <div>
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            Tidak ada hasil pencarian
                                        </h4>
                                        <p
                                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                                        >
                                            Coba ubah kata kunci pencarian.
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
</template>
