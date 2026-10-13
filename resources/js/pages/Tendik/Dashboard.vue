<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Building2, FileText, Users } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatCard from '@/Components/StatCard.vue';
import type { TendikDashboardProps } from '@/types/tendik-facilities';
const props = defineProps<TendikDashboardProps>();
</script>
<template>
    <Head title="Dashboard Tendik" />
    <div class="space-y-6">
        <PageHeaderBox
            badge="Portal Tendik"
            title="Administrasi Tugas Akhir"
            description="Ringkasan seluruh data terdaftar, bukan statistik semester atau bulan berjalan."
        />
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Antrean Verifikasi"
                :value="props.stats.antreanVerifikasi"
                subtitle="Pendaftaran berstatus menunggu"
                :icon="FileText"
                icon-color="amber"
            />
            <StatCard
                title="Total Ruangan"
                :value="props.stats.totalRuangan"
                subtitle="Seluruh ruangan terdaftar"
                :icon="Building2"
                icon-color="blue"
            />
            <StatCard
                title="Ruangan Tersedia"
                :value="props.stats.ruanganTersedia"
                subtitle="Status administratif, bukan pemakaian saat ini"
                :icon="Building2"
                icon-color="emerald"
            />
            <StatCard
                title="Akun Mahasiswa"
                :value="props.stats.totalMahasiswa"
                subtitle="Akun dengan peran mahasiswa"
                :icon="Users"
                icon-color="indigo"
            />
        </div>
        <Card class="space-y-3 p-6"
            ><h3 class="font-bold">Antrean Pendaftaran</h3>
            <p>
                {{ props.stats.antreanSempro }} Sempro •
                {{ props.stats.antreanSidang }} Sidang
            </p>
            <p class="text-sm text-slate-500">
                Jumlah pendaftaran berstatus menunggu dari seluruh periode;
                bukan jumlah mahasiswa unik.
            </p>
            <Link href="/tendik/verifikasi" class="text-blue-600"
                >Buka Verifikasi</Link
            ></Card
        >
        <div class="grid gap-4 sm:grid-cols-3">
            <Card class="p-6"
                ><Link href="/tendik/ruangan" class="text-blue-600"
                    >Kelola Ruangan</Link
                ></Card
            ><Card class="p-6"
                ><Link href="/tendik/mahasiswa" class="text-blue-600"
                    >Kelola Akun Mahasiswa</Link
                ></Card
            ><Card class="p-6"
                ><Link href="/tendik/arsip" class="text-blue-600"
                    >Status Layanan Arsip</Link
                >
                <p class="mt-2 text-sm text-slate-500">
                    Pengarsipan dan pencetakan belum tersedia.
                </p></Card
            >
        </div>
    </div>
</template>
