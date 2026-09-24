<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download, FileText, Lock } from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Prosedur Tugas Akhir - SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header Title Box -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h2
                    class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Prosedur TA
                </h2>
            </div>

            <!-- Card Dokumen Prosedur TA (Sesuai Mockup) -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h3
                    class="border-b border-slate-100 pb-4 text-base font-bold text-slate-900 dark:border-slate-800 dark:text-white"
                >
                    Dokumen Prosedur TA
                </h3>

                <div class="mt-4 space-y-4">
                    <!-- Item 1: Lembar Pengesahan (Sesuai Mockup) -->
                    <div
                        class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    Lembar Pengesahan Tugas Akhir
                                </h4>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Belum dapat diunduh - menunggu ACC revisi
                                </p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-800"
                            >
                                <Lock class="h-3.5 w-3.5" /> Terkunci
                            </span>
                        </div>
                    </div>

                    <!-- Additional standard SOP items -->
                    <div
                        class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    SOP & Buku Pedoman Tugas Akhir Informatika
                                    ITK (Edisi 2026)
                                </h4>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Panduan teknis penulisan skripsi, format
                                    sitasi IEEE, dan alur pendaftaran
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-400"
                            >
                                <Download class="h-3.5 w-3.5" /> Unduh PDF
                            </button>
                        </div>
                    </div>

                    <div
                        class="rounded-xl border border-slate-200/80 p-4 dark:border-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <h4
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    Template Formulir Pengajuan & Monitoring
                                    Bimbingan (Form TA-01 s/d TA-05)
                                </h4>
                                <p class="mt-0.5 text-[11px] text-slate-400">
                                    Format resmi berkas administrasi cetak
                                    sistem
                                </p>
                            </div>
                            <button
                                type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:bg-blue-950 dark:text-blue-400"
                            >
                                <Download class="h-3.5 w-3.5" /> Unduh ZIP
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
