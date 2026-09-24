<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Check,
    CheckCircle2,
    Clock,
    FileCheck,
    FileText,
    Filter,
    HelpCircle,
    PenTool,
    Search,
    ShieldCheck,
    Users,
    X,
} from 'lucide-vue-next';

import AppLayout from '@/Layouts/AppLayout.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import Modal from '@/Components/Modal.vue';
import PageHeaderBox from '@/Components/PageHeaderBox.vue';
import StatusBadge from '@/Components/StatusBadge.vue';

const activeSection = ref<'judul' | 'sempro' | 'sidang'>('judul');
const academicSearch = ref('');
const academicAngkatan = ref('Semua');
const selectedAcademicSubmission = ref<AcademicSubmission | null>(null);
const showAcademicModal = ref(false);
const academicNote = ref('');
const academicDecision = ref<'Sempro' | 'Sidang TA'>('Sempro');

// ponytail: mock state resets on reload; connect to the approval API when backend contracts exist.

// Submissions list
const submissions = ref([
    {
        id: 1,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        angkatan: '2022',
        bidang: 'Computer Vision & AI',
        judul: 'Implementasi Algoritma YOLOv8 untuk Deteksi Kerusakan Aspal Jalan Raya pada Citra Drone',
        deskripsi:
            'Penelitian ini berfokus pada inspeksi otomatis kualitas jalan di area penyangga IKN menggunakan wahana drone nirawak dan model YOLOv8 untuk segmentasi retak buaya dan lubang jalan.',
        pembimbing1Usulan: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        pembimbing2Usulan: 'Sri Wahyuni, S.Kom., M.T.',
        pembimbing1Final: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        pembimbing2Final: 'Sri Wahyuni, S.Kom., M.T.',
        tanggal: '17 September 2026',
        status: 'pending',
    },
    {
        id: 2,
        nama: 'Nabila Zahra Syahrani',
        nim: '11221056',
        angkatan: '2022',
        bidang: 'Rekayasa Perangkat Lunak',
        judul: 'Rancang Bangun Microservices Architecture Berbasis Golang dan RabbitMQ untuk Sistem Presensi Kampus',
        deskripsi:
            'Mengembangkan sistem backend presensi berskala besar berbasis event-driven architecture untuk menangani lonjakan konkurensi mahasiswa hingga 10.000 request per menit.',
        pembimbing1Usulan: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2Usulan: 'Andi Kurniawan, M.Kom.',
        pembimbing1Final: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2Final: 'Andi Kurniawan, M.Kom.',
        tanggal: '18 September 2026',
        status: 'pending',
    },
    {
        id: 3,
        nama: 'Akmal Falah Maulana',
        nim: '11231006',
        angkatan: '2023',
        bidang: 'Rekayasa Perangkat Lunak',
        judul: 'Pengembangan Portal Tugas Akhir Informatika ITK Berbasis Inertia Vue 3 & Optimasi Penjadwalan Algoritma Genetika',
        deskripsi:
            'Portal terintegrasi dengan algoritma genetika untuk mengoptimalkan alokasi slot waktu dan ruangan seminar.',
        pembimbing1Usulan: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2Usulan: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        pembimbing1Final: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        pembimbing2Final: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        tanggal: '05 September 2026',
        status: 'approved',
    },
]);

type AcademicSubmission = {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    judul: string;
    status: string;
    pembimbing: string;
    penguji: string;
    jadwal: string;
    files: string[];
    catatan?: string;
    requirements?: { label: string; ready: boolean }[];
};

const semproSubmissions = ref<AcademicSubmission[]>([
    {
        id: 1,
        nama: 'Anisa Rahmadani',
        nim: '11231010',
        angkatan: '2023',
        judul: 'Analisis Perbandingan Kinerja dan Generalisasi Model Computer Vision pada Edge Device',
        status: 'Menunggu Validasi',
        pembimbing: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        penguji:
            'Sri Wahyuni, S.Kom., M.T. · Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        jadwal: 'Selasa, 29 Sep 2026 · 09.00 WITA',
        files: [
            'Proposal Bab 1–3.pdf',
            'Lembar Persetujuan.pdf',
            'Turnitin 14%.pdf',
            'Logbook TA-04.pdf',
        ],
    },
    {
        id: 2,
        nama: 'Bayu Aditya Saputra',
        nim: '11231089',
        angkatan: '2023',
        judul: 'Rancang Bangun Sistem Lokalisasi Indoor Menggunakan WiFi Fingerprinting',
        status: 'Perlu Revisi',
        pembimbing: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        penguji:
            'Sri Wahyuni, S.Kom., M.T. · Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        jadwal: 'Menunggu verifikasi berkas',
        files: [
            'Proposal WiFi Fingerprint.pdf',
            'Lembar Persetujuan.pdf',
            'Turnitin 19%.pdf',
        ],
    },
    {
        id: 3,
        nama: 'Ahmad Fauzan Pratama',
        nim: '11231065',
        angkatan: '2023',
        judul: 'Analisis Ketahanan Model Countermeasure Terhadap Serangan Adversarial pada Citra Medis',
        status: 'Disetujui Akademik',
        pembimbing: 'Sri Wahyuni, S.Kom., M.T.',
        penguji:
            'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom. · Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        jadwal: 'Rabu, 30 Sep 2026 · 13.30 WITA',
        files: [
            'Proposal Citra Medis.pdf',
            'Turnitin 12%.pdf',
            'Logbook TA-04.pdf',
        ],
    },
]);

const sidangSubmissions = ref([
    {
        id: 1,
        nama: 'Siti Nurhaliza Putri',
        nim: '11211045',
        angkatan: '2021',
        judul: 'Sistem Deteksi Retinopati Diabetik Menggunakan Vision Transformer pada Citra Fundus',
        status: 'Menunggu Validasi',
        pembimbing: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
        penguji: 'Dr. Muhammad Rizal, M.Kom. · Hendra Pratama, S.Kom., M.Sc.',
        jadwal: 'Menunggu hasil penjadwalan',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: true },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: [
            'Naskah TA Final.pdf',
            'Lembar Persetujuan Sidang.pdf',
            'Turnitin 11%.pdf',
        ],
    },
    {
        id: 2,
        nama: 'Dinda Rahmadani',
        nim: '11221012',
        angkatan: '2022',
        judul: 'Analisis Sentimen Kebijakan IKN Menggunakan IndoBERT dan Topic Modeling',
        status: 'Perlu Revisi',
        pembimbing: 'Sri Wahyuni, S.Kom., M.T.',
        penguji: 'Belum ditetapkan',
        jadwal: 'Belum tersedia',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: false },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: ['Naskah TA.pdf', 'Turnitin 16%.pdf'],
        catatan: 'Lengkapi bukti minimal 12 kali bimbingan.',
    },
    {
        id: 3,
        nama: 'Rizky Pratama Adhitya',
        nim: '11221034',
        angkatan: '2022',
        judul: 'Deteksi Kerusakan Aspal Jalan Raya pada Citra Drone Menggunakan YOLOv8',
        status: 'Disetujui Akademik',
        pembimbing: 'Gusti Ahmad Fanshuri, S.Kom., M.Cs.',
        penguji:
            'Prof. Dr. Agus Tri Haryanto, M.T. · Sri Wahyuni, S.Kom., M.T.',
        jadwal: 'Kamis, 1 Okt 2026 · 09.00 WITA',
        requirements: [
            { label: 'Persetujuan kedua pembimbing', ready: true },
            { label: 'Minimal 12 kali bimbingan', ready: true },
            { label: 'Turnitin maksimal 20%', ready: true },
            { label: 'Bebas tanggungan laboratorium', ready: true },
        ],
        files: [
            'Naskah TA Final.pdf',
            'Lembar Persetujuan Sidang.pdf',
            'Turnitin 9%.pdf',
        ],
    },
]);

const activeAcademicSubmissions = computed(() =>
    activeSection.value === 'sidang'
        ? sidangSubmissions.value
        : semproSubmissions.value,
);

const filteredAcademicSubmissions = computed(() => {
    const query = academicSearch.value.toLowerCase().trim();
    return activeAcademicSubmissions.value.filter((item) => {
        const matchesAngkatan =
            academicAngkatan.value === 'Semua' ||
            item.angkatan === academicAngkatan.value;
        const matchesQuery =
            !query ||
            [item.nama, item.nim, item.judul].some((value) =>
                value.toLowerCase().includes(query),
            );
        return matchesAngkatan && matchesQuery;
    });
});

const openAcademicDetail = (item: AcademicSubmission) => {
    selectedAcademicSubmission.value = item;
    academicNote.value = item.catatan || '';
    academicDecision.value =
        activeSection.value === 'sidang' ? 'Sidang TA' : 'Sempro';
    showAcademicModal.value = true;
};

const updateAcademicStatus = (
    status: 'Disetujui Akademik' | 'Perlu Revisi',
) => {
    if (
        !selectedAcademicSubmission.value ||
        (status === 'Perlu Revisi' && !academicNote.value.trim()) ||
        (status === 'Disetujui Akademik' &&
            selectedAcademicSubmission.value.requirements?.some(
                (req) => !req.ready,
            ))
    )
        return;
    selectedAcademicSubmission.value.status = status;
    selectedAcademicSubmission.value.catatan = academicNote.value.trim();
    showAcademicModal.value = false;
};

// Modal state
const showModal = ref(false);
const activeItem = ref<any>(null);
const selectedP1 = ref('');
const selectedP2 = ref('');
const skNumber = ref('042/ITK/FSTI/IF/TA/2026');

const openApprovalModal = (item: any) => {
    activeItem.value = item;
    selectedP1.value = item.pembimbing1Final || item.pembimbing1Usulan;
    selectedP2.value = item.pembimbing2Final || item.pembimbing2Usulan;
    showModal.value = true;
};

const confirmApproval = () => {
    if (activeItem.value) {
        activeItem.value.status = 'approved';
        activeItem.value.pembimbing1Final = selectedP1.value;
        activeItem.value.pembimbing2Final = selectedP2.value;
    }
    showModal.value = false;
};
</script>

<template>
    <AppLayout title="Persetujuan Akademik TA">
        <Head title="Persetujuan Akademik • SIPTA IF" />

        <div class="space-y-6">
            <PageHeaderBox
                badge="Wewenang Ketua Program Studi"
                title="Persetujuan Akademik Tugas Akhir"
                description="Tinjau judul, Seminar Proposal, dan Sidang TA. Verifikasi administrasi tetap menjadi kewenangan Tendik."
            />

            <div
                class="flex w-fit gap-1 rounded-xl bg-slate-100 p-1 dark:bg-slate-800"
            >
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                    :class="
                        activeSection === 'judul'
                            ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-900 dark:text-blue-300'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                    @click="activeSection = 'judul'"
                >
                    Persetujuan Judul
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                    :class="
                        activeSection === 'sempro'
                            ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-900 dark:text-blue-300'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                    @click="activeSection = 'sempro'"
                >
                    Pengajuan Sempro
                </button>
                <button
                    type="button"
                    class="rounded-lg px-4 py-2 text-xs font-bold transition-colors"
                    :class="
                        activeSection === 'sidang'
                            ? 'bg-white text-blue-700 shadow-sm dark:bg-slate-900 dark:text-blue-300'
                            : 'text-slate-500 dark:text-slate-400'
                    "
                    @click="activeSection = 'sidang'"
                >
                    Pengajuan Sidang TA
                </button>
            </div>

            <!-- Submissions Table -->
            <Card v-if="activeSection === 'judul'" class="p-6">
                <div
                    class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 sm:flex-row sm:items-center dark:border-slate-800"
                >
                    <div>
                        <h3
                            class="text-sm font-extrabold text-slate-900 dark:text-white"
                        >
                            Daftar Pengajuan Judul Mahasiswa
                        </h3>
                        <p class="text-xs text-slate-500">
                            Persetujuan judul merupakan syarat utama sebelum
                            mahasiswa memulai konsultasi bimbingan TA-04
                        </p>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table
                        class="w-full text-left text-xs text-slate-600 dark:text-slate-300"
                    >
                        <thead
                            class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                        >
                            <tr>
                                <th class="px-4 py-3">Mahasiswa</th>
                                <th class="px-4 py-3">
                                    Topik & Judul Tugas Akhir
                                </th>
                                <th class="px-4 py-3">Bidang Keahlian</th>
                                <th class="px-4 py-3">Dosen Pembimbing</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-100 dark:divide-slate-800"
                        >
                            <tr
                                v-for="item in submissions"
                                :key="item.id"
                                class="hover:bg-slate-50/50 dark:hover:bg-slate-900/40"
                            >
                                <td class="px-4 py-3.5">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.nama }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ item.nim }} • Angkatan
                                        {{ item.angkatan }}
                                    </p>
                                </td>
                                <td class="max-w-xs px-4 py-3.5">
                                    <p
                                        class="line-clamp-2 font-semibold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ item.judul }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                    >
                                        {{ item.bidang }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-[11px]">
                                    <p>
                                        <span
                                            class="font-semibold text-slate-500"
                                            >P1:</span
                                        >
                                        {{ item.pembimbing1Final }}
                                    </p>
                                    <p>
                                        <span
                                            class="font-semibold text-slate-500"
                                            >P2:</span
                                        >
                                        {{ item.pembimbing2Final }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span
                                        class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                        :class="[
                                            item.status === 'approved'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
                                        ]"
                                    >
                                        {{
                                            item.status === 'approved'
                                                ? 'Disetujui (SK Terbit)'
                                                : 'Menunggu Validasi'
                                        }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openApprovalModal(item)"
                                    >
                                        {{
                                            item.status === 'approved'
                                                ? 'Detail SK'
                                                : 'Review & ACC'
                                        }}
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </Card>
            <Card v-else class="space-y-4 p-5">
                <p class="text-xs text-slate-500">
                    Simulasi frontend. Keputusan akademik tidak mengubah
                    verifikasi administrasi Tendik dan direset saat memuat
                    ulang.
                </p>
                <div class="flex flex-wrap gap-3">
                    <input
                        v-model="academicSearch"
                        :aria-label="
                            'Cari pengajuan ' +
                            (activeSection === 'sidang'
                                ? 'Sidang TA'
                                : 'Sempro')
                        "
                        placeholder="Cari nama, NIM, atau judul"
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                    />
                    <select
                        v-model="academicAngkatan"
                        aria-label="Filter angkatan"
                        class="rounded-xl border border-slate-300 p-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                    >
                        <option>Semua</option>
                        <option>2023</option>
                        <option>2022</option>
                        <option>2021</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead
                            class="bg-slate-50 text-[11px] font-bold tracking-wider text-slate-700 uppercase dark:bg-slate-900 dark:text-slate-400"
                        >
                            <tr>
                                <th class="p-3">Mahasiswa</th>
                                <th class="p-3">Judul</th>
                                <th class="p-3">Pembimbing</th>
                                <th
                                    v-if="activeSection === 'sidang'"
                                    class="p-3"
                                >
                                    Kelayakan
                                </th>
                                <th class="p-3">Keputusan akademik</th>
                                <th class="p-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="item in filteredAcademicSubmissions"
                                :key="item.id"
                                class="border-t border-slate-200 dark:border-slate-800"
                            >
                                <td class="p-3">
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ item.nama }}
                                    </p>
                                    <p class="text-[10px] text-slate-500">
                                        NIM: {{ item.nim }} · Angkatan
                                        {{ item.angkatan }}
                                    </p>
                                </td>
                                <td class="max-w-sm p-3">
                                    <p
                                        class="line-clamp-2 text-slate-800 dark:text-slate-200"
                                    >
                                        {{ item.judul }}
                                    </p>
                                </td>
                                <td class="p-3 text-[11px]">
                                    {{ item.pembimbing }}
                                </td>
                                <td
                                    v-if="activeSection === 'sidang'"
                                    class="p-3"
                                >
                                    <div
                                        v-if="'requirements' in item"
                                        class="space-y-0.5"
                                    >
                                        <div
                                            v-for="req in item.requirements"
                                            :key="req.label"
                                            class="flex items-center gap-1 text-[10px]"
                                        >
                                            <CheckCircle2
                                                v-if="req.ready"
                                                class="h-3 w-3 text-emerald-500"
                                            />
                                            <X
                                                v-else
                                                class="h-3 w-3 text-red-400"
                                            />
                                            <span
                                                :class="
                                                    req.ready
                                                        ? 'text-slate-600 dark:text-slate-400'
                                                        : 'font-semibold text-red-600 dark:text-red-400'
                                                "
                                                >{{ req.label }}</span
                                            >
                                        </div>
                                    </div>
                                </td>
                                <td class="p-3">
                                    <span
                                        class="rounded-md px-2 py-0.5 text-[10px] font-bold"
                                        :class="[
                                            item.status === 'Disetujui Akademik'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : item.status === 'Perlu Revisi'
                                                  ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'
                                                  : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
                                        ]"
                                    >
                                        {{ item.status }}
                                    </span>
                                </td>
                                <td class="p-3">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        @click="openAcademicDetail(item)"
                                        >Tinjau</Button
                                    >
                                </td>
                            </tr>
                            <tr v-if="!filteredAcademicSubmissions.length">
                                <td
                                    :colspan="
                                        activeSection === 'sidang' ? 6 : 5
                                    "
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

        <Modal
            :show="showAcademicModal"
            max-width="lg"
            @close="showAcademicModal = false"
        >
            <div
                v-if="selectedAcademicSubmission"
                class="space-y-4 p-6 text-sm"
            >
                <h3 class="font-bold">
                    Tinjauan Akademik {{ academicDecision }}
                </h3>
                <p>
                    {{ selectedAcademicSubmission.nama }} ·
                    {{ selectedAcademicSubmission.nim }}
                </p>
                <p>{{ selectedAcademicSubmission.judul }}</p>
                <p class="text-xs text-amber-600">
                    Simulasi keputusan akademik; berkas administrasi tetap
                    menjadi kewenangan Tendik.
                </p>
                <p>Pembimbing: {{ selectedAcademicSubmission.pembimbing }}</p>
                <p>
                    Penguji usulan (mock):
                    {{ selectedAcademicSubmission.penguji }}
                </p>
                <p>Jadwal usulan: {{ selectedAcademicSubmission.jadwal }}</p>
                <ul class="space-y-1 text-xs text-slate-500">
                    <li
                        v-for="file in selectedAcademicSubmission.files"
                        :key="file"
                    >
                        {{ file }} — contoh berkas, belum tersedia untuk diunduh
                    </li>
                </ul>
                <label class="block" for="sempro-note"
                    >Catatan akademik / revisi</label
                >
                <textarea
                    id="sempro-note"
                    v-model="academicNote"
                    class="w-full rounded-xl border border-slate-300 p-3 dark:border-slate-700 dark:bg-slate-900"
                />
                <div class="flex flex-wrap justify-end gap-2">
                    <Button
                        variant="secondary"
                        @click="showAcademicModal = false"
                        >Tutup</Button
                    >
                    <Button
                        variant="outline"
                        :disabled="!academicNote.trim()"
                        @click="updateAcademicStatus('Perlu Revisi')"
                        >Minta Revisi</Button
                    >
                    <Button
                        :disabled="
                            selectedAcademicSubmission.requirements?.some(
                                (req) => !req.ready,
                            )
                        "
                        @click="updateAcademicStatus('Disetujui Akademik')"
                        >Setujui Akademik</Button
                    >
                </div>
            </div>
        </Modal>

        <!-- Approval & SK Assignment Modal -->
        <Modal :show="showModal" max-width="lg" @close="showModal = false">
            <div class="p-6">
                <div
                    class="flex items-center justify-between border-b border-slate-200 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-white"
                    >
                        Penetapan Dosen Pembimbing & Penerbitan SK
                    </h3>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="showModal = false"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div v-if="activeItem" class="mt-4 space-y-4 text-xs">
                    <div>
                        <span
                            class="font-bold text-slate-700 dark:text-slate-300"
                            >Mahasiswa Pengusul:</span
                        >
                        <p class="font-semibold text-slate-900 dark:text-white">
                            {{ activeItem.nama }} ({{ activeItem.nim }})
                        </p>
                    </div>

                    <div>
                        <span
                            class="font-bold text-slate-700 dark:text-slate-300"
                            >Judul Tugas Akhir:</span
                        >
                        <p
                            class="mt-1 rounded-xl bg-slate-50 p-3 leading-relaxed text-slate-700 dark:bg-slate-900 dark:text-slate-300"
                        >
                            {{ activeItem.judul }}
                        </p>
                    </div>

                    <!-- P1 Selection -->
                    <div>
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Tetapkan Dosen Pembimbing 1 (Utama)
                        </label>
                        <input
                            v-model="selectedP1"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- P2 Selection -->
                    <div>
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Tetapkan Dosen Pembimbing 2 (Pendamping)
                        </label>
                        <input
                            v-model="selectedP2"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>

                    <!-- Nomor SK -->
                    <div>
                        <label
                            class="block font-bold text-slate-700 dark:text-slate-300"
                        >
                            Nomor Surat Keputusan (SK) Kaprodi
                        </label>
                        <input
                            v-model="skNumber"
                            type="text"
                            class="mt-1 w-full rounded-xl border border-slate-300 p-2.5 text-xs text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button variant="secondary" @click="showModal = false">
                        Tutup
                    </Button>
                    <Button variant="primary" @click="confirmApproval">
                        Setujui & Terbitkan SK
                    </Button>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
