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
import StatCard from '@/Components/StatCard.vue';

interface SidangFile {
    name: string;
    url?: string;
}

export interface SidangStudentItem {
    id: number;
    nama: string;
    nim: string;
    angkatan: string;
    subtab: 'bimbingan' | 'uji';
    judul: string;
    statusBerkas: string;
    persetujuan: string;
    nilai: string;
    revisi: string;
    penguji1: string;
    penguji2: string;
    tanggal: string;
    waktu: string;
    ruang: string;
    files: SidangFile[];
}

const props = defineProps<{
    dbSidang?: any[];
}>();

const emit = defineEmits<{
    (e: 'back-to-judul'): void;
}>();

// User Auth context from Inertia page props
const page = usePage();
const authUser = computed(() => (page.props as any).auth?.user || {});
const currentUserName = computed(
    () => authUser.value.name || 'Prof. Dr. Eng. Muhammad Ridwan',
);
const currentUserNip = computed(
    () => authUser.value.nip || '198804102019032020',
);
const currentUserEmail = computed(
    () => authUser.value.email || 'dosen@itk.ac.id',
);
const currentUserRole = computed(
    () => authUser.value.role || 'Koordinator Program Studi',
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

// Toast Notification
const toast = ref<{ show: boolean; message: string; isError: boolean }>({
    show: false,
    message: '',
    isError: false,
});

let toastTimer: any = null;
const showToast = (message: string, isError = false) => {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, isError };
    toastTimer = setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// Filter states
const activeSubtab = ref<'bimbingan' | 'uji'>('bimbingan');
const filterAngkatan = ref<string>('all');
const searchQuery = ref<string>('');

// Default Demo Dataset for Sidang TA
const defaultSidangStudents: SidangStudentItem[] = [
    {
        id: 1,
        nama: 'Riyo Rizky Prayogi',
        nim: '11201085',
        angkatan: '2020',
        subtab: 'bimbingan',
        judul: 'Pembangunan RESTful API Penjadwalan Sidang dengan Rate Limiting dan Caching Redis Berperforma Tinggi',
        statusBerkas: 'Selesai Validasi',
        persetujuan: 'Disetujui Pembimbing',
        nilai: 'A',
        revisi: 'Perbaiki lampiran kueri Redis',
        penguji1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji2: 'Dr. Budi Wicaksono, M.Kom.',
        tanggal: 'Senin, 05 Okt 2026',
        waktu: '09:00 - 10:30',
        ruang: 'Lab. Komputasi Cerdas 2',
        files: [
            { name: 'Naskah_Lengkap_Skripsi_11201085.pdf' },
            { name: 'Hasil_Cek_Plagiasi_Turnitin_14%.pdf' },
            { name: 'Lembar_Persetujuan_Sidang_Pembimbing.pdf' },
            { name: 'Transkrip_Nilai_IAET_520.pdf' },
        ],
    },
    {
        id: 2,
        nama: 'Muhammad Azka Yunastio',
        nim: '11231036',
        angkatan: '2023',
        subtab: 'bimbingan',
        judul: 'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Penjadwalan Seminar Tugas Akhir',
        statusBerkas: 'Menunggu Validasi',
        persetujuan: 'Menunggu Persetujuan',
        nilai: '',
        revisi: '',
        penguji1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji2: 'Rian Kurniawan, S.Kom., M.Eng.',
        tanggal: 'Rabu, 07 Okt 2026',
        waktu: '10:30 - 12:00',
        ruang: 'Ruang Sidang FSTI 1',
        files: [
            { name: 'Draf_Lengkap_Bab_1-5_11231036.pdf' },
            { name: 'Sertifikat_Turnitin_11%.pdf' },
            { name: 'Formulir_Bimbingan_8_Sesi.pdf' },
        ],
    },
    {
        id: 3,
        nama: 'Ahmad Iqbal Fadhillah',
        nim: '11191004',
        angkatan: '2019',
        subtab: 'uji',
        judul: 'Rancang Bangun Sistem Informasi Geografis Pemetaan Fasilitas Kampus Berbasis Web 3D',
        statusBerkas: 'Selesai Validasi',
        persetujuan: 'Disetujui Pembimbing',
        nilai: 'AB',
        revisi: 'Lengkapi dokumentasi API',
        penguji1: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        penguji2: 'Dian Permata Sari, S.Kom., M.Kom.',
        tanggal: 'Selasa, 06 Okt 2026',
        waktu: '13:30 - 15:00',
        ruang: 'Lab. Rekayasa Perangkat Lunak',
        files: [
            { name: 'Skripsi_Final_Ahmad_Iqbal.pdf' },
            { name: 'Turnitin_Plagiasi_15%.pdf' },
            { name: 'Logbook_Bimbingan.pdf' },
        ],
    },
    {
        id: 4,
        nama: 'Andriani Fenny Maulidha',
        nim: '11191009',
        angkatan: '2019',
        subtab: 'uji',
        judul: 'Implementasi Algoritma Naive Bayes untuk Klasifikasi Sentimen Ulasan Pengguna Aplikasi E-Commerce',
        statusBerkas: 'Selesai Validasi',
        persetujuan: 'Disetujui Pembimbing',
        nilai: 'A',
        revisi: 'Sudah Bebas Revisi',
        penguji1: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        penguji2: 'Rian Kurniawan, S.Kom., M.Eng.',
        tanggal: 'Kamis, 08 Okt 2026',
        waktu: '09:00 - 10:30',
        ruang: 'Ruang Sidang FSTI 2',
        files: [
            { name: 'Naskah_Skripsi_Fenny_11191009.pdf' },
            { name: 'Turnitin_Similarity_12%.pdf' },
        ],
    },
];

const studentsList = ref<SidangStudentItem[]>(defaultSidangStudents);

// Hydrate from DB props if present
watch(
    () => props.dbSidang,
    (newVal) => {
        if (newVal && newVal.length > 0) {
            const mapped: SidangStudentItem[] = newVal.map(
                (item: any, idx: number) => {
                    return {
                        id: item.id || idx + 1,
                        nama:
                            item.nama_mahasiswa ||
                            item.mahasiswa?.nama ||
                            `Mahasiswa Sidang ${idx + 1}`,
                        nim: item.nim || item.mahasiswa?.nim || `1120108${idx}`,
                        angkatan: item.angkatan || '2020',
                        subtab: idx % 2 === 0 ? 'bimbingan' : 'uji',
                        judul:
                            item.judul ||
                            item.judul_ta ||
                            'Judul Sidang Akhir Skripsi Mahasiswa',
                        statusBerkas: item.status_berkas || 'Selesai Validasi',
                        persetujuan:
                            item.status_persetujuan || 'Disetujui Pembimbing',
                        nilai: item.nilai || (idx % 2 === 0 ? 'A' : 'AB'),
                        revisi: item.revisi || 'Perbaiki format lampiran',
                        penguji1:
                            item.penguji1 ||
                            'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
                        penguji2:
                            item.penguji2 ||
                            'Dian Permata Sari, S.Kom., M.Kom.',
                        tanggal: item.tanggal || 'Senin, 05 Okt 2026',
                        waktu: item.waktu || '09:00 - 10:30',
                        ruang: item.ruang || 'Ruang Sidang FSTI 1',
                        files: [
                            { name: 'Naskah_Lengkap_Skripsi.pdf' },
                            { name: 'Lembar_Persetujuan_Sidang.pdf' },
                        ],
                    };
                },
            );
            studentsList.value = mapped;
        } else {
            studentsList.value = defaultSidangStudents;
        }
    },
    { immediate: true },
);

// Crucial: For Mahasiswa Uji, ensure current logged-in user is one of the examiners
const displayStudentsList = computed(() => {
    return studentsList.value.map((s) => {
        if (s.subtab === 'uji') {
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

// Subtab Counts
const bimbinganCount = computed(
    () =>
        displayStudentsList.value.filter((s) => s.subtab === 'bimbingan')
            .length,
);
const ujiCount = computed(
    () => displayStudentsList.value.filter((s) => s.subtab === 'uji').length,
);

// Attendance confirmation per student ID (grounding the Dosen Penguji status)
const confirmedAttendance = ref<Record<number, boolean>>({
    1: true,
    3: true,
});

// Active reschedule requests per student ID
const rescheduledStudents = ref<Record<number, boolean>>({
    2: true,
});

// KPI Summary counts
const totalSidangCount = computed(() => displayStudentsList.value.length);
const terjadwalCount = computed(
    () => displayStudentsList.value.filter((s) => s.tanggal).length,
);
const pendingValidationCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.statusBerkas === 'Menunggu Validasi',
        ).length,
);
const selesaiDinilaiCount = computed(
    () => displayStudentsList.value.filter((s) => s.nilai).length,
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
            (s) =>
                s.subtab === 'bimbingan' && !!rescheduledStudents.value[s.id],
        ).length,
);
const ujiRescheduleCount = computed(
    () =>
        displayStudentsList.value.filter(
            (s) => s.subtab === 'uji' && !!rescheduledStudents.value[s.id],
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
        const matchTab = s.subtab === activeSubtab.value;
        const matchAngkatan =
            filterAngkatan.value === 'all' ||
            s.angkatan === filterAngkatan.value;
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

watch([activeSubtab, filterAngkatan, searchQuery], () => {
    currentPage.value = 1;
});

const totalPages = computed(
    () => Math.ceil(filteredStudents.value.length / itemsPerPage) || 1,
);

const paginationStart = computed(() =>
    filteredStudents.value.length
        ? (currentPage.value - 1) * itemsPerPage + 1
        : 0,
);

const paginationEnd = computed(() =>
    Math.min(currentPage.value * itemsPerPage, filteredStudents.value.length),
);

const paginatedStudents = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    return filteredStudents.value.slice(start, start + itemsPerPage);
});

// Selected student for Inspector Panel
const selectedStudent = ref<SidangStudentItem | null>(null);

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

const selectStudent = (mhs: SidangStudentItem) => {
    selectedStudent.value = mhs;
};

// Document validation actions
const validateDocument = (isApproved: boolean) => {
    if (!selectedStudent.value) return;
    const target = studentsList.value.find(
        (s) => s.id === selectedStudent.value?.id,
    );
    if (target) {
        if (isApproved) {
            target.statusBerkas = 'Selesai Validasi';
            target.persetujuan = 'Disetujui Pembimbing';
            showToast(
                `Berkas sidang untuk "${target.nama}" berhasil disetujui!`,
            );
        } else {
            target.statusBerkas = 'Perlu Revisi Berkas';
            showToast(
                `Catatan revisi berkas dikirimkan kepada "${target.nama}".`,
                true,
            );
        }
    }
};

// Modal Nilai Sidang
const isModalNilaiOpen = ref(false);
const targetStudentNilai = ref<SidangStudentItem | null>(null);
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

const openModalNilai = (student: SidangStudentItem) => {
    targetStudentNilai.value = student;
    const initialSkor =
        student.nilai === 'A' ? 88 : student.nilai === 'AB' ? 80 : 85;
    formNilai.value = {
        skor: initialSkor,
        revisi: student.revisi || '',
    };
    isModalNilaiOpen.value = true;
};

const submitNilaiSidang = () => {
    if (!targetStudentNilai.value) return;
    const target = studentsList.value.find(
        (s) => s.id === targetStudentNilai.value?.id,
    );
    if (target) {
        target.nilai = hitungGrade(formNilai.value.skor);
        target.revisi = formNilai.value.revisi;
        showToast(
            `Nilai sidang (${target.nilai}) untuk "${target.nama}" berhasil disimpan!`,
        );
    }
    isModalNilaiOpen.value = false;
};

// Modal Reschedule Jadwal Sidang (Dosen: "Melihat Jadwal Seminar extend mengajukan reschedule")
const isModalRescheduleOpen = ref(false);
const formReschedule = ref({
    alasan: 'Bentrok Jadwal Mengajar / Kuliah',
    catatan: '',
    usulanHari: 'Senin',
    usulanWaktu: '10:00 - 11:30 WITA',
});

const openModalReschedule = (student: SidangStudentItem) => {
    if (!student) return;
    formReschedule.value = {
        alasan: 'Bentrok Jadwal Mengajar / Kuliah',
        catatan: '',
        usulanHari: 'Senin',
        usulanWaktu: '10:00 - 11:30 WITA',
    };
    isModalRescheduleOpen.value = true;
};

const submitReschedule = () => {
    if (!selectedStudent.value) return;
    rescheduledStudents.value[selectedStudent.value.id] = true;
    showToast(
        `Permohonan reschedule sidang untuk "${selectedStudent.value.nama}" berhasil dikirimkan ke Koorprodi.`,
    );
    isModalRescheduleOpen.value = false;
};

const cancelReschedule = (student: SidangStudentItem) => {
    if (!student) return;
    delete rescheduledStudents.value[student.id];
    showToast(
        `Pengajuan reschedule sidang untuk "${student.nama}" berhasil dibatalkan.`,
    );
};

// Modal Pendaftaran Sidang TA Baru
const isModalDaftarOpen = ref(false);
const formDaftar = ref({
    nama: '',
    nim: '',
    angkatan: '2023',
    judul: '',
});

const openModalDaftar = () => {
    formDaftar.value = {
        nama: '',
        nim: '',
        angkatan: '2023',
        judul: '',
    };
    isModalDaftarOpen.value = true;
};

const submitDaftarSidang = () => {
    if (
        !formDaftar.value.nama ||
        !formDaftar.value.nim ||
        !formDaftar.value.judul
    ) {
        showToast('Mohon lengkapi seluruh field formulir pendaftaran!', true);
        return;
    }
    const newId = studentsList.value.length + 1;
    const newStudent: SidangStudentItem = {
        id: newId,
        nama: formDaftar.value.nama,
        nim: formDaftar.value.nim,
        angkatan: formDaftar.value.angkatan || '2023',
        subtab: 'bimbingan',
        judul: formDaftar.value.judul,
        statusBerkas: 'Menunggu Validasi',
        persetujuan: 'Menunggu Persetujuan',
        nilai: '',
        revisi: '',
        penguji1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji2: 'Rian Kurniawan, S.Kom., M.Eng.',
        tanggal: 'Rabu, 14 Okt 2026',
        waktu: '10:00 - 11:30',
        ruang: 'Ruang Sidang FSTI 1',
        files: [
            { name: `Naskah_Lengkap_${formDaftar.value.nim}.pdf` },
            { name: 'Lembar_Persetujuan_Sidang.pdf' },
            { name: 'Sertifikat_Turnitin.pdf' },
        ],
    };
    studentsList.value.unshift(newStudent);
    activeSubtab.value = 'bimbingan';
    selectedStudent.value = newStudent;
    isModalDaftarOpen.value = false;
    showToast(
        `Pendaftaran sidang untuk "${newStudent.nama}" berhasil diajukan!`,
    );
};

const toggleKonfirmasiHadir = (studentId: number) => {
    confirmedAttendance.value[studentId] =
        !confirmedAttendance.value[studentId];
    if (confirmedAttendance.value[studentId]) {
        showToast('Kehadiran sidang tugas akhir berhasil dikonfirmasi!');
    } else {
        showToast('Konfirmasi kehadiran dibatalkan.');
    }
};

const isPengujiConfirmed = (
    student: SidangStudentItem,
    pengujiName: string,
) => {
    if (pengujiName === currentUserName.value) {
        return !!confirmedAttendance.value[student.id];
    }
    return student.id % 2 === 1;
};

// Badge styling helpers (NO DOTS)
const getStatusBadge = (status: string) => {
    if (status === 'Selesai Validasi') {
        return 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-800/80 dark:bg-emerald-950/60 dark:text-emerald-300';
    }
    if (status === 'Perlu Revisi Berkas') {
        return 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-800/80 dark:bg-rose-950/60 dark:text-rose-300';
    }
    return 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-800/80 dark:bg-amber-950/60 dark:text-amber-300';
};

const getPersetujuanBadge = (persetujuan: string) => {
    if (persetujuan === 'Disetujui Pembimbing') {
        return 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800/80 dark:bg-blue-950/60 dark:text-blue-300';
    }
    return 'border-slate-200 bg-slate-100 text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300';
};
</script>

<template>
    <div class="space-y-6">
        <!-- Toast Notification Popup -->
        <transition
            enter-active-class="transform transition duration-300 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transform transition duration-200 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-if="toast.show"
                class="fixed top-24 right-6 z-50 flex items-center gap-3 rounded-2xl border p-4 shadow-xl backdrop-blur-md"
                :class="
                    toast.isError
                        ? 'border-rose-200 bg-rose-50/95 text-rose-800 dark:border-rose-800 dark:bg-rose-950/90 dark:text-rose-200'
                        : 'border-emerald-200 bg-emerald-50/95 text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/90 dark:text-emerald-200'
                "
            >
                <span class="text-xs font-bold sm:text-sm">{{
                    toast.message
                }}</span>
            </div>
        </transition>

        <!-- PAGE HEADER -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="text-2xl font-black tracking-tight text-black dark:text-white"
                >
                    Sidang Tugas Akhir
                </h1>
                <p
                    class="mt-0.5 text-xs font-normal text-black dark:text-slate-400"
                >
                    <template v-if="isKoorprodi">
                        Pendaftaran sidang akhir, verifikasi berkas naskah
                        skripsi, dan penetapan dewan penguji.
                    </template>
                    <template v-else>
                        Informasi jadwal sidang tugas akhir mahasiswa bimbingan
                        & uji, konfirmasi kehadiran, dan penilaian.
                    </template>
                </p>
            </div>
        </div>

        <!-- 4 KPI SUMMARY CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                :title="
                    isKoorprodi
                        ? 'Total Pendaftar Sidang'
                        : 'Total Mahasiswa Sidang'
                "
                :value="`${totalSidangCount} mhs`"
                :icon="GraduationCap"
                icon-color="blue"
            />
            <StatCard
                :title="
                    isKoorprodi ? 'Jadwal Tervalidasi' : 'Konfirmasi Kehadiran'
                "
                :value="
                    isKoorprodi
                        ? `${terjadwalCount} mhs`
                        : `${konfirmasiHadirCount} mhs`
                "
                :icon="isKoorprodi ? Calendar : CheckCircle2"
                icon-color="emerald"
            />
            <StatCard
                :title="
                    isKoorprodi
                        ? 'Menunggu Validasi'
                        : 'Ajuan Reschedule Jadwal'
                "
                :value="
                    isKoorprodi
                        ? `${pendingValidationCount} mhs`
                        : `${ajuanRescheduleCount} pengajuan`
                "
                :icon="isKoorprodi ? Clock : CalendarClock"
                icon-color="amber"
            />
            <StatCard
                title="Selesai Dinilai"
                :value="`${selesaiDinilaiCount} mhs`"
                :icon="FileCheck"
                icon-color="slate"
            />
        </div>

        <!-- MAIN LAYOUT: LEFT (PROFILE + TABLE) & RIGHT (INSPECTOR PANEL) -->
        <div class="flex w-full flex-col items-start gap-5 lg:flex-row">
            <!-- LEFT SECTION: PROFILE & TABLE (FLEX-1) -->
            <div class="w-full min-w-0 flex-1 space-y-5">
                <!-- DOSEN PROFILE CARD -->
                <div
                    class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center"
                    >
                        <!-- Avatar with online dot -->
                        <div
                            class="relative flex h-14 w-14 shrink-0 items-center justify-center rounded-full border-2 border-white bg-slate-100 shadow-xs sm:h-16 sm:w-16 dark:border-slate-700 dark:bg-slate-800"
                        >
                            <UserIcon
                                class="h-8 w-8 text-black dark:text-slate-300"
                            />
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

                            <div
                                class="flex flex-wrap items-center gap-2 pt-1 text-xs"
                            >
                                <span
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-2.5 py-0.5 font-bold text-blue-800 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300"
                                >
                                    NIP: {{ currentUserNip }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-0.5 font-normal text-black dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <Mail
                                        class="h-3.5 w-3.5 text-black dark:text-slate-400"
                                    />
                                    <span class="truncate">{{
                                        currentUserEmail
                                    }}</span>
                                </span>
                                <span
                                    class="inline-flex items-center gap-1 rounded-xl border border-slate-200 bg-white px-2.5 py-0.5 font-bold text-black dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    <GraduationCap
                                        class="h-3.5 w-3.5 text-black dark:text-slate-400"
                                    />
                                    <span>{{ currentUserRole }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Subtabs: Mahasiswa Bimbingan | Mahasiswa Uji -->
                    <div
                        class="flex flex-wrap items-center gap-2 border-t border-slate-100 pt-3 sm:gap-3 dark:border-slate-800"
                    >
                        <button
                            type="button"
                            @click="activeSubtab = 'bimbingan'"
                            class="flex cursor-pointer items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs transition-all"
                            :class="[
                                activeSubtab === 'bimbingan'
                                    ? 'bg-blue-600 font-bold text-white shadow-xs'
                                    : 'bg-slate-100 font-bold text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                        >
                            <span>Mahasiswa Bimbingan</span>
                            <span
                                class="py-0.2 rounded-full px-1.5 text-[10px] font-black"
                                :class="
                                    activeSubtab === 'bimbingan'
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
                                    activeSubtab === 'bimbingan'
                                        ? 'bg-amber-400 text-amber-950'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                "
                            >
                                {{ bimbinganRescheduleCount }} Reschedule
                            </span>
                        </button>
                        <button
                            type="button"
                            @click="activeSubtab = 'uji'"
                            class="flex cursor-pointer items-center gap-2 rounded-xl px-3.5 py-1.5 text-xs transition-all"
                            :class="[
                                activeSubtab === 'uji'
                                    ? 'bg-blue-600 font-bold text-white shadow-xs'
                                    : 'bg-slate-100 font-bold text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                        >
                            <span>Mahasiswa Uji</span>
                            <span
                                class="py-0.2 rounded-full px-1.5 text-[10px] font-black"
                                :class="
                                    activeSubtab === 'uji'
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
                                    activeSubtab === 'uji'
                                        ? 'bg-amber-400 text-amber-950'
                                        : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'
                                "
                            >
                                {{ ujiRescheduleCount }} Reschedule
                            </span>
                        </button>
                    </div>
                </div>

                <!-- TABLE DAFTAR MAHASISWA SIDANG TUGAS AKHIR -->
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <!-- Card Header -->
                    <div
                        class="flex flex-col justify-between gap-3 border-b border-slate-100 p-4 sm:flex-row sm:items-center sm:p-5 dark:border-slate-800"
                    >
                        <h3
                            class="text-sm leading-snug font-black text-black sm:text-base dark:text-white"
                        >
                            Daftar Mahasiswa Sidang TA
                        </h3>

                        <div class="flex items-center gap-3">
                            <!-- Filter Angkatan -->
                            <div class="flex shrink-0 items-center">
                                <select
                                    v-model="filterAngkatan"
                                    class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-bold text-black focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                >
                                    <option value="all">Semua Angkatan</option>
                                    <option
                                        v-for="angkatan in angkatanOptions.filter(
                                            (a) => a !== 'all',
                                        )"
                                        :key="angkatan"
                                        :value="angkatan"
                                    >
                                        {{ angkatan }}
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
                                class="border-b border-slate-100 bg-slate-50 text-[11px] font-bold tracking-wider text-black uppercase dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="w-8 px-2 py-3 text-center">
                                        NO
                                    </th>
                                    <th class="min-w-[140px] px-2 py-3">
                                        NAMA
                                    </th>
                                    <th class="min-w-[90px] px-2 py-3">NIM</th>
                                    <th class="min-w-[200px] px-2 py-3">
                                        JUDUL
                                    </th>
                                    <th
                                        class="min-w-[110px] px-2 py-3 text-center"
                                    >
                                        STATUS
                                    </th>
                                    <th
                                        class="min-w-[120px] px-2 py-3 text-center"
                                    >
                                        PERSETUJUAN
                                    </th>
                                    <th class="w-14 px-2 py-3 text-center">
                                        NILAI
                                    </th>
                                    <th
                                        class="min-w-[100px] px-2 py-3 text-center"
                                    >
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
                                    :class="[
                                        selectedStudent &&
                                        selectedStudent.id === mhs.id
                                            ? 'bg-blue-50/90 ring-2 ring-blue-500 ring-inset dark:bg-blue-950/50'
                                            : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40',
                                        'cursor-pointer transition-colors',
                                    ]"
                                >
                                    <!-- NO -->
                                    <td
                                        class="px-2 py-3 text-center font-normal text-black dark:text-slate-400"
                                    >
                                        {{
                                            (currentPage - 1) * itemsPerPage +
                                            idx +
                                            1
                                        }}
                                    </td>

                                    <!-- NAMA -->
                                    <td
                                        class="px-2 py-3 leading-snug font-bold text-black dark:text-white"
                                    >
                                        {{ mhs.nama }}
                                    </td>

                                    <!-- NIM -->
                                    <td
                                        class="px-2 py-3 font-normal whitespace-nowrap text-black dark:text-slate-400"
                                    >
                                        {{ mhs.nim }}
                                    </td>

                                    <!-- JUDUL -->
                                    <td class="px-2 py-3">
                                        <p
                                            class="line-clamp-2 text-[11px] leading-relaxed font-bold text-black dark:text-slate-200"
                                        >
                                            {{ mhs.judul }}
                                        </p>
                                    </td>

                                    <!-- STATUS (NO DOTS) -->
                                    <td
                                        class="px-2 py-3 text-center whitespace-nowrap"
                                    >
                                        <span
                                            :class="
                                                getStatusBadge(mhs.statusBerkas)
                                            "
                                            class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold shadow-2xs"
                                        >
                                            {{ mhs.statusBerkas }}
                                        </span>
                                    </td>

                                    <!-- PERSETUJUAN (NO DOTS) -->
                                    <td
                                        class="px-2 py-3 text-center whitespace-nowrap"
                                    >
                                        <span
                                            :class="
                                                getPersetujuanBadge(
                                                    mhs.persetujuan,
                                                )
                                            "
                                            class="inline-block rounded-full border px-2.5 py-1 text-[10px] font-bold shadow-2xs"
                                        >
                                            {{ mhs.persetujuan }}
                                        </span>
                                    </td>

                                    <!-- NILAI -->
                                    <td
                                        class="px-2 py-3 text-center font-bold"
                                        :class="
                                            mhs.nilai
                                                ? 'text-sm text-emerald-600 dark:text-emerald-400'
                                                : 'text-black dark:text-slate-400'
                                        "
                                    >
                                        {{ mhs.nilai || '—' }}
                                    </td>

                                    <!-- REVISI -->
                                    <td
                                        class="max-w-[120px] truncate px-2 py-3 text-center font-normal text-black dark:text-slate-400"
                                        :title="
                                            mhs.revisi || 'Tidak Ada Revisi'
                                        "
                                    >
                                        {{ mhs.revisi || '—' }}
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="filteredStudents.length === 0">
                                    <td colspan="8" class="py-14 text-center">
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
                                                    Belum ada data sidang tugas
                                                    akhir
                                                </p>
                                                <p
                                                    class="text-xs font-normal text-black dark:text-slate-400"
                                                >
                                                    {{
                                                        searchQuery
                                                            ? 'Tidak ada mahasiswa sidang yang cocok dengan kriteria pencarian.'
                                                            : 'Belum ada mahasiswa yang mengajukan sidang tugas akhir pada kategori ini.'
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
            <div class="w-full shrink-0 space-y-4 lg:sticky lg:top-20 lg:w-96">
                <!-- Active Selection Pill Banner (Visible when student selected) -->
                <div
                    v-if="selectedStudent"
                    class="flex items-center justify-between rounded-2xl border border-blue-200 bg-blue-50/80 p-3 shadow-xs dark:border-blue-800/60 dark:bg-blue-950/50"
                >
                    <div class="truncate">
                        <span
                            class="block text-[10px] font-black tracking-wider text-blue-700 dark:text-blue-400"
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
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black dark:text-white"
                        >
                            <Users
                                class="h-4 w-4 text-blue-600 dark:text-blue-400"
                            />
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
                    <div
                        v-if="selectedStudent"
                        class="space-y-2.5 pt-1 text-xs"
                    >
                        <div
                            class="space-y-1 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <span
                                class="block text-[10px] font-black text-black dark:text-slate-400"
                            >
                                Penguji 1 (Ketua)
                            </span>
                            <p
                                class="text-xs font-bold text-black dark:text-white"
                            >
                                {{ selectedStudent.penguji1 }}
                            </p>
                            <p
                                class="text-[10px] font-bold"
                                :class="
                                    isPengujiConfirmed(
                                        selectedStudent,
                                        selectedStudent.penguji1,
                                    )
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400'
                                "
                            >
                                {{
                                    isPengujiConfirmed(
                                        selectedStudent,
                                        selectedStudent.penguji1,
                                    )
                                        ? '✓ Konfirmasi Hadir'
                                        : 'Menunggu Konfirmasi'
                                }}
                            </p>
                        </div>
                        <div
                            class="space-y-1 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <span
                                class="block text-[10px] font-black text-black dark:text-slate-400"
                            >
                                Penguji 2
                            </span>
                            <p
                                class="text-xs font-bold text-black dark:text-white"
                            >
                                {{ selectedStudent.penguji2 }}
                            </p>
                            <p
                                class="text-[10px] font-bold"
                                :class="
                                    isPengujiConfirmed(
                                        selectedStudent,
                                        selectedStudent.penguji2,
                                    )
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : 'text-amber-600 dark:text-amber-400'
                                "
                            >
                                {{
                                    isPengujiConfirmed(
                                        selectedStudent,
                                        selectedStudent.penguji2,
                                    )
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
                        <p class="font-normal">
                            Klik nama mahasiswa untuk melihat penguji
                        </p>
                    </div>
                </div>

                <!-- CARD 2: JADWAL -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black dark:text-white"
                        >
                            <Calendar
                                class="h-4 w-4 text-purple-600 dark:text-purple-400"
                            />
                            <span>Jadwal</span>
                        </h3>
                    </div>

                    <!-- Selected State -->
                    <div v-if="selectedStudent" class="space-y-2 pt-1 text-xs">
                        <div
                            class="space-y-2 rounded-xl border border-slate-200/60 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/60"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-normal text-black dark:text-slate-400"
                                    >Hari & Tanggal:</span
                                >
                                <strong
                                    class="font-bold text-black dark:text-white"
                                >
                                    {{ selectedStudent.tanggal }}
                                </strong>
                            </div>
                            <div class="flex items-center justify-between">
                                <span
                                    class="font-normal text-black dark:text-slate-400"
                                    >Waktu:</span
                                >
                                <strong
                                    class="font-bold text-blue-700 dark:text-blue-400"
                                >
                                    {{ selectedStudent.waktu }} WITA
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
                                <Clock
                                    class="mt-0.5 h-3.5 w-3.5 shrink-0 text-amber-600 dark:text-amber-400"
                                />
                                <span
                                    >Pengajuan reschedule telah dikirimkan ke
                                    Koorprodi (Menunggu persetujuan).</span
                                >
                            </div>

                            <!-- Actions Jadwal Dosen (Bisa diajukan baik pada Bimbingan maupun Uji) -->
                            <div
                                class="space-y-2 border-t border-slate-200/50 pt-2 dark:border-slate-700/50"
                            >
                                <!-- Toggle Reschedule / Batalkan Reschedule -->
                                <button
                                    v-if="
                                        rescheduledStudents[selectedStudent.id]
                                    "
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
                                    @click="
                                        openModalReschedule(selectedStudent)
                                    "
                                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-amber-300 bg-amber-50 py-2 text-xs font-bold text-amber-900 transition-colors hover:bg-amber-100 dark:border-amber-800/60 dark:bg-amber-950/40 dark:text-amber-300"
                                >
                                    <CalendarClock class="h-3.5 w-3.5" />
                                    <span>Ajukan Reschedule Jadwal</span>
                                </button>

                                <!-- Konfirmasi Hadir hanya jika dosen penguji di subtab uji -->
                                <button
                                    v-if="selectedStudent.subtab === 'uji'"
                                    type="button"
                                    @click="
                                        toggleKonfirmasiHadir(
                                            selectedStudent.id,
                                        )
                                    "
                                    class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl border py-2 text-xs font-bold transition-all"
                                    :class="[
                                        confirmedAttendance[selectedStudent.id]
                                            ? 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-300'
                                            : 'border-blue-300 bg-blue-50 text-blue-800 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-950/40 dark:text-blue-300',
                                    ]"
                                >
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    <span>{{
                                        confirmedAttendance[selectedStudent.id]
                                            ? 'Kehadiran Terkonfirmasi'
                                            : 'Konfirmasi Hadir'
                                    }}</span>
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
                        <p class="font-normal">
                            Klik nama mahasiswa untuk melihat jadwal
                        </p>
                    </div>
                </div>

                <!-- CARD 3: FILE PENDUKUNG -->
                <div
                    class="space-y-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <div class="flex items-center justify-between">
                        <h3
                            class="flex items-center gap-2 text-xs font-black tracking-wider text-black dark:text-white"
                        >
                            <FileText
                                class="h-4 w-4 text-emerald-600 dark:text-emerald-400"
                            />
                            <span>File Pendukung</span>
                        </h3>
                    </div>

                    <!-- Selected State -->
                    <div v-if="selectedStudent" class="space-y-3 pt-1 text-xs">
                        <div class="max-h-48 space-y-2 overflow-y-auto pr-1">
                            <div
                                v-for="(f, fIdx) in selectedStudent.files"
                                :key="fIdx"
                                class="flex items-center justify-between gap-2 rounded-xl border border-slate-200/80 bg-slate-50 p-2.5 dark:border-slate-700/60 dark:bg-slate-800/60"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <FileText
                                        class="h-4 w-4 shrink-0 text-rose-500"
                                    />
                                    <span
                                        class="truncate font-medium text-black dark:text-slate-200"
                                    >
                                        {{ f.name }}
                                    </span>
                                </div>
                                <button
                                    type="button"
                                    @click="showToast('Mengunduh ' + f.name)"
                                    class="shrink-0 cursor-pointer text-[11px] font-bold text-blue-600 hover:underline dark:text-blue-400"
                                >
                                    Unduh
                                </button>
                            </div>
                        </div>

                        <!-- Action Buttons: Validasi Berkas & Input Nilai -->
                        <div
                            class="space-y-2 border-t border-slate-100 pt-2 dark:border-slate-800"
                        >
                            <!-- Validasi Berkas (Proposal: "koorprodi bisa Validasi Berkas TA") -->
                            <div
                                v-if="isKoorprodi"
                                class="flex items-center gap-2"
                            >
                                <button
                                    type="button"
                                    @click="validateDocument(true)"
                                    class="flex-1 cursor-pointer rounded-xl bg-emerald-600 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-emerald-700 active:scale-95"
                                >
                                    ✓ Setujui Berkas
                                </button>
                                <button
                                    type="button"
                                    @click="validateDocument(false)"
                                    class="cursor-pointer rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-black transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                                >
                                    Revisi
                                </button>
                            </div>

                            <!-- Input Nilai Sidang: Hanya di bagian Mahasiswa Uji (Dosen Penguji) -->
                            <button
                                v-if="selectedStudent.subtab === 'uji'"
                                type="button"
                                @click="openModalNilai(selectedStudent)"
                                class="flex w-full cursor-pointer items-center justify-center gap-1.5 rounded-xl bg-indigo-700 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-indigo-800 active:scale-95"
                            >
                                <Edit3 class="h-3.5 w-3.5" />
                                <span>Input / Edit Nilai Sidang</span>
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
                        <p class="font-normal">
                            Klik nama mahasiswa untuk melihat file
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL FORM: INPUT NILAI HASIL SIDANG -->
        <div
            v-if="isModalNilaiOpen"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-base font-extrabold text-black dark:text-white"
                    >
                        Form Penilaian Sidang TA
                    </h3>
                    <button
                        type="button"
                        @click="isModalNilaiOpen = false"
                        class="cursor-pointer text-black hover:text-slate-600 dark:text-slate-400 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div v-if="targetStudentNilai" class="space-y-4 text-xs">
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700/60 dark:bg-slate-800/50"
                    >
                        <p class="font-bold text-black dark:text-white">
                            {{ targetStudentNilai.nama }}
                        </p>
                        <p class="text-[11px] text-black dark:text-slate-400">
                            NIM: {{ targetStudentNilai.nim }}
                        </p>
                    </div>

                    <!-- Skor Nilai Angka -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Skor Nilai Sidang (0 - 100)
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model.number="formNilai.skor"
                            type="number"
                            min="0"
                            max="100"
                            placeholder="Contoh: 85.5"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-black focus:border-purple-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                        <p
                            class="text-[11px] font-semibold text-black dark:text-slate-400"
                        >
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
                            placeholder="Tuliskan poin-poin revisi yang wajib diselesaikan mahasiswa dalam 14 hari..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-purple-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <button
                        type="button"
                        @click="isModalNilaiOpen = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold text-black hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitNilaiSidang"
                        class="cursor-pointer rounded-xl bg-purple-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-purple-700 active:scale-95"
                    >
                        Simpan Nilai
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FORM: PENDAFTARAN SIDANG TA BARU -->
        <div
            v-if="isModalDaftarOpen"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-lg space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-base font-extrabold text-black dark:text-white"
                    >
                        Pendaftaran Sidang Akhir Skripsi
                    </h3>
                    <button
                        type="button"
                        @click="isModalDaftarOpen = false"
                        class="cursor-pointer text-black hover:text-slate-600 dark:text-slate-400 dark:hover:text-white"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Nama Lengkap Mahasiswa
                            <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="formDaftar.nama"
                            type="text"
                            placeholder="Masukkan Nama Lengkap"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label
                                class="font-bold text-black dark:text-slate-300"
                            >
                                NIM <span class="text-rose-500">*</span>
                            </label>
                            <input
                                v-model="formDaftar.nim"
                                type="text"
                                placeholder="11201085"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 font-mono text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="font-bold text-black dark:text-slate-300"
                                >Angkatan</label
                            >
                            <input
                                v-model="formDaftar.angkatan"
                                type="text"
                                placeholder="2023"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Judul Naskah Tugas Akhir
                            <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="formDaftar.judul"
                            rows="2"
                            placeholder="Judul lengkap skripsi Bab 1-5"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>

                    <div
                        class="space-y-1.5 rounded-xl border border-purple-200 bg-purple-50 p-3 dark:border-purple-900/60 dark:bg-purple-950/40"
                    >
                        <p class="font-bold text-black dark:text-purple-300">
                            Kelengkapan Berkas Wajib:
                        </p>
                        <p
                            class="text-[11px] leading-relaxed text-black dark:text-slate-400"
                        >
                            1. Naskah Lengkap Skripsi (Bab 1 - Bab 5)<br />
                            2. Lembar Persetujuan Pembimbing & Beban Bimbingan
                            (min. 8x)<br />
                            3. Bukti Bebas Plagiasi Turnitin (&lt; 20%) &
                            Sertifikat IAET
                        </p>
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <button
                        type="button"
                        @click="isModalDaftarOpen = false"
                        class="cursor-pointer rounded-xl px-4 py-2 text-xs font-bold text-black hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        @click="submitDaftarSidang"
                        class="cursor-pointer rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white shadow-md transition-all hover:bg-blue-700 active:scale-95"
                    >
                        Ajukan Permohonan Sidang
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL FORM: PENGAJUAN RESCHEDULE JADWAL SIDANG (Proposal: "Dosen: Melihat Jadwal Seminar extend mengajukan reschedule") -->
        <div
            v-if="isModalRescheduleOpen"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/60 p-4 backdrop-blur-xs"
        >
            <div
                class="w-full max-w-md space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                >
                    <h3
                        class="text-base font-extrabold text-black dark:text-white"
                    >
                        Pengajuan Reschedule Sidang TA
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
                        <p
                            class="text-[11px] font-normal text-black dark:text-slate-300"
                        >
                            Jadwal Saat Ini: {{ selectedStudent.tanggal }} ({{
                                selectedStudent.waktu
                            }}
                            WITA)
                        </p>
                    </div>

                    <!-- Alasan Reschedule -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300">
                            Alasan Berhalangan / Reschedule
                            <span class="text-rose-500">*</span>
                        </label>
                        <select
                            v-model="formReschedule.alasan"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-semibold text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        >
                            <option value="Bentrok Jadwal Mengajar / Kuliah">
                                Bentrok Jadwal Mengajar / Kuliah
                            </option>
                            <option value="Tugas Dinas Luar / Konferensi">
                                Tugas Dinas Luar / Konferensi
                            </option>
                            <option value="Keperluan Akademik Lain">
                                Keperluan Akademik Lain
                            </option>
                            <option value="Izin / Sakit">Izin / Sakit</option>
                        </select>
                    </div>

                    <!-- Usulan Hari & Waktu -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label
                                class="font-bold text-black dark:text-slate-300"
                                >Usulan Hari / Tanggal</label
                            >
                            <input
                                v-model="formReschedule.usulanHari"
                                type="text"
                                placeholder="Contoh: Senin, 12 Okt 2026"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                        <div class="space-y-1">
                            <label
                                class="font-bold text-black dark:text-slate-300"
                                >Usulan Jam</label
                            >
                            <input
                                v-model="formReschedule.usulanWaktu"
                                type="text"
                                placeholder="Contoh: 10:00 - 11:30"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                            />
                        </div>
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="space-y-1">
                        <label class="font-bold text-black dark:text-slate-300"
                            >Catatan Tambahan untuk Koorprodi</label
                        >
                        <textarea
                            v-model="formReschedule.catatan"
                            rows="2"
                            placeholder="Tuliskan keterangan detail bentrok jadwal..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-black focus:border-blue-500 focus:outline-hidden dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        ></textarea>
                    </div>
                </div>

                <div
                    class="flex items-center justify-end gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
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
