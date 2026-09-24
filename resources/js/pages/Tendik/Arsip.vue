<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Award,
    Check,
    Download,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Filter,
    FolderArchive,
    Printer,
    Search,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';

const documents = ref([
    {
        id: 1,
        kode: 'TA-06',
        nama: 'Berita Acara Seminar Proposal (Sempro)',
        kategori: 'Berita Acara',
        deskripsi:
            'Dokumen pencatatan resmi hasil ujian seminar proposal, lembar revisi penguji, dan status kelulusan sempro.',
        tersedia: 68,
    },
    {
        id: 2,
        kode: 'TA-09',
        nama: 'Berita Acara Ujian Sidang Akhir Tugas Akhir',
        kategori: 'Berita Acara',
        deskripsi:
            'Formulir resmi penetapan nilai akhir yudisium skripsi oleh ketua penguji, anggota penguji, dan pembimbing.',
        tersedia: 34,
    },
    {
        id: 3,
        kode: 'ST-01',
        nama: 'Surat Tugas Tim Dosen Penguji & Pembimbing',
        kategori: 'Surat Tugas',
        deskripsi:
            'Surat penugasan dekanat/jurusan untuk dosen penguji 1, penguji 2, dan dosen pembimbing.',
        tersedia: 102,
    },
    {
        id: 4,
        kode: 'SK-TA',
        nama: 'Surat Keterangan Bebas Tugas Akhir (SK Bebas TA)',
        kategori: 'Surat Keterangan',
        deskripsi:
            'Syarat pengambilan ijazah & transkrip final yang menyatakan naskah skripsi telah tuntas dijilid & diunggah di repository.',
        tersedia: 24,
    },
]);

const recentPrints = ref([
    {
        id: 101,
        namaMhs: 'Akmal Falah Maulana',
        nim: '11231006',
        dokumen: 'Berita Acara Sidang Akhir (TA-09)',
        tanggal: '18 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Siap Cetak',
    },
    {
        id: 102,
        namaMhs: 'Siti Nurhaliza Putri',
        nim: '11211045',
        dokumen: 'Surat Tugas Tim Penguji (ST-01)',
        tanggal: '18 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Sudah Dicetak',
    },
    {
        id: 103,
        namaMhs: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        dokumen: 'Berita Acara Sempro (TA-06)',
        tanggal: '17 September 2026',
        petugas: 'Siti Nurhaliza, S.Kom.',
        status: 'Sudah Dicetak',
    },
]);
</script>

<template>
    <AppLayout title="Pengarsipan & Cetak Dokumen Resmi">
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
                            <Printer class="h-3.5 w-3.5" /> Cetak Template
                            Blanko
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
                            Dokumen resmi mahasiswa yang siap diserahkan ke
                            ruang sidang
                        </p>
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
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="item in recentPrints"
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
                    </table>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
