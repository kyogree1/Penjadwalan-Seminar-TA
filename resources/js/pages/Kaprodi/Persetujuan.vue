<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookOpen,
    Check,
    CheckCircle2,
    ClipboardList,
    Clock,
    FileEdit,
    FileText,
    FolderOpen,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import type {
    AcademicReview,
    JudulReview,
    KaprodiReviewProps,
    SupervisorOption,
} from '@/types/kaprodi-persetujuan';

const props = defineProps<KaprodiReviewProps>();

type Section = 'judul' | 'sempro' | 'sidang';
const activeSection = ref<Section>('judul');

// Authenticated user context
const page = usePage();
const currentUser = computed(() => (page.props as any).auth?.user);

// Dynamic demo dataset strictly respecting the 3 conditions and user as supervisor
const getDemoJudulListForUser = (user: any): JudulReview[] => {
    const currentId = user?.id || 5;
    const currentName = user?.name || 'Dra. Nurul Rahmania, M.T.';

    return [
        {
            id: 101,
            mahasiswa: {
                id: 1,
                name: 'Akmal Falah Maulana',
                nim_nip: '11231006',
                angkatan: '2023',
            },
            judul_ta:
                'Rancang Bangun Frontend Dashboard Monitoring Progres Bimbingan Tugas Akhir Menggunakan Progressive Web Apps',
            bidang_penelitian: 'Human-Computer Interaction & Software Eng.',
            status: 'menunggu',
            pembimbing_1_id: currentId,
            pembimbing_2_id: 2,
            pembimbing1: { id: currentId, name: currentName },
            pembimbing2: {
                id: 2,
                name: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            },
            catatan_kaprodi: 'Menunggu telaah awal calon pembimbing tugas akhir.',
            abstrak:
                'Penerapan Progressive Web Apps (PWA) dan caching cerdas pada frontend dashboard monitoring tugas akhir mahasiswa untuk aksesibilitas tinggi di piranti bergerak.',
            created_at: '2026-09-18T13:45:00Z',
        },
        {
            id: 102,
            mahasiswa: {
                id: 2,
                name: 'Bagus Setiawan',
                nim_nip: '11211018',
                angkatan: '2021',
            },
            judul_ta:
                'Implementasi NLP dan Named Entity Recognition untuk Ekstraksi Otomatis Dokumen Skripsi',
            bidang_penelitian: 'Artificial Intelligence & NLP',
            status: 'menunggu',
            pembimbing_1_id: 19,
            pembimbing_2_id: currentId,
            pembimbing1: {
                id: 19,
                name: 'Dian Permata Sari, S.Kom., M.Kom.',
            },
            pembimbing2: { id: currentId, name: currentName },
            catatan_kaprodi: 'Dalam antrean telaah oleh calon pembimbing.',
            abstrak:
                'Penerapan IndoBERT untuk ekstraksi otomatis informasi metrik, pembimbing, dan luaran dari repositori skripsi mahasiswa.',
            created_at: '2026-09-30T14:20:00Z',
        },
        {
            id: 103,
            mahasiswa: {
                id: 3,
                name: 'Kevin Julio Alexander',
                nim_nip: '11221045',
                angkatan: '2022',
            },
            judul_ta:
                'Pengembangan Sistem Deteksi Intrusi Jaringan Berbasis Deep Learning dan Packet Inspection',
            bidang_penelitian: 'Keamanan Siber & Jaringan',
            status: 'menunggu',
            pembimbing_1_id: currentId,
            pembimbing_2_id: 20,
            pembimbing1: { id: currentId, name: currentName },
            pembimbing2: {
                id: 20,
                name: 'Rian Kurniawan, S.Kom., M.Eng.',
            },
            catatan_kaprodi:
                'Pengajuan usulan judul baru dari mahasiswa, menunggu review kelayakan dan metodologi.',
            abstrak:
                'Deteksi intrusi jaringan skala kampus menggunakan algoritma deep learning CNN-LSTM pada header packet inspection secara real-time.',
            created_at: '2026-10-01T08:15:00Z',
        },
        {
            id: 104,
            mahasiswa: {
                id: 4,
                name: 'Mochammad Reezqi Pratama',
                nim_nip: '11231041',
                angkatan: '2023',
            },
            judul_ta:
                'Optimasi Alokasi Dosen Penguji Ujian Skripsi dengan Pendekatan Constraint Satisfaction Problem dan Algoritma Genetika',
            bidang_penelitian: 'Computational Intelligence & Optimasi',
            status: 'revisi',
            pembimbing_1_id: 18,
            pembimbing_2_id: currentId,
            pembimbing1: {
                id: 18,
                name: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
            },
            pembimbing2: { id: currentId, name: currentName },
            catatan_kaprodi:
                'Perjelas batas waktu optimasi penjadwalan dan parameter mutasi pada bab metodologi.',
            abstrak:
                'Eksperimen tuning hyperparameter mutation rate dan crossover rate untuk mencari konvergensi fitness tercepat pada penjadwalan sidang.',
            created_at: '2026-09-20T11:00:00Z',
        },
        {
            id: 105,
            mahasiswa: {
                id: 5,
                name: 'Junnior Marcellino Polla',
                nim_nip: '11231034',
                angkatan: '2023',
            },
            judul_ta:
                'Rancang Bangun Arsitektur Microservices dan Keamanan Data pada Portal Layanan Ujian Tugas Akhir',
            bidang_penelitian: 'Cloud Computing & Cyber Security',
            status: 'revisi',
            pembimbing_1_id: currentId,
            pembimbing_2_id: 21,
            pembimbing1: { id: currentId, name: currentName },
            pembimbing2: {
                id: 21,
                name: 'Dr. Budi Wicaksono, M.Kom.',
            },
            catatan_kaprodi:
                'Perjelas studi kasus pembanding dan batasan pengujian keamanan sistem pada bagian latar belakang.',
            abstrak:
                'Membahas perancangan backend REST API dengan autentikasi JWT dan enkripsi data sensitif dokumen tugas akhir mahasiswa.',
            created_at: '2026-09-22T10:15:00Z',
        },
        {
            id: 106,
            mahasiswa: {
                id: 6,
                name: 'Muhammad Azka Yunastio',
                nim_nip: '11231036',
                angkatan: '2023',
            },
            judul_ta:
                'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Penjadwalan Seminar Tugas Akhir',
            bidang_penelitian: 'Artificial Intelligence & NLP',
            status: 'disetujui',
            pembimbing_1_id: currentId,
            pembimbing_2_id: 18,
            pembimbing1: { id: currentId, name: currentName },
            pembimbing2: {
                id: 18,
                name: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
            },
            catatan_kaprodi:
                'Topik penelitian sangat relevan dan metodologi sudah matang. Disetujui.',
            abstrak:
                'Rancang bangun sistem penjadwalan seminar tugas akhir otomatis dengan representasi kromosom GA dan asisten chatbot terintegrasi pedoman akademik.',
            created_at: '2026-09-28T08:00:00Z',
        },
        {
            id: 107,
            mahasiswa: {
                id: 7,
                name: 'Hari Yatun Nasifa',
                nim_nip: '11231030',
                angkatan: '2023',
            },
            judul_ta:
                'Pengembangan Antarmuka Sistem Informasi Akademik Tugas Akhir Berbasis Desain Komponen Reusable dan Responsif',
            bidang_penelitian: 'Human-Computer Interaction & Software Eng.',
            status: 'disetujui',
            pembimbing_1_id: 2,
            pembimbing_2_id: currentId,
            pembimbing1: {
                id: 2,
                name: 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
            },
            pembimbing2: { id: currentId, name: currentName },
            catatan_kaprodi:
                'Topik disetujui. Lanjutkan persiapan Bab 1-3 untuk Seminar Proposal.',
            abstrak:
                'Fokus pada rancang bangun UI/UX web modern berbasis Vue 3 dan Tailwind CSS untuk mempermudah pemantauan alur sidang TA mahasiswa secara real-time dan ramah pengguna.',
            created_at: '2026-09-25T09:30:00Z',
        },
        {
            id: 108,
            mahasiswa: {
                id: 8,
                name: 'Putri Ayu Wandira',
                nim_nip: '11211052',
                angkatan: '2021',
            },
            judul_ta:
                'Sistem Rekomendasi Pemilihan Topik Skripsi Menggunakan Content-Based Filtering dan Analisis Minat Bakat',
            bidang_penelitian: 'Sistem Cerdas & Rekomendasi',
            status: 'disetujui',
            pembimbing_1_id: 19,
            pembimbing_2_id: currentId,
            pembimbing1: {
                id: 19,
                name: 'Dian Permata Sari, S.Kom., M.Kom.',
            },
            pembimbing2: { id: currentId, name: currentName },
            catatan_kaprodi:
                'Topik penelitian sangat bagus dan metodologi filtering sesuai. Disetujui.',
            abstrak:
                'Algoritma Cosine Similarity dan TF-IDF untuk mencocokkan riwayat mata kuliah peminatan mahasiswa dengan topik riset dosen.',
            created_at: '2026-09-29T11:20:00Z',
        },
    ];
};

// Reactive judul dataset: jika props.dbJudul memiliki data, gunakan props.dbJudul;
// jika kosong (misal akun login belum memiliki data bimbingan di DB), sediakan demo dataset otomatis
const resolveInitialJudulList = (): JudulReview[] => {
    if (Array.isArray(props.dbJudul) && props.dbJudul.length > 0) {
        return JSON.parse(JSON.stringify(props.dbJudul));
    }
    return getDemoJudulListForUser(currentUser.value);
};

const judulList = ref<JudulReview[]>(resolveInitialJudulList());

watch(
    () => props.dbJudul,
    (val) => {
        if (Array.isArray(val) && val.length > 0) {
            judulList.value = JSON.parse(JSON.stringify(val));
        } else {
            judulList.value = getDemoJudulListForUser(currentUser.value);
        }
    },
    { deep: true },
);

// Toast notification state
const toast = ref({
    show: false,
    message: '',
    isError: false,
});

let toastTimer: ReturnType<typeof setTimeout> | null = null;
const showToast = (message: string, isError = false) => {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { show: true, message, isError };
    toastTimer = setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// Filter states
const searchQuery = ref('');
const filterStatus = ref<
    'all' | 'Menunggu Review' | 'Disetujui' | 'Perlu Revisi' | 'Ditolak'
>('all');
const filterBidang = ref('Semua Bidang Kajian');
const filterAngkatan = ref('all');

// Helper to extract student angkatan without "Angk." prefix
const getStudentAngkatan = (item: JudulReview | null | undefined): string => {
    if (!item?.mahasiswa) return '2023';
    if (item.mahasiswa.angkatan) return String(item.mahasiswa.angkatan);
    const nim = item.mahasiswa.nim_nip ?? '';
    if (nim.length >= 4 && !isNaN(Number(nim.slice(2, 4)))) {
        return `20${nim.slice(2, 4)}`;
    }
    return '2023';
};

const getStudentName = (item: JudulReview | null | undefined): string => {
    return item?.mahasiswa?.name || 'Mahasiswa';
};

const getStudentNim = (item: JudulReview | null | undefined): string => {
    return item?.mahasiswa?.nim_nip || '—';
};

// Supervisor names resolution
const getSupervisorName = (
    pos: 1 | 2,
    item: JudulReview | null | undefined,
): string => {
    if (!item) return '—';
    if (pos === 1) {
        if (item.pembimbing1?.name) return item.pembimbing1.name;
        if (item.pembimbing_1_id) {
            const found = props.dosenOptions?.find(
                (d) => d.id === item.pembimbing_1_id,
            );
            if (found) return found.name;
        }
        return 'Belum ditetapkan';
    } else {
        if (item.pembimbing2?.name) return item.pembimbing2.name;
        if (item.pembimbing_2_id) {
            const found = props.dosenOptions?.find(
                (d) => d.id === item.pembimbing_2_id,
            );
            if (found) return found.name;
        }
        return '—';
    }
};

// Status normalization & display
const getDisplayStatus = (
    item: JudulReview,
): 'Menunggu Review' | 'Disetujui' | 'Perlu Revisi' | 'Ditolak' => {
    const raw = String(item.status || '').toLowerCase();
    if (raw === 'disetujui') return 'Disetujui';
    if (raw === 'revisi') return 'Perlu Revisi';
    if (raw === 'ditolak') return 'Ditolak';
    return 'Menunggu Review';
};

const getStatusBadgeClass = (status: string) => {
    const s = String(status).toLowerCase();
    if (s.includes('setuju')) {
        return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30';
    }
    if (s.includes('revisi')) {
        return 'bg-purple-500/10 text-purple-700 dark:text-purple-300 border border-purple-500/30';
    }
    if (s.includes('tolak')) {
        return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/30';
    }
    return 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/30';
};

const getStatusDotClass = (status: string) => {
    const s = String(status).toLowerCase();
    if (s.includes('setuju')) return 'bg-emerald-500';
    if (s.includes('revisi')) return 'bg-purple-600 dark:bg-purple-400';
    if (s.includes('tolak')) return 'bg-rose-500';
    return 'bg-amber-500';
};

const formatTanggal = (dateString?: string) => {
    if (!dateString) return '28 Sep 2026';
    try {
        const d = new Date(dateString);
        if (isNaN(d.getTime())) return dateString;
        return d.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

// Filter: hanya judul di mana user bersangkutan (dosen/koorprodi) menjadi Pembimbing 1 atau Pembimbing 2
const isUserSupervisor = (item: JudulReview): boolean => {
    const user = currentUser.value;
    if (!user) return true; // jika belum ada context auth (e.g. testing standalone) tampilkan semua

    // Cocokkan berdasarkan ID pembimbing
    if (
        (item.pembimbing_1_id && item.pembimbing_1_id === user.id) ||
        (item.pembimbing_2_id && item.pembimbing_2_id === user.id) ||
        (item.pembimbing1?.id && item.pembimbing1.id === user.id) ||
        (item.pembimbing2?.id && item.pembimbing2.id === user.id)
    ) {
        return true;
    }

    // Cocokkan berdasarkan nama dosen/user
    const currentName = (user.name || '').toLowerCase().trim();
    if (!currentName) return false;

    const p1Name = (item.pembimbing1?.name || '').toLowerCase().trim();
    const p2Name = (item.pembimbing2?.name || '').toLowerCase().trim();

    const cleanTokens = currentName
        .replace(/(dr\.|dra\.|ir\.|prof\.|s\.t\.|m\.t\.|m\.kom\.|ph\.d\.|s\.kom\.|m\.eng\.)/gi, '')
        .split(/[\s,]+/)
        .filter((t: string) => t.length >= 3);

    const matchesP1 = cleanTokens.some((t: string) => p1Name.includes(t));
    const matchesP2 = cleanTokens.some((t: string) => p2Name.includes(t));

    return matchesP1 || matchesP2;
};

// Dataset judul yang hanya berisikan judul di mana user yang login menjadi Pembimbing 1 atau Pembimbing 2
const myJudulList = computed(() => {
    const filtered = judulList.value.filter(isUserSupervisor);
    if (filtered.length > 0) return filtered;
    return judulList.value;
});

// KPI Metrics computed from myJudulList
const totalCount = computed(() => myJudulList.value.length);
const pendingCount = computed(
    () =>
        myJudulList.value.filter((j) => getDisplayStatus(j) === 'Menunggu Review')
            .length,
);
const approvedCount = computed(
    () =>
        myJudulList.value.filter((j) => getDisplayStatus(j) === 'Disetujui')
            .length,
);
const revisionCount = computed(
    () =>
        myJudulList.value.filter((j) => getDisplayStatus(j) === 'Perlu Revisi')
            .length,
);

// Dynamic unique lists for select filters
const bidangList = computed(() => {
    const set = new Set<string>();
    for (const item of myJudulList.value) {
        if (item.bidang_penelitian?.trim())
            set.add(item.bidang_penelitian.trim());
    }
    return ['Semua Bidang Kajian', ...Array.from(set)];
});

const angkatanList = computed(() => {
    const set = new Set<string>();
    for (const item of myJudulList.value) {
        const angk = getStudentAngkatan(item);
        if (angk) set.add(angk);
    }
    return Array.from(set).sort((a, b) => Number(b) - Number(a));
});

// Filtered list
const filteredJudulList = computed(() => {
    return myJudulList.value.filter((item) => {
        const q = searchQuery.value.toLowerCase().trim();
        const studentName = getStudentName(item).toLowerCase();
        const studentNim = getStudentNim(item).toLowerCase();
        const judulText = (item.judul_ta || '').toLowerCase();
        const matchSearch =
            !q ||
            studentName.includes(q) ||
            studentNim.includes(q) ||
            judulText.includes(q);

        const currentDisplay = getDisplayStatus(item);
        const matchStatus =
            filterStatus.value === 'all' ||
            currentDisplay === filterStatus.value;

        const matchBidang =
            filterBidang.value === 'Semua Bidang Kajian' ||
            item.bidang_penelitian === filterBidang.value;

        const matchAngkatan =
            filterAngkatan.value === 'all' ||
            getStudentAngkatan(item) === filterAngkatan.value;

        return matchSearch && matchStatus && matchBidang && matchAngkatan;
    });
});

// Pagination state (mentoknya sesuaikan dengan tinggi panel telaah usulan judul, ~5 item/halaman)
const currentPage = ref(1);
const entriesPerPage = ref(5);

const paginatedJudulList = computed(() => {
    const start = (currentPage.value - 1) * entriesPerPage.value;
    const end = start + entriesPerPage.value;
    return filteredJudulList.value.slice(start, end);
});

const totalPages = computed(() =>
    Math.max(
        1,
        Math.ceil(filteredJudulList.value.length / entriesPerPage.value),
    ),
);

const paginationStart = computed(() =>
    filteredJudulList.value.length === 0
        ? 0
        : (currentPage.value - 1) * entriesPerPage.value + 1,
);

const paginationEnd = computed(() =>
    Math.min(
        currentPage.value * entriesPerPage.value,
        filteredJudulList.value.length,
    ),
);

watch([searchQuery, filterStatus, filterBidang, filterAngkatan], () => {
    currentPage.value = 1;
});

watch([totalPages, currentPage], () => {
    currentPage.value = Math.min(
        Math.max(1, currentPage.value),
        totalPages.value,
    );
});

const resetFilter = () => {
    searchQuery.value = '';
    filterStatus.value = 'all';
    filterBidang.value = 'Semua Bidang Kajian';
    filterAngkatan.value = 'all';
    currentPage.value = 1;
};

// Inspector & Selection State
const selectedJudulId = ref<number | null>(null);

const selectedJudul = computed(() => {
    if (selectedJudulId.value === -1) return null;
    if (selectedJudulId.value !== null) {
        const found = myJudulList.value.find(
            (j) => j.id === selectedJudulId.value,
        );
        if (found) return found;
    }
    return (
        paginatedJudulList.value[0] ??
        filteredJudulList.value[0] ??
        myJudulList.value[0] ??
        null
    );
});

const selectJudul = (item: JudulReview) => {
    selectedJudulId.value = item.id;
};

// Form review values inside Inspector
const reviewDecision = ref<'disetujui' | 'revisi'>('disetujui');
const reviewNote = ref('');
const saving = ref(false);
const errorMsg = ref('');

// Synchronize inspector fields when selectedJudul changes
watch(
    selectedJudul,
    (item) => {
        if (!item) return;
        const raw = String(item.status || '').toLowerCase();
        if (raw === 'revisi') {
            reviewDecision.value = 'revisi';
        } else {
            reviewDecision.value = 'disetujui';
        }

        reviewNote.value = item.catatan_kaprodi ?? '';
        errorMsg.value = '';
    },
    { immediate: true },
);

// Explicit status change from button click
const chooseDecision = (status: 'disetujui' | 'revisi') => {
    reviewDecision.value = status;
};

// Save review decision to server
const saveDecision = () => {
    const item = selectedJudul.value;
    if (!item || saving.value) return;

    // Untuk item demo (id > 100), perbarui state lokal langsung tanpa perlu patch server
    if (item.id > 100) {
        const foundIndex = judulList.value.findIndex(
            (j) => j.id === item.id,
        );
        if (foundIndex !== -1) {
            judulList.value[foundIndex] = {
                ...judulList.value[foundIndex],
                status: reviewDecision.value,
                catatan_kaprodi: reviewNote.value,
            };
        }
        const label =
            reviewDecision.value === 'disetujui'
                ? 'Disetujui'
                : 'Perlu Revisi';
        showToast(
            `Status judul untuk "${getStudentName(item)}" berhasil diperbarui menjadi ${label}!`,
        );
        return;
    }

    saving.value = true;
    errorMsg.value = '';

    const payload = {
        status: reviewDecision.value,
        catatan_kaprodi: reviewNote.value.trim() || null,
    };

    router.patch(`/kaprodi/persetujuan/judul/${item.id}`, payload, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash as {
                success?: string;
                error?: string;
            };
            if (flash?.error) {
                errorMsg.value = flash.error;
                showToast(flash.error, true);
            } else {
                // Update local state in judulList
                const foundIndex = judulList.value.findIndex(
                    (j) => j.id === item.id,
                );
                if (foundIndex !== -1) {
                    judulList.value[foundIndex] = {
                        ...judulList.value[foundIndex],
                        status: reviewDecision.value,
                        catatan_kaprodi: reviewNote.value,
                    };
                }
                const label =
                    reviewDecision.value === 'disetujui'
                        ? 'Disetujui'
                        : 'Perlu Revisi';
                showToast(
                    `Status judul untuk "${getStudentName(item)}" berhasil diperbarui menjadi ${label}!`,
                );
            }
        },
        onError: (errs) => {
            errorMsg.value =
                Object.values(errs).join(' ') ||
                'Gagal menyimpan keputusan review.';
            showToast(errorMsg.value, true);
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};

// Delete proposal method
const confirmDeleteJudul = (item: JudulReview) => {
    if (saving.value) return;
    const studentName = getStudentName(item);
    if (
        !confirm(
            `Apakah Anda yakin ingin menghapus pengajuan judul dari "${studentName}"?`,
        )
    ) {
        return;
    }

    // Untuk item demo (id > 100), hapus dari list lokal langsung
    if (item.id > 100) {
        judulList.value = judulList.value.filter(
            (j) => j.id !== item.id,
        );
        if (selectedJudulId.value === item.id) {
            selectedJudulId.value =
                filteredJudulList.value[0]?.id ?? null;
        }
        showToast(`Pengajuan judul "${studentName}" berhasil dihapus.`);
        return;
    }

    saving.value = true;
    router.delete(`/kaprodi/persetujuan/judul/${item.id}`, {
        preserveScroll: true,
        onSuccess: (page) => {
            const flash = page.props.flash as {
                success?: string;
                error?: string;
            };
            if (flash?.error) {
                showToast(flash.error, true);
            } else {
                // Remove from local list
                judulList.value = judulList.value.filter(
                    (j) => j.id !== item.id,
                );
                if (selectedJudulId.value === item.id) {
                    selectedJudulId.value =
                        filteredJudulList.value[0]?.id ?? null;
                }
                showToast(`Pengajuan judul "${studentName}" berhasil dihapus.`);
            }
        },
        onError: (errs) => {
            showToast(
                Object.values(errs).join(' ') ||
                    'Gagal menghapus pengajuan judul.',
                true,
            );
        },
        onFinish: () => {
            saving.value = false;
        },
    });
};

// Navigation button to Sempro
const goToSempro = () => {
    activeSection.value = 'sempro';
};

// Check query param on mount (e.g. ?section=sempro or ?section=sidang)
onMounted(() => {
    if (typeof window !== 'undefined') {
        const params = new URLSearchParams(window.location.search);
        const sec = params.get('section');
        if (sec === 'sempro' || sec === 'sidang' || sec === 'judul') {
            activeSection.value = sec;
        }
    }
});

// Sempro / Sidang fallback review modal state
const showAcademicModal = ref(false);
const academicSelected = ref<AcademicReview | null>(null);
const academicNote = ref('');
const academicDecision = ref<'disetujui' | 'revisi' | 'ditolak'>('disetujui');

const openAcademicReview = (row: AcademicReview) => {
    academicSelected.value = row;
    academicNote.value = row.catatan_kaprodi ?? '';
    academicDecision.value =
        (row.status_akademik as 'disetujui' | 'revisi' | 'ditolak') ||
        'disetujui';
    showAcademicModal.value = true;
};

const closeAcademicModal = () => {
    if (!saving.value) showAcademicModal.value = false;
};

const submitAcademicReview = (
    decision?: 'disetujui' | 'revisi' | 'ditolak',
) => {
    const row = academicSelected.value;
    if (!row || saving.value) return;
    const finalDecision = decision ?? academicDecision.value;
    saving.value = true;

    router.patch(
        `/kaprodi/persetujuan/${activeSection.value}/${row.id}`,
        {
            status_akademik: finalDecision,
            catatan_kaprodi: academicNote.value.trim() || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showToast(
                    `Review akademik ${activeSection.value} berhasil disimpan!`,
                );
                showAcademicModal.value = false;
            },
            onError: (errs) => {
                showToast(
                    Object.values(errs).join(' ') || 'Permintaan gagal.',
                    true,
                );
            },
            onFinish: () => {
                saving.value = false;
            },
        },
    );
};
</script>

<template>
    <Head title="Daftar & Pengajuan Judul TA • SIPTA IF" />

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
        <span class="text-base">{{ toast.isError ? '⚠️' : '✅' }}</span>
        <span class="text-xs font-bold sm:text-sm">{{ toast.message }}</span>
    </div>

    <!-- 1. MODE: DAFTAR & PENGAJUAN JUDUL TA (MATCHING FRONT-END EXACTLY) -->
    <div v-if="activeSection === 'judul'" class="space-y-6">
        <!-- HEADER & ACTIONS (No breadcrumb, directly Daftar & Pengajuan Judul TA) -->
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <h1
                    class="text-2xl font-black tracking-tight text-black dark:text-white"
                >
                    Daftar & Pengajuan Judul TA
                </h1>
                <p class="mt-0.5 text-xs font-normal text-black dark:text-slate-400">
                    Pendaftaran topik penelitian, verifikasi orisinalitas, dan
                    alokasi calon pembimbing tugas akhir.
                </p>
            </div>

            <!-- Action Buttons: Ajukan Judul Baru is omitted, Lanjut ke Sempro kept -->
            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    @click="goToSempro"
                    class="flex cursor-pointer items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-black transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                >
                    <span>Lanjut ke Sempro</span>
                    <ArrowRight
                        class="h-3.5 w-3.5 font-black text-blue-600 dark:text-blue-400"
                    />
                </button>
            </div>
        </div>

        <!-- 4 KPI SUMMARY CARDS (Lucide icons, no AI slop) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Pengajuan -->
            <div
                class="flex items-center justify-between rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-black uppercase dark:text-slate-400"
                    >
                        Total Pengajuan
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-black dark:text-white"
                    >
                        {{ totalCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400"
                >
                    <BookOpen class="h-5 w-5" />
                </div>
            </div>

            <!-- Menunggu Review -->
            <div
                class="flex items-center justify-between rounded-2xl border border-amber-200/80 bg-white p-4 shadow-xs dark:border-amber-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-amber-700 uppercase dark:text-amber-400"
                    >
                        Menunggu Review
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-amber-700 dark:text-amber-400"
                    >
                        {{ pendingCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400"
                >
                    <Clock class="h-5 w-5" />
                </div>
            </div>

            <!-- Judul Disetujui -->
            <div
                class="flex items-center justify-between rounded-2xl border border-emerald-200/80 bg-white p-4 shadow-xs dark:border-emerald-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-emerald-700 uppercase dark:text-emerald-400"
                    >
                        Judul Disetujui
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-emerald-700 dark:text-emerald-400"
                    >
                        {{ approvedCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </div>

            <!-- Perlu Revisi -->
            <div
                class="flex items-center justify-between rounded-2xl border border-purple-200/80 bg-white p-4 shadow-xs dark:border-purple-900/40 dark:bg-[#111827]"
            >
                <div>
                    <p
                        class="text-[11px] font-bold tracking-wider text-purple-700 uppercase dark:text-purple-400"
                    >
                        Perlu Revisi
                    </p>
                    <p
                        class="mt-1 text-2xl font-black text-purple-700 dark:text-purple-400"
                    >
                        {{ revisionCount }}
                    </p>
                </div>
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400"
                >
                    <FileEdit class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- MAIN LAYOUT: TABLE ON LEFT, INSPECTOR DRAWER ON RIGHT -->
        <div class="flex w-full flex-col items-start gap-5 lg:flex-row">
            <!-- LEFT SECTION: TABLE & FILTERS -->
            <div class="w-full min-w-0 flex-1 space-y-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
                >
                    <!-- Card Header with Filters -->
                    <div
                        class="space-y-3 border-b border-slate-100 p-4 sm:p-5 dark:border-slate-800"
                    >
                        <div
                            class="flex flex-col justify-between gap-3 sm:flex-row sm:items-center"
                        >
                            <div class="flex items-center gap-2">
                                <ClipboardList
                                    class="h-4 w-4 text-blue-600 dark:text-blue-400"
                                />
                                <h2
                                    class="text-sm font-black text-black sm:text-base dark:text-white"
                                >
                                    Daftar Judul
                                </h2>
                            </div>

                            <!-- Quick Status Badges Filter -->
                            <div
                                class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs sm:pb-0"
                            >
                                <button
                                    type="button"
                                    @click="filterStatus = 'all'"
                                    class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                                    :class="[
                                        filterStatus === 'all'
                                            ? 'bg-blue-600 text-white shadow-sm'
                                            : 'bg-slate-100 text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Semua
                                </button>
                                <button
                                    type="button"
                                    @click="filterStatus = 'Menunggu Review'"
                                    class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                                    :class="[
                                        filterStatus === 'Menunggu Review'
                                            ? 'bg-amber-600 text-white shadow-sm'
                                            : 'bg-slate-100 text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Review
                                </button>
                                <button
                                    type="button"
                                    @click="filterStatus = 'Disetujui'"
                                    class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                                    :class="[
                                        filterStatus === 'Disetujui'
                                            ? 'bg-emerald-600 text-white shadow-sm'
                                            : 'bg-slate-100 text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Disetujui
                                </button>
                                <button
                                    type="button"
                                    @click="filterStatus = 'Perlu Revisi'"
                                    class="cursor-pointer rounded-xl px-3 py-1.5 text-xs font-bold whitespace-nowrap transition-all"
                                    :class="[
                                        filterStatus === 'Perlu Revisi'
                                            ? 'bg-purple-600 text-white shadow-sm'
                                            : 'bg-slate-100 text-black hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                    ]"
                                >
                                    Revisi
                                </button>
                            </div>
                        </div>

                        <!-- Secondary Filters: Search, Bidang Kajian, Angkatan -->
                        <div
                            class="grid grid-cols-1 gap-3 pt-2 sm:grid-cols-2 md:grid-cols-3"
                        >
                            <!-- Search Input -->
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Cari judul, nama, atau NIM..."
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pr-3 pl-8 text-xs font-medium text-black outline-hidden focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-100 placeholder:text-black dark:placeholder:text-slate-400"
                                />
                                <Search
                                    class="absolute top-2.5 left-2.5 h-3.5 w-3.5 text-black dark:text-slate-400"
                                />
                            </div>

                            <!-- Bidang Kajian Filter -->
                            <select
                                v-model="filterBidang"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-black outline-hidden dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200"
                            >
                                <option
                                    v-for="b in bidangList"
                                    :key="b"
                                    :value="b"
                                >
                                    {{ b }}
                                </option>
                            </select>

                            <!-- Angkatan Filter -->
                            <select
                                v-model="filterAngkatan"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-black outline-hidden dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200"
                            >
                                <option value="all">Semua Angkatan</option>
                                <option
                                    v-for="angk in angkatanList"
                                    :key="angk"
                                    :value="angk"
                                >
                                    Angkatan {{ angk }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- TABLE CONTENT (No AKSI column, selection on row click) -->
                    <div class="w-full overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-black tracking-wider text-black uppercase dark:border-slate-800 dark:bg-slate-800/70 dark:text-slate-400"
                            >
                                <tr>
                                    <th class="w-12 px-3 py-3 text-center">
                                        NO
                                    </th>
                                    <th class="min-w-[140px] px-3 py-3">
                                        NAMA
                                    </th>
                                    <th
                                        class="min-w-[90px] px-3 py-3 font-mono"
                                    >
                                        NIM
                                    </th>
                                    <th class="min-w-[260px] px-3 py-3">
                                        JUDUL TA
                                    </th>
                                    <th class="min-w-[160px] px-3 py-3">
                                        PEMBIMBING 1
                                    </th>
                                    <th class="min-w-[160px] px-3 py-3">
                                        PEMBIMBING 2
                                    </th>
                                    <th
                                        class="min-w-[110px] px-3 py-3 text-center"
                                    >
                                        STATUS
                                    </th>
                                    <th class="w-16 px-3 py-3 text-center">
                                        AKSI
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-100 dark:divide-slate-800/60"
                            >
                                <tr
                                    v-for="(item, idx) in paginatedJudulList"
                                    :key="item.id"
                                    @click="selectJudul(item)"
                                    class="cursor-pointer transition-colors"
                                    :class="[
                                        selectedJudul &&
                                        selectedJudul.id === item.id
                                            ? 'bg-blue-50/70 ring-1 ring-blue-500/40 ring-inset dark:bg-blue-950/40'
                                            : 'hover:bg-slate-50/80 dark:hover:bg-slate-800/40',
                                    ]"
                                >
                                    <td
                                        class="px-3 py-3 text-center font-bold text-black dark:text-slate-400"
                                    >
                                        {{
                                            (currentPage - 1) *
                                                entriesPerPage +
                                            idx +
                                            1
                                        }}
                                    </td>

                                    <!-- Nama & Angkatan (Angk. prefix removed, purely year) -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="leading-snug font-black text-black dark:text-white"
                                        >
                                            {{ getStudentName(item) }}
                                        </p>
                                        <span
                                            class="font-mono text-[10px] font-normal text-black dark:text-slate-400"
                                        >
                                            {{ getStudentAngkatan(item) }}
                                        </span>
                                    </td>

                                    <!-- NIM -->
                                    <td
                                        class="px-3 py-3 font-mono font-normal whitespace-nowrap text-black dark:text-slate-400"
                                    >
                                        {{ getStudentNim(item) }}
                                    </td>

                                    <!-- Judul TA & Bidang -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="line-clamp-2 text-xs leading-relaxed font-bold text-black dark:text-slate-200"
                                        >
                                            {{ item.judul_ta }}
                                        </p>
                                        <span
                                            class="mt-1 inline-block text-[10px] font-bold text-blue-700 dark:text-blue-400"
                                        >
                                            #
                                            {{
                                                item.bidang_penelitian || 'Umum'
                                            }}
                                        </span>
                                    </td>

                                    <!-- Pembimbing 1 -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="max-w-[160px] truncate text-[11px] font-bold text-black dark:text-slate-200"
                                            :title="getSupervisorName(1, item)"
                                        >
                                            {{ getSupervisorName(1, item) }}
                                        </p>
                                        <span
                                            class="text-[10px] font-normal text-black dark:text-slate-400"
                                            >Utama</span
                                        >
                                    </td>

                                    <!-- Pembimbing 2 -->
                                    <td class="px-3 py-3">
                                        <p
                                            class="max-w-[160px] truncate text-[11px] font-bold text-black dark:text-slate-200"
                                            :title="getSupervisorName(2, item)"
                                        >
                                            {{ getSupervisorName(2, item) }}
                                        </p>
                                        <span
                                            class="text-[10px] font-normal text-black dark:text-slate-400"
                                            >Pendamping</span
                                        >
                                    </td>

                                    <!-- Status Badge (No dot circle) -->
                                    <td
                                        class="px-3 py-3 text-center whitespace-nowrap"
                                    >
                                        <span
                                            :class="
                                                getStatusBadgeClass(item.status)
                                            "
                                            class="inline-flex items-center justify-center rounded-full px-2.5 py-1 text-[10px] font-extrabold shadow-2xs"
                                        >
                                            <span>{{
                                                getDisplayStatus(item)
                                            }}</span>
                                        </span>
                                    </td>

                                    <!-- Aksi (Delete) -->
                                    <td
                                        class="px-3 py-3 text-center whitespace-nowrap"
                                        @click.stop
                                    >
                                        <button
                                            type="button"
                                            @click="confirmDeleteJudul(item)"
                                            class="inline-flex cursor-pointer items-center justify-center rounded-lg p-1.5 text-black transition-colors hover:bg-rose-50 hover:text-rose-600 dark:text-slate-400 dark:hover:bg-rose-950/50 dark:hover:text-rose-400"
                                            title="Hapus Pengajuan"
                                            aria-label="Hapus Pengajuan"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </td>
                                </tr>

                                <!-- Empty State -->
                                <tr v-if="filteredJudulList.length === 0">
                                    <td colspan="8" class="py-14 text-center">
                                        <div
                                            class="flex flex-col items-center justify-center space-y-3"
                                        >
                                            <div
                                                class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-black shadow-inner dark:bg-slate-800/80 dark:text-slate-400"
                                            >
                                                <FolderOpen class="h-6 w-6" />
                                            </div>
                                            <div class="space-y-1">
                                                <p
                                                    class="text-sm font-black text-black dark:text-slate-300"
                                                >
                                                    Tidak Ada Data
                                                </p>
                                                <p
                                                    class="max-w-sm text-xs font-normal text-black dark:text-slate-400"
                                                >
                                                    Tidak ada pengajuan yang
                                                    sesuai kriteria pencarian
                                                    atau filter yang dipilih.
                                                </p>
                                            </div>
                                            <button
                                                type="button"
                                                @click="resetFilter"
                                                class="rounded-xl border border-blue-200 bg-blue-50 px-3.5 py-1.5 text-xs font-bold text-blue-700 hover:bg-blue-100 dark:border-blue-800/60 dark:bg-blue-950/60 dark:text-blue-300"
                                            >
                                                Reset Filter
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Table Footer & Pagination (Matching Data Master Mahasiswa) -->
                    <div
                        class="flex flex-col justify-between gap-3 border-t border-slate-100 p-4 text-xs font-normal text-black sm:flex-row sm:items-center dark:border-slate-800 dark:text-slate-400"
                    >
                        <div class="text-xs font-semibold text-black dark:text-white">
                            Menampilkan
                            <b class="font-bold text-black dark:text-white">{{
                                paginationStart
                            }}</b>
                            sampai
                            <b class="font-bold text-black dark:text-white">{{
                                paginationEnd
                            }}</b>
                            dari
                            <b class="font-bold text-black dark:text-white">{{
                                filteredJudulList.length
                            }}</b>
                            pengajuan judul
                        </div>

                        <div class="flex items-center gap-1 overflow-x-auto">
                            <button
                                type="button"
                                :disabled="currentPage === 1"
                                class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border border-slate-300 font-bold text-black hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
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
                                        ? 'border border-blue-600 bg-blue-600 font-black text-white shadow-xs'
                                        : 'border border-slate-300 font-bold text-black hover:bg-slate-100 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800'
                                "
                                class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg text-xs transition-colors"
                                @click="currentPage = p"
                            >
                                {{ p }}
                            </button>
                            <button
                                type="button"
                                :disabled="currentPage === totalPages"
                                class="flex h-7 w-7 cursor-pointer items-center justify-center rounded-lg border border-slate-300 font-bold text-black hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-30 dark:border-slate-700 dark:text-white dark:hover:bg-slate-800"
                                @click="currentPage++"
                            >
                                ›
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT SECTION: DETAIL & REVIEW INSPECTOR PANEL -->
            <div
                v-if="selectedJudul"
                class="sticky top-20 w-full space-y-5 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all lg:w-96 dark:border-slate-800/80 dark:bg-[#111827]"
            >
                <!-- Inspector Header -->
                <div
                    class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800"
                >
                    <div class="flex items-center gap-2">
                        <Search
                            class="h-4 w-4 text-blue-600 dark:text-blue-400"
                        />
                        <h3
                            class="text-sm font-black text-black dark:text-white"
                        >
                            Telaah Usulan Judul
                        </h3>
                    </div>
                    <button
                        type="button"
                        @click="selectedJudulId = -1"
                        class="rounded-lg p-1 text-black hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                        title="Tutup panel review"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Student Profile Summary (Angk. text removed, only year) -->
                <div
                    class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-800/50"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-blue-700 to-blue-500 text-sm font-black text-white shadow-xs"
                    >
                        {{ getStudentName(selectedJudul).charAt(0) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4
                            class="truncate text-xs font-black text-black sm:text-sm dark:text-white"
                        >
                            {{ getStudentName(selectedJudul) }}
                        </h4>
                        <p class="font-mono text-[11px] font-normal text-black dark:text-slate-400">
                            NIM: {{ getStudentNim(selectedJudul) }} •
                            {{ getStudentAngkatan(selectedJudul) }}
                        </p>
                    </div>
                </div>

                <!-- Title & Topic Details -->
                <div class="space-y-2">
                    <span
                        class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                        >Judul Penelitian</span
                    >
                    <p
                        class="rounded-xl border border-slate-200/60 bg-slate-50/70 p-3 text-xs leading-snug font-bold text-black dark:border-slate-700/60 dark:bg-slate-800/40 dark:text-slate-100"
                    >
                        {{ selectedJudul.judul_ta }}
                    </p>
                    <div
                        class="flex items-center justify-between pt-1 text-[11px] font-normal text-black dark:text-slate-400"
                    >
                        <span
                            >Bidang:
                            <b class="font-bold text-black dark:text-slate-300">{{
                                selectedJudul.bidang_penelitian || 'Umum'
                            }}</b></span
                        >
                        <span
                            >Tgl:
                            <b class="font-bold text-black dark:text-slate-300">{{
                                formatTanggal(selectedJudul.created_at)
                            }}</b></span
                        >
                    </div>
                </div>

                <!-- Abstract / Summary -->
                <div class="space-y-1.5">
                    <span
                        class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                        >Ringkasan Ide / Abstrak</span
                    >
                    <p
                        class="max-h-28 overflow-y-auto pr-1 text-xs leading-relaxed font-normal text-black dark:text-slate-300"
                    >
                        {{
                            selectedJudul.abstrak ||
                            'Penelitian ini berfokus pada analisis, pemodelan arsitektur, dan evaluasi hasil komputasi tugas akhir mahasiswa program studi Informatika ITK.'
                        }}
                    </p>
                </div>

                <!-- Proposed Supervisors (Chosen by student, displayed automatically) -->
                <div
                    class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <span
                        class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                        >Calon Pembimbing</span
                    >
                    <div class="space-y-1.5 text-xs">
                        <div
                            class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/40"
                        >
                            <span class="text-[11px] font-normal text-black dark:text-slate-400"
                                >Pembimbing 1:
                            </span>
                            <b
                                class="font-bold text-black dark:text-slate-200"
                                :title="getSupervisorName(1, selectedJudul)"
                            >
                                {{ getSupervisorName(1, selectedJudul) }}
                            </b>
                        </div>
                        <div
                            class="rounded-xl bg-slate-50 p-2.5 dark:bg-slate-800/40"
                        >
                            <span class="text-[11px] font-normal text-black dark:text-slate-400"
                                >Pembimbing 2:
                            </span>
                            <b
                                class="font-bold text-black dark:text-slate-200"
                                :title="getSupervisorName(2, selectedJudul)"
                            >
                                {{ getSupervisorName(2, selectedJudul) }}
                            </b>
                        </div>
                    </div>
                </div>

                <!-- KEPUTUSAN REVIEW & CATATAN: 3 KONDISI -->
                <!-- KONDISI 1: MENUNGGU REVIEW (FORM AKTIF) -->
                <template
                    v-if="getDisplayStatus(selectedJudul) === 'Menunggu Review'"
                >
                    <!-- Review Feedback Notes (Editable) -->
                    <div
                        class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Catatan Review / Feedback</span
                        >
                        <textarea
                            v-model="reviewNote"
                            rows="3"
                            placeholder="Tuliskan catatan arahan atau poin perbaikan untuk mahasiswa..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-2.5 text-xs font-normal text-black outline-hidden placeholder:text-black/60 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200 dark:placeholder:text-slate-500"
                        ></textarea>
                    </div>

                    <div
                        class="space-y-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                                >Keputusan Review</span
                            >
                            <span
                                class="rounded-full px-2 py-0.5 text-[9px] font-black tracking-wide uppercase"
                                :class="[
                                    reviewDecision === 'disetujui'
                                        ? 'border border-emerald-500/30 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                        : 'border border-purple-500/30 bg-purple-500/10 text-purple-700 dark:text-purple-300',
                                ]"
                            >
                                {{
                                    reviewDecision === 'disetujui'
                                        ? 'Disetujui'
                                        : 'Revisi'
                                }}
                            </span>
                        </div>

                        <!-- 2 Interactive Options: Setujui & Revisi -->
                        <div class="grid grid-cols-2 gap-2">
                            <!-- Option 1: Setujui -->
                            <button
                                type="button"
                                @click="chooseDecision('disetujui')"
                                title="Setujui Judul"
                                aria-label="Setujui Judul"
                                class="flex cursor-pointer items-center justify-center rounded-xl p-2.5 text-xs font-bold transition-all"
                                :class="[
                                    reviewDecision === 'disetujui'
                                        ? 'scale-[1.02] bg-emerald-600 text-white shadow-md ring-2 shadow-emerald-600/30 ring-emerald-500 ring-offset-2 dark:ring-offset-[#111827]'
                                        : 'border border-slate-200 bg-slate-50 text-black font-bold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                ]"
                            >
                                Setujui
                            </button>

                            <!-- Option 2: Revisi (Warna Ungu) -->
                            <button
                                type="button"
                                @click="chooseDecision('revisi')"
                                title="Minta Revisi"
                                aria-label="Minta Revisi"
                                class="flex cursor-pointer items-center justify-center rounded-xl p-2.5 text-xs font-bold transition-all"
                                :class="[
                                    reviewDecision === 'revisi'
                                        ? 'scale-[1.02] bg-purple-600 text-white shadow-md ring-2 shadow-purple-600/30 ring-purple-500 ring-offset-2 dark:ring-offset-[#111827]'
                                        : 'border border-slate-200 bg-slate-50 text-black font-bold hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                                ]"
                            >
                                Revisi
                            </button>
                        </div>

                        <p
                            v-if="errorMsg"
                            role="alert"
                            class="text-xs font-semibold text-rose-600 dark:text-rose-400"
                        >
                            {{ errorMsg }}
                        </p>

                        <!-- Save Decision Action Button -->
                        <button
                            type="button"
                            @click="saveDecision"
                            :disabled="saving"
                            class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-md shadow-blue-600/20 transition-all hover:bg-blue-700 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <Check class="h-4 w-4" />
                            <span>{{
                                saving
                                    ? 'Menyimpan Keputusan...'
                                    : 'Simpan Keputusan Review'
                            }}</span>
                        </button>
                    </div>
                </template>

                <!-- KONDISI 2: DISETUJUI (KEPUTUSAN TERSIMPAN, TIDAK ADA BUTTON LAGI) -->
                <template
                    v-else-if="getDisplayStatus(selectedJudul) === 'Disetujui'"
                >
                    <!-- Catatan Review Tersimpan (jika ada) -->
                    <div
                        v-if="selectedJudul.catatan_kaprodi"
                        class="space-y-1.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Catatan Review / Feedback</span
                        >
                        <p
                            class="rounded-xl border border-slate-200/60 bg-slate-50 p-2.5 text-xs leading-relaxed font-normal text-black dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200"
                        >
                            {{ selectedJudul.catatan_kaprodi }}
                        </p>
                    </div>

                    <!-- Keputusan Review: Disetujui (Bagian kiri tulisan Disetujui, tidak ada button bawahnya) -->
                    <div
                        class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Keputusan Review</span
                        >
                        <div
                            class="flex items-center justify-between rounded-xl border border-emerald-500/30 bg-emerald-50/70 p-3 dark:border-emerald-500/20 dark:bg-emerald-950/20"
                        >
                            <span
                                class="text-xs font-black tracking-wide text-emerald-700 dark:text-emerald-400"
                            >
                                Disetujui
                            </span>
                            <span
                                class="text-[10px] font-bold text-emerald-600/80 dark:text-emerald-400/80"
                            >
                                Tersimpan
                            </span>
                        </div>
                    </div>
                </template>

                <!-- KONDISI 3: PERLU REVISI (WARNA UNGU, MENUNGGU MAHASISWA REVISI, TIDAK ADA BUTTON LAGI) -->
                <template
                    v-else-if="getDisplayStatus(selectedJudul) === 'Perlu Revisi'"
                >
                    <!-- Catatan Arahan Revisi (jika ada) -->
                    <div
                        v-if="selectedJudul.catatan_kaprodi"
                        class="space-y-1.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Catatan Arahan Revisi</span
                        >
                        <p
                            class="rounded-xl border border-purple-200/80 bg-purple-50/50 p-2.5 text-xs leading-relaxed font-normal text-black dark:border-purple-800/40 dark:bg-purple-950/20 dark:text-purple-200"
                        >
                            {{ selectedJudul.catatan_kaprodi }}
                        </p>
                    </div>

                    <!-- Keputusan Review: Perlu Revisi (Warna Ungu, bagian kiri tulisan Perlu Revisi, tidak ada button bawahnya) -->
                    <div
                        class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Keputusan Review</span
                        >
                        <div
                            class="flex items-center justify-between rounded-xl border border-purple-500/30 bg-purple-50/70 p-3 dark:border-purple-500/20 dark:bg-purple-950/20"
                        >
                            <span
                                class="text-xs font-black tracking-wide text-purple-700 dark:text-purple-300"
                            >
                                Perlu Revisi
                            </span>
                            <span
                                class="text-[10px] font-bold text-purple-600/80 dark:text-purple-400/80"
                            >
                                Menunggu Mahasiswa
                            </span>
                        </div>
                    </div>
                </template>

                <!-- KONDISI FALLBACK / DITOLAK (JIKA ADA) -->
                <template v-else>
                    <div
                        v-if="selectedJudul.catatan_kaprodi"
                        class="space-y-1.5 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Catatan</span
                        >
                        <p
                            class="rounded-xl border border-slate-200/60 bg-slate-50 p-2.5 text-xs leading-relaxed font-normal text-black dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-200"
                        >
                            {{ selectedJudul.catatan_kaprodi }}
                        </p>
                    </div>

                    <div
                        class="space-y-2 border-t border-slate-100 pt-3 dark:border-slate-800"
                    >
                        <span
                            class="text-[10px] font-black tracking-wider text-black uppercase dark:text-slate-400"
                            >Keputusan Review</span
                        >
                        <div
                            class="flex items-center justify-between rounded-xl border border-rose-500/30 bg-rose-50/70 p-3 dark:border-rose-500/20 dark:bg-rose-950/20"
                        >
                            <span
                                class="text-xs font-black tracking-wide text-rose-700 dark:text-rose-400"
                            >
                                {{ getDisplayStatus(selectedJudul) }}
                            </span>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Placeholder if no judul selected or list empty (matching Lembar Kendali Bimbingan) -->
            <div
                v-else
                class="sticky top-20 flex w-full shrink-0 flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center lg:w-96 dark:border-slate-700 dark:bg-[#111827]"
            >
                <div
                    class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400"
                >
                    <ClipboardList class="h-6 w-6" />
                </div>
                <h3
                    class="mt-3 text-sm font-black text-black dark:text-white"
                >
                    Telaah Usulan Judul
                </h3>
                <p class="mt-1 text-xs font-normal text-black dark:text-slate-400">
                    Pilih salah satu mahasiswa dari tabel di sebelah kiri untuk
                    melihat detail usulan judul dan status keputusan review.
                </p>
            </div>
        </div>
    </div>

    <!-- 2. MODE: SEMPRO & SIDANG TA ACADEMIC REVIEWS (PRESERVED SECTIONS) -->
    <div v-else class="space-y-6">
        <div
            class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center"
        >
            <div>
                <button
                    type="button"
                    @click="activeSection = 'judul'"
                    class="mb-2 flex cursor-pointer items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                >
                    <span>← Kembali ke Daftar Judul</span>
                </button>
                <h1
                    class="text-2xl font-black tracking-tight text-black dark:text-white"
                >
                    Tinjauan Akademik
                    {{
                        activeSection === 'sempro'
                            ? 'Seminar Proposal'
                            : 'Sidang Tugas Akhir'
                    }}
                </h1>
                <p class="mt-0.5 text-xs font-normal text-black dark:text-slate-400">
                    Tinjau berkas akademik dan berikan arahan untuk tahapan
                    {{ activeSection === 'sempro' ? 'Sempro' : 'Sidang TA' }}.
                </p>
            </div>

            <!-- Tab Switcher for Sempro / Sidang -->
            <div class="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    class="font-bold text-black"
                    :class="{
                        '!bg-blue-600 !text-white':
                            activeSection === 'sempro',
                    }"
                    @click="activeSection = 'sempro'"
                >
                    Sempro ({{ dbSempro?.length ?? 0 }})
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    class="font-bold text-black"
                    :class="{
                        '!bg-blue-600 !text-white':
                            activeSection === 'sidang',
                    }"
                    @click="activeSection = 'sidang'"
                >
                    Sidang TA ({{ dbSidang?.length ?? 0 }})
                </Button>
            </div>
        </div>

        <!-- Academic Submissions Table -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800/80 dark:bg-[#111827]"
        >
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead
                        class="border-b border-slate-100 bg-slate-50 font-black text-black uppercase dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-400"
                    >
                        <tr>
                            <th class="p-3">Mahasiswa</th>
                            <th class="p-3">Judul Penelitian</th>
                            <th class="p-3">Status Verifikasi</th>
                            <th class="p-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-100 dark:divide-slate-800"
                    >
                        <tr
                            v-for="item in activeSection === 'sempro'
                                ? (dbSempro ?? [])
                                : (dbSidang ?? [])"
                            :key="`${activeSection}-${item.id}`"
                            class="hover:bg-slate-50/50 dark:hover:bg-slate-800/40"
                        >
                            <td class="p-3">
                                <p
                                    class="font-black text-black dark:text-white"
                                >
                                    {{ item.mahasiswa?.name ?? 'Mahasiswa' }}
                                </p>
                                <span
                                    class="font-mono text-[10px] font-normal text-black dark:text-slate-400"
                                    >NIM:
                                    {{ item.mahasiswa?.nim_nip ?? '—' }}</span
                                >
                            </td>
                            <td class="p-3">
                                <p
                                    class="font-bold text-black dark:text-slate-200"
                                >
                                    {{ item.judul_ta }}
                                </p>
                                <p
                                    v-if="item.catatan"
                                    class="mt-0.5 text-[11px] font-normal text-black dark:text-slate-400"
                                >
                                    Catatan: {{ item.catatan }}
                                </p>
                            </td>
                            <td class="p-3">
                                <span
                                    class="rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-bold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                                >
                                    {{ item.status }}
                                </span>
                                <p
                                    v-if="item.status_akademik"
                                    class="mt-1 text-[10px] font-normal text-black dark:text-slate-400"
                                >
                                    Akademik:
                                    <b class="font-bold capitalize text-black dark:text-white">{{
                                        item.status_akademik
                                    }}</b>
                                </p>
                            </td>
                            <td class="p-3 text-center">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="font-bold text-black"
                                    @click="openAcademicReview(item)"
                                >
                                    Tinjau
                                </Button>
                            </td>
                        </tr>
                        <tr
                            v-if="
                                !(
                                    activeSection === 'sempro'
                                        ? (dbSempro ?? [])
                                        : (dbSidang ?? [])
                                ).length
                            "
                        >
                            <td
                                colspan="4"
                                class="p-8 text-center font-normal text-black dark:text-slate-500"
                            >
                                Tidak ada pengajuan yang sesuai.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ACADEMIC MODAL FOR SEMPRO & SIDANG -->
    <Modal :show="showAcademicModal" max-width="lg" @close="closeAcademicModal">
        <div class="space-y-4 p-6 text-sm" v-if="academicSelected">
            <h3 class="font-black text-black dark:text-white">
                Tinjauan Akademik
                {{
                    activeSection === 'sempro'
                        ? 'Seminar Proposal'
                        : 'Sidang Tugas Akhir'
                }}
            </h3>
            <p class="text-xs font-normal text-black dark:text-slate-300">
                {{ academicSelected.judul_ta }}
            </p>

            <div class="space-y-1.5">
                <label
                    class="block text-xs font-black text-black dark:text-slate-300"
                    >Catatan Akademik & Arahan Kaprodi</label
                >
                <textarea
                    v-model="academicNote"
                    rows="3"
                    placeholder="Tuliskan arahan akademik..."
                    class="w-full rounded-xl border border-slate-200 p-2.5 text-xs font-normal text-black outline-hidden placeholder:text-black/60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:placeholder:text-slate-500"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <Button
                    variant="secondary"
                    :disabled="saving"
                    class="font-bold text-black"
                    @click="closeAcademicModal"
                    >Batal</Button
                >
                <Button
                    variant="outline"
                    :disabled="saving"
                    class="font-bold text-black"
                    @click="submitAcademicReview('revisi')"
                    >Minta Revisi</Button
                >
                <Button
                    variant="primary"
                    :disabled="saving"
                    class="font-bold"
                    @click="submitAcademicReview('disetujui')"
                    >Simpan Catatan</Button
                >
            </div>
        </div>
    </Modal>
</template>
