<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import { computed } from 'vue';

// ─── Props dari MonitoringController ─────────────────────────────────────────
const props = defineProps<{
    totalMahasiswa: number;
    progressCounts: {
        pengajuan_judul: number;
        judul_disetujui: number;
        sempro_menunggu: number;
        sempro_terjadwal: number;
        sempro_selesai: number;
        sidang_menunggu: number;
        sidang_terjadwal: number;
        sidang_selesai: number;
    };
    bimbinganRekap: {
        id: number;
        name: string;
        nip: string;
        total_bimbingan: number;
        total_sesi: number;
        sesi_disetujui: number;
    }[];
    mahasiswaList: {
        id: number;
        nama: string;
        nim: string;
        judul: string | null;
        status_judul: string;
        pembimbing1: string | null;
        pembimbing2: string | null;
    }[];
    jadwalAktif: {
        id: number;
        tipe: string;
        tanggal: string;
        jam_mulai: string;
        jam_selesai: string;
        mahasiswa: string;
        nim: string;
        pembimbing: string;
        penguji: string;
        gelombang: number;
    }[];
}>();

// ─── Label Status ─────────────────────────────────────────────────────────────
const statusLabel: Record<string, string> = {
    belum_mengajukan: 'Belum Mengajukan',
    draft: 'Draft',
    menunggu: 'Menunggu',
    disetujui: 'Disetujui',
    ditolak: 'Ditolak',
    revisi: 'Revisi',
};

const statusColor: Record<string, string> = {
    belum_mengajukan: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    menunggu: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    disetujui: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    ditolak: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    revisi: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
};

const sempro_aktif = computed(
    () => props.progressCounts.sempro_terjadwal + props.progressCounts.sempro_menunggu,
);
const sidang_aktif = computed(
    () => props.progressCounts.sidang_terjadwal + props.progressCounts.sidang_menunggu,
);
</script>

<template>
    <Head title="Monitoring Kemajuan TA" />
    <div class="space-y-6">
        <PageHeaderBox title="Monitoring Kemajuan Tugas Akhir Mahasiswa" />

        <!-- ── Kartu Statistik ──────────────────────────────────────────── -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div
                class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800"
            >
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Total Mahasiswa
                </p>
                <p class="mt-1 text-2xl font-bold text-slate-800 dark:text-white">
                    {{ totalMahasiswa }}
                </p>
            </div>
            <div
                class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800"
            >
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Judul Disetujui
                </p>
                <p class="mt-1 text-2xl font-bold text-green-600">
                    {{ progressCounts.judul_disetujui }}
                </p>
            </div>
            <div
                class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800"
            >
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Sempro Aktif
                </p>
                <p class="mt-1 text-2xl font-bold text-blue-600">{{ sempro_aktif }}</p>
                <p class="text-xs text-slate-500">
                    {{ progressCounts.sempro_selesai }} selesai
                </p>
            </div>
            <div
                class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800"
            >
                <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                    Sidang Aktif
                </p>
                <p class="mt-1 text-2xl font-bold text-purple-600">{{ sidang_aktif }}</p>
                <p class="text-xs text-slate-500">
                    {{ progressCounts.sidang_selesai }} selesai
                </p>
            </div>
        </div>

        <!-- ── Jadwal Aktif ─────────────────────────────────────────────── -->
        <section
            class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white dark:border-slate-800"
        >
            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <h2 class="font-semibold text-slate-800 dark:text-white">
                    Jadwal Terpublikasi ({{ jadwalAktif.length }})
                </h2>
            </div>
            <div v-if="jadwalAktif.length === 0" class="px-4 py-6 text-center text-sm text-slate-400">
                Belum ada jadwal yang dipublikasikan.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-2 font-medium">Tanggal</th>
                            <th class="px-4 py-2 font-medium">Tipe</th>
                            <th class="px-4 py-2 font-medium">Mahasiswa</th>
                            <th class="px-4 py-2 font-medium">Pembimbing</th>
                            <th class="px-4 py-2 font-medium">Penguji</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="j in jadwalAktif"
                            :key="j.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                        >
                            <td class="px-4 py-2 tabular-nums">
                                {{ j.tanggal }}<br />
                                <span class="text-xs text-slate-400">{{ j.jam_mulai }}–{{ j.jam_selesai }}</span>
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="j.tipe === 'sempro' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700'"
                                >
                                    {{ j.tipe === 'sempro' ? 'Sempro' : 'Sidang' }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <p class="font-medium text-slate-800 dark:text-white">{{ j.mahasiswa }}</p>
                                <p class="text-xs text-slate-400">{{ j.nim }}</p>
                            </td>
                            <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ j.pembimbing ?? '—' }}</td>
                            <td class="px-4 py-2 text-slate-600 dark:text-slate-300">{{ j.penguji ?? '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ── Rekap Bimbingan per Dosen ────────────────────────────────── -->
        <section
            class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white dark:border-slate-800"
        >
            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <h2 class="font-semibold text-slate-800 dark:text-white">
                    Rekap Bimbingan per Dosen
                </h2>
            </div>
            <div v-if="bimbinganRekap.length === 0" class="px-4 py-6 text-center text-sm text-slate-400">
                Belum ada data bimbingan.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-2 font-medium">Dosen</th>
                            <th class="px-4 py-2 font-medium text-center">Mhs. Dibimbing</th>
                            <th class="px-4 py-2 font-medium text-center">Total Sesi</th>
                            <th class="px-4 py-2 font-medium text-center">Sesi Disetujui</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="d in bimbinganRekap"
                            :key="d.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                        >
                            <td class="px-4 py-2">
                                <p class="font-medium text-slate-800 dark:text-white">{{ d.name }}</p>
                                <p class="text-xs text-slate-400">{{ d.nip }}</p>
                            </td>
                            <td class="px-4 py-2 text-center">{{ d.total_bimbingan }}</td>
                            <td class="px-4 py-2 text-center">{{ d.total_sesi }}</td>
                            <td class="px-4 py-2 text-center">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="d.sesi_disetujui > 0 ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ d.sesi_disetujui }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ── Daftar Status TA per Mahasiswa ───────────────────────────── -->
        <section
            class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white dark:border-slate-800"
        >
            <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800">
                <h2 class="font-semibold text-slate-800 dark:text-white">
                    Status Pengajuan Judul per Mahasiswa ({{ mahasiswaList.length }})
                </h2>
            </div>
            <div v-if="mahasiswaList.length === 0" class="px-4 py-6 text-center text-sm text-slate-400">
                Belum ada data mahasiswa.
            </div>
            <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                            <th class="px-4 py-2 font-medium">Mahasiswa</th>
                            <th class="px-4 py-2 font-medium">Judul TA</th>
                            <th class="px-4 py-2 font-medium">Status</th>
                            <th class="px-4 py-2 font-medium">Pembimbing</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        <tr
                            v-for="m in mahasiswaList"
                            :key="m.id"
                            class="hover:bg-slate-50 dark:hover:bg-slate-800/30"
                        >
                            <td class="px-4 py-2">
                                <p class="font-medium text-slate-800 dark:text-white">{{ m.nama }}</p>
                                <p class="text-xs text-slate-400">{{ m.nim }}</p>
                            </td>
                            <td class="max-w-xs px-4 py-2 text-slate-600 dark:text-slate-300">
                                {{ m.judul ?? '—' }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    class="rounded px-2 py-0.5 text-xs font-medium"
                                    :class="statusColor[m.status_judul] ?? statusColor.belum_mengajukan"
                                >
                                    {{ statusLabel[m.status_judul] ?? m.status_judul }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300">
                                <span v-if="m.pembimbing1">{{ m.pembimbing1 }}</span>
                                <span v-if="m.pembimbing1 && m.pembimbing2"> / </span>
                                <span v-if="m.pembimbing2">{{ m.pembimbing2 }}</span>
                                <span v-if="!m.pembimbing1 && !m.pembimbing2">—</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</template>
