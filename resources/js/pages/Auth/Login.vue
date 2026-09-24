<script setup lang="ts">
import { computed, onMounted, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import {
    Calendar as CalendarIcon,
    ChevronLeft,
    ChevronRight,
    Clock,
    Eye,
    EyeOff,
    FileCheck,
    FileText,
    GraduationCap,
    Lock,
    Mail,
    MapPin,
    Moon,
    Sun,
    User,
    Users,
    X,
} from "lucide-vue-next";

import { useTheme } from "@/composables/useTheme";
import CompactCalendar from "@/Components/CompactCalendar.vue";
import WeekSelector from "@/Components/WeekSelector.vue";
import WeeklyAgendaList from "@/Components/WeeklyAgendaList.vue";

const { isDarkMode, toggleDarkMode, initializeTheme } = useTheme();

onMounted(() => {
    initializeTheme();
});

const showPassword = ref(false);

const form = useForm({
    email: "",
    password: "",
    remember: false,
    demo_role: "",
});

const submitLogin = () => {
    form.demo_role = "";
    form.post("/login", {
        preserveScroll: true,
        onError: () => {
            form.reset("password");
        },
    });
};

const quickLoginAs = (role: "mahasiswa" | "dosen" | "kaprodi" | "tendik") => {
    const credentials = {
        mahasiswa: { email: "11231006", password: "password" },
        dosen: { email: "198504122010121003", password: "password" },
        kaprodi: { email: "198003152005011002", password: "password" },
        tendik: { email: "199208192018032001", password: "password" },
    };

    form.email = credentials[role].email;
    form.password = credentials[role].password;
    form.demo_role = role;
    form.post("/login");
};

interface SessionDetail {
    id: string | number;
    type: "Sempro" | "Sidang" | "Admin";
    title: string;
    studentName?: string;
    studentNim?: string;
    prodi?: string;
    angkatan?: string;
    time: string;
    room?: string;
    building?: string;
    mode?: string;
    judul?: string;
    pembimbing?: { name: string; nip: string; role: string }[];
    penguji?: { name: string; nip: string; role: string }[];
    notes?: string;
}

interface CalendarEvent {
    id: string | number;
    title: string;
    type: "primary" | "warning" | "danger" | "success" | "info" | "today";
    detail?: SessionDetail;
    sessions?: SessionDetail[];
}

interface CalendarDayItem {
    day: number;
    dateString: string;
    currentMonth: boolean;
    isToday?: boolean;
    events: CalendarEvent[];
}

const isModalOpen = ref(false);
const activeModalView = ref<"agenda" | "detail">("agenda");
const selectedDay = ref<CalendarDayItem | null>(null);
const selectedSession = ref<SessionDetail | null>(null);

// Mobile calendar view mode
const mobileCalendarView = ref<"list" | "grid">("list");
const currentMonth = ref("Oktober 2026");

const calendarDays: CalendarDayItem[] = [
    {
        day: 29,
        dateString: "Selasa, 29 September 2026",
        currentMonth: false,
        events: [
            {
                id: "e-29",
                title: "Bimbingan Mandiri",
                type: "info",
                detail: {
                    id: "d-29",
                    type: "Admin",
                    title: "Konsultasi Bimbingan Mandiri",
                    time: "09:00 - 15:00 WITA",
                    notes: "Konsultasi kelengkapan dokumen proposal bersama dosen pembimbing.",
                },
            },
        ],
    },
    {
        day: 30,
        dateString: "Rabu, 30 September 2026",
        currentMonth: false,
        events: [],
    },
    {
        day: 31,
        dateString: "Kamis, 31 September 2026",
        currentMonth: false,
        events: [],
    },
    {
        day: 1,
        dateString: "Kamis, 1 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-1-1",
                title: "Buka Pendaftaran Gel. 2",
                type: "success",
                detail: {
                    id: "d-1",
                    type: "Admin",
                    title: "Pembukaan Pendaftaran Gelombang 2",
                    time: "08:00 - 23:59 WITA",
                    notes: "Pendaftaran Seminar Proposal dan Sidang Tugas Akhir Periode Gasal 2026/2027 resmi dibuka secara daring melalui portal SIPTA IF.",
                },
            },
            {
                id: "e-1-2",
                title: "Briefing Dosen Pembimbing",
                type: "info",
                detail: {
                    id: "d-1-2",
                    type: "Admin",
                    title: "Briefing Tim Dosen Pembimbing TA",
                    time: "13:30 - 15:00 WITA",
                    notes: "Penyelarasan standar rubrik bimbingan dan verifikasi logbook digital mahasiswa.",
                },
            },
            {
                id: "e-1-3",
                title: "Verifikasi Usulan Topik TA",
                type: "primary",
                detail: {
                    id: "d-1-3",
                    type: "Admin",
                    title: "Verifikasi Usulan Topik oleh KBK",
                    time: "09:00 - 16:00 WITA",
                    notes: "Pemeriksaan kesesuaian roadmap riset informatika ITK oleh Koordinator KBK.",
                },
            },
        ],
    },
    {
        day: 2,
        dateString: "Jumat, 2 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 3,
        dateString: "Sabtu, 3 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-3-1",
                title: "Sempro: Akmal Falah (09:00)",
                type: "primary",
                sessions: [
                    {
                        id: "s-3-1",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Akmal Falah Maulana",
                        studentNim: "11231006",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2023",
                        time: "09:00 - 10:30 WITA",
                        room: "Ruang Lab Riset Multimedia",
                        building: "Gedung A, Lantai 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Sistem Penjadwalan Seminar dan Chatbot Layanan Akademik Menggunakan Algoritma Genetika dan Large Language Model",
                        pembimbing: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Co-Pembimbing",
                            },
                        ],
                        penguji: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 2",
                            },
                        ],
                        notes: "Terbuka untuk mahasiswa Informatika sebagai peserta seminar pendengar. Mahasiswa wajib hadir 15 menit sebelum acara dimulai dengan jas almamater.",
                    },
                ],
            },
            {
                id: "e-3-2",
                title: "Sempro: Bagus Pratama (10:45)",
                type: "primary",
                sessions: [
                    {
                        id: "s-3-2",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Bagus Pratama Hendrawan",
                        studentNim: "11221089",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "10:45 - 12:15 WITA",
                        room: "Ruang Lab Jaringan & Keamanan Siber",
                        building: "Gedung B, Lantai 3",
                        mode: "Tatap Muka (Offline)",
                        judul: "Implementasi Protokol LoRaWAN untuk Pemantauan Kualitas Air Tambak Berbasis IoT di Kawasan Pesisir Balikpapan",
                        pembimbing: [
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Co-Pembimbing",
                            },
                        ],
                        penguji: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Ir. Budi Santoso, M.Eng.",
                                nip: "198003152005011002",
                                role: "Penguji 2",
                            },
                        ],
                        notes: "Pengujian prototipe modul transmisi frekuensi 915 MHz dan simulasi paket data.",
                    },
                ],
            },
            {
                id: "e-3-3",
                title: "Batas Input Nilai Sempro",
                type: "info",
                detail: {
                    id: "d-3-3",
                    type: "Admin",
                    title: "Batas Akhir Input Penilaian Sempro oleh Tim Penguji",
                    time: "23:59 WITA",
                    notes: "Dosen penguji diharapkan menyelesaikan pengisian form penilaian di portal sebelum batas waktu.",
                },
            },
        ],
    },
    {
        day: 4,
        dateString: "Minggu, 4 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 5,
        dateString: "Senin, 5 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-5-1",
                title: "Sempro: Dinda Rahmadani (13:30)",
                type: "primary",
                sessions: [
                    {
                        id: "s-5-1",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Dinda Rahmadani",
                        studentNim: "11221012",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "13:30 - 15:00 WITA",
                        room: "Ruang Sidang Jurusan JSTI",
                        building: "Gedung Lab Terpadu Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Fine-Tuning IndoBERT untuk Analisis Sentimen Kebijakan Transportasi Publik IKN pada Media Sosial Twitter/X",
                        pembimbing: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Pembimbing Utama",
                            },
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Co-Pembimbing",
                            },
                        ],
                        penguji: [
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 2",
                            },
                        ],
                        notes: "Presentasi metode scraping API Twitter dan data preprocessing teks berbahasa Indonesia.",
                    },
                ],
            },
            {
                id: "e-5-2",
                title: "Klinik Penulisan Naskah TA",
                type: "info",
                detail: {
                    id: "d-5-2",
                    type: "Admin",
                    title: "Klinik Tata Tulis Format Tugas Akhir",
                    time: "10:00 - 11:30 WITA",
                    room: "Lab Pemrograman Komputer",
                    notes: "Bimbingan tata tulis sitasi Mendeley/Zotero dan template baku naskah skripsi ITK.",
                },
            },
            {
                id: "e-5-3",
                title: "Konsultasi Bebas Plagiasi",
                type: "success",
                detail: {
                    id: "d-5-3",
                    type: "Admin",
                    title: "Layanan Pra-Cek Turnitin Mahasiswa",
                    time: "15:30 - 17:00 WITA",
                    notes: "Pengecekan mandiri bab 1 hingga bab 3 dengan batas maksimal similarity 20%.",
                },
            },
        ],
    },
    {
        day: 6,
        dateString: "Selasa, 6 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 7,
        dateString: "Rabu, 7 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-7-1",
                title: "Bimbingan Mandiri Dosen",
                type: "info",
                detail: {
                    id: "d-7-1",
                    type: "Admin",
                    title: "Sesi Bimbingan Rutin Mingguan",
                    time: "09:00 - 12:00 WITA",
                    notes: "Konsultasi kemajuan penelitian mahasiswa bersama dosen pembimbing.",
                },
            },
            {
                id: "e-7-2",
                title: "Rapat Tim Tugas Akhir",
                type: "warning",
                detail: {
                    id: "d-7-2",
                    type: "Admin",
                    title: "Rapat Evaluasi Penjadwalan Sidang",
                    time: "14:00 - 15:30 WITA",
                    notes: "Koordinasi Kaprodi, Koordinator TA, dan staf Tendik terkait ketersediaan ruangan.",
                },
            },
        ],
    },
    {
        day: 8,
        dateString: "Kamis, 8 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 9,
        dateString: "Jumat, 9 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-9-1",
                title: "Sidang TA: Dimas Wahyu (08:30)",
                type: "warning",
                sessions: [
                    {
                        id: "s-9-1",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Dimas Wahyu Pratama",
                        studentNim: "11211044",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "08:30 - 10:30 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A, Lantai 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Deteksi Kerusakan Permukaan Jalan Tol IKN Balikpapan Menggunakan Arsitektur YOLOv10 dan Edge Computing",
                        pembimbing: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Co-Pembimbing",
                            },
                        ],
                        penguji: [
                            {
                                name: "Ir. Budi Santoso, M.Eng.",
                                nip: "198003152005011002",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 2",
                            },
                        ],
                        notes: "Ujian Sidang Tertutup Skripsi. Mahasiswa wajib berpakaian resmi kemeja putih, dasi, dan jas almamater.",
                    },
                ],
            },
            {
                id: "e-9-2",
                title: "Sidang TA: Farhan K. (13:30)",
                type: "warning",
                sessions: [
                    {
                        id: "s-9-2",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Farhan Kurniawan",
                        studentNim: "11211078",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "13:30 - 15:30 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A, Lantai 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Rancang Bangun Sistem Informasi Rekomendasi Karir Berbasis Graph Neural Network untuk Lulusan ITK",
                        pembimbing: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Pembimbing Utama",
                            },
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Co-Pembimbing",
                            },
                        ],
                        penguji: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Penguji 2",
                            },
                        ],
                        notes: "Demonstrasi sistem berbasis web dan evaluasi akurasi metrik precision@k.",
                    },
                ],
            },
            {
                id: "e-9-3",
                title: "Bimbingan Skripsi Gel. 2",
                type: "info",
                detail: {
                    id: "d-9-3",
                    type: "Admin",
                    title: "Konsultasi Perbaikan Metodologi Penelitian",
                    time: "10:30 - 12:00 WITA",
                    notes: "Konsultasi khusus mahasiswa bimbingan KBK Rekayasa Perangkat Lunak.",
                },
            },
            {
                id: "e-9-4",
                title: "Unggah Revisi Sempro Tahap 1",
                type: "primary",
                detail: {
                    id: "d-9-4",
                    type: "Admin",
                    title: "Batas Unggah Naskah Proposal Telah Direvisi",
                    time: "23:59 WITA",
                    notes: "Unggah lembar persetujuan perbaikan sempro yang telah ditandatangani penguji.",
                },
            },
        ],
    },
    {
        day: 10,
        dateString: "Sabtu, 10 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 11,
        dateString: "Minggu, 11 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 12,
        dateString: "Senin, 12 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 13,
        dateString: "Selasa, 13 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-13-1",
                title: "Sempro: Nurul Azizah (09:00)",
                type: "primary",
                sessions: [
                    {
                        id: "s-13-1",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Nurul Azizah",
                        studentNim: "11221033",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "09:00 - 10:30 WITA",
                        room: "Ruang Seminar Informatika",
                        building: "Gedung A Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Penerapan Algoritma K-Means dan Random Forest untuk Klasterisasi Wilayah Rentan Banjir di Balikpapan",
                        pembimbing: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Ir. Budi Santoso, M.Eng.",
                                nip: "198003152005011002",
                                role: "Penguji 1",
                            },
                        ],
                        notes: "Terbuka untuk mahasiswa pendengar.",
                    },
                ],
            },
            {
                id: "e-13-2",
                title: "Sempro: Rizky Maulana (10:45)",
                type: "primary",
                sessions: [
                    {
                        id: "s-13-2",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Rizky Maulana",
                        studentNim: "11221055",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "10:45 - 12:15 WITA",
                        room: "Ruang Seminar Informatika",
                        building: "Gedung A Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Pengembangan Aplikasi Mobile Edukasi Konservasi Terumbu Karang Teluk Balikpapan dengan Gamifikasi",
                        pembimbing: [
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 1",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-13-3",
                title: "Penyerahan Lembar Revisi",
                type: "info",
                detail: {
                    id: "d-13-3",
                    type: "Admin",
                    title: "Batas Penyerahan Bukti Revisi Sempro",
                    time: "16:00 WITA",
                    notes: "Penyerahan lembar pengesahan tanda tangan dosen penguji ke loket Tendik.",
                },
            },
        ],
    },
    {
        day: 14,
        dateString: "Rabu, 14 Oktober 2026",
        currentMonth: true,
        isToday: true,
        events: [
            {
                id: "e-14-1",
                title: "Hari Ini",
                type: "today",
            },
            {
                id: "e-14-2",
                title: "Sosialisasi Turnitin TA (10:00)",
                type: "info",
                detail: {
                    id: "d-14",
                    type: "Admin",
                    title: "Sosialisasi Pengecekan Turnitin & Unggah Bebas Plagiasi",
                    time: "10:00 - 11:30 WITA",
                    room: "Ruang Aula Gedung D Lt. 3 & Zoom",
                    notes: "Wajib dihadiri seluruh mahasiswa tingkat akhir yang memprogram mata kuliah Tugas Akhir periode Gasal 2026/2027.",
                },
            },
            {
                id: "e-14-3",
                title: "Buka Pengajuan Judul Susulan",
                type: "success",
                detail: {
                    id: "d-14-3",
                    type: "Admin",
                    title: "Pembukaan Pengajuan Judul Skripsi Gelombang Susulan",
                    time: "08:00 - 23:59 WITA",
                    notes: "Khusus mahasiswa angkatan 2020 dan 2021 yang belum memiliki judul terverifikasi.",
                },
            },
        ],
    },
    {
        day: 15,
        dateString: "Kamis, 15 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 16,
        dateString: "Jumat, 16 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 17,
        dateString: "Sabtu, 17 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-17-1",
                title: "Sidang TA: Kevin Jonathan (09:00)",
                type: "warning",
                sessions: [
                    {
                        id: "s-17-1",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Kevin Jonathan",
                        studentNim: "11211029",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "09:00 - 11:00 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Optimasi Hyperparameter Arsitektur Vision Transformer (ViT) untuk Klasifikasi Penyakit Daun Kelapa Sawit",
                        pembimbing: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Penguji 1 (Ketua)",
                            },
                            {
                                name: "Ir. Budi Santoso, M.Eng.",
                                nip: "198003152005011002",
                                role: "Penguji 2",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-17-2",
                title: "Sidang TA: Hendra Kusuma (13:00)",
                type: "warning",
                sessions: [
                    {
                        id: "s-17-2",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Hendra Kusuma Jaya",
                        studentNim: "11211050",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "13:00 - 15:00 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Sistem Deteksi Phishing URL Menggunakan Kombinasi Fitur Lexical dan Transformer BERT",
                        pembimbing: [
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-17-3",
                title: "Batas Validasi Berkas",
                type: "danger",
                detail: {
                    id: "d-17-3",
                    type: "Admin",
                    title: "Batas Validasi Berkas Sidang Kelulusan",
                    time: "17:00 WITA",
                    notes: "Batas verifikasi persetujuan draf laporan skripsi oleh koordinator KBK.",
                },
            },
        ],
    },
    {
        day: 18,
        dateString: "Minggu, 18 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 19,
        dateString: "Senin, 19 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-19-1",
                title: "Bimbingan Akbar Pra-Sidang",
                type: "info",
                detail: {
                    id: "d-19",
                    type: "Admin",
                    title: "Sesi Bimbingan Akbar Menjelang Sidang",
                    time: "13:00 - 16:00 WITA",
                    room: "Lab Rekayasa Perangkat Lunak",
                    notes: "Konsultasi kelengkapan logbook minimal 8 kali dan validasi tanda tangan persetujuan dosen pembimbing.",
                },
            },
            {
                id: "e-19-2",
                title: "Verifikasi Bebas Pustaka ITK",
                type: "success",
                detail: {
                    id: "d-19-2",
                    type: "Admin",
                    title: "Layanan Pengurusan Surat Bebas Pustaka",
                    time: "09:00 - 15:00 WITA",
                    notes: "Pengembalian buku pinjaman dan verifikasi sumbangan buku skripsi.",
                },
            },
        ],
    },
    {
        day: 20,
        dateString: "Selasa, 20 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-20-1",
                title: "Sempro: Aditya Pratama (09:00)",
                type: "primary",
                sessions: [
                    {
                        id: "s-20-1",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Aditya Pratama Ramadhan",
                        studentNim: "11221042",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "09:00 - 10:30 WITA",
                        room: "Ruang Seminar Informatika",
                        building: "Gedung A Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Rancang Bangun Sistem Pemantauan Emisi Karbon Berbasis IoT Menggunakan Blockchain Hyperledger Fabric",
                        pembimbing: [
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-20-2",
                title: "Sidang TA: Anita Sari (10:30)",
                type: "warning",
                sessions: [
                    {
                        id: "s-20-2",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Anita Sari Dewi",
                        studentNim: "11211015",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "10:30 - 12:30 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Analisis User Experience Aplikasi Presensi Kampus Berbasis Pengenalan Wajah dengan Metode HEART Framework",
                        pembimbing: [
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-20-3",
                title: "Yudisium Periode Gasal",
                type: "success",
                detail: {
                    id: "d-20-3",
                    type: "Admin",
                    title: "Rapat Pleno Yudisium Kelulusan Gasal",
                    time: "14:00 - 16:00 WITA",
                    room: "Ruang Rapat Jurusan JSTI",
                    notes: "Penetapan kelulusan yudisium bagi mahasiswa Informatika yang telah menyelesaikan sidang dan revisi.",
                },
            },
            {
                id: "e-20-4",
                title: "Penyerahan Hardcover Skripsi",
                type: "info",
                detail: {
                    id: "d-20-4",
                    type: "Admin",
                    title: "Batas Penyerahan Buku Skripsi Hardcover",
                    time: "16:30 WITA",
                    notes: "Pengumpulan 2 eksemplar hardcover warna biru donker ke ruang administrasi Tendik.",
                },
            },
        ],
    },
    {
        day: 21,
        dateString: "Rabu, 21 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 22,
        dateString: "Kamis, 22 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 23,
        dateString: "Jumat, 23 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-23-1",
                title: "Sempro: Wahyu Hidayat (09:00)",
                type: "primary",
                sessions: [
                    {
                        id: "s-23-1",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Wahyu Hidayat",
                        studentNim: "11221070",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "09:00 - 10:30 WITA",
                        room: "Ruang Lab Riset Multimedia",
                        building: "Gedung A Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Analisis Performa Model Retrieval-Augmented Generation (RAG) pada Dokumen Regulasi Akademik Kampus",
                        pembimbing: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Penguji 1",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-23-2",
                title: "Sempro: Salsabila Putri (10:45)",
                type: "primary",
                sessions: [
                    {
                        id: "s-23-2",
                        type: "Sempro",
                        title: "Seminar Proposal TA",
                        studentName: "Salsabila Putri",
                        studentNim: "11221081",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2022",
                        time: "10:45 - 12:15 WITA",
                        room: "Ruang Lab Riset Multimedia",
                        building: "Gedung A Lt. 2",
                        mode: "Tatap Muka (Offline)",
                        judul: "Sistem Pendukung Keputusan Penentuan Dosen Pembimbing TA Menggunakan Metode TOPSIS Berdasarkan Topik Riset",
                        pembimbing: [
                            {
                                name: "Rina Agustina, S.T., M.Kom.",
                                nip: "198906242015042002",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Penguji 1",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-23-3",
                title: "Review Logbook Bimbingan",
                type: "info",
                detail: {
                    id: "d-23-3",
                    type: "Admin",
                    title: "Pemeriksaan Berkala Logbook oleh Dosen Wali",
                    time: "14:00 - 16:00 WITA",
                    notes: "Verifikasi keaktifan bimbingan minimal mahasiswa sebelum pengajuan ujian lanjutan.",
                },
            },
        ],
    },
    {
        day: 24,
        dateString: "Sabtu, 24 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 25,
        dateString: "Minggu, 25 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-25-1",
                title: "Sidang TA: Aldi Firmansyah (08:30)",
                type: "warning",
                sessions: [
                    {
                        id: "s-25-1",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Aldi Firmansyah",
                        studentNim: "11211002",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "08:30 - 10:30 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Sistem Rekomendasi Tempat Magang Berbasis Content-Based Filtering & Natural Language Processing",
                        pembimbing: [
                            {
                                name: "Siti Nurhaliza, S.Kom., M.Cs.",
                                nip: "199108152019032004",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Prof. Dr. Agus Susanto, M.T.",
                                nip: "197802112003121001",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-25-2",
                title: "Sidang TA: Nabila Maharani (10:45)",
                type: "warning",
                sessions: [
                    {
                        id: "s-25-2",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "Nabila Maharani",
                        studentNim: "11211019",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "10:45 - 12:45 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Audit Keamanan Informasi Sistem Akademik Berdasarkan Kerangka Kerja ISO 27001:2022",
                        pembimbing: [
                            {
                                name: "Tejo Wahyu Utomo, S.Kom., M.Cs.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-25-3",
                title: "Sidang TA: M. Fakhri (13:30)",
                type: "warning",
                sessions: [
                    {
                        id: "s-25-3",
                        type: "Sidang",
                        title: "Sidang Tugas Akhir",
                        studentName: "M. Fakhri Al-Farizi",
                        studentNim: "11211062",
                        prodi: "S1 Informatika",
                        angkatan: "Angkatan 2021",
                        time: "13:30 - 15:30 WITA",
                        room: "Ruang Sidang Utama Informatika",
                        building: "Gedung A Lt. 3",
                        mode: "Tatap Muka (Tertutup)",
                        judul: "Segmentasi Citra MRI Tumor Otak Berbasis Modifikasi Unet++ dengan Attention Gate",
                        pembimbing: [
                            {
                                name: "Dr. Ir. Hendra Wijaya, M.Kom.",
                                nip: "198504122010121003",
                                role: "Pembimbing Utama",
                            },
                        ],
                        penguji: [
                            {
                                name: "Ir. Budi Santoso, M.Eng.",
                                nip: "198003152005011002",
                                role: "Penguji 1 (Ketua)",
                            },
                        ],
                    },
                ],
            },
            {
                id: "e-25-4",
                title: "Rapat Pleno Kelulusan TA",
                type: "success",
                detail: {
                    id: "d-25-4",
                    type: "Admin",
                    title: "Rapat Pleno Penetapan Kelulusan Ujian Skripsi",
                    time: "16:00 - 17:30 WITA",
                    notes: "Rekapitulasi berita acara penilaian sidang oleh dewan penguji Informatika.",
                },
            },
        ],
    },
    {
        day: 26,
        dateString: "Senin, 26 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 27,
        dateString: "Selasa, 27 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-27-1",
                title: "Verifikasi Berkas Tendik",
                type: "info",
                detail: {
                    id: "d-27",
                    type: "Admin",
                    title: "Verifikasi Berkas Akhir Tendik",
                    time: "09:00 - 16:00 WITA",
                    notes: "Pemeriksaan keabsahan dokumen bebas plagiasi Turnitin, sertifikat TOEFL/IAET, dan lembar bimbingan.",
                },
            },
            {
                id: "e-27-2",
                title: "Validasi Sertifikat TOEFL/IAET",
                type: "primary",
                detail: {
                    id: "d-27-2",
                    type: "Admin",
                    title: "Validasi Nilai Kemampuan Bahasa Inggris Mahasiswa",
                    time: "10:00 - 14:00 WITA",
                    notes: "Pengecekan skor minimal 450 untuk syarat kelulusan program sarjana Informatika.",
                },
            },
        ],
    },
    {
        day: 28,
        dateString: "Rabu, 28 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 29,
        dateString: "Kamis, 29 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 30,
        dateString: "Jumat, 30 Oktober 2026",
        currentMonth: true,
        events: [
            {
                id: "e-30-1",
                title: "Tutup Gel. 2 (23:59 WITA)",
                type: "danger",
                detail: {
                    id: "d-30",
                    type: "Admin",
                    title: "Penutupan Pendaftaran Gelombang 2",
                    time: "23:59 WITA",
                    notes: "Batas akhir pengunggahan proposal dan draft laporan skripsi periode Gasal 2026/2027.",
                },
            },
            {
                id: "e-30-2",
                title: "Batas Akhir Revisi Nilai Dosen",
                type: "warning",
                detail: {
                    id: "d-30-2",
                    type: "Admin",
                    title: "Batas Akhir Revisi Penilaian Ujian Dosen",
                    time: "18:00 WITA",
                    notes: "Dosen penguji mengunci seluruh nilai akhir seminar dan sidang.",
                },
            },
            {
                id: "e-30-3",
                title: "Rekapitulasi Nilai Akhir TA",
                type: "info",
                detail: {
                    id: "d-30-3",
                    type: "Admin",
                    title: "Rekapitulasi Nilai Transkrip Sementara Tendik",
                    time: "09:00 - 16:00 WITA",
                    notes: "Penyusunan transkrip nilai kelulusan tugas akhir oleh staf tendik.",
                },
            },
        ],
    },
    {
        day: 31,
        dateString: "Sabtu, 31 Oktober 2026",
        currentMonth: true,
        events: [],
    },
    {
        day: 1,
        dateString: "Minggu, 1 November 2026",
        currentMonth: false,
        events: [],
    },
];

const getAllSessions = (dayItem: CalendarDayItem | null): SessionDetail[] => {
    if (!dayItem || !dayItem.events) return [];
    const results: SessionDetail[] = [];
    dayItem.events.forEach((ev) => {
        if (ev.sessions && ev.sessions.length > 0) {
            results.push(...ev.sessions);
        } else if (ev.detail) {
            results.push(ev.detail);
        } else if (ev.type !== "today") {
            results.push({
                id: ev.id,
                type: "Admin",
                title: ev.title,
                time: "Sesuai Jadwal Terbit",
                notes: "Agenda kegiatan akademik terdaftar di sistem portal ITK.",
            });
        }
    });
    return results;
};

const openDayAgenda = (dayItem: CalendarDayItem) => {
    selectedDay.value = dayItem;
    activeModalView.value = "agenda";
    selectedSession.value = null;
    isModalOpen.value = true;
};

const openSessionDetail = (session: SessionDetail) => {
    selectedSession.value = session;
    activeModalView.value = "detail";
};

const backToAgenda = () => {
    activeModalView.value = "agenda";
    selectedSession.value = null;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        activeModalView.value = "agenda";
        selectedSession.value = null;
    }, 200);
};

// Daftar hari yang memiliki agenda (untuk tampilan mobile list)
const daysWithEvents = computed(() => {
    return calendarDays.filter(
        (day) => day.currentMonth && day.events && day.events.length > 0,
    );
});

const selectedMobileWeek = ref(0);
const mobileWeeks = computed(() => {
    return Array.from(
        { length: Math.ceil(calendarDays.length / 7) },
        (_, index) => {
            const days = calendarDays.slice(index * 7, index * 7 + 7);
            const first = days[0]?.dateString?.replace(/^[^,]+, /, "") || "";
            const last =
                days[days.length - 1]?.dateString?.replace(/^[^,]+, /, "") ||
                "";
            return {
                index,
                days,
                label: `Minggu ${index + 1}`,
                range: `${first} - ${last}`,
            };
        },
    );
});

const selectedMobileWeekDays = computed(() => {
    return (
        mobileWeeks.value[selectedMobileWeek.value]?.days.filter(
            (day) => day.currentMonth && day.events.length > 0,
        ) ?? []
    );
});

const getAgendaCount = (day: CalendarDayItem) => getAllSessions(day).length;

const getEventChipClass = (type: string) => {
    switch (type) {
        case "primary":
            return "bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100 hover:border-blue-300 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800";
        case "warning":
            return "bg-purple-50 text-purple-700 border-purple-200 hover:bg-purple-100 hover:border-purple-300 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800";
        case "danger":
            return "bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100 hover:border-rose-300 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800";
        case "success":
            return "bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800";
        case "today":
            return "bg-blue-600 text-white font-bold border-blue-700 shadow-xs dark:bg-blue-600";
        default:
            return "bg-sky-50 text-sky-700 border-sky-200 hover:bg-sky-100 hover:border-sky-300 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800";
    }
};

const getEventDotClass = (type: string) => {
    switch (type) {
        case "primary":
            return "bg-blue-500";
        case "warning":
            return "bg-purple-500";
        case "danger":
            return "bg-rose-500";
        case "success":
            return "bg-emerald-500";
        case "today":
            return "bg-white";
        default:
            return "bg-sky-500";
    }
};

onMounted(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
        if (e.key === "Escape" && isModalOpen.value) {
            closeModal();
        }
    };
    window.addEventListener("keydown", handleKeyDown);
});
</script>

<template>
    <div
        class="min-h-screen bg-[#F8FAFC] font-sans text-slate-800 antialiased dark:bg-[#080D1A] dark:text-slate-100"
    >
        <Head title="Masuk - SIPTA IF" />

        <!-- Top Header: Theme Toggle -->
        <div
            class="mx-auto flex max-w-7xl justify-end px-4 pt-6 sm:px-6 lg:px-8"
        >
            <button
                type="button"
                class="flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-slate-700 shadow-xs backdrop-blur-xs transition-all hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-900/80 dark:text-slate-200 dark:hover:bg-slate-800"
                @click="toggleDarkMode"
                :title="
                    isDarkMode
                        ? 'Beralih ke mode terang (Light mode)'
                        : 'Beralih ke mode gelap (Dark mode)'
                "
            >
                <Sun v-if="!isDarkMode" class="h-3.5 w-3.5 text-amber-500" />
                <Moon v-else class="h-3.5 w-3.5 text-blue-400" />
                <span>{{ isDarkMode ? "Dark mode" : "Light mode" }}</span>
            </button>
        </div>

        <!-- Header / Hero Section (Sesuai Mockup) -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                <!-- Left: Branding SIPTA IF -->
                <div class="space-y-4 text-center lg:text-left">
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-600 text-white shadow-xl shadow-blue-600/30 lg:mx-0"
                    >
                        <GraduationCap class="h-9 w-9" />
                    </div>
                    <h1
                        class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl dark:text-white"
                    >
                        SIPTA IF
                    </h1>
                    <p
                        class="text-sm font-semibold tracking-wider text-blue-600 uppercase dark:text-blue-400"
                    >
                        Sistem Informasi Tugas Akhir & Penjadwalan Seminar
                    </p>
                    <p
                        class="max-w-md text-xs leading-relaxed text-slate-500 sm:text-sm dark:text-slate-400"
                    >
                        Program Studi Informatika, Jurusan Sains dan Teknologi
                        Informasi, Institut Teknologi Kalimantan. Kelola seluruh
                        proses pendaftaran Sempro, Sidang, hingga logbook
                        bimbingan secara terpadu.
                    </p>
                </div>

                <!-- Right: Sign In Box (Sesuai Mockup) -->
                <div class="mx-auto w-full max-w-md">
                    <div
                        class="rounded-3xl border border-blue-100 bg-[#C2D8FF] p-8 shadow-xl dark:border-blue-900/40 dark:bg-blue-950/60"
                    >
                        <h2
                            class="text-xl font-extrabold text-slate-900 dark:text-white"
                        >
                            Sign in
                        </h2>
                        <p
                            class="mt-1 text-xs text-slate-700 dark:text-slate-300"
                        >
                            Masuk sesuai hak akses: Mahasiswa, Dosen, Kaprodi,
                            atau Tendik
                        </p>

                        <!-- Error alert -->
                        <div
                            v-if="form.errors.email"
                            class="mt-4 rounded-xl border border-rose-300 bg-rose-50 p-3 text-xs text-rose-700 dark:border-rose-900/50 dark:bg-rose-950/50 dark:text-rose-300"
                        >
                            {{ form.errors.email }}
                        </div>

                        <form
                            class="mt-4 space-y-4"
                            @submit.prevent="submitLogin"
                        >
                            <!-- Username -->
                            <div class="space-y-1">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Username / NIM / NIP / Email
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.email"
                                        type="text"
                                        placeholder="Contoh: 11231006 atau 19850412..."
                                        required
                                        class="w-full rounded-xl border border-transparent bg-white/90 px-3.5 py-2.5 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-hidden dark:bg-slate-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="space-y-1">
                                <label
                                    class="block text-xs font-bold text-slate-800 dark:text-slate-200"
                                >
                                    Password
                                </label>
                                <div class="relative">
                                    <input
                                        v-model="form.password"
                                        :type="
                                            showPassword ? 'text' : 'password'
                                        "
                                        placeholder="Masukkan password"
                                        required
                                        class="w-full rounded-xl border border-transparent bg-white/90 py-2.5 pr-10 pl-3.5 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-600 focus:bg-white focus:outline-hidden dark:bg-slate-900 dark:text-white"
                                    />
                                    <button
                                        type="button"
                                        class="absolute top-2.5 right-3 text-slate-400 hover:text-slate-600"
                                        @click="showPassword = !showPassword"
                                    >
                                        <Eye
                                            v-if="!showPassword"
                                            class="h-4 w-4"
                                        />
                                        <EyeOff v-else class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Forgot Password Link -->
                            <div
                                class="flex items-center justify-between text-[11px]"
                            >
                                <label
                                    class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300"
                                >
                                    <input
                                        v-model="form.remember"
                                        type="checkbox"
                                        class="rounded border-slate-300 text-blue-600 shadow-xs focus:ring-blue-500"
                                    />
                                    Ingat saya
                                </label>
                                <a
                                    href="/panduan"
                                    class="font-medium text-slate-600 hover:text-blue-700 dark:text-slate-400"
                                >
                                    Lupa password? Hubungi Tendik
                                </a>
                            </div>

                            <!-- Login Button -->
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full rounded-xl bg-[#0C1A40] py-3 text-xs font-bold text-white shadow-lg transition-all hover:bg-slate-900 active:scale-95 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Memproses...</span>
                                <span v-else>Masuk ke Portal</span>
                            </button>
                        </form>

                        <!-- Demo Accounts Quick Access -->
                        <div
                            class="mt-5 border-t border-blue-200/80 pt-4 dark:border-blue-900/50"
                        >
                            <p
                                class="text-[10px] font-bold tracking-wider text-slate-600 uppercase dark:text-slate-400"
                            >
                                Masuk Cepat Sebagai Akun Uji:
                            </p>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    class="flex flex-col items-start rounded-xl border border-blue-300/80 bg-white/80 p-2 text-left transition-all hover:border-blue-600 hover:bg-white active:scale-95 dark:border-blue-800 dark:bg-slate-900/80 dark:hover:bg-slate-900"
                                    @click="quickLoginAs('mahasiswa')"
                                >
                                    <span
                                        class="text-[11px] font-bold text-blue-700 dark:text-blue-300"
                                        >🎓 Mahasiswa</span
                                    >
                                    <span class="text-[9px] text-slate-500"
                                        >NIM: 11231006</span
                                    >
                                </button>
                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    class="flex flex-col items-start rounded-xl border border-blue-300/80 bg-white/80 p-2 text-left transition-all hover:border-blue-600 hover:bg-white active:scale-95 dark:border-blue-800 dark:bg-slate-900/80 dark:hover:bg-slate-900"
                                    @click="quickLoginAs('dosen')"
                                >
                                    <span
                                        class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300"
                                        >👨‍🏫 Dosen</span
                                    >
                                    <span class="text-[9px] text-slate-500"
                                        >Pembimbing & Penguji</span
                                    >
                                </button>
                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    class="flex flex-col items-start rounded-xl border border-blue-300/80 bg-white/80 p-2 text-left transition-all hover:border-blue-600 hover:bg-white active:scale-95 dark:border-blue-800 dark:bg-slate-900/80 dark:hover:bg-slate-900"
                                    @click="quickLoginAs('kaprodi')"
                                >
                                    <span
                                        class="text-[11px] font-bold text-purple-700 dark:text-purple-300"
                                        >🏛️ Kaprodi</span
                                    >
                                    <span class="text-[9px] text-slate-500"
                                        >Koordinator TA</span
                                    >
                                </button>
                                <button
                                    type="button"
                                    :disabled="form.processing"
                                    class="flex flex-col items-start rounded-xl border border-blue-300/80 bg-white/80 p-2 text-left transition-all hover:border-blue-600 hover:bg-white active:scale-95 dark:border-blue-800 dark:bg-slate-900/80 dark:hover:bg-slate-900"
                                    @click="quickLoginAs('tendik')"
                                >
                                    <span
                                        class="text-[11px] font-bold text-emerald-700 dark:text-emerald-300"
                                        >📋 Tendik</span
                                    >
                                    <span class="text-[9px] text-slate-500"
                                        >Staf Administrasi</span
                                    >
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Kalender Jadwal Publik (Lebar & Responsif) -->
        <div
            class="mx-auto max-w-[1650px] px-4 pb-16 sm:px-6 lg:px-10 xl:px-12"
        >
            <div class="mt-8 space-y-4">
                <div
                    class="flex flex-col justify-between gap-3 border-t border-slate-200 pt-8 sm:flex-row sm:items-center dark:border-slate-800"
                >
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h3
                                class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl dark:text-white"
                            >
                                JADWAL SEMINAR & SIDANG TERBIT (WITA)
                            </h3>
                            <span
                                class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                            >
                                Realtime Sync
                            </span>
                        </div>
                        <p
                            class="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600 dark:text-slate-400"
                        >
                            Pantau jadwal pelaksanaan seminar proposal dan
                            sidang skripsi terkini yang telah disinkronkan oleh
                            sistem penjadwalan.
                        </p>
                    </div>
                    <!-- View Toggle for Mobile -->
                    <div
                        class="flex items-center gap-1 rounded-xl border border-slate-200 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-900 sm:hidden"
                    >
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                            :class="
                                mobileCalendarView === 'list'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="mobileCalendarView = 'list'"
                        >
                            Daftar Agenda
                        </button>
                        <button
                            type="button"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
                            :class="
                                mobileCalendarView === 'grid'
                                    ? 'bg-blue-600 text-white shadow-xs'
                                    : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800'
                            "
                            @click="mobileCalendarView = 'grid'"
                        >
                            Kalender
                        </button>
                    </div>
                </div>

                <!-- Compact Calendar (Mobile Grid Mode Only) -->
                <div v-if="mobileCalendarView === 'grid'" class="lg:hidden">
                    <CompactCalendar
                        :days="calendarDays"
                        :current-month="currentMonth"
                        :get-event-dot-class="getEventDotClass"
                        @day-click="openDayAgenda"
                    />
                </div>

                <!-- Full Calendar Grid (Desktop Only) -->
                <div
                    class="hidden overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs dark:border-slate-800 dark:bg-[#0E1626] lg:block"
                >
                    <!-- Calendar Days Header -->
                    <div
                        class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 text-center text-xs font-bold text-slate-700 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <div class="py-3">SENIN / MON</div>
                        <div class="py-3">SELASA / TUE</div>
                        <div class="py-3">RABU / WED</div>
                        <div class="py-3">KAMIS / THU</div>
                        <div class="py-3">JUMAT / FRI</div>
                        <div class="py-3">SABTU / SAT</div>
                        <div class="py-3">MINGGU / SUN</div>
                    </div>

                    <!-- Calendar Cells Grid -->
                    <div
                        class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <div
                            v-for="(item, idx) in calendarDays"
                            :key="idx"
                            class="group relative flex min-h-48 cursor-pointer flex-col justify-between p-3 transition-all select-none hover:bg-blue-50/40 sm:min-h-56 sm:p-3.5 md:min-h-60 dark:hover:bg-slate-900/60"
                            :class="[
                                { 'opacity-40': !item.currentMonth },
                                item.isToday
                                    ? 'bg-blue-50/20 dark:bg-blue-950/10'
                                    : '',
                            ]"
                            @click="openDayAgenda(item)"
                        >
                            <!-- Day Number Header -->
                            <div class="flex items-start justify-between">
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-transform group-hover:scale-110 sm:h-8 sm:w-8 sm:text-sm"
                                    :class="
                                        item.isToday
                                            ? 'bg-blue-600 text-white shadow-xs'
                                            : 'text-slate-700 dark:text-slate-300'
                                    "
                                >
                                    {{ item.day }}
                                </span>

                                <span
                                    v-if="item.events.length > 0"
                                    class="flex items-center gap-1 rounded-md bg-blue-50/80 px-1.5 py-0.5 text-[10px] font-bold text-blue-600 opacity-0 transition-all group-hover:opacity-100 dark:bg-blue-950/60 dark:text-blue-400"
                                >
                                    <span>Buka</span>
                                    <ChevronRight class="h-3 w-3" />
                                </span>
                            </div>

                            <!-- Events Badges inside Day Cell: Google Calendar Style (Max 3) -->
                            <div class="mt-3 space-y-2">
                                <div
                                    v-for="(ev, eIdx) in item.events.slice(
                                        0,
                                        3,
                                    )"
                                    :key="eIdx"
                                    class="flex items-center justify-between gap-2 truncate rounded-lg border px-2.5 py-1.5 text-[11px] font-semibold shadow-2xs transition-all hover:scale-[1.01]"
                                    :class="getEventChipClass(ev.type)"
                                    :title="ev.title"
                                >
                                    <div
                                        class="flex items-center gap-2 truncate"
                                    >
                                        <span
                                            class="h-2 w-2 shrink-0 rounded-full"
                                            :class="getEventDotClass(ev.type)"
                                        />
                                        <span class="truncate font-medium">{{
                                            ev.title
                                        }}</span>
                                    </div>
                                    <ChevronRight
                                        class="h-3.5 w-3.5 shrink-0 opacity-40 transition-opacity group-hover:opacity-100"
                                    />
                                </div>

                                <!-- "+X more" if > 3 events -->
                                <div
                                    v-if="item.events.length > 3"
                                    class="pt-0.5 pl-1.5 text-[10.5px] font-bold text-slate-500 transition-colors hover:text-blue-600 dark:text-slate-400"
                                >
                                    +{{ item.events.length - 3 }} agenda lainnya
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Agenda List View -->
                <div
                    v-if="mobileCalendarView === 'list'"
                    class="space-y-4 lg:hidden"
                >
                    <WeekSelector
                        :weeks="mobileWeeks"
                        :selected-week="selectedMobileWeek"
                        @select-week="selectedMobileWeek = $event"
                    />
                    <WeeklyAgendaList
                        :days="selectedMobileWeekDays"
                        :max-events="2"
                        :get-event-dot-class="getEventDotClass"
                        :get-agenda-count="getAgendaCount"
                        @open-day="openDayAgenda"
                    />
                </div>
            </div>
        </div>

        <!-- MODAL DETAIL AGENDA & JADWAL SIDANG/SEMPRO (OPSI A: In-Modal Drilldown) -->
        <div
            v-if="isModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-3 backdrop-blur-sm transition-all sm:p-6"
            @click.self="closeModal"
        >
            <div
                class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-3xl border border-slate-200/90 bg-white text-slate-800 shadow-2xl transition-all sm:max-w-3xl lg:max-w-4xl dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-100"
            >
                <!-- ============================================== -->
                <!-- VIEW 1: DAFTAR AGENDA PADA HARI TERPILIH      -->
                <!-- ============================================== -->
                <template v-if="activeModalView === 'agenda'">
                    <!-- Header Modal View 1 -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800/80"
                    >
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3
                                    class="text-base font-bold text-slate-900 sm:text-lg dark:text-white"
                                >
                                    Agenda & Jadwal Kegiatan
                                </h3>
                                <span
                                    class="rounded-full bg-blue-100 px-2.5 py-0.5 text-[10px] font-extrabold text-blue-700 dark:bg-blue-950 dark:text-blue-300"
                                >
                                    {{ getAllSessions(selectedDay).length }}
                                    Agenda
                                </span>
                            </div>
                            <p
                                class="mt-0.5 flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <CalendarIcon
                                    class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                />
                                {{ selectedDay?.dateString || "Oktober 2026" }}
                            </p>
                        </div>

                        <!-- Tombol Close Modal (Sesuai Permintaan User) -->
                        <button
                            type="button"
                            class="flex h-8 w-8 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            title="Tutup Modal (Esc)"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body List Sessions View 1 -->
                    <div class="flex-1 space-y-3.5 overflow-y-auto p-5 sm:p-6">
                        <template v-if="getAllSessions(selectedDay).length > 0">
                            <div
                                v-for="(session, sIdx) in getAllSessions(
                                    selectedDay,
                                )"
                                :key="sIdx"
                            >
                                <!-- Card Sesi Akademik: Sempro atau Sidang TA -->
                                <div
                                    v-if="
                                        session.type === 'Sempro' ||
                                        session.type === 'Sidang'
                                    "
                                    class="group cursor-pointer rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4.5 transition-all duration-150 hover:border-blue-500/80 hover:bg-blue-50/30 hover:shadow-md dark:border-slate-800 dark:bg-slate-900/60 dark:hover:border-blue-500/50 dark:hover:bg-blue-950/20"
                                    @click="openSessionDetail(session)"
                                >
                                    <!-- Row 1: Badges, Time & Detail Arrow -->
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <div
                                            class="flex flex-wrap items-center gap-2"
                                        >
                                            <span
                                                class="rounded-lg px-2.5 py-1 text-[10.5px] font-bold tracking-wider uppercase"
                                                :class="
                                                    session.type === 'Sidang'
                                                        ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                                        : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                                "
                                            >
                                                {{
                                                    session.type === "Sidang"
                                                        ? "Sidang Tugas Akhir"
                                                        : "Seminar Proposal TA"
                                                }}
                                            </span>
                                            <span
                                                class="flex items-center gap-1 text-xs font-semibold text-slate-600 dark:text-slate-300"
                                            >
                                                <Clock
                                                    class="h-3.5 w-3.5 text-slate-400"
                                                />
                                                {{ session.time }}
                                            </span>
                                        </div>

                                        <div
                                            class="flex shrink-0 items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-600 transition-all group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-950/60 dark:text-blue-300 dark:group-hover:bg-blue-600 dark:group-hover:text-white"
                                        >
                                            <span>Lihat Detail</span>
                                            <ChevronRight
                                                class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5"
                                            />
                                        </div>
                                    </div>

                                    <!-- Row 2: Title & Student Name -->
                                    <div class="mt-3">
                                        <h4
                                            class="line-clamp-2 text-sm leading-snug font-bold text-slate-900 transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                                        >
                                            {{ session.judul || session.title }}
                                        </h4>
                                        <div
                                            class="mt-1.5 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-600 dark:text-slate-300"
                                        >
                                            <span
                                                class="font-bold text-slate-800 dark:text-slate-200"
                                            >
                                                {{ session.studentName }}
                                            </span>
                                            <span
                                                class="text-slate-300 dark:text-slate-700"
                                                >•</span
                                            >
                                            <span
                                                class="text-slate-500 dark:text-slate-400"
                                            >
                                                NIM: {{ session.studentNim }}
                                            </span>
                                            <span
                                                class="text-slate-300 dark:text-slate-700"
                                                >•</span
                                            >
                                            <span
                                                class="text-slate-500 dark:text-slate-400"
                                            >
                                                {{
                                                    session.prodi ||
                                                    "S1 Informatika"
                                                }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Row 3: Meta Footer (Ruangan & Dosen Pembimbing Singkat) -->
                                    <div
                                        class="mt-3.5 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/70 pt-3 text-xs text-slate-500 dark:border-slate-800/80 dark:text-slate-400"
                                    >
                                        <div
                                            class="flex items-center gap-1.5 text-slate-700 dark:text-slate-300"
                                        >
                                            <MapPin
                                                class="h-3.5 w-3.5 shrink-0 text-rose-500"
                                            />
                                            <span class="font-medium">{{
                                                session.room
                                            }}</span>
                                            <span class="text-slate-400"
                                                >({{ session.building }})</span
                                            >
                                        </div>

                                        <div
                                            v-if="
                                                session.pembimbing &&
                                                session.pembimbing.length > 0
                                            "
                                            class="truncate text-[11px]"
                                        >
                                            <span
                                                class="font-semibold text-slate-700 dark:text-slate-300"
                                            >
                                                Pembimbing:
                                            </span>
                                            {{ session.pembimbing[0].name }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Card Kegiatan Administratif / Umum -->
                                <div
                                    v-else
                                    class="rounded-2xl border border-slate-200/90 bg-slate-50/70 p-4.5 dark:border-slate-800 dark:bg-slate-900/60"
                                >
                                    <div
                                        class="flex items-center justify-between gap-2"
                                    >
                                        <span
                                            class="rounded-lg bg-emerald-100 px-2.5 py-1 text-[10.5px] font-bold tracking-wider text-emerald-700 uppercase dark:bg-emerald-950 dark:text-emerald-300"
                                        >
                                            Pengumuman & Agenda Umum
                                        </span>
                                        <span
                                            class="flex items-center gap-1 text-xs font-semibold text-slate-500 dark:text-slate-400"
                                        >
                                            <Clock class="h-3.5 w-3.5" />
                                            {{ session.time }}
                                        </span>
                                    </div>

                                    <h4
                                        class="mt-2 text-sm font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ session.title }}
                                    </h4>
                                    <p
                                        class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-300"
                                    >
                                        {{ session.notes }}
                                    </p>
                                    <div
                                        v-if="session.room"
                                        class="mt-2.5 flex items-center gap-1.5 border-t border-slate-200/70 pt-2 text-xs text-slate-600 dark:border-slate-800/80 dark:text-slate-400"
                                    >
                                        <MapPin
                                            class="h-3.5 w-3.5 text-rose-500"
                                        />
                                        <span>{{ session.room }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State jika tidak ada sesi -->
                        <div v-else class="py-12 text-center text-slate-400">
                            <CalendarIcon
                                class="mx-auto mb-3 h-12 w-12 stroke-[1.5] text-slate-300 dark:text-slate-600"
                            />
                            <p
                                class="text-sm font-bold text-slate-700 dark:text-slate-300"
                            >
                                Tidak ada jadwal pada tanggal ini
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                Belum ada sesi seminar atau sidang yang
                                diterbitkan pada hari ini.
                            </p>
                        </div>
                    </div>

                    <!-- Footer Modal View 1 -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 sm:px-6 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <span
                            class="hidden text-[11px] text-slate-500 sm:inline dark:text-slate-400"
                        >
                            Tip: Klik salah satu kartu seminar/sidang untuk
                            melihat informasi penguji dan judul lengkap.
                        </span>
                        <span
                            class="text-[11px] text-slate-500 sm:hidden dark:text-slate-400"
                        >
                            Klik kartu untuk melihat detail lengkap.
                        </span>

                        <button
                            type="button"
                            class="ml-auto rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="closeModal"
                        >
                            Tutup
                        </button>
                    </div>
                </template>

                <!-- ============================================== -->
                <!-- VIEW 2: DETAIL LENGKAP SIDANG / SEMPRO        -->
                <!-- ============================================== -->
                <template
                    v-else-if="activeModalView === 'detail' && selectedSession"
                >
                    <!-- Header Modal View 2: Tombol Kembali di kiri atas dengan simbol panah kanan & Tombol Close di kanan atas -->
                    <div
                        class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800/80"
                    >
                        <div class="flex items-center gap-2 overflow-hidden">
                            <button
                                type="button"
                                class="flex shrink-0 items-center gap-1.5 rounded-xl border border-slate-200/90 bg-slate-50 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 active:scale-95 dark:border-slate-800 dark:bg-slate-900/70 dark:text-slate-200 dark:hover:border-blue-800 dark:hover:bg-blue-950/40 dark:hover:text-blue-400"
                                @click="backToAgenda"
                            >
                                <ChevronLeft class="h-4 w-4" />
                                <span>Kembali</span>
                            </button>

                            <!-- Simbol ke arah kanan (Breadcrumb ke Detail) -->
                            <ChevronRight
                                class="h-4 w-4 shrink-0 text-slate-400 dark:text-slate-600"
                            />

                            <div
                                class="flex items-center gap-1.5 truncate text-xs font-semibold text-slate-500 dark:text-slate-400"
                            >
                                <span class="hidden sm:inline"
                                    >Agenda {{ selectedDay?.day }} Okt</span
                                >
                                <ChevronRight
                                    class="hidden h-3.5 w-3.5 shrink-0 text-slate-300 sm:inline dark:text-slate-600"
                                />
                                <span
                                    class="truncate font-bold text-slate-800 dark:text-slate-200"
                                    >Detail Sesi</span
                                >
                            </div>
                        </div>

                        <!-- Tombol Close Modal (Sesuai Permintaan User) -->
                        <button
                            type="button"
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                            title="Tutup Modal (Esc)"
                            @click="closeModal"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Body Detail View 2 -->
                    <div class="flex-1 space-y-5 overflow-y-auto p-5 sm:p-6">
                        <!-- Category Badge & Format -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-2"
                        >
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-xl px-3 py-1 text-xs font-bold tracking-wider uppercase shadow-2xs"
                                    :class="
                                        selectedSession.type === 'Sidang'
                                            ? 'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300'
                                            : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300'
                                    "
                                >
                                    {{
                                        selectedSession.type === "Sidang"
                                            ? "Sidang Tugas Akhir (Skripsi)"
                                            : "Seminar Proposal Tugas Akhir"
                                    }}
                                </span>

                                <span
                                    class="rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
                                >
                                    {{
                                        selectedSession.mode ||
                                        "Tatap Muka (Offline)"
                                    }}
                                </span>
                            </div>

                            <span
                                class="flex items-center gap-1.5 text-xs font-bold text-emerald-600 dark:text-emerald-400"
                            >
                                <FileCheck class="h-4 w-4" /> Terjadwal Resmi di
                                Portal ITK
                            </span>
                        </div>

                        <!-- Judul Lengkap Tugas Akhir (Card Utama) -->
                        <div
                            class="rounded-2xl border border-blue-200/80 bg-blue-50/40 p-5 dark:border-blue-900/50 dark:bg-blue-950/30"
                        >
                            <span
                                class="text-[10px] font-bold tracking-wider text-blue-700 uppercase dark:text-blue-300"
                            >
                                Judul Tugas Akhir
                            </span>
                            <h3
                                class="mt-1.5 text-base leading-snug font-extrabold text-slate-900 sm:text-lg dark:text-white"
                            >
                                "{{
                                    selectedSession.judul ||
                                    selectedSession.title
                                }}"
                            </h3>
                        </div>

                        <!-- 2 Kolom: Data Mahasiswa & Waktu/Lokasi -->
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <!-- Kolom 1: Mahasiswa -->
                            <div
                                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4.5 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <h5
                                    class="mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                                >
                                    <User
                                        class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400"
                                    />
                                    Data Mahasiswa
                                </h5>
                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >Nama Mahasiswa</span
                                        >
                                        <span
                                            class="text-sm font-bold text-slate-900 dark:text-white"
                                        >
                                            {{
                                                selectedSession.studentName ||
                                                "-"
                                            }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >NIM</span
                                            >
                                            <span
                                                class="font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    selectedSession.studentNim ||
                                                    "-"
                                                }}
                                            </span>
                                        </div>
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >Program Studi</span
                                            >
                                            <span
                                                class="font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    selectedSession.prodi ||
                                                    "S1 Informatika"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom 2: Waktu & Lokasi -->
                            <div
                                class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4.5 dark:border-slate-800 dark:bg-slate-900/60"
                            >
                                <h5
                                    class="mb-3 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                                >
                                    <MapPin class="h-3.5 w-3.5 text-rose-500" />
                                    Waktu & Ruangan
                                </h5>
                                <div class="space-y-2 text-xs">
                                    <div>
                                        <span
                                            class="block text-[11px] text-slate-400"
                                            >Hari & Tanggal</span
                                        >
                                        <span
                                            class="font-bold text-slate-900 dark:text-white"
                                        >
                                            {{ selectedDay?.dateString }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-1">
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >Jam Pelaksanaan</span
                                            >
                                            <span
                                                class="font-semibold text-blue-600 dark:text-blue-400"
                                            >
                                                {{ selectedSession.time }}
                                            </span>
                                        </div>
                                        <div>
                                            <span
                                                class="block text-[11px] text-slate-400"
                                                >Ruangan</span
                                            >
                                            <span
                                                class="font-semibold text-slate-800 dark:text-slate-200"
                                            >
                                                {{
                                                    selectedSession.room || "-"
                                                }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tim Dosen Lengkap: Pembimbing & Penguji -->
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-5 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <h5
                                class="mb-4 flex items-center gap-1.5 text-xs font-bold tracking-wider text-slate-400 uppercase dark:text-slate-500"
                            >
                                <GraduationCap
                                    class="h-4 w-4 text-indigo-600 dark:text-indigo-400"
                                />
                                Tim Dosen Pembimbing & Penguji
                            </h5>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <!-- Tim Dosen Pembimbing -->
                                <div class="space-y-2.5">
                                    <span
                                        class="block text-[11px] font-bold tracking-wide text-indigo-700 uppercase dark:text-indigo-300"
                                    >
                                        Dosen Pembimbing
                                    </span>
                                    <div
                                        v-for="(
                                            p, pIdx
                                        ) in selectedSession.pembimbing"
                                        :key="pIdx"
                                        class="rounded-xl border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-[#0E1626]"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <div>
                                                <p
                                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                                >
                                                    {{ p.name }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-[10px] text-slate-400"
                                                >
                                                    NIP: {{ p.nip }}
                                                </p>
                                            </div>
                                            <span
                                                class="rounded-md bg-indigo-50 px-2 py-0.5 text-[9px] font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                                            >
                                                {{ p.role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tim Dosen Penguji -->
                                <div class="space-y-2.5">
                                    <span
                                        class="block text-[11px] font-bold tracking-wide text-purple-700 uppercase dark:text-purple-300"
                                    >
                                        Dosen Penguji
                                    </span>
                                    <div
                                        v-for="(
                                            u, uIdx
                                        ) in selectedSession.penguji"
                                        :key="uIdx"
                                        class="rounded-xl border border-slate-200/70 bg-white p-3 shadow-2xs dark:border-slate-800 dark:bg-[#0E1626]"
                                    >
                                        <div
                                            class="flex items-start justify-between gap-2"
                                        >
                                            <div>
                                                <p
                                                    class="text-xs font-bold text-slate-900 dark:text-white"
                                                >
                                                    {{ u.name }}
                                                </p>
                                                <p
                                                    class="mt-0.5 text-[10px] text-slate-400"
                                                >
                                                    NIP: {{ u.nip }}
                                                </p>
                                            </div>
                                            <span
                                                class="rounded-md bg-purple-50 px-2 py-0.5 text-[9px] font-bold text-purple-700 dark:bg-purple-950 dark:text-purple-300"
                                            >
                                                {{ u.role }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Pelaksanaan -->
                        <div
                            v-if="selectedSession.notes"
                            class="rounded-2xl border border-amber-200/80 bg-amber-50/50 p-4 text-xs dark:border-amber-900/40 dark:bg-amber-950/20"
                        >
                            <p
                                class="font-bold text-amber-900 dark:text-amber-200"
                            >
                                Catatan Pelaksanaan:
                            </p>
                            <p
                                class="mt-1 leading-relaxed text-slate-600 dark:text-slate-300"
                            >
                                {{ selectedSession.notes }}
                            </p>
                        </div>
                    </div>

                    <!-- Footer Modal View 2: Hanya Tombol Tutup (Tombol kembali hanya satu di kiri atas sesuai permintaan) -->
                    <div
                        class="flex items-center justify-between border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 sm:px-6 dark:border-slate-800 dark:bg-slate-900/40"
                    >
                        <span
                            class="hidden text-[11px] text-slate-500 sm:inline dark:text-slate-400"
                        >
                            Jadwal diverifikasi resmi oleh Koordinator TA
                            Informatika ITK.
                        </span>

                        <button
                            type="button"
                            class="ml-auto rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            @click="closeModal"
                        >
                            Tutup
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <footer
            class="mt-12 border-t border-slate-200/80 bg-white px-4 py-6 text-center text-xs text-slate-500 sm:px-8 dark:border-slate-800 dark:bg-[#0E1626] dark:text-slate-400"
        >
            <div
                class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 sm:flex-row"
            >
                <div class="flex items-center gap-4">
                    <a href="#" class="hover:text-blue-600">Privacy Policy</a>
                    <span>|</span>
                    <a href="#" class="hover:text-blue-600"
                        >Terms & Conditions</a
                    >
                    <span>|</span>
                    <a href="#" class="hover:text-blue-600">Cookie Policy</a>
                </div>
                <p>© 2026 Informatika ITK • SIPTA IF</p>
            </div>
        </footer>
    </div>
</template>
