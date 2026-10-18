<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import {
    User as UserIcon,
    Mail,
    GraduationCap,
    Search,
    Filter,
    Calendar,
    FileText,
    Download,
    Check,
    X,
    Users,
    Clock,
    FileCheck,
    Edit3,
    CalendarClock,
    CheckCircle2,
} from 'lucide-vue-next';

interface SemproFile {
    name: string;
    url?: string;
}

export interface SemproStudentItem {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    roleTab: 'bimbingan' | 'uji';
    judul: string;
    statusBerkas: string;
    persetujuan: string;
    bentukTa: string;
    nilai: string | null;
    revisi: string | null;
    penguji1: string;
    penguji1MatchScore: number;
    penguji2: string;
    hari: string;
    tanggal: string;
    mulai: string;
    selesai: string;
    files: SemproFile[];
}

const props = defineProps<{
    dbSempro?: any[];
}>();

const emit = defineEmits<{
    (e: 'back-to-judul'): void;
}>();

const page = usePage();
const authUser = computed(() => (page.props.auth as any)?.user ?? null);

const currentUserName = computed(
    () => authUser.value?.name ?? 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
);
const currentUserNip = computed(
    () =>
        authUser.value?.nim_nip?.replace(/^NIP:\s*/i, '') ??
        '198804102019032020',
);
const currentUserEmail = computed(
    () => authUser.value?.email ?? 'nisafadhliana@lecturer.itk.ac.id',
);

const userRole = computed(() => authUser.value?.role || 'kaprodi');

// Koorprodi permission check
const isKoorprodi = computed(() => {
    const role = userRole.value;
    const jabatan = (authUser.value?.jabatan || '').toLowerCase();
    if (role === 'dosen') {
        return jabatan.includes('koordinator') || jabatan.includes('kaprodi');
    }
    return true; // kaprodi / koordinator / default
});

// Dosen permission check (Koorprodi is ALSO an active Dosen)
const isDosen = computed(() => true);

// Active Tab: Mahasiswa Bimbingan vs Mahasiswa Uji
const activeTab = ref<'bimbingan' | 'uji'>('bimbingan');

// Filter & Search
const searchQuery = ref('');
const filterAngkatan = ref('all');

// Toast notification state
const toast = ref<{ show: boolean; message: string; isError: boolean }>({
    show: false,
    message: '',
    isError: false,
});
let toastTimeout: any = null;
const showToast = (message: string, isError = false) => {
    toast.value = { show: true, message, isError };
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// 6 Realistic demo items matching Front-End pengajuan-sempro.html mock data
const defaultDemoList: SemproStudentItem[] = [
    {
        id: 1,
        nama: 'Anisa Rahmadani',
        nim: '11231010',
        angkatan: '2023',
        roleTab: 'bimbingan',
        judul: 'Analisis Perbandingan Kinerja dan Generalisasi Model Computer Vision pada Edge Device',
        statusBerkas: 'Berkas Valid',
        persetujuan: 'Disetujui Pembimbing',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Tidak Ada',
        penguji1: 'Dian Permata Sari, S.Kom., M.Kom.',
        penguji1MatchScore: 92,
        penguji2: 'Dr. Budi Wicaksono, M.Kom.',
        hari: 'Selasa',
        tanggal: '29 Sep 2026',
        mulai: '09:00',
        selesai: '10:30',
        files: [
            { name: 'Draf_Proposal_Skripsi_Bab1_3.pdf' },
            { name: 'Lembar_Persetujuan_Seminar.pdf' },
            { name: 'Bukti_Cek_Plagiasi_Turnitin_14%.pdf' },
            { name: 'Kartu_Bimbingan_8x_Acc.pdf' },
        ],
    },
    {
        id: 2,
        nama: 'Bayu Aditya Saputra',
        nim: '11231089',
        angkatan: '2023',
        roleTab: 'bimbingan',
        judul: 'RANCANG BANGUN SISTEM LOKALISASI INDOOR MENGGUNAKAN WIFI FINGERPRINTING',
        statusBerkas: 'Menunggu Validasi',
        persetujuan: 'Disetujui Pembimbing',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Bab 3 Alur Kalibrasi',
        penguji1: 'Rian Kurniawan, S.Kom., M.Eng.',
        penguji1MatchScore: 94,
        penguji2: 'Ahmad Faisal Ridwan, Ph.D.',
        hari: 'Rabu',
        tanggal: '30 Sep 2026',
        mulai: '10:45',
        selesai: '12:15',
        files: [
            { name: 'Draf_Proposal_WiFi_Fingerprint.pdf' },
            { name: 'Lembar_Persetujuan_Dosen.pdf' },
            { name: 'Turnitin_Check_19%.pdf' },
        ],
    },
    {
        id: 3,
        nama: 'Nadia Putri Lestari',
        nim: '11231033',
        angkatan: '2023',
        roleTab: 'bimbingan',
        judul: 'Sistem Deteksi Anomali Jaringan IoT Berdasarkan Pola Traffic Menggunakan Isolation Forest',
        statusBerkas: 'Menunggu Validasi',
        persetujuan: 'Draf Pengajuan',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Transkrip Nilai Kurang',
        penguji1: 'Dr. Budi Wicaksono, M.Kom.',
        penguji1MatchScore: 95,
        penguji2: 'Dian Permata Sari, S.Kom., M.Kom.',
        hari: 'Kamis',
        tanggal: '1 Okt 2026',
        mulai: '09:00',
        selesai: '10:30',
        files: [
            { name: 'Proposal_Deteksi_Anomali_IoT.pdf' },
            { name: 'Formulir_Bimbingan_Terbaru.pdf' },
        ],
    },
    {
        id: 4,
        nama: 'Ahmad Fauzan Pratama',
        nim: '11231065',
        angkatan: '2023',
        roleTab: 'uji',
        judul: 'Analisis Ketahanan Model Countermeasure Terhadap Serangan Adversarial pada Citra Medis',
        statusBerkas: 'Berkas Valid',
        persetujuan: 'Disetujui Pembimbing',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Tidak Ada',
        penguji1: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        penguji1MatchScore: 96,
        penguji2: 'Rian Kurniawan, S.Kom., M.Eng.',
        hari: 'Selasa',
        tanggal: '29 Sep 2026',
        mulai: '13:30',
        selesai: '15:00',
        files: [
            { name: 'Adversarial_Medical_Image_Proposal.pdf' },
            { name: 'Turnitin_Similarity_12%.pdf' },
            { name: 'Kartu_Konsultasi_Lengkap.pdf' },
        ],
    },
    {
        id: 5,
        nama: 'Siti Rahmawati',
        nim: '11221068',
        angkatan: '2022',
        roleTab: 'uji',
        judul: 'Peramalan Kuantitas Penjualan Harian Produk Retail Menggunakan Hybrid ARIMA-LSTM',
        statusBerkas: 'Berkas Valid',
        persetujuan: 'Disetujui Pembimbing',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Tidak Ada',
        penguji1: 'Dr. Budi Wicaksono, M.Kom.',
        penguji1MatchScore: 98,
        penguji2: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        hari: 'Senin',
        tanggal: '28 Sep 2026',
        mulai: '10:45',
        selesai: '12:15',
        files: [
            { name: 'Proposal_ARIMA_LSTM_Retail.pdf' },
            { name: 'Turnitin_Report_21%.pdf' },
        ],
    },
    {
        id: 6,
        nama: 'MUHAMMAD RAIHAN',
        nim: '11211030',
        angkatan: '2021',
        roleTab: 'uji',
        judul: 'ANALISIS KERENTANAN APLIKASI WEB PORTAL KAMPUS DENGAN METODE OWASP TOP 10',
        statusBerkas: 'Berkas Valid',
        persetujuan: 'Disetujui Pembimbing',
        bentukTa: 'Reguler',
        nilai: null,
        revisi: 'Tidak Ada',
        penguji1: 'Ahmad Faisal Ridwan, Ph.D.',
        penguji1MatchScore: 95,
        penguji2: 'Rizki Pratama Nugraha, M.Han.',
        hari: 'Senin',
        tanggal: '28 Sep 2026',
        mulai: '09:00',
        selesai: '10:30',
        files: [
            { name: 'OWASP_Campus_Security_Proposal.pdf' },
            { name: 'Bukti_Turnitin_15%.pdf' },
        ],
    },
];

const studentsList = ref<SemproStudentItem[]>(defaultDemoList);

// Integrate real backend data if present
watch(
    () => props.dbSempro,
    (newVal) => {
        if (newVal && newVal.length > 0) {
            const mapped: SemproStudentItem[] = newVal.map((item: any, idx: number) => {
                const angk =
                    item.mahasiswa?.angkatan ||
                    (item.mahasiswa?.nim_nip ? '20' + String(item.mahasiswa.nim_nip).substring(2, 4) : '2023');
                return {
                    id: item.id || idx + 100,
                    nama: item.mahasiswa?.name || 'Mahasiswa ' + (idx + 1),
                    nim: item.mahasiswa?.nim_nip || '—',
                    angkatan: String(angk),
                    roleTab: idx % 2 === 0 ? 'bimbingan' : 'uji',
                    judul: item.judul_ta || item.judul || 'Judul Seminar Proposal',
                    statusBerkas: item.status_akademik === 'disetujui' ? 'Berkas Valid' : (item.status_akademik === 'revisi' ? 'Perlu Revisi' : 'Menunggu Validasi'),
                    persetujuan: item.status === 'disetujui' ? 'Disetujui Pembimbing' : 'Draf Pengajuan',
                    bentukTa: item.bentuk_ta || 'Reguler',
                    nilai: item.nilai || null,
                    revisi: item.catatan_kaprodi || item.catatan || 'Tidak Ada',
                    penguji1: item.penguji1 || 'Dr. Budi Wicaksono, M.Kom.',
                    penguji1MatchScore: 95,
                    penguji2: item.penguji2 || 'Dian Permata Sari, S.Kom., M.Kom.',
                    hari: 'Selasa',
                    tanggal: '29 Sep 2026',
                    mulai: '09:00',
                    selesai: '10:30',
                    files: [
                        { name: 'Draf_Proposal_Skripsi.pdf' },
                        { name: 'Lembar_Persetujuan_Seminar.pdf' },
                    ],
                };
            });
            studentsList.value = mapped;
        } else {
            studentsList.value = defaultDemoList;
        }
    },
    { immediate: true },
);

// Guarantee current logged-in user is one of the examiners in Mahasiswa Uji
const displayStudentsList = computed(() => {
    return studentsList.value.map((s) => {
        if (s.roleTab === 'uji') {
            if (s.id % 2 === 0) {
                return {
                    ...s,
                    penguji1: currentUserName.value,
                    penguji2:
                        s.penguji2 === currentUserName.value
                            ? 'Dian Permata Sari, S.Kom., M.Kom.'
                            : s.penguji2,
                };
            } else {
                return {
                    ...s,
                    penguji1:
                        s.penguji1 === currentUserName.value
                            ? 'Dr. Budi Wicaksono, M.Kom.'
                            : s.penguji1,
                    penguji2: currentUserName.value,
                };
            }
        }
        return s;
    });
});

// Tab Counts
const bimbinganCount = computed(
    () => displayStudentsList.value.filter((s) => s.roleTab === 'bimbingan').length,
);
const ujiCount = computed(
    () => displayStudentsList.value.filter((s) => s.roleTab === 'uji').length,
);

// Attendance confirmation per student ID (grounding the Dosen Penguji status)
const confirmedAttendance = ref<Record<number, boolean>>({
    1: true,
    4: true,
});

// Active reschedule requests per student ID
const rescheduledStudents = ref<Record<number, boolean>>({
    2: true,
});

// 4 KPI Summary Counts (Harmonized with Sidang TA)
const totalSemproCount = computed(() => displayStudentsList.value.length);
const terjadwalCount = computed(
    () => displayStudentsList.value.filter((s) => s.tanggal && s.mulai).length,
);
const pendingValidationCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.statusBerkas === 'Menunggu Validasi',
        ).length,
);
const selesaiDinilaiCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.nilai && s.nilai !== '-' && s.nilai !== '',
        ).length,
);
const konfirmasiHadirCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => !!confirmedAttendance.value[s.id],
        ).length,
);
const ajuanRescheduleCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => !!rescheduledStudents.value[s.id],
        ).length,
);
const bimbinganRescheduleCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.roleTab === 'bimbingan' && !!rescheduledStudents.value[s.id],
        ).length,
);
const ujiRescheduleCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.roleTab === 'uji' && !!rescheduledStudents.value[s.id],
        ).length,
);

// Unique Angkatan list for filter dropdown
const angkatanOptions = computed(() => {
    const set = new Set<string>();
    for (const item of displayStudentsList.value) {
        if (item.angkatan) set.add(item.angkatan);
    }
    return ['all', ...Array.from(set).sort((a, b) => Number(b) - Number(a))];
});

// Filtered list
const filteredStudents = computed(() => {
    return displayStudentsList.value.filter((s) => {
        const matchTab = s.roleTab === activeTab.value;
        const matchAngkatan =
            filterAngkatan.value === 'all' || s.angkatan === filterAngkatan.value;
        const q = searchQuery.value.toLowerCase().trim();
        const matchSearch =
            !q ||
            s.nama.toLowerCase().includes(q) ||
            s.nim.toLowerCase().includes(q) ||
            s.judul.toLowerCase().includes(q);
        return matchTab && matchAngkatan && matchSearch;
    });
});

// Pagination matching Data Master Mahasiswa (5 items per page)
const currentPage = ref(1);
const itemsPerPage = 5;

watch([activeTab, filterAngkatan, searchQuery], () => {
    currentPage.value = 1;
});

const totalPages = computed(() =>
    Math.ceil(filteredStudents.value.length / itemsPerPage) || 1,
);

const paginationStart = computed(() =>
    filteredStudents.value.length
        ? (currentPage.value - 1) * itemsPerPage + 1
        : 0,
);

const paginationEnd = computed(() =>
    Math.min(
        currentPage.value * itemsPerPage,
        filteredStudents.value.length,
    ),
);

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredStudents.value.slice(start, start + itemsPerPage);
});

// Selected student for Inspector Panel
const selectedStudent = ref<SemproStudentItem | null>(null);

// Auto-select first item or preserve selection on tab switch
watch(
    filteredStudents,
    (list) => {
        if (selectedStudent.value) {
            const exists = list.find((s) => s.id === selectedStudent.value?.id);
            if (!exists) {
                selectedStudent.value = list[0] ?? null;
            }
        } else {
            selectedStudent.value = list[0] ?? null;
        }
    },
    { immediate: true },
);

const selectStudent = (item: SemproStudentItem) => {
    if (selectedStudent.value?.id === item.id) {
        selectedStudent.value = null;
    } else {
        selectedStudent.value = item;
    }
};

// Modal for Revisi Berkas
const showRevisiModal = ref(false);
const revisiNote = ref('');

const openRevisiModal = () => {
    if (!selectedStudent.value) return;
    revisiNote.value =
        selectedStudent.value.revisi && selectedStudent.value.revisi !== 'Tidak Ada'
            ? selectedStudent.value.revisi
            : 'Lengkapi dokumen lampiran Bab 3 dan lembar bimbingan';
    showRevisiModal.value = true;
};

const approveBerkas = () => {
    if (!selectedStudent.value) return;
    const target = selectedStudent.value;
    target.statusBerkas = 'Berkas Valid';
    target.persetujuan = 'Disetujui Pembimbing';
    showToast(`Berkas pendaftaran Sempro "${target.nama}" berhasil disetujui!`);
};

const saveRevisi = () => {
    if (!selectedStudent.value) return;
    const target = selectedStudent.value;
    target.statusBerkas = 'Perlu Revisi';
    target.revisi = revisiNote.value.trim() || 'Perbaiki kelengkapan berkas';
    showRevisiModal.value = false;
    showToast(`Catatan revisi berkas telah dikirimkan ke "${target.nama}".`);
};

const downloadFile = (fileName: string) => {
    showToast(`Mengunduh dokumen: ${fileName}`);
};

// Modal Form Penilaian Sempro (Dosen & Koorprodi as Dosen)
const isModalNilaiOpen = ref(false);
const formNilai = ref<{ skor: number; revisi: string }>({
    skor: 85,
    revisi: '',
});

const hitungGrade = (skor: number) => {
    if (skor >= 85) return 'A';
    if (skor >= 80) return 'AB';
    if (skor >= 75) return 'B';
    if (skor >= 70) return 'BC';
    if (skor >= 65) return 'C';
    if (skor >= 50) return 'D';
    return 'E';
};

const openModalNilai = (student: SemproStudentItem) => {
    if (!student) return;
    const initialSkor = student.nilai === 'A' ? 88 : student.nilai === 'AB' ? 80 : 85;
    formNilai.value = {
        skor: initialSkor,
        revisi: student.revisi && student.revisi !== 'Tidak Ada' ? student.revisi : '',
    };
    isModalNilaiOpen.value = true;
};

const submitNilaiSempro = () => {
    if (!selectedStudent.value) return;
    selectedStudent.value.nilai = hitungGrade(formNilai.value.skor);
    selectedStudent.value.revisi = formNilai.value.revisi || 'Sudah Bebas Revisi';
    showToast(
        `Nilai seminar proposal (${selectedStudent.value.nilai}) untuk "${selectedStudent.value.nama}" berhasil disimpan!`,
    );
    isModalNilaiOpen.value = false;
};

// Modal Reschedule Jadwal (Dosen: "Melihat Jadwal Seminar extend mengajukan reschedule")
const isModalRescheduleOpen = ref(false);
const formReschedule = ref({
    alasan: 'Bentrok Jadwal Mengajar / Kuliah',
    catatan: '',
    usulanHari: 'Rabu',
    usulanWaktu: '13:00 - 14:30 WITA',
});

const openModalReschedule = (student: SemproStudentItem) => {
    if (!student) return;
    formReschedule.value = {
        alasan: 'Bentrok Jadwal Mengajar / Kuliah',
        catatan: '',
        usulanHari: 'Rabu',
        usulanWaktu: '13:00 - 14:30 WITA',
    };
    isModalRescheduleOpen.value = true;
};

const submitReschedule = () => {
    if (!selectedStudent.value) return;
    rescheduledStudents.value[selectedStudent.value.id] = true;
    showToast(
        `Permohonan reschedule untuk "${selectedStudent.value.nama}" berhasil dikirimkan ke Koorprodi.`,
    );
    isModalRescheduleOpen.value = false;
};

const cancelReschedule = (student: SemproStudentItem) => {
    if (!student) return;
    delete rescheduledStudents.value[student.id];
    showToast(`Pengajuan reschedule untuk "${student.nama}" berhasil dibatalkan.`);
};

const toggleKonfirmasiHadir = (studentId: number) => {
    confirmedAttendance.value[studentId] = !confirmedAttendance.value[studentId];
    if (confirmedAttendance.value[studentId]) {
        showToast('Kehadiran seminar proposal berhasil dikonfirmasi!');
    } else {
        showToast('Konfirmasi kehadiran dibatalkan.');
    }
};

const isPengujiConfirmed = (student: SemproStudentItem, pengujiName: string) => {
    if (pengujiName === currentUserName.value) {
        return !!confirmedAttendance.value[student.id];
    }
    return student.id % 2 === 1;
};

// Badge helper classes (Strictly NO DOTS beside text!)
const getStatusBerkasClass = (status: string) => {
    const s = status.toLowerCase();
    if (s.includes('menunggu')) {
        return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/60 dark:text-amber-400';
    }
    if (s.includes('valid') || s.includes('disetujui')) {
        return 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300';
    }
    if (s.includes('revisi')) {
        return 'border-purple-200 bg-purple-50 text-purple-700 dark:border-purple-800 dark:bg-purple-950/60 dark:text-purple-300';
    }
    return 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-900/60 dark:bg-amber-950/60 dark:text-amber-400';
};

const getPersetujuanClass = (persetujuan: string) => {
    const p = persetujuan.toLowerCase();
    if (p.includes('disetujui')) {
        return 'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300';
    }
    return 'border-slate-200 bg-slate-100 text-black dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300';
};
</script>

<template>
    <div class="space-y-6">
        <!-- TOAST NOTIFICATION POPUP -->
        <div
            v-if="toast.show"
            class="fixed top-20 right-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-xl backdrop-blur-md transition-all duration-300"
            :class="[
                toast.isError
                    ? 'border-rose-300 bg-rose-50/95 text-rose-800 dark:border-rose-800 dark:bg-rose-950/90 dark:text-rose-200'
                    : 'border-emerald-300 bg-emerald-50/95 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-200',
            ]"
        >
            <span class="text-xs font-bold sm:text-sm">{{ toast.message }}</span>
        </div>

        <!-- PAGE HEADER -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1
                    class="text-2xl font-black tracking-tight text-black dark:text-white"
                >
                    Seminar Proposal
                </h1>
                <p class="mt-0.5 text-xs font-normal text-black dark:text-slate-400">
                    <template v-if="isKoorprodi">
                        Data pengajuan seminar proposal, plotting penguji, dan verifikasi berkas akademik.
                    </template>
                    <template v-else>
                        Informasi jadwal seminar proposal mahasiswa bimbingan & uji, konfirmasi kehadiran, dan penilaian.
                    </template>
                </p>
            </div>
        </div>

        <!-- 4 KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Total Mahasiswa Sempro -->
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-black uppercase dark:text-slate-400"
                    >
                        {{ isKoorprodi ? 'Total Pendaftar Sempro' : 'Total Mahasiswa Sempro' }}
                    </p>
                    <p class="mt-1 text-2xl font-black text-black dark:text-white">
                        {{ totalSemproCount }} Mhs
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                >
                    <GraduationCap class="h-5 w-5" />
                </div>
            </div>

            <!-- 2. Jadwal Tervalidasi (Koorprodi) vs Konfirmasi Kehadiran (Dosen) -->
            <div
                class="flex items-center justify-between rounded-2xl border border-emerald-200/80 bg-white p-4 shadow-xs dark:border-emerald-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-emerald-700 uppercase dark:text-emerald-400"
                    >
                        {{ isKoorprodi ? 'Jadwal Tervalidasi' : 'Konfirmasi Kehadiran' }}
                    </p>
                    <p class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-400">
                        {{ isKoorprodi ? `${terjadwalCount} Mhs` : `${konfirmasiHadirCount} Mhs` }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                    <component :is="isKoorprodi ? Calendar : CheckCircle2" class="h-5 w-5" />
                </div>
            </div>

            <!-- 3. Menunggu Validasi (Koorprodi) vs Ajuan Reschedule Jadwal (Dosen) -->
            <div
                class="flex items-center justify-between rounded-2xl border border-amber-200/80 bg-white p-4 shadow-xs dark:border-amber-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-700 uppercase dark:text-amber-400"
                    >
                        {{ isKoorprodi ? 'Menunggu Validasi' : 'Ajuan Reschedule Jadwal' }}
                    </p>
                    <p class="mt-1 text-2xl font-black text-amber-700 dark:text-amber-400">
                        {{ isKoorprodi ? `${pendingValidationCount} Mhs` : `${ajuanRescheduleCount} Pengajuan` }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                >
                    <component :is="isKoorprodi ? Clock : CalendarClock" class="h-5 w-5" />
                </div>
            </div>

            <!-- 4. Selesai Dinilai -->
            <div
                class="flex items-center justify-between rounded-2xl border border-purple-200/80 bg-white p-4 shadow-xs dark:border-purple-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-purple-700 uppercase dark:text-purple-400"
                    >
                        Selesai Dinilai
                    </p>
                    <p class="mt-1 text-2xl font-black text-purple-700 dark:text-purple-400">
                        {{ selesaiDinilaiCount }} Mhs
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                >
                    <FileCheck class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- MAIN LAYOUT: LEFT (PROFILE + TABLE) & RIGHT (INSPECTOR PANEL) -->
        <div class="flex flex-col items-start gap-5 lg:flex-row w-full">
            <!-- LEFT SECTION: PROFILE & TABLE (FLEX-1) -->
            <div class="w-full min-w-0 flex-1 space-y-5">
                <!-- DOSEN PROFILE CARD -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs space-y-4 dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <!-- Avatar with online dot -->
                        <div
                            class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-full bg-slate-100 border-2 border-white shadow-xs dark:bg-slate-800 dark:border-slate-700 sm:h-16 sm:w-16"
                        >
                            <UserIcon class="h-8 w-8 text-black dark:text-slate-300" />
                            <span
                                class="absolute right-0.5 bottom-0.5 h-3.5 w-3.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-[#111827]"
                            />
                        </div>

                        <div class="min-w-0 flex-1 space-y-1">
                            <h2
                                class="truncate text-base font-black text-black sm:text-lg dark:text-white"
                            >
                                {{ currentUserName }}
                            </h2>

                            <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                                <span
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-2.5 py-0.5 font-bold text-blue-800 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300"
                                >
                                    NIP: {{ currentUserNip }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-0.5 font-normal text-black dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <Mail class="h-3.5 w-3.5 text-black dark:text-slate-400" />
                                    <span class="truncate">{{ currentUserEmail }}</span>
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-0.5 font-bold text-black dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <GraduationCap class="h-3.5 w-3.5 text-black dark:text-slate-400" />
                                    <span>Dosen</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Subtabs: Mahasiswa Bimbingan & Mahasiswa Uji -->
                    <div
                        class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-800 sm:gap-3"
                    >
                        <button
                            type="button"
                            @click="activeTab = 'bimbingan'"
                            class="flex cursor-pointer items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs transition-all"
                            :class="[
                                activeTab === 'bimbingan'
                                    ? 'bg-blue-600 font-bold text-white shadow-xs'
                                    : 'bg-slate-100 font-bold text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                        >
                            <span>Mahasiswa Bimbingan</span>
                            <span
                                class="rounded-full px-1.5 py-0.2 text-[10px] font-black"
                                :class="
                                    activeTab === 'bimbingan'
                                        ? 'bg-white/20 text-white'
                                        : 'bg-slate-200 text-black dark:bg-slate-700 dark:text-slate-300'
                                "
                            >
                                {{ bimbinganCount }}
                            </span>
                            <span
                                v-if="bimbinganRescheduleCount > 0"
                                class="rounded-full px-1.5 py-0.5 text-[9px] font-bold"
                                :class="
                                    activeTab === 'bimbingan'
                                        ? 'bg-amber-400 text-amber-950'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                "
                            >
                                {{ bimbinganRescheduleCount }} Reschedule
                            </span>
                        </button>
                        <button
                            type="button"
                            @click="activeTab = 'uji'"
                            class="flex cursor-pointer items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs transition-all"
                            :class="[
                                activeTab === 'uji'
                                    ? 'bg-blue-600 font-bold text-white shadow-xs'
                                    : 'bg-slate-100 font-bold text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                        >
                            <span>Mahasiswa Uji</span>
                            <span
                                class="rounded-full px-1.5 py-0.2 text-[10px] font-black"
                                :class="
                                    activeTab === 'uji'
                                        ? 'bg-white/20 text-white'
                                        : 'bg-slate-200 text-black dark:bg-slate-700 dark:text-slate-300'
                                "
                            >
                                {{ ujiCount }}
                            </span>
                            <span
                                v-if="ujiRescheduleCount > 0"
                                class="rounded-full px-1.5 py-0.5 text-[9px] font-bold"
                                :class="
                                    activeTab === 'uji'
                                        ? 'bg-amber-400 text-amber-950'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                "
                            >
                                {{ ujiRescheduleCount }} Reschedule
                            </span>
                        </button>
                    </div>
                </div>

                <!-- TABLE DAFTAR MAHASISWA SEMINAR PROPOSAL -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <!-- Card Header -->
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:p-5 dark:border-slate-800"
                    >
                        <h3
                            class="text-sm font-black leading-snug text-black sm:text-base dark:text-white"
                        >
                            Daftar Mahasiswa Seminar Proposal
                        </h3>

                        <div class="flex items-center gap-3">
                            <!-- Filter Angkatan -->
                            <div class="flex items-center shrink-0">
                                <select
                                    v-model="filterAngkatan"
                                    class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-black focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="all">Semua Angkatan</option>
                                    <option
                                        v-for="angk in angkatanOptions.filter((a) => a !== 'all')"
                                        :key="angk"
                                        :value="angk"
                                    >
                                        {{ angk }}
                                    </option>
                                </select>
                            </div>

                            <!-- Search Input -->
                            <div class="relative w-full sm:w-60">
                                <Search
                                    class="absolute top-2.5 left-2.5 h-3.5 w-3.5 text-slate-400"
                                />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari nama, NIM, judul..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-1.5 pr-3 pl-8 text-xs font-normal text-black placeholder:text-black/60 focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 dark:placeholder:text-slate-500"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Table Content -->
                    <div class="w-full overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-100 bg-slate-50/90 font-black tracking-wider text-black uppercase dark:border-slate-800 dark:bg-slate-800/60 dark:text-white"
                            >
                                <tr>
                                    <th class="w-8 px-2.5 py-3 text-center">NO</th>
                                    <th class="min-w-[120px] px-2.5 py-3">NAMA</th>
                                    <th class="min-w-[85px] px-2 py-3">NIM</th>
                                    <th class="min-w-[200px] px-2.5 py-3">JUDUL</th>
                                    <th class="min-w-[100px] px-2 py-3 text-center">
                                        STATUS
                                    </th>
                                    <th class="min-w-[120px] px-2 py-3 text-center">
                                        PERSETUJUAN
                                    </th>
                                    <th class="min-w-[80px] px-2 py-3 text-center">
                                        BENTUK
                                    </th>
                                    <th class="w-12 px-2 py-3 text-center">NILAI</th>
                                    <th class="min-w-[90px] px-2 py-3 text-center">
                                        REVISI
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800/60"
                            >
                                <tr
                                    v-for="(mhs, idx) in paginatedStudents"
                                    :key="mhs.id"
                                    @click="selectStudent(mhs)"
                                    class="cursor-pointer transition-colors"
                                    :class="[
                                        selectedStudent && selectedStudent.id === mhs.id
                                            ? 'bg-blue-50/80 ring-1 ring-blue-500/40 ring-inset dark:bg-blue-950/40'
                                            : 'hover:bg-slate-50/70 dark:hover:bg-slate-800/40',
                                    ]"
                                >
                                    <!-- NO -->
                                    <td
                                        class="px-2.5 py-3 text-center font-bold text-black dark:text-slate-400"
                                    >
                                        {{ (currentPage - 1) * itemsPerPage + idx + 1 }}
                                    </td>

                                    <!-- NAMA -->
                                    <td class="px-2.5 py-3">
                                        <p
                                            class="leading-snug font-black text-black dark:text-white"
                                        >
                                            {{ mhs.nama }}
                                        </p>
                                    </td>

                                    <!-- NIM -->
                                    <td
                                        class="px-2 py-3 whitespace-nowrap font-normal text-black dark:text-slate-400"
                                    >
                                        {{ mhs.nim }}
                                    </td>

                                    <!-- JUDUL -->
                                    <td class="px-2.5 py-3">
                                        <p
                                            class="line-clamp-2 text-[11px] leading-relaxed font-bold text-black dark:text-slate-200"
                                        >
                                            {{ mhs.judul }}
                                        </p>
                                    </td>

                                    <!-- STATUS BERKAS (NO DOTS) -->
                                    <td class="px-2 py-3 text-center whitespace-nowrap">
                                        <span
                                            :class="getStatusBerkasClass(mhs.statusBerkas)"
                                            class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold shadow-2xs"
                                        >
                                            {{ mhs.statusBerkas }}
                                        </span>
                                    </td>

                                    <!-- PERSETUJUAN (NO DOTS) -->
                                    <td class="px-2 py-3 text-center whitespace-nowrap">
                                        <span
                                            :class="getPersetujuanClass(mhs.persetujuan)"
                                            class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold shadow-2xs"
                                        >
                                            {{ mhs.persetujuan }}
                                        </span>
                                    </td>

                                    <!-- BENTUK -->
                                    <td
                                        class="px-2 py-3 text-center font-normal text-black dark:text-slate-400"
                                    >
                                        {{ mhs.bentukTa }}
                                    </td>

                                    <!-- NILAI -->
                                    <td
                                        class="px-2 py-3 text-center font-bold text-black dark:text-white"
                                    >
                                        {{ mhs.nilai || '—' }}
                                    </td>

                                    <!-- REVISI -->
                                    <td
                                        class="max-w-[120px] truncate px-2 py-3 text-center font-normal text-black dark:text-slate-400"
                                        :title="mhs.revisi || 'Tidak Ada'"
                                    >
                                        {{ mhs.revisi || '—' }}
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="filteredStudents.length === 0">
                                    <td colspan="9" class="py-14 text-center">
                                        <div
                                            class="flex flex-col items-center justify-center space-y-3"
                                        >
                                            <div
                                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-black shadow-inner dark:bg-slate-800/80 dark:text-slate-400"
                                            >
                                                <Calendar class="h-6 w-6" />
                                            </div>
                                            <div class="space-y-1">
                                                <p
                                                    class="text-sm font-black text-black dark:text-slate-300"
                                                >
                                                    Belum ada data seminar proposal
                                                </p>
                                                <p
                                                    class="text-xs font-normal text-black dark:text-slate-400"
                                                >
                                                    {{
                                                        searchQuery
                                                            ? 'Tidak ada mahasiswa seminar proposal yang sesuai kriteria pencarian.'
                                                            : 'Belum ada mahasiswa yang mengajukan seminar proposal pada kategori ini.'
                                                    }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Note & Pagination (Matching Data Master Mahasiswa) -->
                    <div
                        class="flex flex-col justify-between gap-3 border-t border-slate-200 bg-slate-50/50 p-4 text-xs sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-900/30"
                    >
                        <div
                            class="text-xs font-semibold text-slate-900 dark:text-white"
                        >
                            Menampilkan
                            <b class="text-slate-900 dark:text-white">{{
                                paginationStart
                            }}</b>
                            sampai
                            <b class="text-slate-900 dark:text-white">{{
                                paginationEnd
                            }}</b>
                            dari
                            <b class="text-slate-900 dark:text-white">{{
                                filteredStudents.length
                            }}</b>
                            mahasiswa
                        </div>

                        <div class="flex items-center gap-1 overflow-x-auto">
                            <button
                                type="button"
                                :disabled="currentPage === 1"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 text-slate-900 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
                                @click="currentPage--"
                            >
                                ‹
                            </button>
                            <button
                                v-for="p in totalPages"
                                :key="p"
                                type="button"
                                :class="
                                    currentPage === p
                                        ? 'border-blue-600 bg-blue-600 font-bold text-white shadow-xs'
                                        : 'border border-slate-300 text-slate-900 hover:bg-slate-100 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800'
                                "
                                class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-semibold transition-colors"
                                @click="currentPage = p"
                            >
                                {{ p }}
                            </button>
                            <button
                                type="button"
                                :disabled="currentPage === totalPages"
                                class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-300 text-slate-900 hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
                                @click="currentPage++"
                            >
                                ›
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: INSPECTOR PANEL (FIXED WIDTH, STICKY) -->
            <div
                class="w-full shrink-0 space-y-4 lg:sticky lg:top-20 lg:w-96"
            >
                <!-- Active Selection Pill Banner (Visible when student selected) -->
                <div
                    v-if="selectedStudent"
                    class="flex items-center justify-between rounded-2xl border border-blue-200 bg-blue-50/80 p-3 shadow-xs dark:border-blue-800/60 dark:bg-blue-950/50"
                >
                    <div class="truncate">
                        <span
                            class="block text-[10px] font-black tracking-wider text-blue-700 uppercase dark:text-blue-400"
                        >
                            Mahasiswa Terpilih
                        </span>
                        <p
                            class="truncate text-xs font-black text-black dark:text-white"
                        >
                            {{ selectedStudent.nama }}
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="selectedStudent = null"
                        class="cursor-pointer rounded-lg p-1 text-black transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-950/50"
                        title="Tutup detail"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- CARD 1: DOSEN PENGUJI -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black uppercase dark:text-white"
                        >
                            <Users class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                            <span>Dosen Penguji</span>
                        </h3>
                        <span
                            v-if="selectedStudent"
                            class="rounded-md border border-emerald-300 bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
                        >
                            Terjadwal (GA)
                        </span>
                    </div>

                    <!-- Selected State -->
                    <div v-if="selectedStudent" class="space-y-2.5 pt-1 text-xs">
                        <div
                            class="space-y-1 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="text-[10px] font-black text-black uppercase dark:text-slate-400"
                                >
                                    Penguji 1 (Ketua)
                                </span>
                                <span
                                    class="rounded bg-purple-50 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                                >
                                    {{ selectedStudent.penguji1MatchScore }}% Match KBK
                                </span>
                            </div>
                            <p class="text-xs font-bold text-black dark:text-white">
                                {{ selectedStudent.penguji1 }}
                            </p>
                            <p
                                class="text-[10px] font-bold"
                                :class="
                                    isPengujiConfirmed(selectedStudent, selectedStudent.penguji1)
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400'
                                "
                            >
                                {{
                                    isPengujiConfirmed(selectedStudent, selectedStudent.penguji1)
                                        ? '✓ Konfirmasi Hadir'
                                        : 'Menunggu Konfirmasi'
                                }}
                            </p>
                        </div>

                        <div
                            class="space-y-1 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <span
                                class="block text-[10px] font-black text-black uppercase dark:text-slate-400"
                            >
                                Penguji 2
                            </span>
                            <p class="text-xs font-bold text-black dark:text-white">
                                {{ selectedStudent.penguji2 }}
                            </p>
                            <p
                                class="text-[10px] font-bold"
                                :class="
                                    isPengujiConfirmed(selectedStudent, selectedStudent.penguji2)
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400'
                                "
                            >
                                {{
                                    isPengujiConfirmed(selectedStudent, selectedStudent.penguji2)
                                        ? '✓ Konfirmasi Hadir'
                                        : 'Menunggu Konfirmasi'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="space-y-1.5 py-6 text-center text-xs font-normal text-black dark:text-slate-400"
                    >
                        <div
                            class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-black dark:bg-slate-800 dark:text-slate-400"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                        <p class="font-normal">Klik nama mahasiswa untuk melihat penguji</p>
                    </div>
                </div>

                <!-- CARD 2: JADWAL -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black uppercase dark:text-white"
                        >
                            <Calendar class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                            <span>Jadwal</span>
                        </h3>
                    </div>

                    <!-- Selected State -->
                    <div v-if="selectedStudent" class="space-y-2 pt-1 text-xs">
                        <div
                            class="space-y-2 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-normal text-black dark:text-slate-400">Hari & Tanggal:</span>
                                <strong class="font-bold text-black dark:text-white">
                                    {{ selectedStudent.hari }}, {{ selectedStudent.tanggal }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="font-normal text-black dark:text-slate-400">Waktu:</span>
                                <strong class="font-bold text-blue-700 dark:text-blue-400">
                                    {{ selectedStudent.mulai }} - {{ selectedStudent.selesai }} WITA
                                </strong>
                            </div>
                            <div
                                class="border-t border-slate-200/50 pt-2 text-[11px] font-bold text-emerald-700 dark:border-slate-700/50 dark:text-emerald-400"
                            >
                                <span>✓ Bebas kendala ruangan</span>
                            </div>

                            <!-- Status Pengajuan Reschedule Banner -->
                            <div
                                v-if="rescheduledStudents[selectedStudent.id]"
                                class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50/80 p-2.5 text-[11px] font-medium text-amber-900 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300"
                            >
                                <Clock class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600 dark:text-amber-400" />
                                <span>Pengajuan reschedule telah dikirimkan ke Koorprodi (Menunggu persetujuan).</span>
                            </div>

                            <!-- Actions Jadwal Dosen (Bisa diajukan baik pada Bimbingan maupun Uji) -->
                            <div
                                class="space-y-2 border-t border-slate-200/50 pt-2 dark:border-slate-700/50"
                            >
                                <!-- Toggle Reschedule / Batalkan Reschedule -->
                                <button
                                    v-if="rescheduledStudents[selectedStudent.id]"
                                    type="button"
                                    @click="cancelReschedule(selectedStudent)"
                                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-rose-300 bg-rose-50 py-2 text-xs font-bold text-rose-700 transition-colors hover:bg-rose-100 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300"
                                >
                                    <X class="h-3.5 w-3.5" />
                                    <span>Batalkan Reschedule</span>
                                </button>
                                <button
                                    v-else
                                    type="button"
                                    @click="openModalReschedule(selectedStudent)"
                                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 py-2 text-xs font-bold text-amber-900 transition-colors hover:bg-amber-100 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300"
                                >
                                    <CalendarClock class="h-3.5 w-3.5" />
                                    <span>Ajukan Reschedule Jadwal</span>
                                </button>

                                <!-- Konfirmasi Hadir hanya jika dosen penguji di tab uji -->
                                <button
                                    v-if="selectedStudent.roleTab === 'uji'"
                                    type="button"
                                    @click="toggleKonfirmasiHadir(selectedStudent.id)"
                                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border py-2 text-xs font-bold transition-all"
                                    :class="[
                                        confirmedAttendance[selectedStudent.id]
                                            ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : 'border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300'
                                    ]"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    <span>{{ confirmedAttendance[selectedStudent.id] ? 'Kehadiran Terkonfirmasi' : 'Konfirmasi Hadir' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="space-y-1.5 py-6 text-center text-xs font-normal text-black dark:text-slate-400"
                    >
                        <div
                            class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-black dark:bg-slate-800 dark:text-slate-400"
                        >
                            <Clock class="h-4 w-4" />
                        </div>
                        <p class="font-normal">Klik nama mahasiswa untuk melihat jadwal</p>
                    </div>
                </div>

                <!-- CARD 3: FILE PENDUKUNG -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black uppercase dark:text-white"
                        >
                            <FileCheck class="h-4 w-4 text-emerald-600 dark:text-emerald-400" />
                            <span>File Pendukung</span>
                        </h3>
                    </div>

                    <!-- Selected State -->
                    <div v-if="selectedStudent" class="space-y-3 pt-1 text-xs">
                        <div class="max-h-48 space-y-2 overflow-y-auto pr-1">
                            <div
                                v-for="(f, fIdx) in selectedStudent.files"
                                :key="fIdx"
                                class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/60 bg-slate-50 p-2.5 dark:border-slate-700/60 dark:bg-slate-800/60"
                            >
                                <div class="flex min-w-0 items-center gap-2">
                                    <span class="text-rose-500 font-bold">📄</span>
                                    <span
                                        class="truncate font-normal text-black dark:text-slate-200"
                                        :title="f.name"
                                    >
                                        {{ f.name }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="downloadFile(f.name)"
                                    class="cursor-pointer shrink-0 text-[11px] font-bold text-blue-600 hover:text-blue-800 dark:text-blue-400"
                                >
                                    Unduh
                                </button>
                            </div>
                        </div>

                        <!-- Actions: Validasi Berkas & Input Nilai -->
                        <div class="space-y-2 border-t border-slate-100 pt-2 dark:border-slate-800">
                            <!-- Validasi Berkas (Proposal: "koorprodi bisa Validasi Berkas Seminar") -->
                            <div v-if="isKoorprodi" class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="approveBerkas"
                                    class="flex-1 cursor-pointer rounded-xl bg-emerald-600 py-2 text-xs font-bold text-white shadow-2xs transition-all hover:bg-emerald-700 active:scale-95"
                                >
                                    ✓ Setujui Berkas
                                </button>
                                <button
                                    type="button"
                                    @click="openRevisiModal"
                                    class="cursor-pointer rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-black transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    Revisi
                                </button>
                            </div>

                            <!-- Input Nilai Sempro: Hanya di bagian Mahasiswa Uji (Dosen Penguji) -->
                            <button
                                v-if="selectedStudent.roleTab === 'uji'"
                                type="button"
                                @click="openModalNilai(selectedStudent)"
                                class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-gradient-to-r from-purple-700 to-indigo-600 py-2 text-xs font-bold text-white shadow-xs transition-all hover:from-purple-800 hover:to-indigo-700 active:scale-95"
                            >
                                <Edit3 class="h-3.5 w-3.5" />
                                <span>Input / Edit Nilai Sempro</span>
                            </button>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="space-y-1.5 py-6 text-center text-xs font-normal text-black dark:text-slate-400"
                    >
                        <div
                            class="mx-auto flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-black dark:bg-slate-800 dark:text-slate-400"
                        >
                            <FileText class="h-4 w-4" />
                        </div>
                        <p class="font-normal">Klik nama mahasiswa untuk melihat file</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL: CATATAN REVISI BERKAS -->
        <div
            v-if="showRevisiModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md space-y-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-[#111827]"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-sm font-black text-black dark:text-white">
                        Catatan Revisi Berkas Sempro
                    </h3>
                    <button
                        type="button"
                        @click="showRevisiModal = false"
                        class="cursor-pointer text-black hover:text-black/70 dark:text-slate-400 dark:hover:text-slate-200"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-1">
                    <p class="text-xs font-bold text-black dark:text-white">
                        Mahasiswa: {{ selectedStudent?.nama }}
                    </p>
                    <label class="block text-xs font-bold text-black dark:text-white pt-2">
                        Instruksi / Catatan Revisi:
                    </label>
                    <textarea
                        v-model="revisiNote"
                        rows="4"
                        placeholder="Tuliskan kekurangan berkas yang harus diperbaiki mahasiswa..."
                        class="w-full rounded-xl border border-slate-200 bg-white p-3 text-xs font-normal text-black placeholder:text-black/60 focus:border-blue-600 focus:outline-hidden dark:border-slate-700 dark:bg-slate-900 dark:text-white dark:placeholder:text-slate-500"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button
                        type="button"
                        @click="showRevisiModal = false"
                        class="cursor-pointer rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-bold text-black hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="saveRevisi"
                        class="cursor-pointer rounded-xl bg-purple-600 px-4 py-2 text-xs font-bold text-white transition-all hover:bg-purple-700"
                    >
                        Kirim Catatan Revisi
                    </button>
                </div>
            </div>
        </div>
        <!-- MODAL FORM: INPUT NILAI HASIL SEMPRO (Proposal: "Dosen: input nilai hasil sempro dan semhas") -->
        <div
            v-if="isModalNilaiOpen"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-extrabold text-black dark:text-white">
                        Form Penilaian Seminar Proposal
                    </h3>
                    <button
                        type="button"
                        @click="isModalNilaiOpen = false"
                        class="cursor-pointer text-black hover:text-slate-600 dark:text-slate-400 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="selectedStudent" class="space-y-4 text-xs">
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/50"
                    >
                        <p class="font-bold text-black dark:text-white">
                            {{ selectedStudent.nama }}
                        </p>
                        <p class="text-[11px] text-black dark:text-slate-400">
                            NIM: {{ selectedStudent.nim }}
                        </p>
                    </div>

                    <!-- Skor Nilai Angka -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Skor Nilai Seminar (0 - 100) <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="formNilai.skor"
                            type="number"
                            min="0"
                            max="100"
                            placeholder="Contoh: 85.5"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-black focus:border-purple-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p class="text-[11px] font-semibold text-black dark:text-slate-400">
                            Konversi Nilai Huruf:
                            <b class="text-purple-600 dark:text-purple-400">{{
                                hitungGrade(formNilai.skor)
                            }}</b>
                        </p>
                    </div>

                    <!-- Catatan Revisi & Waktu Tenggat -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Catatan Revisi Penguji
                        </label>
                        <textarea
                            v-model="formNilai.revisi"
                            rows="3"
                            placeholder="Tuliskan poin-poin revisi yang wajib diselesaikan mahasiswa..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-purple-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <button
                        type="button"
                        @click="isModalNilaiOpen = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold text-black hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitNilaiSempro"
                        class="cursor-pointer rounded-xl bg-purple-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-purple-700 active:scale-95"
                    >
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FORM: PENGAJUAN RESCHEDULE JADWAL (Proposal: "Dosen: Melihat Jadwal Seminar extend mengajukan reschedule") -->
        <div
            v-if="isModalRescheduleOpen"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <h3 class="text-base font-extrabold text-black dark:text-white">
                        Pengajuan Reschedule Seminar
                    </h3>
                    <button
                        type="button"
                        @click="isModalRescheduleOpen = false"
                        class="cursor-pointer text-black hover:text-slate-600 dark:text-slate-400 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="selectedStudent" class="space-y-4 text-xs">
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/50"
                    >
                        <p class="font-bold text-black dark:text-white">
                            Mahasiswa: {{ selectedStudent.nama }}
                        </p>
                        <p class="text-[11px] font-normal text-black dark:text-slate-300">
                            Jadwal Saat Ini: {{ selectedStudent.hari }}, {{ selectedStudent.tanggal }} ({{ selectedStudent.mulai }} - {{ selectedStudent.selesai }} WITA)
                        </p>
                    </div>

                    <!-- Alasan Reschedule -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Alasan Berhalangan / Reschedule <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="formReschedule.alasan"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-semibold text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Bentrok Jadwal Mengajar / Kuliah">Bentrok Jadwal Mengajar / Kuliah</option>
                            <option value="Tugas Dinas Luar / Konferensi">Tugas Dinas Luar / Konferensi</option>
                            <option value="Keperluan Akademik Lain">Keperluan Akademik Lain</option>
                            <option value="Izin / Sakit">Izin / Sakit</option>
                        </select>
                    </div>

                    <!-- Usulan Hari & Waktu -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="font-bold text-black dark:text-slate-300">Usulan Hari</label>
                            <input
                                v-model="formReschedule.usulanHari"
                                type="text"
                                placeholder="Contoh: Rabu"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="font-bold text-black dark:text-slate-300">Usulan Jam</label>
                            <input
                                v-model="formReschedule.usulanWaktu"
                                type="text"
                                placeholder="Contoh: 13:00 - 14:30"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">Catatan Tambahan untuk Koorprodi</label>
                        <textarea
                            v-model="formReschedule.catatan"
                            rows="2"
                            placeholder="Tuliskan keterangan detail bentrok jadwal..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                    <button
                        type="button"
                        @click="isModalRescheduleOpen = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold text-black hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitReschedule"
                        class="cursor-pointer rounded-xl bg-amber-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-amber-700 active:scale-95"
                    >
                        Kirim Permohonan Reschedule
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
