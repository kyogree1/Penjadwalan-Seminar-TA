<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import { Calendar, FileCheck } from 'lucide-vue-next';
const props = defineProps<{
    stats: { pendingBimbingan: number; jadwalUjiCount: number };
}>();
const stats = computed(() => props.stats);
const page = usePage();
const lecturer = computed(
    () =>
        (
            page.props.auth as {
                user?: {
                    name: string;
                    nim_nip?: string | null;
                    jabatan?: string | null;
                };
            }
        )?.user,
);
</script>
<template>
    <Head title="Dashboard Dosen • SIPTA IF" />
    <div class="space-y-6">
        <PageHeaderBox title="Portal Dosen" :subtitle="lecturer?.name ?? ''" />
        <p class="text-sm text-slate-500">
            NIP: {{ lecturer?.nim_nip ?? 'Belum tersedia' }} •
            {{ lecturer?.jabatan ?? 'Jabatan belum tersedia' }}
        </p>
        <div class="grid gap-4 sm:grid-cols-2">
            <StatCard
                title="Antrean Logbook"
                :value="stats.pendingBimbingan"
                subtitle="Sesi milik Anda berstatus process"
                :icon="FileCheck"
                icon-color="amber"
            />
            <StatCard
                title="Jadwal Menguji Mendatang"
                :value="stats.jadwalUjiCount"
                subtitle="Jadwal terpublikasi mulai hari ini; posisi penguji"
                :icon="Calendar"
                icon-color="blue"
            />
        </div>
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
        >
            <h2 class="font-bold">Bimbingan &amp; jadwal penilaian</h2>
            <div class="mt-4 flex gap-4">
                <Link href="/dosen/bimbingan" class="text-blue-600"
                    >Buka mahasiswa bimbingan &amp; validasi logbook</Link
                ><Link href="/dosen/penilaian" class="text-blue-600"
                    >Lihat penugasan seminar / sidang</Link
                >
            </div>
            <p class="mt-4 text-sm text-slate-500">
                Kuota, tahap akademik, skor Turnitin, dan hasil penilaian belum
                tersedia dalam kontrak dashboard.
            </p>
        </section>
    </div>
</template>
