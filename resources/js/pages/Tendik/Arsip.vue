<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
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
            title="Arsip & Pencetakan"
            description="Status dukungan layanan, bukan daftar dokumen terbit."
        />
        <Card class="space-y-4 p-6">
            <h3 v-if="!props.archiveCapabilities.persistence" class="font-bold">
                Pengarsipan belum tersedia
            </h3>
            <p>
                Riwayat pencetakan belum tersimpan. Belum ada penyimpanan arsip
                atau catatan penerbitan dokumen pada sistem ini; tampilan ini
                tidak menyatakan bahwa arsip tersimpan berjumlah nol.
            </p>
            <h3 v-if="!props.archiveCapabilities.printing" class="font-bold">
                Pencetakan belum tersedia
            </h3>
            <p>
                Template resmi, penerbitan SK, unduh dokumen, dan cetak ulang
                belum didukung. Tidak ada dokumen contoh atau tombol yang
                mengklaim berhasil mencetak.
            </p>
        </Card>
    </div>
</template>
