<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import type { ScheduleRecord } from '@/types/r2-read-contracts';
const props = defineProps<{ dbJadwalUji: ScheduleRecord[] }>();
const schedules = computed(() => props.dbJadwalUji ?? []);
const page = usePage();
const feedback = computed(
    () => (page.props.flash ?? {}) as { success?: string; error?: string },
);
const selected = ref<number | null>(null);
const form = useForm({ nilai: null as number | null, catatan: '' });
const selectExam = (exam: ScheduleRecord) => {
    if (!exam.dapat_menilai || form.processing) return;
    selected.value = exam.id;
    form.nilai = null;
    form.catatan = '';
    form.clearErrors();
};
const submit = () => {
    if (
        form.processing ||
        !schedules.value.find((exam) => exam.id === selected.value)
            ?.dapat_menilai
    )
        return;
    form.post(`/dosen/penilaian/${selected.value}`, { preserveScroll: true });
};
</script>
<template>
    <Head title="Penugasan Penilaian • SIPTA IF" />
    <div class="space-y-6">
        <PageHeaderBox
            title="Penugasan Seminar &amp; Sidang"
            subtitle="Jadwal dengan penugasan Anda sebagai pembimbing atau penguji"
        />
        <p
            class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:bg-amber-950 dark:text-amber-200"
        >
            Setiap penguji menyimpan satu nilai 0–100, maksimal dua angka
            desimal. Nilai tidak dapat ditimpa. Ujian selesai setelah dua
            penguji berbeda mengirim nilai; nilai akhir adalah rata-rata
            keduanya. Rubrik dan berita acara belum tersedia.
        </p>
        <p v-if="!schedules.length" class="text-slate-500">
            Belum ada penugasan seminar / sidang.
        </p>
        <section
            v-for="schedule in schedules"
            :key="schedule.id"
            class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
        >
            <h2 class="font-bold">
                {{ schedule.mahasiswa?.name ?? 'Mahasiswa belum tersedia' }} —
                {{ schedule.tipe }}
            </h2>
            <p>NIM: {{ schedule.mahasiswa?.nim_nip ?? 'Belum tersedia' }}</p>
            <p>
                {{ schedule.tanggal?.slice(0, 10) }} •
                {{ schedule.jam_mulai }} – {{ schedule.jam_selesai }}
            </p>
            <p>Status: {{ schedule.status }}</p>
            <p v-if="schedule.catatan" class="mt-3 text-sm">
                Catatan jadwal: {{ schedule.catatan }}
            </p>
            <div class="my-3 space-y-2">
                <h3 class="font-semibold">Dokumen untuk penguji</h3>
                <a
                    v-for="document in schedule.documents"
                    :key="document.url"
                    :href="document.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block text-blue-600 underline"
                    >Buka {{ document.label }}</a
                >
                <p
                    v-if="!schedule.documents?.length"
                    class="text-sm text-slate-500"
                >
                    Dokumen privat belum tersedia atau Anda bukan penguji.
                    Berkas lama di penyimpanan publik memerlukan pemindahan
                    terkontrol.
                </p>
            </div>
            <p>Penilaian tersimpan: {{ schedule.jumlah_penilaian ?? 0 }}/2</p>
            <p v-if="schedule.penilaian_saya">
                Nilai Anda: {{ schedule.penilaian_saya.nilai }} —
                {{ schedule.penilaian_saya.catatan }}
            </p>
            <p v-if="schedule.nilai_akhir != null">
                Nilai akhir: {{ schedule.nilai_akhir }}
            </p>
            <button
                v-if="schedule.dapat_menilai"
                type="button"
                :disabled="form.processing"
                @click="selectExam(schedule)"
            >
                Isi Nilai
            </button>
            <form
                v-if="selected === schedule.id && schedule.dapat_menilai"
                class="space-y-3"
                @submit.prevent="submit"
            >
                <label :for="`nilai-${schedule.id}`">Nilai (0–100)</label>
                <input
                    :id="`nilai-${schedule.id}`"
                    v-model.number="form.nilai"
                    type="number"
                    min="0"
                    max="100"
                    step="0.01"
                    required
                    :disabled="form.processing"
                />
                <label :for="`catatan-${schedule.id}`">Catatan penguji</label>
                <textarea
                    :id="`catatan-${schedule.id}`"
                    v-model="form.catatan"
                    :disabled="form.processing"
                />
                <p v-for="message in form.errors" :key="message" role="alert">
                    {{ message }}
                </p>
                <button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Nilai' }}
                </button>
            </form>
        </section>
        <p v-if="feedback.success" role="status">{{ feedback.success }}</p>
        <p v-if="feedback.error" role="alert">{{ feedback.error }}</p>
    </div>
</template>
