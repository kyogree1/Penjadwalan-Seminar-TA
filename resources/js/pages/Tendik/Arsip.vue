<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Archive,
    ArrowRight,
    FileCheck2,
    FileText,
    Info,
    Printer,
    Users,
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type { TendikArchiveProps } from '@/types/tendik-facilities';

const props = defineProps<TendikArchiveProps>();
</script>

<template>
    <Head title="Status Arsip - Portal Tendik" />

    <div class="space-y-6">
        <PageHeaderBox
            badge="Layanan Dokumen"
            title="Arsip & Pencetakan Berkas"
            description="Informasi kapabilitas penyimpanan dokumen permanen dan penerbitan berkas cetak akademik Tugas Akhir."
        />

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
