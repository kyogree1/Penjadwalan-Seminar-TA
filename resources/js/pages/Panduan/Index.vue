<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ChevronDown,
    FileCheck2,
    FileText,
    KeyRound,
    Search,
    ShieldAlert,
    Users,
} from 'lucide-vue-next';
import { useAuth } from '@/composables/useAuth';

const { role } = useAuth();
const searchQuery = ref('');
const openIndex = ref<number | null>(0);

const toggleAccordion = (idx: number) => {
    openIndex.value = openIndex.value === idx ? null : idx;
};

interface GuideItem {
    title: string;
    menu: string;
    link?: string;
    content: string[];
    warning?: {
        title: string;
        notes: string[];
    };
}

const mahasiswaGuides: GuideItem[] = [
    {
        title: 'Masuk & Melengkapi Profil',
        menu: 'User Profile & Unggah e-TTD',
        link: '/profile',
        content: [
            'Masuk memakai username (alamat email kampus Anda) dengan password berupa NIM. Percobaan login dibatasi 5 kali per menit.',
            'Buka menu User Profile untuk melengkapi data diri dan mengunggah tanda tangan digital (e-TTD).',
        ],
        warning: {
            title: 'Perhatian Penting!',
            notes: [
                'Password mahasiswa tetap NIM dan tidak bisa diganti sendiri. Kalau tidak bisa masuk, hubungi Tendik — halaman login sengaja tidak menyediakan tautan lupa password.',
                'Selama tanda tangan belum diunggah, semua menu pendaftaran terkunci dan Anda akan selalu dikembalikan ke halaman User Profile. Tanda tangan dipakai untuk membubuhkan paraf Anda di formulir TA yang dicetak sistem.',
                'Data Prodi Anda diisi oleh Tendik/Koorprodi. Prodi wajib terisi karena periode pendaftaran Sempro dan Sidang TA dibuka per prodi — kalau kosong, pendaftaran akan ditolak dengan pesan "Prodi Anda belum diatur".',
            ],
        },
    },
    {
        title: 'Pengajuan Judul & Dosen Pembimbing',
        menu: 'Menu: Pengajuan Judul',
        link: '/pendaftaran/judul',
        content: [
            'Pastikan telah berdiskusi awal dengan calon Dosen Pembimbing 1 dan 2 sebelum menginput data.',
            'Masukkan judul lengkap dan deskripsi bidang penelitian (AI, IoT, Cyber Security, RPL, dll.).',
            'Centang pernyataan konfirmasi bahwa kedua pembimbing telah bersedia.',
            'Setelah dikirim dan disetujui, pembimbing TIDAK DAPAT DIUBAH lagi secara mandiri.',
        ],
    },
    {
        title: 'Pelaksanaan Bimbingan & Logbook (Form TA-04)',
        menu: 'Menu: Bimbingan',
        link: '/pendaftaran/bimbingan',
        content: [
            'Wajib melakukan bimbingan minimal 8 kali per semester untuk masing-masing pembimbing.',
            'Input logbook setiap selesai bimbingan (tanggal, rangkuman materi, dan keterangan tempat online/offline).',
            'Unduh Lembar Monitoring Bimbingan (Form TA-04) saat hendak mendaftar Sempro atau Sidang.',
        ],
    },
    {
        title: 'Pendaftaran Seminar Proposal (Sempro)',
        menu: 'Menu: Seminar Proposal',
        link: '/pendaftaran/sempro',
        content: [
            'Buka formulir pendaftaran Sempro saat status periode gelombang aktif dibuka.',
            'Unggah berkas: Lembar Kehadiran Sempro (Form TA-03D min. 5 kali), Proposal BAB 1-3, dan Bukti Cek Plagiasi Turnitin (maks 20%).',
            'Tunggu verifikasi berkas dari tim akademik sebelum jadwal seminar diterbitkan oleh sistem penjadwalan.',
        ],
    },
    {
        title: 'Pendaftaran Sidang Tugas Akhir',
        menu: 'Menu: Sidang TA',
        link: '/pendaftaran/sidang',
        content: [
            'Prasyarat: Telah dinyatakan LULUS seminar proposal dan menyelesaikan revisi proposal.',
            'Unggah Draft Laporan TA (BAB 1-5), Skor IAET (ITK Academic English Test) berupa angka (opsional), dan Bukti Turnitin terbaru.',
            'Setelah sidang dan revisi di-ACC semua dosen, Lembar Pengesahan Tugas Akhir resmi akan otomatis terbit.',
        ],
    },
];

const tendikGuides: GuideItem[] = [
    {
        title: 'Verifikasi Berkas Seminar Proposal & Sidang TA',
        menu: 'Layanan Verifikasi Berkas',
        link: '/tendik/verifikasi',
        content: [
            'Buka menu Verifikasi Berkas untuk melihat antrean pengajuan pendaftaran dari mahasiswa.',
            'Gunakan tab filter "Sidang Akhir TA" atau "Seminar Proposal" serta kolom pencarian (nama/NIM/judul) untuk memfilter pendaftar.',
            'Klik tombol "Periksa" untuk membuka modal tinjauan berkas pendaftaran.',
            'Periksa status ketersediaan setiap dokumen persyaratan yang diunggah mahasiswa (Proposal/Draft, Lembar Kehadiran, Cek Turnitin, Skor IAET).',
            'Tentukan hasil pemeriksaan: klik "Verifikasi Berkas" untuk meloloskan ke tahap penjadwalan Kaprodi, atau "Tolak Berkas" jika dokumen belum lengkap.',
        ],
        warning: {
            title: 'Ketentuan Verifikasi Tendik',
            notes: [
                'Jika berkas ditolak, sertakan alasan penolakan secara jelas pada field "Catatan Petugas Tendik" agar mahasiswa memahami bagian berkas yang perlu diperbaiki.',
                'Pendaftaran yang sudah berstatus "terjadwal" atau "selesai" tidak dapat diverifikasi ulang demi integritas jadwal ujian yang sudah terbit.',
            ],
        },
    },
    {
        title: 'Manajemen Akun Mahasiswa & Data Direktori',
        menu: 'Layanan Direktori Mahasiswa',
        link: '/tendik/mahasiswa',
        content: [
            'Akses menu Kelola Mahasiswa untuk melihat seluruh akun mahasiswa aktif di lingkungan Program Studi Informatika.',
            'Pencarian fleksibel: cari data pendaftar berdasarkan nama lengkap, NIM, username, maupun alamat email kampus.',
            'Klik tombol "Tambah Mahasiswa" untuk menambahkan data akun baru secara manual (wajib menyertakan nama dan email).',
            'Gunakan fitur "Edit" untuk memperbarui nama/email dan "Hapus" untuk menghapus akun yang belum memiliki riwayat akademik.',
        ],
        warning: {
            title: 'Perhatian Integritas Akun',
            notes: [
                'Akun mahasiswa yang sudah memiliki riwayat pendaftaran judul, bimbingan, sempro, maupun sidang dilindungi oleh sistem dan tidak dapat dihapus.',
                'Username dan NIM akun mahasiswa terikat pada basis data akademik utama prodi.',
            ],
        },
    },
    {
        title: 'Import Massal Data Mahasiswa via CSV',
        menu: 'Layanan Import CSV',
        link: '/tendik/mahasiswa',
        content: [
            'Unduh format resmi berkas melalui tombol "Unduh Template CSV" di panel Import CSV Mahasiswa.',
            'Isi data sesuai kolom standar: name,email (baris header wajib dipertahankan).',
            'Format berkas harus CSV UTF-8 dengan ukuran maksimal 1 MiB dan paling banyak 500 baris data per proses import.',
            'Pilih berkas CSV pada input file; sistem akan secara otomatis melakukan validasi dan menampilkan pratinjau (preview) data yang valid.',
            'Periksa jumlah akun pada hasil preview server, lalu klik "Simpan import CSV" untuk menyelesaikan pendaftaran akun massal.',
        ],
        warning: {
            title: 'Ketentuan Validasi CSV',
            notes: [
                'Sistem hanya membuat akun baru; jika terdapat duplikasi email pada sistem maupun dalam file CSV, proses import akan dibatalkan untuk mencegah konflik identitas.',
                'Import parsial tidak didukung — pastikan seluruh baris data pada berkas CSV sudah valid sebelum menekan tombol simpan.',
            ],
        },
    },
    {
        title: 'Reset Password Default Akun Mahasiswa',
        menu: 'Layanan Reset Password',
        link: '/tendik/mahasiswa',
        content: [
            'Gunakan tombol "Reset" atau "Reset Password" pada baris data mahasiswa yang lupa sandi atau terkendala akses masuk.',
            'Konfirmasi identitas mahasiswa (Nama dan NIM) pada dialog konfirmasi reset password.',
            'Klik tombol "Ya, Reset Password" untuk mengembalikan kata sandi akun ke konfigurasi default sistem.',
            'Informasikan kepada mahasiswa yang bersangkutan bahwa kata sandi telah berhasil dikembalikan ke default.',
        ],
        warning: {
            title: 'Kebijakan Keamanan Akun',
            notes: [
                'Demi alasan keamanan, nilai password default tidak ditampilkan secara terbuka di layar petugas.',
                'Halaman login publik sengaja menonaktifkan fitur lupa password mandiri bagi mahasiswa; segala reset akun wajib melalui wewenang petugas Tendik.',
            ],
        },
    },
    {
        title: 'Pemantauan Status Arsip & Dokumen Akademik',
        menu: 'Layanan Status Arsip',
        link: '/tendik/arsip',
        content: [
            'Buka menu Status Arsip untuk meninjau ketersediaan infrastruktur cold storage dokumen dan modul penerbitan SK digital.',
            'Halaman ini memberikan informasi transparan terkait integrasi sistem yang sedang berjalan dan batasan operasional portal saat ini.',
        ],
    },
];

const isTendik = computed(() => role.value === 'tendik');

const currentGuides = computed(() =>
    isTendik.value ? tendikGuides : mahasiswaGuides,
);

const filteredGuideItems = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    if (!query) return currentGuides.value;
    return currentGuides.value.filter((item) =>
        [item.title, item.menu, ...item.content, ...(item.warning?.notes ?? [])]
            .join(' ')
            .toLowerCase()
            .includes(query),
    );
});
</script>

<template>
    <Head
        :title="
            isTendik
                ? 'Panduan Operasional Tendik - SIPTA IF'
                : 'Panduan SIPTA IF'
        "
    />

    <div class="mx-auto max-w-7xl space-y-6">
        <!-- Header Title Box -->
        <div
            class="rounded-2xl border border-slate-200/80 bg-white px-6 py-5 shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div class="flex items-center gap-2">
                <span
                    class="rounded-md px-2 py-0.5 text-[10px] font-semibold"
                    :class="
                        isTendik
                            ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                            : 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                    "
                >
                    {{ isTendik ? 'Petugas Tendik' : 'Mahasiswa' }}
                </span>
            </div>
            <h1
                class="mt-1 text-xl font-bold tracking-tight text-slate-900 dark:text-white"
            >
                {{
                    isTendik
                        ? 'Panduan Operasional Tendik'
                        : 'Panduan Layanan Mahasiswa'
                }}
            </h1>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                {{
                    isTendik
                        ? 'Pedoman pelaksanaan tugas verifikasi berkas, pengelolaan akun mahasiswa, dan administrasi akademik SIPTA IF.'
                        : 'Alur pelaksanaan Tugas Akhir mulai dari pengajuan judul, logbook bimbingan, hingga pendaftaran sidang.'
                }}
            </p>
        </div>

        <!-- Search Box Box -->
        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <div>
                <h2 class="text-base font-bold text-slate-900 dark:text-white">
                    {{
                        isTendik ? 'Prosedur & SOP Tendik' : 'Panduan Mahasiswa'
                    }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{
                        isTendik
                            ? 'Temukan tata cara verifikasi pendaftaran dan penanganan kendala akses akun.'
                            : 'Alur Tugas Akhir dari pengajuan judul sampai nilai sidang keluar.'
                    }}
                </p>
            </div>

            <div class="relative w-full sm:w-72">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                />
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Cari prosedur atau kata kunci..."
                    class="h-9 w-full rounded-xl border border-slate-200 bg-slate-50/80 pr-3 pl-9 text-xs text-slate-800 transition-colors placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:outline-hidden dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:focus:bg-slate-900"
                />
            </div>
        </div>

        <!-- Accordion List -->
        <div v-if="filteredGuideItems.length" class="space-y-4">
            <div
                v-for="(item, idx) in filteredGuideItems"
                :key="idx"
                class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs transition-colors dark:border-slate-800 dark:bg-[#0E1626]"
            >
                <!-- Accordion Header -->
                <div
                    class="flex cursor-pointer items-center justify-between p-5 hover:bg-slate-50/60 dark:hover:bg-slate-900/60"
                    @click="toggleAccordion(idx)"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold"
                            :class="
                                isTendik
                                    ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/80 dark:text-amber-400'
                                    : 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400'
                            "
                        >
                            {{ idx + 1 }}
                        </span>
                        <div>
                            <h3
                                class="text-sm font-bold text-slate-900 dark:text-white"
                            >
                                {{ item.title }}
                            </h3>
                            <p
                                class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500"
                            >
                                {{ item.menu }}
                            </p>
                        </div>
                    </div>

                    <ChevronDown
                        class="h-4 w-4 text-slate-400 transition-transform"
                        :class="{ 'rotate-180': openIndex === idx }"
                    />
                </div>

                <!-- Accordion Content -->
                <div
                    v-if="openIndex === idx"
                    class="space-y-4 border-t border-slate-100 p-6 text-xs text-slate-700 dark:border-slate-800 dark:text-slate-300"
                >
                    <ul class="list-disc space-y-2 pl-5 leading-relaxed">
                        <li v-for="(p, pIdx) in item.content" :key="pIdx">
                            {{ p }}
                        </li>
                    </ul>

                    <!-- Warning / Note Box -->
                    <div
                        v-if="item.warning"
                        class="space-y-2 rounded-xl border border-amber-200/90 bg-amber-50/70 p-4 text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        <h4 class="text-xs font-bold">
                            {{ item.warning.title }}
                        </h4>
                        <ul
                            class="list-disc space-y-1 pl-4 text-[11px] leading-relaxed text-amber-800 dark:text-amber-300"
                        >
                            <li
                                v-for="(note, nIdx) in item.warning.notes"
                                :key="nIdx"
                            >
                                {{ note }}
                            </li>
                        </ul>
                    </div>

                    <Link
                        v-if="item.link"
                        :href="item.link"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-xs font-bold text-slate-800 transition-colors hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                    >
                        <span
                            >Buka
                            {{
                                item.menu
                                    .replace('Menu: ', '')
                                    .replace('Layanan ', '')
                            }}</span
                        >
                        <svg
                            class="h-3.5 w-3.5"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M9 5l7 7-7 7"
                            />
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <div
            v-else
            class="rounded-2xl border border-slate-200/80 bg-white p-10 text-center shadow-xs dark:border-slate-800 dark:bg-[#0E1626]"
        >
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Panduan tidak ditemukan. Coba kata kunci lain.
            </p>
        </div>
    </div>
</template>
