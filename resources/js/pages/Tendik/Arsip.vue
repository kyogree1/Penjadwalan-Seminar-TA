<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Archive,
    ArrowRight,
    Award,
    CheckCircle2,
    FileCheck2,
    FileText,
    GraduationCap,
    Info,
    Printer,
    Search,
    Users,
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type { TendikArchiveProps } from '@/types/tendik-facilities';

const props = defineProps<TendikArchiveProps>();

const searchQuery = ref('');
const filterTipe = ref('all');

const filteredArsipList = computed(() => {
    let list = props.arsipList ?? [];
    if (filterTipe.value !== 'all') {
        list = list.filter((item) => item.tipe === filterTipe.value);
    }
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = list.filter(
            (item) =>
                (item.mahasiswa && item.mahasiswa.toLowerCase().includes(q)) ||
                (item.nim && item.nim.toLowerCase().includes(q)) ||
                (item.judul && item.judul.toLowerCase().includes(q)),
        );
    }
    return list;
});
</script>

<template>
    <Head title="Status Arsip - Portal Tendik" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Layanan Dokumen"
            title="Arsip & Pencetakan Berkas"
            description="Informasi kapabilitas penyimpanan dokumen permanen dan penerbitan berkas cetak akademik Tugas Akhir."
        />

        <!-- Ringkasan Statistik Arsip -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0E1626]">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Mahasiswa Lulus</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ props.summary.totalLulus }}</p>
                <p class="text-[11px] text-slate-400">Sidang TA selesai</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0E1626]">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sidang Selesai</p>
                <p class="mt-1 text-2xl font-bold text-purple-600">{{ props.summary.totalSelesaiSidang }}</p>
                <p class="text-[11px] text-slate-400">Berkas sidang akhir</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0E1626]">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Sempro Selesai</p>
                <p class="mt-1 text-2xl font-bold text-blue-600">{{ props.summary.totalSelesaiSempro }}</p>
                <p class="text-[11px] text-slate-400">Proposal tervalidasi</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-[#0E1626]">
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Periode Terakhir</p>
                <p class="mt-1 truncate text-lg font-bold text-slate-800 dark:text-slate-100">
                    {{ props.summary.periodeTerakhir ?? '2026/2027' }}
                </p>
                <p class="text-[11px] text-slate-400">Gelombang aktif</p>
            </div>
        </div>

        <!-- Tabel Daftar Arsip Kelulusan -->
        <Card class="p-6">
            <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">
                        Daftar Riwayat Ujian & Kelulusan Selesai
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Data arsip resmi mahasiswa yang telah menyelesaikan tahapan Seminar Proposal atau Sidang Tugas Akhir
                    </p>
                </div>

                <!-- Filter & Search -->
                <div class="flex flex-wrap items-center gap-2">
                    <select
                        v-model="filterTipe"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                    >
                        <option value="all">Semua Tahap</option>
                        <option value="Seminar Proposal">Seminar Proposal</option>
                        <option value="Sidang Tugas Akhir">Sidang Tugas Akhir</option>
                    </select>

                    <div class="relative">
                        <Search class="absolute top-2 left-2.5 h-3.5 w-3.5 text-slate-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari mahasiswa/judul..."
                            class="rounded-xl border border-slate-200 bg-white py-1.5 pr-3 pl-8 text-xs text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200"
                        />
                    </div>
                </div>
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            <th class="py-2.5 pr-4 font-semibold">Mahasiswa</th>
                            <th class="px-4 py-2.5 font-semibold">Tipe Ujian</th>
                            <th class="px-4 py-2.5 font-semibold">Judul Tugas Akhir</th>
                            <th class="px-4 py-2.5 font-semibold">Tanggal Selesai</th>
                            <th class="py-2.5 pl-4 text-center font-semibold">Status Arsip</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <tr v-if="filteredArsipList.length === 0">
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                Tidak ada data arsip yang cocok dengan pencarian.
                            </td>
                        </tr>
                        <tr
                            v-for="item in filteredArsipList"
                            :key="item.id + item.tipe"
                            class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30"
                        >
                            <td class="py-3 pr-4">
                                <p class="font-bold text-slate-900 dark:text-white">{{ item.mahasiswa }}</p>
                                <p class="font-mono text-[11px] text-slate-400">{{ item.nim }} • {{ item.prodi ?? 'Informatika' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                    :class="
                                        item.tipe === 'Seminar Proposal'
                                            ? 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                                            : 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300'
                                    "
                                >
                                    {{ item.tipe }}
                                </span>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-slate-700 dark:text-slate-300">
                                <p class="line-clamp-2 leading-relaxed">{{ item.judul }}</p>
                            </td>
                            <td class="px-4 py-3 font-mono text-[11px] text-slate-500 dark:text-slate-400">
                                {{ item.tanggal_selesai ?? '-' }}
                            </td>
                            <td class="py-3 pl-4 text-center">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    <CheckCircle2 class="h-3 w-3" /> Tersimpan
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>

        <!-- Capability Cards Grid -->
        <div class="grid gap-5 md:grid-cols-2">
            <!-- Card 1: Pengarsipan Berkas -->
            <Card class="flex flex-col justify-between p-6">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Archive class="h-5 w-5" />
                        </div>
                        <span
                            class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                        >
                            {{
                                props.archiveCapabilities.persistence
                                    ? 'Aktif'
                                    : 'Belum Didukung'
                            }}
                        </span>
                    </div>

                    <h3
                        class="mt-4 text-base font-bold text-slate-900 dark:text-white"
                    >
                        Penyimpanan Arsip Digital
                    </h3>
                    <p
                        class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Riwayat pencetakan belum tersimpan. Belum ada
                        penyimpanan arsip atau catatan penerbitan dokumen pada
                        sistem ini; tampilan ini tidak menyatakan bahwa arsip
                        tersimpan berjumlah nol.
                    </p>

                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-[11px] text-slate-500 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400"
                    >
                        <Info
                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400"
                        />
                        <p>
                            Dokumen mahasiswa saat ini tersimpan sementara untuk
                            kebutuhan verifikasi pendaftaran di menu Verifikasi
                            Berkas.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <span
                        class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                    >
                        Fasilitas cold storage & metadata arsip belum
                        terintegrasi
                    </span>
                </div>
            </Card>

            <!-- Card 2: Layanan Pencetakan & Penerbitan SK -->
            <Card class="flex flex-col justify-between p-6">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                        >
                            <Printer class="h-5 w-5" />
                        </div>
                        <span
                            class="inline-flex items-center rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-400"
                        >
                            {{
                                props.archiveCapabilities.printing
                                    ? 'Aktif'
                                    : 'Belum Didukung'
                            }}
                        </span>
                    </div>

                    <h3
                        class="mt-4 text-base font-bold text-slate-900 dark:text-white"
                    >
                        Penerbitan SK & Cetak Dokumen
                    </h3>
                    <p
                        class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                    >
                        Template resmi, penerbitan SK, unduh dokumen, dan cetak
                        ulang belum didukung. Tidak ada dokumen contoh atau
                        tombol yang mengklaim berhasil mencetak.
                    </p>

                    <div
                        class="mt-4 flex items-start gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3 text-[11px] text-slate-500 dark:border-slate-800 dark:bg-slate-900/50 dark:text-slate-400"
                    >
                        <FileText
                            class="mt-0.5 h-3.5 w-3.5 shrink-0 text-slate-400"
                        />
                        <p>
                            Format penomoran surat keputusan dan cetak berita
                            acara fisik masih diproses secara manual di luar
                            portal.
                        </p>
                    </div>
                </div>

                <div
                    class="mt-6 border-t border-slate-100 pt-4 dark:border-slate-800"
                >
                    <span
                        class="text-[11px] font-medium text-slate-400 dark:text-slate-500"
                    >
                        Integrasi PDF engine & signature digital belum terpasang
                    </span>
                </div>
            </Card>
        </div>

        <!-- Quick Access Guidance -->
        <Card class="p-6">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                Layanan Tendik yang Aktif
            </h4>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                Pilih modul layanan administrasi lain yang siap digunakan untuk
                operasional saat ini.
            </p>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <Link
                    href="/tendik/verifikasi"
                    class="group flex items-center justify-between rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:border-blue-400 hover:bg-white hover:shadow-xs dark:border-slate-800 dark:bg-slate-900/40 dark:hover:border-blue-500 dark:hover:bg-slate-900"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400"
                        >
                            <FileCheck2 class="h-4 w-4" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                            >
                                Verifikasi Berkas
                            </p>
                            <p
                                class="text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                Tinjau kelengkapan pendaftaran Sempro & Sidang
                                TA
                            </p>
                        </div>
                    </div>
                    <ArrowRight
                        class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-blue-600 dark:text-slate-500 dark:group-hover:text-blue-400"
                    />
                </Link>

                <Link
                    href="/tendik/mahasiswa"
                    class="group flex items-center justify-between rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:border-blue-400 hover:bg-white hover:shadow-xs dark:border-slate-800 dark:bg-slate-900/40 dark:hover:border-blue-500 dark:hover:bg-slate-900"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                        <div>
                            <p
                                class="text-xs font-bold text-slate-900 group-hover:text-indigo-600 dark:text-white dark:group-hover:text-indigo-400"
                            >
                                Direktori Mahasiswa
                            </p>
                            <p
                                class="text-[11px] text-slate-500 dark:text-slate-400"
                            >
                                Kelola akun mahasiswa, reset sandi, dan import
                                CSV
                            </p>
                        </div>
                    </div>
                    <ArrowRight
                        class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-1 group-hover:text-indigo-600 dark:text-slate-500 dark:group-hover:text-indigo-400"
                    />
                </Link>
            </div>
        </Card>
    </div>
</template>
