<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type { KaprodiReviewProps } from '@/types/kaprodi-persetujuan';
const props = defineProps<KaprodiReviewProps>();
type Section = 'judul' | 'sempro' | 'sidang';
const activeSection = ref<Section>('judul');
const search = ref('');
const records = computed(() =>
    activeSection.value === 'judul'
        ? props.dbJudul
        : activeSection.value === 'sempro'
          ? props.dbSempro
          : props.dbSidang,
);
const filtered = computed(() =>
    records.value.filter((item) =>
        [item.judul_ta, item.mahasiswa?.name, item.mahasiswa?.nim_nip].some(
            (value) =>
                value
                    ?.toLowerCase()
                    .includes(search.value.trim().toLowerCase()),
        ),
    ),
);
const selected = ref<{ id: number; tipe: Section } | null>(null);
const activeJudul = computed(() =>
    selected.value?.tipe === 'judul'
        ? props.dbJudul.find((item) => item.id === selected.value?.id)
        : undefined,
);
const activeAcademic = computed(() =>
    selected.value?.tipe !== 'judul'
        ? (selected.value?.tipe === 'sempro'
              ? props.dbSempro
              : props.dbSidang
          ).find((item) => item.id === selected.value?.id)
        : undefined,
);
const showModal = ref(false);
const note = ref('');
const p1 = ref<number | null>(null);
const p2 = ref<number | null>(null);
const saving = ref(false);
const error = ref('');
const success = ref('');
const closeModal = () => {
    if (!saving.value) showModal.value = false;
};
const openReview = (id: number) => {
    if (saving.value) return;
    selected.value = { id, tipe: activeSection.value };
    note.value =
        activeJudul.value?.catatan_kaprodi ??
        activeAcademic.value?.catatan_kaprodi ??
        '';
    p1.value = activeJudul.value?.pembimbing_1_id ?? null;
    p2.value = activeJudul.value?.pembimbing_2_id ?? null;
    error.value = '';
    success.value = '';
    showModal.value = true;
};
const submit = (decision?: 'disetujui' | 'revisi' | 'ditolak') => {
    const identity = selected.value;
    const judul = activeJudul.value;
    const academic = activeAcademic.value;
    if (!identity || (!judul && !academic) || saving.value) return;
    saving.value = true;
    error.value = '';
    success.value = '';
    router.patch(
        `/kaprodi/persetujuan/${identity.tipe}/${identity.id}`,
        judul
            ? {
                  status: decision ?? 'disetujui',
                  pembimbing_1_id: p1.value,
                  pembimbing_2_id: p2.value,
                  catatan_kaprodi: note.value,
              }
            : {
                  status_akademik: decision ?? academic!.status_akademik,
                  catatan_kaprodi: note.value,
              },
        {
            preserveScroll: true,
            onSuccess: (page) => {
                const flash = page.props.flash as KaprodiReviewProps['flash'];
                if (flash?.error) error.value = flash.error;
                else if (flash?.success) {
                    success.value = flash.success;
                    showModal.value = false;
                } else
                    error.value =
                        'Konfirmasi server belum diterima. Muat ulang data sebelum mencoba kembali.';
            },
            onError: (errors) => {
                error.value =
                    Object.values(errors).join(' ') || 'Permintaan gagal.';
            },
            onNetworkError: () => {
                error.value = 'Koneksi gagal. Silakan coba kembali.';
            },
            onHttpException: () => {
                error.value =
                    'Permintaan gagal diproses server. Silakan muat ulang data.';
            },
            onCancel: () => {
                error.value = 'Permintaan dibatalkan.';
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
};
</script>
<template>
    <Head title="Persetujuan Akademik • SIPTA IF" />
    <div class="space-y-6">
        <PageHeaderBox
            badge="Wewenang Ketua Program Studi"
            title="Persetujuan Akademik Tugas Akhir"
            description="Tinjau judul, Seminar Proposal, dan Sidang TA. Verifikasi berkas tetap menjadi kewenangan Tendik."
        />
        <p v-if="success" role="status" class="text-sm text-emerald-700">
            {{ success }}
        </p>
        <p v-if="error" role="alert" class="text-sm text-red-600">
            {{ error }}
        </p>
        <div class="flex gap-2">
            <Button variant="outline" @click="activeSection = 'judul'"
                >Judul ({{ dbJudul.length }})</Button
            >
            <Button variant="outline" @click="activeSection = 'sempro'"
                >Sempro ({{ dbSempro.length }})</Button
            >
            <Button variant="outline" @click="activeSection = 'sidang'"
                >Sidang TA ({{ dbSidang.length }})</Button
            >
        </div>
        <Card class="space-y-4 p-6">
            <input
                v-model="search"
                aria-label="Cari pengajuan"
                placeholder="Cari nama, NIM, atau judul..."
                class="w-full rounded-xl border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-900"
            />
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr>
                            <th class="p-3">Mahasiswa</th>
                            <th class="p-3">Judul</th>
                            <th class="p-3">Status Tersimpan</th>
                            <th class="p-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in filtered"
                            :key="`${activeSection}-${item.id}`"
                            class="border-t border-slate-200 dark:border-slate-800"
                        >
                            <td class="p-3">
                                {{ item.mahasiswa?.name ?? 'Tidak tersedia' }}
                                <p>
                                    NIM:
                                    {{
                                        item.mahasiswa?.nim_nip ??
                                        'Tidak tersedia'
                                    }}
                                </p>
                            </td>
                            <td class="p-3">
                                {{ item.judul_ta }}
                                <p v-if="'bidang_penelitian' in item">
                                    {{ item.bidang_penelitian }}
                                </p>
                                <p v-if="'pembimbing1' in item">
                                    P1:
                                    {{
                                        item.pembimbing1?.name ??
                                        'Belum ditetapkan'
                                    }}
                                    · P2:
                                    {{
                                        item.pembimbing2?.name ??
                                        'Belum ditetapkan'
                                    }}
                                </p>
                            </td>
                            <td class="p-3">
                                {{ item.status }}
                                <p v-if="'status_akademik' in item">
                                    Akademik: {{ item.status_akademik }}
                                </p>
                            </td>
                            <td class="p-3">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    :disabled="saving"
                                    @click="openReview(item.id)"
                                    >{{
                                        activeSection === 'judul'
                                            ? 'Review Judul'
                                            : 'Tinjau'
                                    }}</Button
                                >
                            </td>
                        </tr>
                        <tr v-if="!filtered.length">
                            <td
                                colspan="4"
                                class="p-6 text-center text-slate-500"
                            >
                                Tidak ada pengajuan yang sesuai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>
    <Modal :show="showModal" max-width="lg" @close="closeModal">
        <div class="space-y-4 p-6 text-sm">
            <h3 class="font-bold">
                {{
                    activeJudul
                        ? 'Penetapan Dosen Pembimbing'
                        : 'Tinjauan Akademik'
                }}
            </h3>
            <template v-if="activeJudul || activeAcademic">
                <p>{{ (activeJudul ?? activeAcademic)?.judul_ta }}</p>
                <template v-if="activeJudul">
                    <label for="review-p1">Dosen Pembimbing 1 (Utama)</label>
                    <select
                        id="review-p1"
                        v-model="p1"
                        :disabled="saving"
                        class="w-full rounded-xl border p-2 dark:bg-slate-900"
                    >
                        <option :value="null">
                            Tidak mengubah / belum ditetapkan
                        </option>
                        <option
                            v-for="dosen in dosenOptions"
                            :key="dosen.id"
                            :value="dosen.id"
                        >
                            {{ dosen.name }}
                        </option>
                    </select>
                    <label for="review-p2"
                        >Dosen Pembimbing 2 (Pendamping)</label
                    >
                    <select
                        id="review-p2"
                        v-model="p2"
                        :disabled="saving"
                        class="w-full rounded-xl border p-2 dark:bg-slate-900"
                    >
                        <option :value="null">
                            Tidak mengubah / belum ditetapkan
                        </option>
                        <option
                            v-for="dosen in dosenOptions"
                            :key="dosen.id"
                            :value="dosen.id"
                        >
                            {{ dosen.name }}
                        </option>
                    </select>
                    <p>
                        Penetapan disimpan pada pengajuan judul. Penerbitan SK
                        belum tersedia.
                    </p>
                </template>
                <p v-else>
                    Status: {{ activeAcademic?.status }}. Catatan tidak mengubah
                    status atau verifikasi Tendik. Pratinjau berkas, jadwal, dan
                    kelayakan belum tersedia pada halaman ini.
                </p>
                <label for="review-note"
                    >Catatan Akademik & Arahan Kaprodi</label
                >
                <textarea
                    id="review-note"
                    v-model="note"
                    :disabled="saving"
                    rows="3"
                    class="w-full rounded-xl border p-2 dark:bg-slate-900"
                />
            </template>
            <p v-else>Data pengajuan tidak lagi tersedia.</p>
            <p v-if="error" role="alert" class="text-red-600">{{ error }}</p>
            <p>
                Keputusan akademik terpisah dari verifikasi administrasi dan
                status ujian.
            </p>
            <p v-if="saving" role="status">Menyimpan...</p>
            <Button :disabled="saving" @click="submit('ditolak')">Tolak</Button>
            <Button
                v-if="activeAcademic"
                :disabled="saving"
                @click="submit('disetujui')"
                >Setujui Akademik</Button
            >
            <div class="flex justify-end gap-2">
                <Button
                    variant="secondary"
                    :disabled="saving"
                    @click="closeModal"
                    >Tutup</Button
                >
                <Button
                    variant="outline"
                    :disabled="saving"
                    @click="submit('revisi')"
                    >Minta Revisi</Button
                >
                <Button
                    v-if="activeJudul || activeAcademic"
                    variant="primary"
                    :disabled="saving"
                    @click="submit()"
                    >{{
                        activeJudul ? 'Setujui Judul' : 'Simpan Catatan'
                    }}</Button
                >
            </div>
        </div>
    </Modal>
</template>
