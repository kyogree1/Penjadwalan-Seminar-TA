<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    ChevronDown,
    FileText,
    HelpCircle,
    Info,
    Search,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';

const searchQuery = ref('');

const openIndex = ref<number | null>(0);

const toggleAccordion = (idx: number) => {
    openIndex.value = openIndex.value === idx ? null : idx;
};

const guideItems = [
    {
        title: 'Masuk & Melengkapi Profil',
        menu: 'User Profile & Unggah e-TTD',
        content: [
            'Masuk memakai username (alamat email kampus Anda) dengan password berupa NIM. Percobaan login dibatasi 5 kali per menit.',
            'Buka menu User Profile untuk melengkapi data diri dan mengunggah tanda tangan digital (e-TTD).',
        ],
        warning: {
            title: 'Perhatian Penting!',
            notes: [
                'Password mahasiswa tetap NIM dan tidak bisa diganti sendiri. Kalau tidak bisa masuk, hubungi Tendik — halaman login sengaja tidak menyediakan tautan lupa password.',
                'Selama tanda tangan belum diunggah, semua menu pendaftaran terkunci dan Anda akan selalu dikembalikan ke halaman User Profile. Tanda tangan dipakai untuk membubuhkan paraf Anda di formulir TA yang dicetak sistem.',
                'Data Prodi Anda diisi oleh Tendik/Koorprodi. Prodi wajib terisi karena periode pendaftaran Sempro dan Sidang TA dibuka per prodi — kalau kosong, pendaftaran akan ditolak dengan pesan "Prodi Anda belum diatur".',
            ],
        },
    },
    {
        title: 'Pengajuan Judul & Dosen Pembimbing',
        menu: 'Menu: Pengajuan Judul',
        content: [
            'Pastikan telah berdiskusi awal dengan calon Dosen Pembimbing 1 dan 2 sebelum menginput data.',
            'Masukkan judul lengkap dan deskripsi bidang penelitian (AI, IoT, Cyber Security, RPL, dll.).',
            'Centang pernyataan konfirmasi bahwa kedua pembimbing telah bersedia.',
            'Setelah dikirim dan disetujui, pembimbing TIDAK DAPAT DIUBAH lagi secara mandiri.',
        ],
    },
    {
        title: 'Pelaksanaan Bimbingan & Logbook (Form TA-04)',
        menu: 'Menu: Bimbingan',
        content: [
            'Wajib melakukan bimbingan minimal 8 kali per semester untuk masing-masing pembimbing.',
            'Input logbook setiap selesai bimbingan (tanggal, rangkuman materi, dan keterangan tempat online/offline).',
            'Unduh Lembar Monitoring Bimbingan (Form TA-04) saat hendak mendaftar Sempro atau Sidang.',
        ],
    },
    {
        title: 'Pendaftaran Seminar Proposal (Sempro)',
        menu: 'Menu: Seminar Proposal',
        content: [
            'Buka formulir pendaftaran Sempro saat status periode gelombang aktif dibuka.',
            'Unggah berkas: Lembar Kehadiran Sempro (Form TA-03D min. 5 kali), Proposal BAB 1-3, dan Bukti Cek Plagiasi Turnitin (maks 20%).',
            'Tunggu verifikasi berkas dari tim akademik sebelum jadwal seminar diterbitkan oleh sistem penjadwalan.',
        ],
    },
    {
        title: 'Pendaftaran Sidang Tugas Akhir',
        menu: 'Menu: Sidang TA',
        content: [
            'Prasyarat: Telah dinyatakan LULUS seminar proposal dan menyelesaikan revisi proposal.',
            'Unggah Naskah Lengkap Skripsi (BAB 1-5), Skor IAET (ITK Academic English Test), dan Bukti Turnitin terbaru.',
            'Setelah sidang dan revisi di-ACC semua dosen, Lembar Pengesahan Tugas Akhir resmi akan otomatis terbit.',
        ],
    },
];
</script>

<template>
    <AppLayout title="Dashboard">
        <Head title="Panduan SIPTA IF" />

        <div class="mx-auto max-w-7xl space-y-6">
            <!-- Header Title Box -->
            <div
                class="rounded-2xl border border-slate-200/80 bg-white px-6 py-4 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <h2
                    class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                >
                    Panduan SIPTA IF
                </h2>
            </div>

            <!-- Panduan Mahasiswa Search Box (Sesuai Mockup) -->
            <div
                class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Panduan Mahasiswa
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Alur Tugas Akhir dari pengajuan judul sampai nilai
                        sidang keluar.
                    </p>
                </div>

                <div class="relative w-full sm:w-72">
                    <Search
                        class="absolute top-2.5 left-3 h-3.5 w-3.5 text-slate-400"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari langkah atau menu..."
                        class="w-full rounded-xl border border-slate-300 bg-slate-50/50 py-1.5 pr-3 pl-8 text-xs text-slate-800 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                    />
                </div>
            </div>

            <!-- Accordion List (Sesuai Mockup) -->
            <div class="space-y-4">
                <div
                    v-for="(item, idx) in guideItems"
                    :key="idx"
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <!-- Accordion Header -->
                    <div
                        class="flex cursor-pointer items-center justify-between p-5 hover:bg-slate-50/60 dark:hover:bg-slate-900/60"
                        @click="toggleAccordion(idx)"
                    >
                        <div class="flex items-center gap-3">
                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-xs font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400"
                            >
                                {{ idx + 1 }}.
                            </span>
                            <div>
                                <h4
                                    class="text-sm font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.title }}
                                </h4>
                                <p class="text-[11px] text-slate-400">
                                    {{ item.menu }}
                                </p>
                            </div>
                        </div>

                        <ChevronDown
                            class="h-4 w-4 text-slate-400 transition-transform"
                            :class="{ 'rotate-180': openIndex === idx }"
                        />
                    </div>

                    <!-- Accordion Content -->
                    <div
                        v-if="openIndex === idx"
                        class="space-y-4 border-t border-slate-100 p-6 text-xs text-slate-700 dark:border-slate-800 dark:text-slate-300"
                    >
                        <ul class="list-disc space-y-2 pl-5">
                            <li v-for="(p, pIdx) in item.content" :key="pIdx">
                                {{ p }}
                            </li>
                        </ul>

                        <!-- Warning Box (Jika ada) -->
                        <div
                            v-if="item.warning"
                            class="space-y-2 rounded-xl border border-yellow-200 bg-[#FCFDE1] p-4 text-slate-800 dark:border-yellow-900/50 dark:bg-yellow-950/30 dark:text-yellow-200"
                        >
                            <h5 class="text-xs font-bold">
                                {{ item.warning.title }}
                            </h5>
                            <ul
                                class="list-disc space-y-1 pl-4 text-[11px] leading-relaxed"
                            >
                                <li
                                    v-for="(note, nIdx) in item.warning.notes"
                                    :key="nIdx"
                                >
                                    {{ note }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
