<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import {
    BookOpen,
    CheckCircle2,
    Download,
    FileArchive,
    FileCheck2,
    FileText,
    Lock,
} from 'lucide-vue-next';

const stages = [
    { label: 'Pengajuan Judul', state: 'done' },
    { label: 'Bimbingan TA', state: 'current' },
    { label: 'Seminar Proposal', state: 'next' },
    { label: 'Penyusunan Laporan', state: 'next' },
    { label: 'Sidang TA', state: 'next' },
    { label: 'Revisi & Pengesahan', state: 'next' },
];

const documents: {
    group: string;
    icon: typeof BookOpen;
    items: {
        code?: string;
        title: string;
        description: string;
        action: string;
        available: boolean;
        locked?: boolean;
    }[];
}[] = [
    {
        group: 'Panduan Utama',
        icon: BookOpen,
        items: [
            {
                title: 'SOP & Buku Pedoman Tugas Akhir Informatika ITK',
                description:
                    'Panduan teknis penulisan, format sitasi IEEE, dan alur pendaftaran.',
                action: 'Unduh PDF',
                available: false,
            },
        ],
    },
    {
        group: 'Template Administrasi',
        icon: FileText,
        items: [
            {
                code: 'TA-01 s/d TA-05',
                title: 'Template Formulir Pengajuan & Monitoring Bimbingan',
                description: 'Format resmi berkas administrasi Tugas Akhir.',
                action: 'Unduh ZIP',
                available: false,
            },
        ],
    },
    {
        group: 'Dokumen Tahap Akhir',
        icon: FileCheck2,
        items: [
            {
                title: 'Lembar Pengesahan Tugas Akhir',
                description:
                    'Tersedia setelah revisi disetujui oleh dosen terkait.',
                action: 'Terkunci',
                available: false,
                locked: true,
            },
        ],
    },
];
</script>

<template>
    <Head title="Prosedur Tugas Akhir - SIPTA IF" />

    <div class="mx-auto max-w-7xl space-y-6">
        <section
            class="rounded-2xl border border-slate-200/80 bg-white px-6 py-5 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <p
                class="text-xs font-semibold tracking-[0.12em] text-blue-600 uppercase dark:text-blue-400"
            >
                Panduan Akademik
            </p>
            <h1
                class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
            >
                Prosedur Tugas Akhir
            </h1>
            <p
                class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-500 dark:text-slate-400"
            >
                Lihat alur pelaksanaan Tugas Akhir dan unduh dokumen yang
                diperlukan pada setiap tahap.
            </p>
        </section>

        <section
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Alur Tugas Akhir
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Tahapan umum yang perlu diselesaikan mahasiswa.
                    </p>
                </div>
                <span
                    class="shrink-0 rounded-lg bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300"
                >
                    Tahap berjalan: Bimbingan
                </span>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-6 lg:gap-0">
                <div
                    v-for="(stage, index) in stages"
                    :key="stage.label"
                    class="relative flex items-start gap-3 lg:block lg:pr-4"
                >
                    <div
                        class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 bg-white dark:bg-[#0E1626]"
                        :class="
                            stage.state === 'done'
                                ? 'border-emerald-500 text-emerald-600'
                                : stage.state === 'current'
                                  ? 'border-blue-500 text-blue-600'
                                  : 'border-slate-300 text-slate-400 dark:border-slate-700'
                        "
                    >
                        <CheckCircle2
                            v-if="stage.state === 'done'"
                            class="h-4 w-4"
                        />
                        <span v-else class="text-xs font-bold">{{
                            index + 1
                        }}</span>
                    </div>
                    <div class="pt-0.5 lg:mt-3 lg:pr-2">
                        <p
                            class="text-xs font-bold"
                            :class="
                                stage.state === 'current'
                                    ? 'text-blue-700 dark:text-blue-300'
                                    : 'text-slate-800 dark:text-slate-200'
                            "
                        >
                            {{ stage.label }}
                        </p>
                        <p
                            v-if="stage.state === 'current'"
                            class="mt-1 text-[11px] text-blue-600 dark:text-blue-400"
                        >
                            Sedang berjalan
                        </p>
                    </div>
                    <div
                        v-if="index < stages.length - 1"
                        class="absolute top-8 left-4 h-4 w-px -translate-x-1/2 bg-slate-200 sm:hidden dark:bg-slate-700"
                    />
                    <div
                        v-if="index < stages.length - 1"
                        class="absolute top-4 right-0 left-8 z-0 hidden h-px -translate-y-1/2 bg-slate-200 lg:block dark:bg-slate-700"
                    />
                </div>
            </div>
        </section>

        <section
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800"
            >
                <FileArchive class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                <div>
                    <h2
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Dokumen Prosedur TA
                    </h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Dokumen resmi yang mendukung setiap tahap Tugas Akhir.
                    </p>
                </div>
            </div>

            <div class="mt-5 space-y-5">
                <div v-for="section in documents" :key="section.group">
                    <div class="mb-2 flex items-center gap-2">
                        <component
                            :is="section.icon"
                            class="h-4 w-4 text-slate-400"
                        />
                        <h3
                            class="text-xs font-bold tracking-wide text-slate-500 uppercase dark:text-slate-400"
                        >
                            {{ section.group }}
                        </h3>
                    </div>
                    <div class="space-y-2">
                        <div
                            v-for="item in section.items"
                            :key="item.title"
                            class="flex flex-col gap-3 rounded-xl border border-slate-200/80 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                        >
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="item.code"
                                        class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        >{{ item.code }}</span
                                    >
                                    <h4
                                        class="text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.title }}
                                    </h4>
                                </div>
                                <p
                                    class="mt-1 text-xs leading-relaxed text-slate-500 dark:text-slate-400"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                            <button
                                type="button"
                                disabled
                                class="inline-flex shrink-0 cursor-not-allowed items-center justify-center gap-1.5 rounded-lg bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-400 dark:bg-slate-800 dark:text-slate-500"
                            >
                                <Lock v-if="item.locked" class="h-3.5 w-3.5" />
                                <Download v-else class="h-3.5 w-3.5" />
                                {{
                                    item.locked ? 'Terkunci' : 'Belum tersedia'
                                }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>
