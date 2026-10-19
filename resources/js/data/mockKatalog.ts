export interface KatalogItem {
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

export const kbkOptions = [
    'Artificial Intelligence & NLP',
    'Computational Intelligence & Optimasi',
    'Human-Computer Interaction & Software Eng.',
    'Cloud Computing & Cyber Security',
    'Data Science & Sistem Rekomendasi',
];

export const dosenOptions = [
    'Dra. Nurul Rahmania, M.T.',
    'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
    'Dian Permata Sari, S.Kom., M.Kom.',
    'Dr. Budi Wicaksono, M.Kom.',
    'Rian Kurniawan, S.Kom., M.Eng.',
    'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
    'Rizki Pratama Nugraha, S.Si., M.Han.',
    'Hendri Wijaya, S.T., M.Kom.',
];

export const mockKatalogList: KatalogItem[] = [
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
];
