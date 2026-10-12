<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
</script>
<template>
    <Head title="Monitoring Kemajuan TA" />
    <div class="space-y-4">
        <PageHeaderBox title="Monitoring Kemajuan Tugas Akhir Mahasiswa" />
        <section
            class="dark:bg-navy-surface rounded-xl border border-slate-200 bg-white p-4 text-sm dark:border-slate-800"
            aria-labelledby="monitoring-unavailable"
        >
            <h2 id="monitoring-unavailable" class="font-semibold">
                Monitoring terpadu belum tersedia
            </h2>
            <p class="mt-2 text-slate-600 dark:text-slate-300">
                Halaman ini belum terhubung ke sumber data kemajuan mahasiswa.
                Jumlah mahasiswa, tahap akademik, sesi bimbingan, dan hasil
                Turnitin tidak ditampilkan tanpa data tersimpan. Ekspor laporan
                belum tersedia.
            </p>
            <div class="mt-4 flex flex-wrap gap-4">
                <Link
                    href="/kaprodi/persetujuan"
                    class="font-semibold text-blue-700 underline dark:text-blue-300"
                    >Persetujuan Akademik</Link
                >
                <Link
                    href="/kaprodi/penjadwalan"
                    class="font-semibold text-blue-700 underline dark:text-blue-300"
                    >Jadwal Sempro &amp; Sidang</Link
                >
            </div>
        </section>
    </div>
</template>
