<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Calendar,
    CheckCircle2,
    Clock,
    FileCheck,
    FileSpreadsheet,
    FileText,
    Filter,
    GraduationCap,
    HelpCircle,
    Layers,
    PenTool,
    Search,
    UserCheck,
    Users,
} from 'lucide-vue-next';

import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import LogbookApprovalModal from '@/Components/LogbookApprovalModal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import ScheduleSlotCard from '@/Components/ScheduleSlotCard.vue';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import StudentReviewCard from '@/Components/StudentReviewCard.vue';

// Mock current Lecturer info
const dosenInfo = ref({
    nama: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
    nip: '198504122010121003',
    nidn: '0012048501',
    jabatan: 'Lektor Kepala / Dosen Informatika',
    kuotaBimbingan: '8 / 10',
});

// Mock active advisees
const advisees = ref([
    {
        name: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        statusText: 'Revisi Proposal Disetujui',
        statusVariant: 'emerald' as const,
        bidang: 'Rekayasa Perangkat Lunak',
        bimbinganCount: 9,
        turnitinScore: 14,
        roleAs: 'Pembimbing 1',
        stage: 'Pengerjaan TA',
    },
    {
        name: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        statusText: 'Siap Daftar Sidang',
        statusVariant: 'purple' as const,
        bidang: 'Artificial Intelligence',
        bimbinganCount: 12,
        turnitinScore: 11,
        roleAs: 'Pembimbing 1',
        stage: 'Siap Sidang',
    },
    {
        name: 'Bagus Pratama Hendrawan',
        nim: '11221089',
        angkatan: '2022',
        judul: 'Penerapan Internet of Things untuk Monitoring Kualitas Air Tambak Udang Berbasis LoRaWAN di Balikpapan',
        statusText: 'Menunggu ACC Bab 3',
        statusVariant: 'amber' as const,
        bidang: 'IoT & Embedded System',
        bimbinganCount: 6,
        turnitinScore: 18,
        roleAs: 'Pembimbing 2',
        stage: 'Proposal (Sempro)',
    },
    {
        name: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        judul: 'Analisis Sentimen Kebijakan IKN pada Media Sosial X Menggunakan Indobert dan Topic Modeling LDA',
        statusText: 'Revisi Naskah Bab 4-5',
        statusVariant: 'blue' as const,
        bidang: 'Natural Language Processing',
        bimbinganCount: 10,
        turnitinScore: 16,
        roleAs: 'Pembimbing 1',
        stage: 'Pengerjaan TA',
    },
]);

// Active filter for Advisees
const selectedStageFilter = ref('Semua');
const filteredAdvisees = computed(() => {
    if (selectedStageFilter.value === 'Semua') return advisees.value;
    return advisees.value.filter((a) => a.stage === selectedStageFilter.value);
});

// Pending Logbook validations
const pendingLogbooks = ref([
    {
        id: 101,
        studentName: 'Akmal Falah Maulana',
        studentNim: '11231006',
        date: '17 September 2026',
        bab: 'Bab 4 - Implementasi Arsitektur Frontend',
        topik: 'Penyelesaian Modul Penjadwalan & Integrasi Bun Runtime',
        rangkuman:
            'Telah menyelesaikan migrasi penuh runtime Bun, penyusunan komponen reusable ITK, serta pembuatan tampilan dashboard dosen dan visualisasi GA.',
        metode: 'Tatap Muka (Offline)' as const,
        status: 'Menunggu ACC' as const,
    },
    {
        id: 102,
        studentName: 'Bagus Pratama Hendrawan',
        studentNim: '11221089',
        date: '16 September 2026',
        bab: 'Bab 3 - Metodologi & Skema LoRaWAN',
        topik: 'Perancangan Skema Topologi Jaringan Sensor & Gateway',
        rangkuman:
            'Memaparkan perbandingan performa transmisi frekuensi 915 MHz dan 923 MHz untuk lingkungan pesisir Balikpapan Timur.',
        metode: 'Daring (Online / GMeet)' as const,
        status: 'Menunggu ACC' as const,
    },
    {
        id: 103,
        studentName: 'Dinda Rahmadani',
        studentNim: '11221012',
        date: '15 September 2026',
        bab: 'Bab 4 - Pelatihan Model IndoBERT',
        topik: 'Eksperimen Fine-Tuning & Pengujian F1-Score',
        rangkuman:
            'Model IndoBERT base uncased mencapai F1-score 91.4% setelah 5 epoch dengan learning rate 2e-5. Perlu masukan mengenai confusion matrix.',
        metode: 'Tatap Muka (Offline)' as const,
        status: 'Menunggu ACC' as const,
    },
]);

// Selected logbook item for modal
const selectedLogbook = ref<any>(null);
const showApprovalModal = ref(false);

const openReviewModal = (item: any) => {
    selectedLogbook.value = item;
    showApprovalModal.value = true;
};

const handleLogbookApprove = (payload: {
    id: number | string;
    notes: string;
    status: 'Disetujui' | 'Perlu Revisi';
}) => {
    pendingLogbooks.value = pendingLogbooks.value.filter(
        (l) => l.id !== payload.id,
    );
};

// Scheduled Examination Sessions for this Lecturer
const upcomingExaminations = ref([
    {
        id: 'EXAM-01',
        type: 'Sempro' as const,
        date: 'Kamis, 24 September 2026',
        time: '09:00 - 10:30 WITA',
        room: 'Ruang Sidang FSTI A (Gd. Kuliah Terpadu 304)',
        studentName: 'Akmal Falah Maulana',
        studentNim: '11231006',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        pembimbing: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom. (Anda)',
            'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        ],
        penguji: [
            'Prof. Dr. Agus Tri Haryanto, M.T. (Ketua)',
            'Sri Wahyuni, S.Kom., M.T.',
        ],
        hasConflict: false,
    },
    {
        id: 'EXAM-02',
        type: 'Sidang' as const,
        date: 'Jumat, 25 September 2026',
        time: '14:00 - 15:30 WITA',
        room: 'Ruang Sidang FSTI B / Hybrid Google Meet',
        studentName: 'Siti Nurhaliza Putri',
        studentNim: '11211045',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Arsitektur Vision Transformer pada Citra Fundus',
        pembimbing: [
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom. (Anda)',
            'Dewi Ratnasari, S.T., M.T.',
        ],
        penguji: [
            'Dr. Muhammad Rizal, M.Kom. (Ketua)',
            'Hendra Pratama, S.Kom., M.Sc.',
        ],
        hasConflict: false,
    },
]);
</script>

<template>
    <Head title="Dashboard Dosen • SIPTA IF" />

    <div class="space-y-6">
        <!-- 1. Header Box Profil Dosen -->
        <PageHeaderBox
            title="Portal Evaluasi & Pembimbing Tugas Akhir"
            subtitle="Selamat datang di sistem monitoring bimbingan dan pengujian tugas akhir Program Studi Informatika ITK."
        >
            <template #action>
                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <p
                            class="text-xs font-bold text-slate-900 dark:text-white"
                        >
                            {{ dosenInfo.nama }}
                        </p>
                        <p
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            NIP: {{ dosenInfo.nip }} •
                            {{ dosenInfo.jabatan }}
                        </p>
                    </div>
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-blue-700 to-indigo-600 text-sm font-bold text-white shadow-md shadow-blue-500/20"
                    >
                        TW
                    </div>
                </div>
            </template>
        </PageHeaderBox>

        <!-- 2. Grid Statistik Ringkas Dosen -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Bimbingan Aktif"
                value="8"
                subtitle="Kapasitas kuota: 8 / 10 Mahasiswa"
                :icon="GraduationCap"
                icon-color="blue"
            />
            <StatCard
                title="Antrean Logbook"
                :value="pendingLogbooks.length"
                subtitle="Menunggu validasi Form TA-04"
                :icon="FileCheck"
                icon-color="amber"
            />
            <StatCard
                title="Jadwal Uji Sempro"
                value="4"
                subtitle="Sesi periode Gasal 2026/2027"
                :icon="FileSpreadsheet"
                icon-color="indigo"
            />
            <StatCard
                title="Jadwal Sidang Akhir"
                value="2"
                subtitle="Sesi ujian skripsi pekan ini"
                :icon="Award"
                icon-color="emerald"
            />
        </div>

        <!-- 3. Section: Antrean Validasi Logbook Mahasiswa (Form TA-04) -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Antrean Validasi Logbook Bimbingan (Form TA-04)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Mahasiswa asuhan Anda membutuhkan paraf/e-TTD validasi
                        untuk memenuhi syarat minimal 8x bimbingan.
                    </p>
                </div>
                <span
                    class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:bg-amber-950/40 dark:text-amber-400"
                >
                    {{ pendingLogbooks.length }} Perlu Ditinjau
                </span>
            </div>

            <div
                v-if="pendingLogbooks.length === 0"
                class="rounded-2xl border border-dashed border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <CheckCircle2 class="mx-auto h-10 w-10 text-emerald-500" />
                <p
                    class="mt-2 text-sm font-bold text-slate-800 dark:text-slate-200"
                >
                    Seluruh Logbook Telah Tervalidasi
                </p>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Tidak ada antrean logbook bimbingan mahasiswa yang menunggu
                    persetujuan Anda saat ini.
                </p>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div
                    v-for="item in pendingLogbooks"
                    :key="item.id"
                    class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-blue-300 dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <div>
                        <!-- Header Item -->
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h4
                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                >
                                    {{ item.studentName }}
                                </h4>
                                <p
                                    class="text-[11px] text-slate-500 dark:text-slate-400"
                                >
                                    NIM: {{ item.studentNim }}
                                </p>
                            </div>
                            <span
                                class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950/50 dark:text-blue-400"
                            >
                                {{ item.date }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div
                            class="mt-3 rounded-xl bg-slate-50/80 p-3 dark:bg-slate-800/40"
                        >
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-400 uppercase"
                            >
                                {{ item.bab }}
                            </p>
                            <p
                                class="mt-0.5 text-xs font-semibold text-slate-800 dark:text-slate-200"
                            >
                                {{ item.topik }}
                            </p>
                            <p
                                class="mt-1.5 line-clamp-2 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{ item.rangkuman }}
                            </p>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div
                        class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[11px] text-slate-500 dark:text-slate-400"
                        >
                            {{ item.metode }}
                        </span>
                        <Button
                            size="sm"
                            variant="primary"
                            @click="openReviewModal(item)"
                        >
                            <FileCheck class="mr-1 h-3.5 w-3.5" />
                            <span>Validasi Logbook</span>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Section: Jadwal Pengujian Sempro & Sidang Skripsi -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Jadwal Pengujian Seminar & Sidang (Waktu WITA)
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Sesi ujian yang telah dijadwalkan oleh Koordinator
                        Prodi. Klik "Beri Nilai" untuk membuka lembar penilaian
                        resmi.
                    </p>
                </div>
                <Link
                    href="/dosen/penilaian"
                    class="text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                >
                    Buka Lembar Penilaian →
                </Link>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <ScheduleSlotCard
                    v-for="exam in upcomingExaminations"
                    :key="exam.id"
                    :id="exam.id"
                    :type="exam.type"
                    :date="exam.date"
                    :time="exam.time"
                    :room="exam.room"
                    :student-name="exam.studentName"
                    :student-nim="exam.studentNim"
                    :judul="exam.judul"
                    :pembimbing="exam.pembimbing"
                    :penguji="exam.penguji"
                    action-text="Input Nilai & Revisi"
                    :action-href="'/dosen/penilaian?exam_id=' + exam.id"
                    is-dosen-view
                />
            </div>
        </div>

        <!-- 5. Section: Mahasiswa Bimbingan Aktif -->
        <div class="space-y-4">
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h3
                        class="text-base font-bold text-slate-900 dark:text-white"
                    >
                        Daftar Mahasiswa Asuhan Aktif
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Pantau kemajuan bab, frekuensi bimbingan, serta kemajuan
                        Turnitin mahasiswa bimbingan Anda.
                    </p>
                </div>

                <!-- Filter Tabs -->
                <div
                    class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white p-1 text-xs font-semibold dark:border-slate-800 dark:bg-[#0E1626]"
                >
                    <button
                        v-for="tab in [
                            'Semua',
                            'Proposal (Sempro)',
                            'Pengerjaan TA',
                            'Siap Sidang',
                        ]"
                        :key="tab"
                        type="button"
                        class="rounded-lg px-3 py-1.5 transition-colors"
                        :class="[
                            selectedStageFilter === tab
                                ? 'bg-blue-600 text-white shadow-xs'
                                : 'text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800',
                        ]"
                        @click="selectedStageFilter = tab"
                    >
                        {{ tab }}
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <StudentReviewCard
                    v-for="(stu, idx) in filteredAdvisees"
                    :key="idx"
                    :name="stu.name"
                    :nim="stu.nim"
                    :angkatan="stu.angkatan"
                    :judul="stu.judul"
                    :status-text="stu.statusText"
                    :status-variant="stu.statusVariant"
                    :bidang="stu.bidang"
                    :bimbingan-count="stu.bimbinganCount"
                    :turnitin-score="stu.turnitinScore"
                    :role-as="stu.roleAs"
                    action-text="Detail Bimbingan"
                    action-href="/pendaftaran/bimbingan"
                />
            </div>
        </div>
    </div>

    <!-- Reusable Modal: Validasi Logbook -->
    <LogbookApprovalModal
        :show="showApprovalModal"
        :item="selectedLogbook"
        @close="showApprovalModal = false"
        @approve="handleLogbookApprove"
    />
</template>
