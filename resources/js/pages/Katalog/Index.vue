<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import {
    Award,
    BookOpen,
    Check,
    CheckCircle2,
    ChevronRight,
    Copy,
    Download,
    Eye,
    FileText,
    GraduationCap,
    LayoutGrid,
    List,
    RotateCcw,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    TrendingUp,
    User,
    Users,
    X,
} from 'lucide-vue-next';

interface KatalogItem {
    id: number;
    judul: string;
    judulEn: string;
    penulis: string;
    nim: string;
    angkatan: string;
    tahunLulus: number;
    prodi: string;
    kbk: string;
    pembimbing1: string;
    pembimbing2: string;
    penguji1: string;
    penguji2: string;
    nilai: string;
    abstrakId: string;
    abstrakEn: string;
    keywords: string[];
    views: number;
    downloads: number;
    doi: string;
    fileNaskah: string;
    fileSize: string;
}

// Initial Catalog Dataset based on Informatika ITK Archive
const katalogList = ref<KatalogItem[]>([
    {
        id: 1,
        judul: 'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Otomasi Penjadwalan Seminar Tugas Akhir',
        judulEn: 'Implementation of Genetic Algorithm and Large Language Model in Final Project Seminar Scheduling Automation System',
        penulis: 'Muhammad Azka Yunastio',
        nim: '11231036',
        angkatan: '2023',
        tahunLulus: 2026,
        prodi: 'Informatika',
        kbk: 'Computational Intelligence & Optimasi',
        pembimbing1: 'Dra. Nurul Rahmania, M.T.',
        pembimbing2: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji1: 'Dian Permata Sari, S.Kom., M.Kom.',
        penguji2: 'Dr. Budi Wicaksono, M.Kom.',
        nilai: 'A',
        abstrakId:
            'Penjadwalan seminar proposal dan sidang akhir tugas akhir merupakan proses krusial di perguruan tinggi yang seringkali memakan waktu berhari-hari karena tingginya kompleksitas bentrok waktu dosen penguji, ketersediaan ruang ujian, dan preferensi mahasiswa. Penelitian ini mengusulkan solusi hibrida yang menggabungkan Algoritma Genetika (GA) dengan representasi permutasi jadwal multi-ruang dan asisten pintar berbasis LLM untuk rekomendasi resolusi konflik jadwal. Pengujian menunjukkan sistem mampu menemukan solusi jadwal tanpa bentrok (hard-constraint violation = 0) dengan rata-rata generasi konvergen pada iterasi ke-42 dan mempercepat penyusunan agenda dari 3 hari menjadi kurang dari 10 menit.',
        abstrakEn:
            'Scheduling proposal seminars and thesis final defense is a critical university process that often takes days due to complex schedule conflicts among examiners, room availability, and student preferences. This research proposes a hybrid solution combining Genetic Algorithm (GA) with multi-room schedule permutation representation and an LLM-based intelligent assistant for conflict resolution recommendations. Experimental results show that the system successfully produces conflict-free schedules (zero hard-constraint violations) with average convergence at generation 42, reducing agenda creation time from 3 days to under 10 minutes.',
        keywords: [
            'Algoritma Genetika',
            'Large Language Models',
            'Penjadwalan Otomatis',
            'Constraint Satisfaction Problem',
            'Sistem Informasi',
        ],
        views: 428,
        downloads: 112,
        doi: '10.31219/itk.if.ta.2026.001',
        fileNaskah: 'Naskah_Lengkap_11231036.pdf',
        fileSize: '3.8 MB',
    },
    {
        id: 2,
        judul: 'Sistem Deteksi Retinopati Diabetik pada Citra Fundus Mata Menggunakan Arsitektur EfficientNet-B4 dengan Perhatian Spasial',
        judulEn: 'Diabetic Retinopathy Detection System on Eye Fundus Images Using EfficientNet-B4 Architecture with Spatial Attention',
        penulis: 'Fenny Melina Br Sihombing',
        nim: '11191009',
        angkatan: '2019',
        tahunLulus: 2025,
        prodi: 'Informatika',
        kbk: 'Artificial Intelligence & NLP',
        pembimbing1: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        pembimbing2: 'Rian Kurniawan, S.Kom., M.Eng.',
        penguji1: 'Dra. Nurul Rahmania, M.T.',
        penguji2: 'Dr. Budi Wicaksono, M.Kom.',
        nilai: 'A',
        abstrakId:
            'Retinopati Diabetik (RD) adalah komplikasi diabetes melitus yang dapat menyebabkan kebutaan permanen apabila tidak dideteksi sejak stadium dini. Penelitian ini mengembangkan model deep learning menggunakan convolutional neural network EfficientNet-B4 yang diperkaya dengan modul Convolutional Block Attention Module (CBAM) untuk mengenali lesi mikroaneurisma, eksudat, dan perdarahan pada citra fundus retina dataset APTOS 2019. Model mencapai akurasi sebesar 94.2% dengan Quadratic Weighted Kappa sebesar 0.912, mengungguli arsitektur baseline ResNet-50.',
        abstrakEn:
            'Diabetic Retinopathy (DR) is a diabetes complication leading to irreversible blindness if not detected early. This study develops a deep learning model using the EfficientNet-B4 CNN architecture integrated with CBAM (Convolutional Block Attention Module) to localize microaneurysms, exudates, and hemorrhages on the APTOS 2019 retina fundus dataset. The model achieved 94.2% accuracy and 0.912 Quadratic Weighted Kappa, outperforming the baseline ResNet-50 architecture.',
        keywords: [
            'Deep Learning',
            'EfficientNet-B4',
            'Spatial Attention',
            'Retinopati Diabetik',
            'Pengolahan Citra Medis',
        ],
        views: 651,
        downloads: 198,
        doi: '10.31219/itk.if.ta.2025.018',
        fileNaskah: 'Skripsi_Lengkap_11191009.pdf',
        fileSize: '5.2 MB',
    },
    {
        id: 3,
        judul: 'Analisis Perbandingan Protokol Konsensus Proof of Authority dan RAFT pada Jaringan Blockchain Hyperledger Besu',
        judulEn: 'Comparative Analysis of Proof of Authority and RAFT Consensus Protocols on Hyperledger Besu Blockchain Network',
        penulis: 'Rizky Wahyu Saputra',
        nim: '11201045',
        angkatan: '2020',
        tahunLulus: 2025,
        prodi: 'Informatika',
        kbk: 'Cloud Computing & Cyber Security',
        pembimbing1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        pembimbing2: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        penguji1: 'Hendri Wijaya, S.T., M.Kom.',
        penguji2: 'Dra. Nurul Rahmania, M.T.',
        nilai: 'A',
        abstrakId:
            'Pemanfaatan jaringan blockchain privat untuk integritas data akademik memerlukan pemilihan mekanisme konsensus yang tepat guna menyeimbangkan throughput transaksi dan keandalan sistem terhadap gangguan node (fault tolerance). Penelitian ini melakukan benchmarking performa antara protokol konsensus IBFT 2.0 (Proof of Authority) dan RAFT pada Hyperledger Besu menggunakan Caliper. Hasil pengujian menunjukkan RAFT menghasilkan latensi transaksi lebih rendah (rata-rata 142 ms), sedangkan IBFT 2.0 memiliki ketahanan lebih baik terhadap serangan Byzantine Fault.',
        abstrakEn:
            'The adoption of private blockchain networks for academic data integrity requires appropriate consensus mechanism selection to balance transaction throughput and fault tolerance. This research benchmarks performance between IBFT 2.0 (PoA) and RAFT consensus protocols on Hyperledger Besu using Hyperledger Caliper. Results show RAFT delivers lower latency (avg 142 ms), while IBFT 2.0 provides superior Byzantine Fault tolerance.',
        keywords: [
            'Blockchain',
            'Hyperledger Besu',
            'Proof of Authority',
            'RAFT',
            'Benchmarking',
        ],
        views: 310,
        downloads: 84,
        doi: '10.31219/itk.if.ta.2025.033',
        fileNaskah: 'Naskah_Skripsi_11201045.pdf',
        fileSize: '4.1 MB',
    },
    {
        id: 4,
        judul: 'Pengembangan Arsitektur Komponen Reusable Berbasis Vue 3 dan Tailwind CSS untuk Sistem Informasi Akademik Terintegrasi',
        judulEn: 'Development of Reusable Component Architecture Based on Vue 3 and Tailwind CSS for Integrated Academic Information Systems',
        penulis: 'Hari Yatun Nasifa',
        nim: '11231030',
        angkatan: '2023',
        tahunLulus: 2026,
        prodi: 'Informatika',
        kbk: 'Human-Computer Interaction & Software Eng.',
        pembimbing1: 'Dian Permata Sari, S.Kom., M.Kom.',
        pembimbing2: 'Rian Kurniawan, S.Kom., M.Eng.',
        penguji1: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        penguji2: 'Dra. Nurul Rahmania, M.T.',
        nilai: 'A',
        abstrakId:
            'Pengembangan sistem informasi perguruan tinggi seringkali menghadapi redundansi kode komponen antarmuka yang berdampak pada inkonsistensi pengalaman pengguna (UX) dan beban pemeliharaan tinggi. Penelitian ini menerapkan metodologi Atomic Design dalam membangun pustaka komponen reusable berbasis Vue 3 Composition API dan Tailwind CSS. Pengujian menunjukkan reduksi baris kode berulang hingga 58% dan peningkatan skor kepuasan pengguna (System Usability Scale) dari 68.5 menjadi 84.2.',
        abstrakEn:
            'Higher education information system development often encounters interface component redundancy leading to user experience inconsistency and high maintenance overhead. This research implements the Atomic Design methodology to construct a reusable component library using Vue 3 Composition API and Tailwind CSS. Evaluations indicate a 58% reduction in duplicated code and an increase in System Usability Scale score from 68.5 to 84.2.',
        keywords: [
            'Reusable Components',
            'Atomic Design',
            'Vue 3',
            'Tailwind CSS',
            'System Usability Scale',
        ],
        views: 520,
        downloads: 167,
        doi: '10.31219/itk.if.ta.2026.002',
        fileNaskah: 'Skripsi_Hari_Yatun_11231030.pdf',
        fileSize: '3.2 MB',
    },
    {
        id: 5,
        judul: 'Implementasi Algoritma Naive Bayes dan IndoBERT untuk Klasifikasi Sentimen Ulasan Pengguna Aplikasi Edukasi Kampus',
        judulEn: 'Implementation of Naive Bayes and IndoBERT Algorithms for Sentiment Classification of Campus Educational App Reviews',
        penulis: 'Ahmad Fauzan Pratama',
        nim: '11231015',
        angkatan: '2023',
        tahunLulus: 2026,
        prodi: 'Informatika',
        kbk: 'Artificial Intelligence & NLP',
        pembimbing1: 'Dr. Budi Wicaksono, M.Kom.',
        pembimbing2: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji1: 'Dra. Nurul Rahmania, M.T.',
        penguji2: 'Rian Kurniawan, S.Kom., M.Eng.',
        nilai: 'AB',
        abstrakId:
            'Analisis sentimen pada ulasan pengguna Play Store terhadap aplikasi akademik kampus sangat penting untuk menentukan prioritas perbaikan layanan digital. Penelitian ini membandingkan algoritma pembelajaran mesin tradisional Multinomial Naive Bayes dengan model pra-latih Transformer IndoBERT-base. Model IndoBERT menghasilkan F1-score sebesar 91.8%, mengungguli Naive Bayes (82.4%) terutama pada penanganan kalimat bersarkasme dan bahasa gaul mahasiswa.',
        abstrakEn:
            'Sentiment analysis on Play Store user reviews for campus academic applications is vital for prioritizing digital service enhancements. This study compares traditional Multinomial Naive Bayes with the pre-trained IndoBERT-base transformer model. IndoBERT achieved an F1-score of 91.8%, outperforming Naive Bayes (82.4%), particularly in detecting sarcasm and informal campus slang expressions.',
        keywords: [
            'Sentiment Analysis',
            'IndoBERT',
            'Naive Bayes',
            'Natural Language Processing',
            'Mobile App Reviews',
        ],
        views: 395,
        downloads: 91,
        doi: '10.31219/itk.if.ta.2026.003',
        fileNaskah: 'Naskah_Ahmad_Fauzan_11231015.pdf',
        fileSize: '2.9 MB',
    },
    {
        id: 6,
        judul: 'Sistem Deteksi Intrusi Jaringan Berbasis Deep Autoencoder untuk Mengidentifikasi Serangan DDoS pada Lingkungan Kampus',
        judulEn: 'Deep Autoencoder-Based Network Intrusion Detection System for DDoS Attack Identification in Campus Environment',
        penulis: 'Bagus Setiawan',
        nim: '11211018',
        angkatan: '2021',
        tahunLulus: 2025,
        prodi: 'Informatika',
        kbk: 'Cloud Computing & Cyber Security',
        pembimbing1: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        pembimbing2: 'Hendri Wijaya, S.T., M.Kom.',
        penguji1: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        penguji2: 'Dra. Nurul Rahmania, M.T.',
        nilai: 'A',
        abstrakId:
            'Serangan Distributed Denial of Service (DDoS) kerap melumpuhkan layanan portal akademik saat masa pengisian KRS dan pembayaran UKT. Penelitian ini merancang Network Intrusion Detection System (NIDS) tanpa pengawasan (unsupervised) menggunakan Deep Autoencoder untuk memodelkan pola lalu lintas jaringan normal dan mendeteksi anomali paket SYN-flood serta UDP-flood. Model dievaluasi pada dataset CIC-DDoS2019 dengan deteksi True Positive Rate 96.7% dan False Alarm Rate 1.3%.',
        abstrakEn:
            'Distributed Denial of Service (DDoS) attacks frequently disrupt university portal services during enrollment and tuition payment periods. This study designs an unsupervised Network Intrusion Detection System (NIDS) using Deep Autoencoders to model normal network flow patterns and detect anomalies in SYN-flood and UDP-flood packets. Evaluated on the CIC-DDoS2019 dataset, the model achieved a 96.7% True Positive Rate with only 1.3% False Alarm Rate.',
        keywords: [
            'Network Intrusion Detection',
            'Deep Autoencoder',
            'DDoS Mitigation',
            'Cyber Security',
            'Anomaly Detection',
        ],
        views: 480,
        downloads: 145,
        doi: '10.31219/itk.if.ta.2025.044',
        fileNaskah: 'Skripsi_Bagus_Setiawan_11211018.pdf',
        fileSize: '4.6 MB',
    },
    {
        id: 7,
        judul: 'Penerapan Algoritma Collaborative Filtering dan Matrix Factorization pada Sistem Rekomendasi Buku Perpustakaan Digital',
        judulEn: 'Application of Collaborative Filtering and Matrix Factorization Algorithms in Digital Library Book Recommendation Systems',
        penulis: 'Putri Ayu Wandira',
        nim: '11211052',
        angkatan: '2021',
        tahunLulus: 2025,
        prodi: 'Informatika',
        kbk: 'Data Science & Sistem Rekomendasi',
        pembimbing1: 'Dr. Budi Wicaksono, M.Kom.',
        pembimbing2: 'Dian Permata Sari, S.Kom., M.Kom.',
        penguji1: 'Rian Kurniawan, S.Kom., M.Eng.',
        penguji2: 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
        nilai: 'A',
        abstrakId:
            'Rendahnya tingkat peminjaman literatur di perpustakaan digital seringkali disebabkan oleh sulitnya mahasiswa menemukan buku yang relevan dengan topik skripsi mereka. Penelitian ini menerapkan algoritma Singular Value Decomposition (SVD) berbasis Matrix Factorization untuk mengatasi kendala data sparsity pada sistem rekomendasi. Sistem mampu menghasilkan rekomendasi dengan Root Mean Squared Error (RMSE) 0.841, meningkat 22% dibanding model User-Based Collaborative Filtering konvensional.',
        abstrakEn:
            'Low borrowing rates in digital libraries are often caused by difficulties students face in finding books relevant to their thesis topics. This research implements Singular Value Decomposition (SVD)-based Matrix Factorization to mitigate data sparsity issues in recommendation systems. The system achieves a Root Mean Squared Error (RMSE) of 0.841, a 22% improvement over conventional User-Based Collaborative Filtering.',
        keywords: [
            'Recommender System',
            'Matrix Factorization',
            'Singular Value Decomposition',
            'Collaborative Filtering',
            'Digital Library',
        ],
        views: 340,
        downloads: 78,
        doi: '10.31219/itk.if.ta.2025.062',
        fileNaskah: 'Naskah_Putri_Ayu_11211052.pdf',
        fileSize: '3.5 MB',
    },
    {
        id: 8,
        judul: 'Rancang Bangun Sistem Monitoring Kualitas Udara Ruang Laboratorium Komputer Berbasis IoT Menggunakan MQTT dan ESP32',
        judulEn: 'Design and Implementation of IoT-Based Computer Laboratory Air Quality Monitoring System Using MQTT and ESP32',
        penulis: 'Fajar Nugroho',
        nim: '11201029',
        angkatan: '2020',
        tahunLulus: 2024,
        prodi: 'Informatika',
        kbk: 'Computational Intelligence & Optimasi',
        pembimbing1: 'Hendri Wijaya, S.T., M.Kom.',
        pembimbing2: 'Rizki Pratama Nugraha, S.Si., M.Han.',
        penguji1: 'Dra. Nurul Rahmania, M.T.',
        penguji2: 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
        nilai: 'A',
        abstrakId:
            'Kondisi sirkulasi udara dan konsentrasi CO2 di dalam laboratorium komputer dapat mempengaruhi kenyamanan dan konsentrasi praktikum mahasiswa. Penelitian ini mengembangkan perangkat pemantau nirkabel menggunakan mikrokontroler ESP32, sensor MQ-135, dan DHT22 yang terhubung ke broker MQTT EMQX. Data telemetri dikirim setiap 5 detik dengan tingkat kehilangan paket (packet loss) kurang dari 0.2% dan divisualisasikan pada dashboard web secara real-time dengan peringatan dini berbasis Telegram bot.',
        abstrakEn:
            'Air circulation and CO2 concentration inside computer laboratories impact students comfort and concentration during practical sessions. This study develops a wireless monitoring node using the ESP32 microcontroller, MQ-135, and DHT22 sensors connected to an EMQX MQTT broker. Telemetry packets are transmitted every 5 seconds with packet loss under 0.2% and rendered on a real-time web dashboard featuring Telegram bot alerts.',
        keywords: [
            'Internet of Things',
            'ESP32',
            'MQTT Protocol',
            'Air Quality Monitoring',
            'Smart Campus',
        ],
        views: 612,
        downloads: 205,
        doi: '10.31219/itk.if.ta.2024.015',
        fileNaskah: 'Skripsi_Fajar_Nugroho_11201029.pdf',
        fileSize: '4.8 MB',
    },
]);

// Search & Filters State
const searchQuery = ref('');
const filterKBK = ref('all');
const filterTahun = ref('all');
const filterPembimbing = ref('all');
const sortBy = ref('terbaru');
const viewMode = ref<'grid' | 'table'>('grid');

// Dropdown options
const kbkOptions = [
    'Artificial Intelligence & NLP',
    'Computational Intelligence & Optimasi',
    'Human-Computer Interaction & Software Eng.',
    'Cloud Computing & Cyber Security',
    'Data Science & Sistem Rekomendasi',
];

const dosenOptions = [
    'Dra. Nurul Rahmania, M.T.',
    'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
    'Dian Permata Sari, S.Kom., M.Kom.',
    'Dr. Budi Wicaksono, M.Kom.',
    'Rian Kurniawan, S.Kom., M.Eng.',
    'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
    'Rizki Pratama Nugraha, S.Si., M.Han.',
    'Hendri Wijaya, S.T., M.Kom.',
];

// Toast State
const toast = ref({ show: false, message: '', isError: false });
let toastTimeout: ReturnType<typeof setTimeout> | null = null;

const showToast = (message: string, isError = false) => {
    if (toastTimeout) clearTimeout(toastTimeout);
    toast.value = { show: true, message, isError };
    toastTimeout = setTimeout(() => {
        toast.value.show = false;
    }, 3500);
};

// Filtered & Sorted Katalog Computed
const filteredKatalog = computed(() => {
    const list = katalogList.value.filter((item) => {
        const matchKBK =
            filterKBK.value === 'all' || item.kbk === filterKBK.value;
        const matchTahun =
            filterTahun.value === 'all' ||
            String(item.tahunLulus) === filterTahun.value;
        const matchPembimbing =
            filterPembimbing.value === 'all' ||
            item.pembimbing1 === filterPembimbing.value ||
            item.pembimbing2 === filterPembimbing.value;

        const q = searchQuery.value.toLowerCase().trim();
        const matchQuery =
            !q ||
            item.judul.toLowerCase().includes(q) ||
            item.penulis.toLowerCase().includes(q) ||
            item.nim.toLowerCase().includes(q) ||
            item.keywords.some((k) => k.toLowerCase().includes(q));

        return matchKBK && matchTahun && matchPembimbing && matchQuery;
    });

    if (sortBy.value === 'terbaru') {
        list.sort((a, b) => b.tahunLulus - a.tahunLulus);
    } else if (sortBy.value === 'views') {
        list.sort((a, b) => b.views - a.views);
    } else if (sortBy.value === 'downloads') {
        list.sort((a, b) => b.downloads - a.downloads);
    } else if (sortBy.value === 'judul') {
        list.sort((a, b) => a.judul.localeCompare(b.judul));
    }

    return list;
});

// Summary Statistics
const totalNaskahCount = computed(() => katalogList.value.length);
const totalDownloadsCount = computed(() =>
    katalogList.value.reduce((acc, item) => acc + item.downloads, 0),
);

// Reset Filters
const resetFilters = () => {
    searchQuery.value = '';
    filterKBK.value = 'all';
    filterTahun.value = 'all';
    filterPembimbing.value = 'all';
    sortBy.value = 'terbaru';
};

// KBK Badge styling helper
const getKBKBadgeClass = (kbk: string) => {
    switch (kbk) {
        case 'Artificial Intelligence & NLP':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-950/70 dark:text-purple-300 border border-purple-300/80 dark:border-purple-800';
        case 'Computational Intelligence & Optimasi':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-950/70 dark:text-blue-300 border border-blue-300/80 dark:border-blue-800';
        case 'Human-Computer Interaction & Software Eng.':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-300/80 dark:border-emerald-800';
        case 'Cloud Computing & Cyber Security':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-300/80 dark:border-rose-800';
        default:
            return 'bg-amber-100 text-amber-800 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-300/80 dark:border-amber-800';
    }
};

const getInitials = (name: string) => {
    if (!name) return 'TA';
    return name
        .split(' ')
        .map((n) => n[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

// Modal 1: Detail Naskah (Gambar 3.52)
const isDetailModalOpen = ref(false);
const activeDetailItem = ref<KatalogItem | null>(null);
const detailAbstractTab = ref<'id' | 'en'>('id');

const openDetailModal = (item: KatalogItem) => {
    activeDetailItem.value = item;
    item.views += 1;
    detailAbstractTab.value = 'id';
    isDetailModalOpen.value = true;
};

const closeDetailModal = () => {
    isDetailModalOpen.value = false;
    activeDetailItem.value = null;
};

const downloadNaskah = (item: KatalogItem) => {
    item.downloads += 1;
    showToast(`Mengunduh berkas "${item.fileNaskah}" (${item.fileSize})...`);
};

const generateCitation = (item: KatalogItem | null) => {
    if (!item) return '';
    const parts = item.penulis.split(' ');
    const lastName = parts[parts.length - 1];
    const firstInitials = parts
        .slice(0, -1)
        .map((p) => p[0] + '.')
        .join(' ');
    return `${lastName}, ${firstInitials} (${item.tahunLulus}). ${item.judul}. Skripsi, Institut Teknologi Kalimantan. DOI: ${item.doi}`;
};

const copyCitation = (item: KatalogItem | null) => {
    if (!item) return;
    const cit = generateCitation(item);
    if (navigator?.clipboard?.writeText) {
        navigator.clipboard
            .writeText(cit)
            .then(() => {
                showToast('Format sitasi APA berhasil disalin ke clipboard!');
            })
            .catch(() => {
                showToast('Sitasi: ' + cit);
            });
    } else {
        showToast('Sitasi: ' + cit);
    }
};

// Modal 2: Similarity Check
const isSimilarityModalOpen = ref(false);
const inputTestTitle = ref(
    'Penerapan Algoritma Genetika dan Large Language Model untuk Penjadwalan Tugas Akhir',
);
const hasRunSimilarity = ref(false);
const similarityScore = ref(0);
const mostSimilarTitles = ref<Array<{ item: KatalogItem; score: number }>>([]);

const openSimilarityModal = () => {
    isSimilarityModalOpen.value = true;
};

const closeSimilarityModal = () => {
    isSimilarityModalOpen.value = false;
};

const runSimilarityCheck = () => {
    if (!inputTestTitle.value.trim()) {
        showToast('Silakan masukkan draf judul terlebih dahulu.', true);
        return;
    }

    const qTokens = inputTestTitle.value
        .toLowerCase()
        .split(/\s+/)
        .filter((w) => w.length > 3);

    const scores = katalogList.value.map((item) => {
        const itemTokens = item.judul
            .toLowerCase()
            .split(/\s+/)
            .filter((w) => w.length > 3);
        const common = qTokens.filter((t) => itemTokens.includes(t));
        const score = Math.min(
            Math.round(
                (common.length / Math.max(qTokens.length, 1)) * 100,
            ),
            96,
        );
        return { item, score };
    });

    scores.sort((a, b) => b.score - a.score);
    mostSimilarTitles.value = scores.slice(0, 3).filter((s) => s.score > 20);
    similarityScore.value = scores[0] ? scores[0].score : 15;
    hasRunSimilarity.value = true;
    showToast(
        `Analisis selesai: Kemiripan terdeteksi sebesar ${similarityScore.value}%`,
    );
};
</script>

<template>
    <Head title="Katalog & Repositori Tugas Akhir - SIPTA IF" />

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- 1. HERO BANNER -->
        <div
            class="relative overflow-hidden rounded-2xl border border-blue-700/40 bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 p-6 text-white shadow-xl shadow-blue-950/20 md:p-8"
        >
            <div
                class="pointer-events-none absolute -right-10 -bottom-10 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"
            ></div>
            <div
                class="pointer-events-none absolute top-4 right-20 h-32 w-32 rounded-full bg-blue-400/10 blur-2xl"
            ></div>

            <div
                class="relative z-10 flex flex-col justify-between gap-6 md:flex-row md:items-center"
            >
                <div class="max-w-2xl space-y-2">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold text-blue-200 backdrop-blur-md"
                    >
                        <BookOpen class="h-3.5 w-3.5 text-blue-300" />
                        <span>Repositori Karya Ilmiah Mahasiswa Informatika ITK</span>
                    </div>
                    <h1
                        class="text-2xl font-black tracking-tight text-white leading-tight md:text-3xl"
                    >
                        Katalog & Repositori Tugas Akhir
                    </h1>
                    <p class="text-sm leading-relaxed text-blue-100/85">
                        Pusat eksplorasi arsip penelitian, naskah skripsi lengkap,
                        dan referensi akademik alumni Program Studi Informatika
                        Institut Teknologi Kalimantan untuk mendukung orisinalitas riset.
                    </p>
                </div>

                <!-- Action Buttons: Paling Kanan -->
                <div
                    class="flex flex-col items-end justify-center gap-3 shrink-0 self-end md:self-center md:ml-auto"
                >
                    <button
                        type="button"
                        @click="openSimilarityModal"
                        class="flex w-full sm:w-auto min-w-[205px] cursor-pointer items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2.5 text-xs font-bold text-slate-950 shadow-lg shadow-amber-950/20 transition-all hover:from-amber-600 hover:to-amber-700 active:scale-95 md:text-sm whitespace-nowrap"
                    >
                        <ShieldCheck class="h-4 w-4 text-slate-950" />
                        <span>Cek Kemiripan Judul</span>
                    </button>

                    <button
                        type="button"
                        @click="
                            showToast(
                                'Pedoman format penulisan naskah tugas akhir berhasil diunduh!',
                            )
                        "
                        class="flex w-full sm:w-auto min-w-[205px] cursor-pointer items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-xs font-semibold text-white backdrop-blur-md transition-all hover:bg-white/20 active:scale-95 md:text-sm whitespace-nowrap"
                    >
                        <FileText class="h-4 w-4 text-blue-200" />
                        <span>Pedoman Format TA</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. 4 KPI SUMMARY CARDS -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- KPI 1 -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-navy-surface"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400"
                >
                    <BookOpen class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Total Naskah Terpublikasi
                    </p>
                    <h3
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ totalNaskahCount }}
                        <span class="text-xs font-normal text-slate-400">Judul</span>
                    </h3>
                </div>
            </div>

            <!-- KPI 2 -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-navy-surface"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400"
                >
                    <TrendingUp class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Top Bidang Kajian
                    </p>
                    <h3
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        AI & Data
                        <span
                            class="text-xs font-bold text-purple-600 dark:text-purple-400"
                            >46%</span
                        >
                    </h3>
                </div>
            </div>

            <!-- KPI 3 -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-navy-surface"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
                >
                    <Download class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Total Unduhan Naskah
                    </p>
                    <h3
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        {{ totalDownloadsCount }}
                        <span
                            class="text-xs font-bold text-emerald-600 dark:text-emerald-400"
                            >x diakses</span
                        >
                    </h3>
                </div>
            </div>

            <!-- KPI 4 -->
            <div
                class="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-navy-surface"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"
                >
                    <Award class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        Rata-rata Predikat Sidang
                    </p>
                    <h3
                        class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                    >
                        Grade A
                        <span
                            class="text-xs font-bold text-amber-600 dark:text-amber-400"
                            >(87.5%)</span
                        >
                    </h3>
                </div>
            </div>
        </div>

        <!-- 3. SEARCH & MULTI-FILTER TOOLBAR -->
        <div
            class="space-y-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-navy-surface"
        >
            <!-- Search Bar -->
            <div class="relative">
                <Search
                    class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-slate-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari judul skripsi, nama penulis, NIM, atau kata kunci penelitian (misal: Genetika, Deep Learning, IoT)..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pr-10 pl-11 text-sm text-slate-900 placeholder-slate-400 transition-all focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700/80 dark:bg-slate-900/80 dark:text-white dark:focus:border-blue-400"
                />
                <button
                    v-show="searchQuery"
                    type="button"
                    @click="searchQuery = ''"
                    class="absolute top-1/2 right-3 -translate-y-1/2 p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    title="Hapus pencarian"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Filter Dropdowns & View Switcher -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                <div class="flex flex-wrap items-center gap-3 text-xs">
                    <!-- Filter KBK -->
                    <div class="flex items-center gap-1.5">
                        <label
                            class="font-semibold text-slate-600 whitespace-nowrap dark:text-slate-400"
                        >
                            Bidang Kajian:
                        </label>
                        <select
                            v-model="filterKBK"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <option value="all">Semua Bidang Kajian</option>
                            <option
                                v-for="kbk in kbkOptions"
                                :key="kbk"
                                :value="kbk"
                            >
                                {{ kbk }}
                            </option>
                        </select>
                    </div>

                    <!-- Filter Tahun Lulus -->
                    <div class="flex items-center gap-1.5">
                        <label
                            class="font-semibold text-slate-600 whitespace-nowrap dark:text-slate-400"
                        >
                            Tahun Lulus:
                        </label>
                        <select
                            v-model="filterTahun"
                            class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <option value="all">Semua Tahun</option>
                            <option value="2026">2026 (Terbaru)</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                    </div>

                    <!-- Filter Pembimbing -->
                    <div class="flex items-center gap-1.5">
                        <label
                            class="font-semibold text-slate-600 whitespace-nowrap dark:text-slate-400"
                        >
                            Pembimbing:
                        </label>
                        <select
                            v-model="filterPembimbing"
                            class="max-w-[200px] truncate rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        >
                            <option value="all">Semua Dosen</option>
                            <option
                                v-for="dosen in dosenOptions"
                                :key="dosen"
                                :value="dosen"
                            >
                                {{ dosen }}
                            </option>
                        </select>
                    </div>

                    <!-- Reset Filter Button -->
                    <button
                        v-if="
                            searchQuery ||
                            filterKBK !== 'all' ||
                            filterTahun !== 'all' ||
                            filterPembimbing !== 'all'
                        "
                        type="button"
                        @click="resetFilters"
                        class="flex cursor-pointer items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold text-rose-600 transition-all hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/40"
                    >
                        <RotateCcw class="h-3.5 w-3.5" />
                        <span>Reset</span>
                    </button>
                </div>

                <!-- View Switcher & Sorting -->
                <div class="flex items-center gap-3">
                    <!-- Sort Dropdown -->
                    <select
                        v-model="sortBy"
                        class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    >
                        <option value="terbaru">Tahun Lulus (Terbaru)</option>
                        <option value="views">Paling Banyak Dibaca</option>
                        <option value="downloads">Paling Banyak Diunduh</option>
                        <option value="judul">Judul (A-Z)</option>
                    </select>

                    <!-- View Switch Buttons -->
                    <div
                        class="flex items-center rounded-xl border border-slate-200/80 bg-slate-100 p-1 dark:border-slate-700/60 dark:bg-slate-800"
                    >
                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            :class="
                                viewMode === 'grid'
                                    ? 'bg-white text-blue-600 shadow-xs dark:bg-slate-900 dark:text-blue-400'
                                    : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
                            "
                            class="cursor-pointer rounded-lg p-1.5 transition-all"
                            title="Tampilan Kartu Galeri"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'table'"
                            :class="
                                viewMode === 'table'
                                    ? 'bg-white text-blue-600 shadow-xs dark:bg-slate-900 dark:text-blue-400'
                                    : 'text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
                            "
                            class="cursor-pointer rounded-lg p-1.5 transition-all"
                            title="Tampilan Tabel Rinci"
                        >
                            <List class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. SEARCH RESULTS HEADER -->
        <div class="flex items-center justify-between px-1">
            <p
                class="text-xs font-semibold text-slate-600 dark:text-slate-400"
            >
                Menampilkan
                <span class="font-bold text-slate-900 dark:text-white">{{
                    filteredKatalog.length
                }}</span>
                publikasi skripsi
            </p>
            <span class="text-[11px] text-slate-400">
                Terdaftar di Repositori Digital Informatika ITK
            </span>
        </div>

        <!-- 5. GRID VIEW MODE -->
        <div
            v-if="viewMode === 'grid' && filteredKatalog.length > 0"
            class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3"
        >
            <div
                v-for="item in filteredKatalog"
                :key="item.id"
                class="group relative flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-blue-500/50 hover:shadow-md dark:border-slate-800 dark:bg-navy-surface dark:hover:border-blue-500/50"
            >
                <!-- Card Header: Badges & Year -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <span
                            :class="getKBKBadgeClass(item.kbk)"
                            class="max-w-[200px] truncate rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                        >
                            {{ item.kbk }}
                        </span>
                        <span
                            class="rounded-lg border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs font-extrabold text-slate-700 dark:border-slate-700 dark:bg-slate-800/80 dark:text-slate-300"
                        >
                            Lulus {{ item.tahunLulus }}
                        </span>
                    </div>

                    <!-- Title -->
                    <h3
                        @click="openDetailModal(item)"
                        class="line-clamp-2 cursor-pointer text-base font-bold text-slate-900 leading-snug transition-colors group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400"
                        :title="item.judul"
                    >
                        {{ item.judul }}
                    </h3>

                    <!-- English Subtitle -->
                    <p
                        class="line-clamp-1 text-xs italic text-slate-500 dark:text-slate-400"
                    >
                        {{ item.judulEn }}
                    </p>

                    <!-- Author Info -->
                    <div
                        class="flex items-center gap-2.5 border-t border-slate-100 pt-2 dark:border-slate-800"
                    >
                        <div
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-700 dark:bg-blue-950/60 dark:text-blue-300"
                        >
                            {{ getInitials(item.penulis) }}
                        </div>
                        <div class="min-w-0">
                            <p
                                class="truncate text-xs font-bold text-slate-900 dark:text-white"
                            >
                                {{ item.penulis }}
                            </p>
                            <p class="font-mono text-[11px] text-slate-400">
                                {{ item.nim }} • {{ item.prodi }}
                            </p>
                        </div>
                    </div>

                    <!-- Supervisors -->
                    <div
                        class="space-y-1 rounded-xl border border-slate-100 bg-slate-50 p-2.5 text-[11px] text-slate-600 dark:border-slate-800/60 dark:bg-slate-900/50 dark:text-slate-300"
                    >
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="font-semibold text-slate-400"
                                >Pembimbing 1:</span
                            >
                            <span
                                class="truncate font-medium text-slate-800 dark:text-slate-200"
                                >{{ item.pembimbing1 }}</span
                            >
                        </div>
                        <div class="flex items-center gap-1.5 truncate">
                            <span class="font-semibold text-slate-400"
                                >Pembimbing 2:</span
                            >
                            <span
                                class="truncate font-medium text-slate-800 dark:text-slate-200"
                                >{{ item.pembimbing2 }}</span
                            >
                        </div>
                    </div>

                    <!-- Abstract Excerpt -->
                    <p
                        class="line-clamp-3 text-xs leading-relaxed text-slate-600 dark:text-slate-300"
                    >
                        {{ item.abstrakId }}
                    </p>

                    <!-- Research Keywords Tags -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span
                            v-for="(kw, idx) in item.keywords.slice(0, 3)"
                            :key="idx"
                            class="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:bg-slate-800/70 dark:text-slate-400 dark:hover:bg-blue-950 dark:hover:text-blue-300"
                        >
                            #{{ kw }}
                        </span>
                        <span
                            v-if="item.keywords.length > 3"
                            class="self-center text-[10px] font-bold text-slate-400"
                        >
                            +{{ item.keywords.length - 3 }}
                        </span>
                    </div>
                </div>

                <!-- Card Footer: Stats & Action Buttons -->
                <div
                    class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800"
                >
                    <!-- Views & Download Counter -->
                    <div class="flex items-center gap-3 text-[11px] text-slate-400">
                        <span class="flex items-center gap-1" title="Dilihat">
                            <Eye class="h-3.5 w-3.5" />
                            <span>{{ item.views }}</span>
                        </span>
                        <span class="flex items-center gap-1" title="Diunduh">
                            <Download class="h-3.5 w-3.5" />
                            <span>{{ item.downloads }}</span>
                        </span>
                    </div>

                    <!-- Buttons -->
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="downloadNaskah(item)"
                            class="cursor-pointer rounded-xl border border-slate-200 bg-slate-50 p-2 text-slate-500 transition-all hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-slate-600 dark:hover:bg-slate-700/60 dark:hover:text-blue-400"
                            title="Unduh Naskah Skripsi PDF"
                        >
                            <Download class="h-4 w-4" />
                        </button>

                        <button
                            type="button"
                            @click="openDetailModal(item)"
                            class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-blue-600 px-3 py-1.5 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95"
                        >
                            <span>Buka Naskah</span>
                            <ChevronRight class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. TABLE VIEW MODE -->
        <div
            v-else-if="viewMode === 'table' && filteredKatalog.length > 0"
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-navy-surface"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-slate-200 bg-slate-50 font-bold uppercase tracking-wider text-slate-600 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-400"
                        >
                            <th class="w-12 px-4 py-3.5 text-center">NO</th>
                            <th class="px-4 py-3.5">JUDUL SKRIPSI & KATA KUNCI</th>
                            <th class="px-4 py-3.5">PENULIS / NIM</th>
                            <th class="px-4 py-3.5">BIDANG KAJIAN</th>
                            <th class="px-4 py-3.5">PEMBIMBING</th>
                            <th class="px-4 py-3.5 text-center">TAHUN</th>
                            <th class="px-4 py-3.5 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60">
                        <tr
                            v-for="(item, index) in filteredKatalog"
                            :key="item.id"
                            class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-850/50"
                        >
                            <td class="px-4 py-3 text-center font-bold text-slate-400">
                                {{ index + 1 }}
                            </td>
                            <td class="max-w-md px-4 py-3">
                                <a
                                    href="#"
                                    @click.prevent="openDetailModal(item)"
                                    class="line-clamp-2 font-bold text-slate-900 transition-colors hover:text-blue-600 dark:text-white dark:hover:text-blue-400"
                                >
                                    {{ item.judul }}
                                </a>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span
                                        v-for="kw in item.keywords.slice(0, 2)"
                                        :key="kw"
                                        class="text-[10px] text-slate-400"
                                        >#{{ kw }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-bold text-slate-900 dark:text-white">
                                    {{ item.penulis }}
                                </p>
                                <p class="font-mono text-[11px] text-slate-400">
                                    {{ item.nim }}
                                </p>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    :class="getKBKBadgeClass(item.kbk)"
                                    class="rounded-full px-2 py-0.5 text-[10px] font-bold"
                                >
                                    {{ item.kbk }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-3 text-[11px] text-slate-600 whitespace-nowrap dark:text-slate-300"
                            >
                                <p class="font-medium">1. {{ item.pembimbing1 }}</p>
                                <p class="text-slate-400">2. {{ item.pembimbing2 }}</p>
                            </td>
                            <td
                                class="px-4 py-3 text-center font-extrabold text-slate-700 whitespace-nowrap dark:text-slate-300"
                            >
                                {{ item.tahunLulus }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button
                                        type="button"
                                        @click="openDetailModal(item)"
                                        class="cursor-pointer rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1.5 text-[11px] font-bold text-blue-700 transition-all hover:bg-blue-100 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300 dark:hover:bg-blue-900/60"
                                    >
                                        Detail
                                    </button>
                                    <button
                                        type="button"
                                        @click="downloadNaskah(item)"
                                        class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                        title="Unduh PDF"
                                    >
                                        <Download class="h-4 w-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7. EMPTY SEARCH STATE -->
        <div
            v-if="filteredKatalog.length === 0"
            class="space-y-3 rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-800 dark:bg-navy-surface"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 dark:bg-slate-800"
            >
                <ShieldAlert class="h-8 w-8 text-amber-500" />
            </div>
            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                Tidak Menemukan Naskah Skripsi
            </h3>
            <p
                class="mx-auto max-w-sm text-xs text-slate-500 dark:text-slate-400"
            >
                Tidak ada arsip tugas akhir yang sesuai dengan kriteria pencarian
                "<span class="font-semibold text-blue-600 dark:text-blue-400">{{
                    searchQuery
                }}</span
                >". Silakan gunakan kata kunci lain atau reset filter Anda.
            </p>
            <button
                type="button"
                @click="resetFilters"
                class="cursor-pointer rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95"
            >
                Reset Semua Filter
            </button>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: DETAIL KATALOG TA (Gambar 3.52)   -->
        <!-- ========================================== -->
        <div
            v-if="isDetailModalOpen && activeDetailItem"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
            @click.self="closeDetailModal"
        >
            <div
                class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-[#111827]"
            >
                <!-- Modal Header -->
                <div
                    class="flex items-start justify-between gap-4 border-b border-slate-100 bg-slate-50/50 p-6 dark:border-slate-800 dark:bg-slate-900/40"
                >
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                :class="getKBKBadgeClass(activeDetailItem.kbk)"
                                class="rounded-full px-2.5 py-0.5 text-[11px] font-bold"
                            >
                                {{ activeDetailItem.kbk }}
                            </span>
                            <span
                                class="rounded-full border border-emerald-300 bg-emerald-100 px-2 py-0.5 text-[11px] font-extrabold text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300"
                            >
                                Nilai Sidang: {{ activeDetailItem.nilai }}
                            </span>
                            <span class="text-[11px] font-bold text-slate-500">
                                Tahun Lulus: {{ activeDetailItem.tahunLulus }}
                            </span>
                        </div>
                        <h2
                            class="text-lg font-extrabold text-slate-900 leading-snug md:text-xl dark:text-white"
                        >
                            {{ activeDetailItem.judul }}
                        </h2>
                        <p
                            class="text-xs italic text-slate-500 dark:text-slate-400"
                        >
                            {{ activeDetailItem.judulEn }}
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="closeDetailModal"
                        class="shrink-0 cursor-pointer rounded-xl p-2 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Body (Scrollable) -->
                <div
                    class="space-y-6 overflow-y-auto p-6 text-xs text-slate-700 dark:text-slate-300"
                >
                    <!-- Author & Supervisor Cards Grid -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <!-- Author Card -->
                        <div
                            class="space-y-1 rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Penulis / Mahasiswa
                            </span>
                            <p
                                class="text-sm font-extrabold text-slate-900 dark:text-white"
                            >
                                {{ activeDetailItem.penulis }}
                            </p>
                            <p class="font-mono text-slate-500">
                                NIM: {{ activeDetailItem.nim }} • Angkatan
                                {{ activeDetailItem.angkatan }}
                            </p>
                            <p
                                class="text-[11px] font-semibold text-blue-600 dark:text-blue-400"
                            >
                                {{ activeDetailItem.prodi }} - ITK
                            </p>
                        </div>

                        <!-- Committee Card -->
                        <div
                            class="space-y-1 rounded-xl border border-slate-200/80 bg-slate-50 p-3.5 dark:border-slate-800 dark:bg-slate-900"
                        >
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                            >
                                Dewan Penguji & Pembimbing
                            </span>
                            <p class="text-[11px]">
                                <b class="text-slate-900 dark:text-white">Pembimbing 1:</b>
                                {{ activeDetailItem.pembimbing1 }}
                            </p>
                            <p class="text-[11px]">
                                <b class="text-slate-900 dark:text-white">Pembimbing 2:</b>
                                {{ activeDetailItem.pembimbing2 }}
                            </p>
                            <p class="text-[11px]">
                                <b class="text-slate-900 dark:text-white">Penguji 1:</b>
                                {{ activeDetailItem.penguji1 }}
                            </p>
                            <p class="text-[11px]">
                                <b class="text-slate-900 dark:text-white">Penguji 2:</b>
                                {{ activeDetailItem.penguji2 }}
                            </p>
                        </div>
                    </div>

                    <!-- Abstract Section with Tabs (Bilingual) -->
                    <div class="space-y-3">
                        <div
                            class="flex items-center justify-between border-b border-slate-200 pb-2 dark:border-slate-800"
                        >
                            <div class="flex items-center gap-3">
                                <button
                                    type="button"
                                    @click="detailAbstractTab = 'id'"
                                    :class="
                                        detailAbstractTab === 'id'
                                            ? 'border-blue-600 font-bold text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                            : 'border-transparent text-slate-400 hover:text-slate-600'
                                    "
                                    class="flex cursor-pointer items-center gap-1.5 border-b-2 pb-1 text-xs transition-all"
                                >
                                    <span>🇮🇩 Abstrak (Indonesia)</span>
                                </button>
                                <button
                                    type="button"
                                    @click="detailAbstractTab = 'en'"
                                    :class="
                                        detailAbstractTab === 'en'
                                            ? 'border-blue-600 font-bold text-blue-600 dark:border-blue-400 dark:text-blue-400'
                                            : 'border-transparent text-slate-400 hover:text-slate-600'
                                    "
                                    class="flex cursor-pointer items-center gap-1.5 border-b-2 pb-1 text-xs transition-all"
                                >
                                    <span>🇬🇧 Abstract (English)</span>
                                </button>
                            </div>

                            <span class="font-mono text-[11px] text-slate-400">
                                {{ activeDetailItem.doi }}
                            </span>
                        </div>

                        <div
                            class="rounded-xl border border-slate-200/80 bg-slate-50 p-4 leading-relaxed text-justify text-slate-700 dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300"
                        >
                            <p v-if="detailAbstractTab === 'id'">
                                {{ activeDetailItem.abstrakId }}
                            </p>
                            <p v-else class="italic">
                                {{ activeDetailItem.abstrakEn }}
                            </p>
                        </div>
                    </div>

                    <!-- Research Keywords -->
                    <div class="space-y-1.5">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Kata Kunci (Keywords)
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="(kw, idx) in activeDetailItem.keywords"
                                :key="idx"
                                class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700 dark:border-blue-800 dark:bg-blue-950/60 dark:text-blue-300"
                            >
                                {{ kw }}
                            </span>
                        </div>
                    </div>

                    <!-- Download Files Section -->
                    <div class="space-y-2">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Berkas Tugas Akhir Lengkap
                        </span>
                        <div
                            class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-3.5 dark:border-slate-800 dark:bg-slate-900/30"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-xs font-bold text-rose-600 dark:bg-rose-950/60 dark:text-rose-400"
                                >
                                    PDF
                                </div>
                                <div>
                                    <p
                                        class="font-bold text-slate-900 dark:text-white"
                                    >
                                        {{ activeDetailItem.fileNaskah }}
                                    </p>
                                    <p class="text-[11px] text-slate-400">
                                        Ukuran Berkas:
                                        {{ activeDetailItem.fileSize }} • Digital Watermarked
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="downloadNaskah(activeDetailItem)"
                                class="flex cursor-pointer items-center gap-1.5 rounded-xl bg-blue-600 px-4 py-2 font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-95 whitespace-nowrap"
                            >
                                <Download class="h-4 w-4" />
                                <span>Unduh PDF</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Citation Generator -->
                    <div
                        class="space-y-2 rounded-xl border border-slate-200 bg-slate-100/70 p-4 dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-400"
                            >
                                Kutipan Sitasi (Format APA)
                            </span>
                            <button
                                type="button"
                                @click="copyCitation(activeDetailItem)"
                                class="flex cursor-pointer items-center gap-1 text-xs font-bold text-blue-600 hover:underline dark:text-blue-400"
                            >
                                <Copy class="h-3.5 w-3.5" />
                                <span>Salin Sitasi</span>
                            </button>
                        </div>
                        <p
                            class="rounded-lg border border-slate-200/60 bg-white p-2.5 font-mono text-[11px] text-slate-700 leading-relaxed dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300"
                        >
                            {{ generateCitation(activeDetailItem) }}
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/40"
                >
                    <span class="text-[11px] text-slate-400">
                        Hak Cipta © Institut Teknologi Kalimantan
                    </span>
                    <button
                        type="button"
                        @click="closeDetailModal"
                        class="cursor-pointer rounded-xl bg-slate-200 px-5 py-2 text-xs font-bold text-slate-800 transition-all hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL 2: CEK KEMIRIPAN JUDUL (PRE-CHECK)   -->
        <!-- ========================================== -->
        <div
            v-if="isSimilarityModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
            @click.self="closeSimilarityModal"
        >
            <div
                class="w-full max-w-2xl space-y-5 rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-[#111827]"
            >
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <h3
                            class="flex items-center gap-2 text-lg font-extrabold text-slate-900 dark:text-white"
                        >
                            <ShieldCheck class="h-5 w-5 text-amber-500" />
                            <span>Deteksi Kemiripan Judul (Pre-submission)</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Uji potensi duplikasi topik calon skripsi Anda terhadap
                            database arsip tugas akhir yang telah lulus untuk memastikan
                            novelty riset.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="closeSimilarityModal"
                        class="cursor-pointer rounded-lg p-1.5 text-slate-400 transition-colors hover:text-slate-600 dark:hover:text-slate-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-3">
                    <div>
                        <label
                            class="mb-1 block text-xs font-bold text-slate-700 dark:text-slate-300"
                        >
                            Draf Judul Tugas Akhir Baru:
                        </label>
                        <textarea
                            v-model="inputTestTitle"
                            rows="3"
                            placeholder="Contoh: Penerapan Algoritma Genetika untuk Penjadwalan Sidang Seminar Tugas Akhir..."
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-white"
                        ></textarea>
                    </div>

                    <button
                        type="button"
                        @click="runSimilarityCheck"
                        class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-xl bg-blue-600 py-2.5 text-xs font-bold text-white shadow-xs transition-all hover:bg-blue-700 active:scale-98"
                    >
                        <Search class="h-4 w-4" />
                        <span>Analisis Tingkat Kemiripan</span>
                    </button>
                </div>

                <!-- Similarity Results -->
                <div
                    v-if="hasRunSimilarity"
                    class="space-y-3 border-t border-slate-100 pt-2 dark:border-slate-800"
                >
                    <div
                        class="flex items-center justify-between rounded-xl p-3.5"
                        :class="
                            similarityScore > 65
                                ? 'border border-amber-300 bg-amber-50 dark:border-amber-800 dark:bg-amber-950/60'
                                : 'border border-emerald-300 bg-emerald-50 dark:border-emerald-800 dark:bg-emerald-950/60'
                        "
                    >
                        <div>
                            <p
                                class="text-xs font-bold"
                                :class="
                                    similarityScore > 65
                                        ? 'text-amber-800 dark:text-amber-300'
                                        : 'text-emerald-800 dark:text-emerald-300'
                                "
                            >
                                Tingkat Kemiripan: {{ similarityScore }}%
                            </p>
                            <p
                                class="text-[11px]"
                                :class="
                                    similarityScore > 65
                                        ? 'text-amber-700 dark:text-amber-400'
                                        : 'text-emerald-700 dark:text-emerald-400'
                                "
                            >
                                {{
                                    similarityScore > 65
                                        ? 'Perhatian: Ada kemiripan yang cukup tinggi dengan riset terdahulu. Pastikan kebaruan (novelty) metode atau objek riset Anda.'
                                        : 'Aman: Tingkat kemiripan rendah. Topik memiliki potensi novelty yang baik.'
                                }}
                            </p>
                        </div>
                    </div>

                    <div v-if="mostSimilarTitles.length > 0" class="space-y-2">
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider text-slate-400"
                        >
                            Riset Terdahulu Paling Mendekati:
                        </span>
                        <div
                            v-for="(sim, idx) in mostSimilarTitles"
                            :key="idx"
                            class="flex items-center justify-between gap-3 rounded-xl border border-slate-200/60 bg-slate-50 p-2.5 text-xs dark:border-slate-800 dark:bg-slate-900"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate font-bold text-slate-800 dark:text-slate-200"
                                >
                                    {{ sim.item.judul }}
                                </p>
                                <p class="text-[11px] text-slate-400">
                                    Oleh: {{ sim.item.penulis }} ({{
                                        sim.item.tahunLulus
                                    }})
                                </p>
                            </div>
                            <span
                                class="font-mono text-xs font-bold text-amber-600 whitespace-nowrap dark:text-amber-400"
                            >
                                {{ sim.score }}% mirip
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 8. FLOATING TOAST NOTIFICATION -->
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-4 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform translate-y-4 opacity-0"
        >
            <div
                v-if="toast.show"
                class="fixed right-6 bottom-6 z-50 flex items-center gap-3 rounded-2xl border px-4 py-3 text-xs font-semibold shadow-xl"
                :class="
                    toast.isError
                        ? 'border-rose-700 bg-rose-900 text-white'
                        : 'border-slate-700 bg-slate-900 text-white dark:border-blue-700 dark:bg-blue-950'
                "
            >
                <CheckCircle2
                    v-if="!toast.isError"
                    class="h-4 w-4 text-emerald-400"
                />
                <X v-else class="h-4 w-4 text-rose-400" />
                <span>{{ toast.message }}</span>
            </div>
        </Transition>
    </div>
</template>
