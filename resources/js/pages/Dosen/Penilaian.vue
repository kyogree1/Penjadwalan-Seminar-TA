<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    Award,
    Calendar,
    CheckCircle2,
    Clock,
    FileCheck,
    FileText,
    GraduationCap,
    HelpCircle,
    MapPin,
    PenTool,
    ShieldCheck,
    UserCheck,
} from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import ScoreRubricInput from '@/Components/ScoreRubricInput.vue';

// Active Examination Selection
const examType = ref<'Sempro' | 'Sidang'>('Sempro');

// Student & Exam Data
const currentStudent = ref({
    nama: 'Akmal Falah Maulana',
    nim: '11231006',
    prodi: 'S1 Informatika - ITK',
    angkatan: '2023',
    judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
    pembimbing1: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
    pembimbing2: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
    penguji1: 'Prof. Dr. Agus Tri Haryanto, M.T. (Ketua)',
    penguji2: 'Sri Wahyuni, S.Kom., M.T.',
    tanggal: 'Kamis, 24 September 2026',
    waktu: '09:00 - 10:30 WITA',
    ruangan: 'Ruang Sidang FSTI A (Gedung Kuliah Terpadu 304)',
    roleDosen: 'Pembimbing 1 & Anggota Penguji',
});

// Sempro Scores
const semproScores = ref({
    latarBelakang: 85,
    metodologi: 82,
    rencanaKerja: 90,
    presentasi: 88,
});

// Sidang Scores
const sidangScores = ref({
    naskahSkripsi: 84,
    produkSistem: 90,
    penguasaanMateri: 82,
    sikapKomunikasi: 88,
});

// Dynamic Final Score calculation
const finalTotalScore = computed(() => {
    if (examType.value === 'Sempro') {
        const s = semproScores.value;
        const total =
            (s.latarBelakang * 25) / 100 +
            (s.metodologi * 35) / 100 +
            (s.rencanaKerja * 20) / 100 +
            (s.presentasi * 20) / 100;
        return Number(total.toFixed(2));
    } else {
        const s = sidangScores.value;
        const total =
            (s.naskahSkripsi * 25) / 100 +
            (s.produkSistem * 35) / 100 +
            (s.penguasaanMateri * 30) / 100 +
            (s.sikapKomunikasi * 10) / 100;
        return Number(total.toFixed(2));
    }
});

// ITK Grade Conversion
const gradeResult = computed(() => {
    const score = finalTotalScore.value;
    if (score >= 80)
        return {
            grade: 'A',
            gpa: '4.00',
            desc: 'Sangat Memuaskan / Sangat Baik',
            color: 'emerald',
        };
    if (score >= 75)
        return { grade: 'AB', gpa: '3.50', desc: 'Baik Sekali', color: 'blue' };
    if (score >= 70)
        return { grade: 'B', gpa: '3.00', desc: 'Baik', color: 'cyan' };
    if (score >= 65)
        return { grade: 'BC', gpa: '2.50', desc: 'Cukup Baik', color: 'amber' };
    if (score >= 60)
        return { grade: 'C', gpa: '2.00', desc: 'Cukup', color: 'orange' };
    if (score >= 50)
        return { grade: 'D', gpa: '1.00', desc: 'Kurang', color: 'rose' };
    return {
        grade: 'E',
        gpa: '0.00',
        desc: 'Gagal / Tidak Lulus',
        color: 'rose',
    };
});

// Recommendation Status
const rekomendasiHasil = ref<'LULUS' | 'LULUS_REVISI' | 'MENGULANG'>(
    'LULUS_REVISI',
);
const batasRevisiHari = ref(14);
const catatanRevisi = ref(
    '1. Perjelas batasan implementasi arsitektur frontend Inertia Vue 3 pada bab 3.\n2. Tambahkan diagram alur crossover dan mutasi pada optimasi penjadwalan genetik di bab 4.\n3. Sesuaikan sitasi dengan format standar IEEE ITK.',
);
const confirmAccuracy = ref(false);
const isSubmitting = ref(false);
const showSuccessModal = ref(false);

const submitPenilaian = () => {
    if (!confirmAccuracy.value) return;
    isSubmitting.value = true;
    setTimeout(() => {
        isSubmitting.value = false;
        showSuccessModal.value = true;
    }, 600);
};
</script>

<template>
    <Head title="Lembar Penilaian Ujian TA • Dosen ITK" />

    <div class="space-y-6">
        <!-- 1. Header Box -->
        <PageHeaderBox
            title="Lembar Penilaian & Berita Acara Ujian TA"
            subtitle="Formulir resmi evaluasi kelulusan seminar proposal dan sidang skripsi Program Studi Informatika ITK."
        >
            <template #action>
                <!-- Switch Type Tab -->
                <div
                    class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1 text-xs font-bold dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 transition-all"
                        :class="[
                            examType === 'Sempro'
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                        ]"
                        @click="examType = 'Sempro'"
                    >
                        Seminar Proposal
                    </button>
                    <button
                        type="button"
                        class="rounded-lg px-3 py-1.5 transition-all"
                        :class="[
                            examType === 'Sidang'
                                ? 'bg-purple-600 text-white shadow-xs'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                        ]"
                        @click="examType = 'Sidang'"
                    >
                        Sidang Akhir Skripsi
                    </button>
                </div>
            </template>
        </PageHeaderBox>

        <!-- 2. Candidate Student Banner & Exam Metadata -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div
                class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
            >
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-600 text-lg font-black text-white shadow-md shadow-blue-500/20"
                    >
                        AM
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h3
                                class="text-base font-bold text-slate-900 dark:text-white"
                            >
                                {{ currentStudent.nama }}
                            </h3>
                            <span
                                class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                NIM: {{ currentStudent.nim }}
                            </span>
                            <span
                                class="rounded-md bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                            >
                                {{ currentStudent.prodi }}
                            </span>
                        </div>

                        <p
                            class="mt-1.5 line-clamp-2 text-xs font-semibold text-slate-800 dark:text-slate-200"
                        >
                            "{{ currentStudent.judul }}"
                        </p>

                        <div
                            class="mt-3 flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400"
                        >
                            <span class="flex items-center gap-1.5 font-medium">
                                <Calendar class="h-3.5 w-3.5 text-blue-600" />
                                {{ currentStudent.tanggal }}
                            </span>
                            <span
                                class="flex items-center gap-1.5 font-bold text-blue-600 dark:text-blue-400"
                            >
                                <Clock class="h-3.5 w-3.5" />
                                {{ currentStudent.waktu }}
                            </span>
                            <span class="flex items-center gap-1.5 font-medium">
                                <MapPin class="h-3.5 w-3.5 text-rose-500" />
                                {{ currentStudent.ruangan }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Role Badge of the Lecturer -->
                <div
                    class="shrink-0 rounded-xl bg-indigo-50/80 p-4 text-right dark:bg-indigo-950/30"
                >
                    <span
                        class="text-[10px] font-bold tracking-wider text-indigo-500 uppercase"
                    >
                        Peran Anda Dalam Sesi Ini:
                    </span>
                    <p
                        class="text-xs font-bold text-indigo-900 dark:text-indigo-200"
                    >
                        {{ currentStudent.roleDosen }}
                    </p>
                    <p
                        class="mt-1 text-[11px] text-indigo-600 dark:text-indigo-300"
                    >
                        Status e-TTD:
                        <strong class="text-emerald-600 dark:text-emerald-400"
                            >Tersertifikasi</strong
                        >
                    </p>
                </div>
            </div>
        </div>

        <!-- 3. Two Columns: Rubric Forms on Left & Live Final Grade Card on Right -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Left: Rubrics Criteria (2 Columns Span) -->
            <div class="space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3
                        class="text-sm font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Rubrik Komponen Penilaian (Standar ITK)
                    </h3>
                    <span class="text-xs text-slate-400"
                        >Rentang Skor: 0 - 100</span
                    >
                </div>

                <!-- SEMPRO RUBRICS -->
                <template v-if="examType === 'Sempro'">
                    <ScoreRubricInput
                        id="sempro-latar-belakang"
                        title="1. Latar Belakang & Rumusan Masalah"
                        description="Ketajaman latar belakang, urgensi penelitian, kejelasan rumusan masalah, dan relevansi tujuan."
                        :weight="25"
                        v-model="semproScores.latarBelakang"
                    />
                    <ScoreRubricInput
                        id="sempro-metodologi"
                        title="2. Tinjauan Pustaka & Metodologi"
                        description="Kesesuaian teori pendukung, sitasi artikel bereputasi 5 tahun terakhir, dan ketepatan tahapan metodologi."
                        :weight="35"
                        v-model="semproScores.metodologi"
                    />
                    <ScoreRubricInput
                        id="sempro-rencana-kerja"
                        title="3. Rencana Kerja & Kelayakan Penyelesaian"
                        description="Gantt chart jadwal penelitian, ketersediaan dataset/perangkat, dan kelayakan waktu penyelesaian."
                        :weight="20"
                        v-model="semproScores.rencanaKerja"
                    />
                    <ScoreRubricInput
                        id="sempro-presentasi"
                        title="4. Presentasi & Penguasaan Tanya Jawab"
                        description="Kejelasan pemaparan naskah, ketepatan alokasi waktu bicara, dan ketepatan argumentasi ilmiah."
                        :weight="20"
                        v-model="semproScores.presentasi"
                    />
                </template>

                <!-- SIDANG SKRIPSI RUBRICS -->
                <template v-else>
                    <ScoreRubricInput
                        id="sidang-naskah"
                        title="1. Kualitas Naskah Laporan Skripsi"
                        description="Sistematika penulisan karya ilmiah standar ITK, ketepatan tata bahasa, dan bukti Turnitin < 20%."
                        :weight="25"
                        v-model="sidangScores.naskahSkripsi"
                    />
                    <ScoreRubricInput
                        id="sidang-produk"
                        title="2. Produk / Sistem / Hasil Karya yang Dibangun"
                        description="Fungsionalitas perangkat lunak/model kecerdasan buatan, arsitektur kode, dan pengujian empiris (Testing)."
                        :weight="35"
                        v-model="sidangScores.produkSistem"
                    />
                    <ScoreRubricInput
                        id="sidang-materi"
                        title="3. Penguasaan Materi & Pemecahan Masalah"
                        description="Kedalaman pemahaman algoritma, kemampuan menjawab pertanyaan kritis dewan penguji, dan analisis data."
                        :weight="30"
                        v-model="sidangScores.penguasaanMateri"
                    />
                    <ScoreRubricInput
                        id="sidang-sikap"
                        title="4. Sikap & Komunikasi Ilmiah"
                        description="Etika akademik, kesantunan dalam berdiskusi, dan ketepatan penyajian materi sidang."
                        :weight="10"
                        v-model="sidangScores.sikapKomunikasi"
                    />
                </template>

                <!-- Catatan Revisi & Masukan Penguji -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div class="flex items-center gap-2">
                        <PenTool class="h-4 w-4 text-blue-600" />
                        <h4
                            class="text-sm font-bold text-slate-900 dark:text-white"
                        >
                            Catatan Revisi & Masukan Penguji untuk Mahasiswa
                        </h4>
                    </div>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        Catatan ini akan otomatis masuk ke lembar perbaikan
                        revisi (Form TA-05) yang wajib dipenuhi mahasiswa.
                    </p>

                    <div class="mt-3">
                        <textarea
                            v-model="catatanRevisi"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3.5 text-xs text-slate-900 focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:outline-none dark:border-slate-700 dark:bg-slate-800/80 dark:text-white"
                            placeholder="Tuliskan butir-butir revisi secara spesifik per bab..."
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Right: Sticky Live Final Grade & Recommendation Card -->
            <div class="space-y-4">
                <div
                    class="sticky top-20 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <h4
                        class="text-xs font-bold tracking-wider text-slate-400 uppercase"
                    >
                        Rekapitulasi Nilai Akhir
                    </h4>

                    <!-- Giant Score Circle -->
                    <div
                        class="mt-4 flex flex-col items-center justify-center rounded-2xl bg-slate-50/80 p-5 text-center dark:bg-slate-800/50"
                    >
                        <span
                            class="text-xs font-medium text-slate-500 dark:text-slate-400"
                            >Skor Total Tertimbang</span
                        >
                        <div
                            class="mt-1 text-4xl font-black tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ finalTotalScore }}
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <span
                                class="rounded-lg bg-blue-600 px-2.5 py-0.5 text-sm font-black text-white"
                            >
                                {{ gradeResult.grade }}
                            </span>
                            <span
                                class="text-xs font-semibold text-slate-600 dark:text-slate-300"
                            >
                                (Bobot {{ gradeResult.gpa }})
                            </span>
                        </div>
                        <p
                            class="mt-1.5 text-[11px] font-medium text-slate-500 dark:text-slate-400"
                        >
                            {{ gradeResult.desc }}
                        </p>
                    </div>

                    <!-- Keputusan Hasil Ujian -->
                    <div class="mt-5 space-y-2">
                        <label
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            Rekomendasi Dewan Penguji:
                        </label>

                        <div class="space-y-2">
                            <label
                                class="flex cursor-pointer items-center justify-between rounded-xl border p-3 text-xs transition-colors"
                                :class="[
                                    rekomendasiHasil === 'LULUS'
                                        ? 'border-emerald-500 bg-emerald-50/70 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'
                                        : 'border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                                ]"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="radio"
                                        name="rekomendasi"
                                        value="LULUS"
                                        v-model="rekomendasiHasil"
                                        class="text-emerald-600 focus:ring-emerald-500"
                                    />
                                    <span class="font-bold"
                                        >Lulus Tanpa Revisi</span
                                    >
                                </div>
                                <CheckCircle2
                                    class="h-4 w-4 text-emerald-600"
                                />
                            </label>

                            <label
                                class="flex cursor-pointer items-center justify-between rounded-xl border p-3 text-xs transition-colors"
                                :class="[
                                    rekomendasiHasil === 'LULUS_REVISI'
                                        ? 'border-blue-500 bg-blue-50/70 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300'
                                        : 'border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                                ]"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="radio"
                                        name="rekomendasi"
                                        value="LULUS_REVISI"
                                        v-model="rekomendasiHasil"
                                        class="text-blue-600 focus:ring-blue-500"
                                    />
                                    <span class="font-bold"
                                        >Lulus dengan Revisi</span
                                    >
                                </div>
                                <span
                                    class="rounded-md bg-blue-100 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/50 dark:text-blue-300"
                                >
                                    {{ batasRevisiHari }} Hari
                                </span>
                            </label>

                            <label
                                class="flex cursor-pointer items-center justify-between rounded-xl border p-3 text-xs transition-colors"
                                :class="[
                                    rekomendasiHasil === 'MENGULANG'
                                        ? 'border-rose-500 bg-rose-50/70 text-rose-800 dark:bg-rose-950/40 dark:text-rose-300'
                                        : 'border-slate-200 text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800',
                                ]"
                            >
                                <div class="flex items-center gap-2.5">
                                    <input
                                        type="radio"
                                        name="rekomendasi"
                                        value="MENGULANG"
                                        v-model="rekomendasiHasil"
                                        class="text-rose-600 focus:ring-rose-500"
                                    />
                                    <span class="font-bold"
                                        >Tidak Lulus / Ujian Ulang</span
                                    >
                                </div>
                                <AlertCircle class="h-4 w-4 text-rose-600" />
                            </label>
                        </div>
                    </div>

                    <!-- Confirmation & Digital Signature -->
                    <div
                        class="mt-5 border-t border-slate-100 pt-4 dark:border-slate-800"
                    >
                        <label
                            class="flex cursor-pointer items-start gap-2.5 text-xs text-slate-600 dark:text-slate-400"
                        >
                            <input
                                type="checkbox"
                                v-model="confirmAccuracy"
                                class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            />
                            <span>
                                Saya menyatakan nilai dan catatan revisi ini
                                diberikan secara objektif sesuai rubrik
                                penilaian ITK.
                            </span>
                        </label>

                        <div class="mt-4">
                            <Button
                                variant="primary"
                                class="w-full"
                                :disabled="!confirmAccuracy"
                                :loading="isSubmitting"
                                @click="submitPenilaian"
                            >
                                <ShieldCheck class="mr-1.5 h-4 w-4" />
                                <span>Kunci & Simpan Nilai Ujian</span>
                            </Button>
                        </div>

                        <p class="mt-2 text-center text-[10px] text-slate-400">
                            Berita acara resmi akan dikompilasi bersama nilai
                            dari dewan penguji lainnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal Dialog -->
    <Modal
        :show="showSuccessModal"
        max-width="md"
        @close="showSuccessModal = false"
    >
        <div class="p-6 text-center">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
            >
                <CheckCircle2 class="h-8 w-8" />
            </div>
            <h3 class="mt-4 text-base font-bold text-slate-900 dark:text-white">
                Penilaian Berhasil Disimpan & Dikunci!
            </h3>
            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                Nilai akhir sebesar
                <strong>{{ finalTotalScore }} ({{ gradeResult.grade }})</strong>
                dengan status <strong>{{ rekomendasiHasil }}</strong> telah
                dicatat ke dalam Berita Acara ITK.
            </p>
            <div class="mt-6 flex justify-center gap-3">
                <Link
                    href="/dosen/dashboard"
                    class="inline-flex items-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700"
                >
                    Kembali ke Dashboard Dosen
                </Link>
            </div>
        </div>
    </Modal>
</template>
