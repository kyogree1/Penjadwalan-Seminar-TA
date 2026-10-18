<?php

namespace Database\Seeders;

use App\Models\Bimbingan;
use App\Models\PengajuanJudul;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Mahasiswa
        $mahasiswaSeedData = [
            [
                'name' => 'Muhammad Azka Yunastio',
                'username' => '11231036',
                'nim_nip' => '11231036',
                'email' => '11231036@student.itk.ac.id',
                'no_telpon' => '081255443321',
                'angkatan' => '2023',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Sempro',
            ],
            [
                'name' => 'Hari Yatun Nasifa',
                'username' => '11231030',
                'nim_nip' => '11231030',
                'email' => '11231030@student.itk.ac.id',
                'no_telpon' => '081344556677',
                'angkatan' => '2023',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Bimbingan',
            ],
            [
                'name' => 'Junnior Marcellino Polla',
                'username' => '11231034',
                'nim_nip' => '11231034',
                'email' => '11231034@student.itk.ac.id',
                'no_telpon' => '082155667788',
                'angkatan' => '2023',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Bimbingan',
            ],
            [
                'name' => 'Mochammad Reezqi Pratama',
                'username' => '11231041',
                'nim_nip' => '11231041',
                'email' => '11231041@student.itk.ac.id',
                'no_telpon' => '081399887766',
                'angkatan' => '2023',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Sempro',
            ],
            [
                'name' => 'Akmal Falah Maulana',
                'username' => '11231006',
                'nim_nip' => '11231006',
                'email' => 'akmal.falah@student.itk.ac.id',
                'no_telpon' => '085712345678',
                'angkatan' => '2023',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Aktif TA',
            ],
            [
                'name' => 'Bagus Setiawan',
                'username' => '11211018',
                'nim_nip' => '11211018',
                'email' => '11211018@student.itk.ac.id',
                'no_telpon' => '082199887766',
                'angkatan' => '2021',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Aktif TA',
            ],
            [
                'name' => 'Kevin Julio Alexander',
                'username' => '11221045',
                'nim_nip' => '11221045',
                'email' => '11221045@student.itk.ac.id',
                'no_telpon' => '082351341742',
                'angkatan' => '2022',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Aktif TA',
            ],
            [
                'name' => 'Ahmad Iqbal Fadhillah',
                'username' => '11191004',
                'nim_nip' => '11191004',
                'email' => '11191004@student.itk.ac.id',
                'no_telpon' => '085159119436',
                'angkatan' => '2019',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Lulus',
            ],
            [
                'name' => 'Alan Kartikacandra',
                'username' => '11191006',
                'nim_nip' => '11191006',
                'email' => '11191006@student.itk.ac.id',
                'no_telpon' => '082198969536',
                'angkatan' => '2019',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Lulus',
            ],
            [
                'name' => 'Andriani Fenny Maulidha',
                'username' => '11191009',
                'nim_nip' => '11191009',
                'email' => '11191009@student.itk.ac.id',
                'no_telpon' => '085345385638',
                'angkatan' => '2019',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Lulus',
            ],
            [
                'name' => 'Rizky Wahyu Saputra',
                'username' => '11201045',
                'nim_nip' => '11201045',
                'email' => '11201045@student.itk.ac.id',
                'no_telpon' => '081233445566',
                'angkatan' => '2020',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Sidang Akhir',
            ],
            [
                'name' => 'Putri Ayu Wandira',
                'username' => '11211052',
                'nim_nip' => '11211052',
                'email' => '11211052@student.itk.ac.id',
                'no_telpon' => '081988776655',
                'angkatan' => '2021',
                'jurusan' => 'Teknik Elektro, Informatika, dan Bisnis',
                'prodi' => 'Informatika',
                'status_ta' => 'Aktif TA',
            ],
        ];

        foreach ($mahasiswaSeedData as $mhs) {
            User::updateOrCreate(
                ['email' => $mhs['email']],
                [
                    'name' => $mhs['name'],
                    'username' => $mhs['username'],
                    'role' => 'mahasiswa',
                    'nim_nip' => $mhs['nim_nip'],
                    'no_telpon' => $mhs['no_telpon'],
                    'angkatan' => $mhs['angkatan'],
                    'jurusan' => $mhs['jurusan'],
                    'prodi' => $mhs['prodi'],
                    'status_ta' => $mhs['status_ta'],
                    'jabatan' => 'Mahasiswa ' . $mhs['prodi'],
                    'password' => bcrypt('password'),
                ]
            );
        }

        // 2. Data Master Dosen (Matches Front-End masterDosenList)
        $dosenSeedData = [
            [
                'name' => "Dr.Eng. Devy Setiorini Sa'adiyah, S.T., M.S.",
                'username' => '199201212024062001',
                'nim_nip' => '199201212024062001',
                'prodi' => 'Teknik Mesin',
                'email' => 'devysetiorini@lecturer.itk.ac.id',
                'jabatan' => 'Lektor',
            ],
            [
                'name' => 'Dr.Eng., Samsu Dlukha Nurcholik, S.T., M.Sc.',
                'username' => '198903222019031008',
                'nim_nip' => '198903222019031008',
                'prodi' => 'Teknik Perkapalan',
                'email' => 'dlukha@lecturer.itk.ac.id',
                'jabatan' => 'Lektor Kepala',
            ],
            [
                'name' => 'Rosa Eliviani, S.Kom., M.T.',
                'username' => '199808102025062011',
                'nim_nip' => '199808102025062011',
                'prodi' => 'Teknik Biomedis',
                'email' => 'rosa.eliviani@lecturer.itk.ac.id',
                'jabatan' => 'Asisten Ahli',
            ],
            [
                'name' => 'Nisa Rizqiya Fadhliana, S.Kom., M.T.',
                'username' => '198804102019032020',
                'nim_nip' => '198804102019032020',
                'prodi' => 'Informatika',
                'email' => 'nisafadhliana@lecturer.itk.ac.id',
                'jabatan' => 'Lektor Kepala',
            ],
            [
                'name' => 'Nur Fajri Azhar, S.Kom., M.Kom.',
                'username' => '199205182019031015',
                'nim_nip' => '199205182019031015',
                'prodi' => 'Informatika',
                'email' => 'fajri@lecturer.itk.ac.id',
                'jabatan' => 'Lektor',
            ],
            [
                'name' => 'Ramadhan Paninggalih, S.Si., M.Si., M.Sc.',
                'username' => '199502272020121006',
                'nim_nip' => '199502272020121006',
                'prodi' => 'Informatika',
                'email' => 'ramadhanpaninggalih@lecturer.itk.ac.id',
                'jabatan' => 'Asisten Ahli',
            ],
            [
                'name' => 'Riska Kurniyanto Abdullah, S.T., M.Kom.',
                'username' => '198803082020121011',
                'nim_nip' => '198803082020121011',
                'prodi' => 'Informatika',
                'email' => 'riska.abdullah@lecturer.itk.ac.id',
                'jabatan' => 'Lektor',
            ],
            [
                'name' => 'Rizal Kusuma Putra, M.T.',
                'username' => '199809162024061001',
                'nim_nip' => '199809162024061001',
                'prodi' => 'Informatika',
                'email' => 'rizal.putra@lecturer.itk.ac.id',
                'jabatan' => 'Tenaga Pengajar',
            ],
            [
                'name' => 'Ahmad Faisal Ridwan, S.Kom., M.Kom., Ph.D.',
                'username' => '198901122019031012',
                'nim_nip' => '198901122019031012',
                'prodi' => 'Informatika',
                'email' => 'faisal.ridwan@lecturer.itk.ac.id',
                'jabatan' => 'Lektor Kepala',
            ],
            [
                'name' => 'Dian Permata Sari, S.Kom., M.Kom.',
                'username' => '199205182019032015',
                'nim_nip' => '199205182019032015',
                'prodi' => 'Informatika',
                'email' => 'dian.permata@lecturer.itk.ac.id',
                'jabatan' => 'Lektor',
            ],
            [
                'name' => 'Dr. Budi Wicaksono, M.Kom.',
                'username' => '198506152015041003',
                'nim_nip' => '198506152015041003',
                'prodi' => 'Informatika',
                'email' => 'budi.wicaksono@lecturer.itk.ac.id',
                'jabatan' => 'Lektor Kepala',
            ],
            [
                'name' => 'Ir. Hendra Gunawan, S.T., M.T.',
                'username' => 'EXT-2026-PERTAMINA-01',
                'nim_nip' => 'EXT-2026-PERTAMINA-01',
                'prodi' => 'Industri Energi & Migas',
                'email' => 'hendra.gunawan@pertamina.com',
                'jabatan' => 'Praktisi Ahli',
            ],
        ];

        foreach ($dosenSeedData as $d) {
            User::updateOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['name'],
                    'username' => $d['username'],
                    'role' => 'dosen',
                    'nim_nip' => $d['nim_nip'],
                    'prodi' => $d['prodi'],
                    'jabatan' => $d['jabatan'],
                    'password' => bcrypt('password'),
                ]
            );
        }

        $dosenTejo = User::updateOrCreate(
            ['email' => 'tejo.wahyu@lecturer.itk.ac.id'],
            [
                'name' => 'Dr. Ir. Tejo Wahyu Susanto, S.T., M.Kom.',
                'username' => '198504122010121003',
                'role' => 'dosen',
                'nim_nip' => '198504122010121003',
                'prodi' => 'Informatika',
                'jabatan' => 'Lektor',
                'password' => bcrypt('password'),
            ]
        );

        $dosenFaisal = User::where('email', 'faisal.ridwan@lecturer.itk.ac.id')->first();
        $dosenDian = User::where('email', 'dian.permata@lecturer.itk.ac.id')->first();
        $dosenBudi = User::where('email', 'budi.wicaksono@lecturer.itk.ac.id')->first();

        $dosenRian = User::updateOrCreate(
            ['email' => 'rian.kurniawan@lecturer.itk.ac.id'],
            [
                'name' => 'Rian Kurniawan, S.Kom., M.Eng.',
                'username' => '199103202020121011',
                'role' => 'dosen',
                'nim_nip' => '199103202020121011',
                'prodi' => 'Informatika',
                'jabatan' => 'Asisten Ahli',
                'password' => bcrypt('password'),
            ]
        );

        // 3. Kaprodi (Koordinator Prodi)
        $kaprodi = User::updateOrCreate(
            ['email' => 'kaprodi.if@itk.ac.id'],
            [
                'name' => 'Prof. Dr. Eng. Muhammad Ridwan, S.T., M.T.',
                'username' => '198003152005011002',
                'role' => 'kaprodi',
                'nim_nip' => '198003152005011002',
                'prodi' => 'Informatika',
                'jabatan' => 'Ketua Program Studi Informatika',
                'password' => bcrypt('password'),
            ]
        );

        // 3b. Koorprodi (Koordinator Tugas Akhir)
        $koorprodi = User::updateOrCreate(
            ['email' => 'koorprodi.if@itk.ac.id'],
            [
                'name' => 'Dra. Nurul Rahmania, M.T.',
                'username' => 'koorprodi',
                'role' => 'koordinator',
                'nim_nip' => '198801012015041001',
                'prodi' => 'Informatika',
                'jabatan' => 'Koordinator Tugas Akhir (Koorpro)',
                'password' => bcrypt('password'),
            ]
        );

        // 4. Tendik (Tenaga Kependidikan / Admin Akademik)
        User::updateOrCreate(
            ['email' => 'tendik.fsti@itk.ac.id'],
            [
                'name' => 'Siti Nurhaliza, S.Kom.',
                'username' => '199208192018032001',
                'role' => 'tendik',
                'nim_nip' => '199208192018032001',
                'prodi' => 'Fakultas Sains & Teknologi Informasi',
                'jabatan' => 'Tenaga Kependidikan / Staf Akademik',
                'password' => bcrypt('password'),
            ]
        );

        // 5. Data Master Ruangan
        Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Lab Riset Multimedia'],
            ['gedung' => 'Gedung A', 'lantai' => 'Lantai 2', 'kapasitas' => 30, 'status' => 'tersedia']
        );
        Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Lab Jaringan & Keamanan Siber'],
            ['gedung' => 'Gedung B', 'lantai' => 'Lantai 3', 'kapasitas' => 25, 'status' => 'tersedia']
        );
        Ruangan::updateOrCreate(
            ['nama_ruangan' => 'Ruang Sidang Utama Informatika'],
            ['gedung' => 'Gedung A', 'lantai' => 'Lantai 3', 'kapasitas' => 40, 'status' => 'tersedia']
        );

        // 6. Data Bimbingan & Judul Disetujui (5 sesi per dosen pembimbing)
        $mhsAzka = User::where('email', '11231036@student.itk.ac.id')->first();
        $mhsHari = User::where('email', '11231030@student.itk.ac.id')->first();
        $mhsJunnior = User::where('email', '11231034@student.itk.ac.id')->first();
        $mhsReezqi = User::where('email', '11231041@student.itk.ac.id')->first();
        $mhsAkmal = User::where('email', 'akmal.falah@student.itk.ac.id')->first();
        $mhsBagus = User::where('email', '11211018@student.itk.ac.id')->first();
        $mhsKevin = User::where('email', '11221045@student.itk.ac.id')->first();
        $mhsPutri = User::where('email', '11211052@student.itk.ac.id')->first();

        $titlesData = [
            // 1. Azka: Koorprodi (P1 - 5 sesi), Faisal (P2 - 5 sesi)
            [
                'mahasiswa' => $mhsAzka,
                'judul' => 'Penerapan Algoritma Genetika dan Large Language Model pada Sistem Penjadwalan Seminar Tugas Akhir',
                'bidang' => 'Computational Intelligence & Optimasi',
                'p1' => $koorprodi->id,
                'p2' => $dosenFaisal->id,
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Topik penelitian sangat relevan dan metodologi sudah matang. Disetujui.',
                'sessions' => [
                    ['tanggal' => '2026-08-10', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 1 - Pendahuluan', 'rangkuman' => 'Diskusi rumusan masalah antrian penjadwalan seminar manual di prodi Informatika ITK.', 'catatan' => 'Perkuat rumusan masalah dengan perbandingan jurnal terkait Algoritma Genetika.', 'status' => 'delivered'],
                    ['tanggal' => '2026-08-25', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 2 - Tinjauan Pustaka & State of the Art', 'rangkuman' => 'Kajian komparasi penelitian penjadwalan GA 5 tahun terakhir dan metrik evaluasi fitness.', 'catatan' => 'Tambahkan referensi evaluasi NLP untuk chatbot.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-10', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 3 - Use Case & Activity Diagram', 'rangkuman' => 'Review 21 activity diagram sistem SIPTA IF dan alur swimlane aktor.', 'catatan' => 'Rapikan sequence diagram login dan plotting penguji.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-24', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 3 - Mockup UI & Komponen Web', 'rangkuman' => 'Penyusunan prototype antarmuka Vue 3 + Tailwind CSS untuk pendaftaran Sempro.', 'catatan' => 'Desain responsif sudah bagus. Siapkan draf proposal lengkap.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-28', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 1-3 - Finalisasi Draf Proposal', 'rangkuman' => 'Review kelengkapan draf proposal, abstrak, dan bukti cek plagiasi Turnitin.', 'catatan' => 'Draf proposal disetujui untuk maju Seminar Proposal.', 'status' => 'delivered'],

                    ['tanggal' => '2026-08-18', 'dosen_id' => $dosenFaisal->id, 'bab' => 'Bab 1 - Batasan Masalah & Variabel', 'rangkuman' => 'Penetapan batasan sistem: penjadwalan tanpa ruangan fisik, fokus kuota dosen.', 'catatan' => 'Batasan masalah sudah spesifik. Lanjutkan tinjauan pustaka Bab 2.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-02', 'dosen_id' => $dosenFaisal->id, 'bab' => 'Bab 3 - Representasi Kromosom GA', 'rangkuman' => 'Perancangan kromosom: gen mahasiswa, slot waktu dosen, dan dosen penguji 1 & 2.', 'catatan' => 'Representasi kromosom valid. Tentukan penalty weight.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-17', 'dosen_id' => $dosenFaisal->id, 'bab' => 'Bab 3 - ERD & Database Schema', 'rangkuman' => 'Struktur entitas tabel users, mahasiswa, dosen, ketersediaan_waktu, dan jadwal.', 'catatan' => 'Normalisasi tabel jadwal_seminar sudah tepat.', 'status' => 'delivered'],
                    ['tanggal' => '2026-10-02', 'dosen_id' => $dosenFaisal->id, 'bab' => 'Revisi Pasca Evaluasi Proposal', 'rangkuman' => 'Penyempurnaan slide presentasi seminar proposal.', 'catatan' => 'Slide presentasi sudah terstruktur dengan baik.', 'status' => 'delivered'],
                    ['tanggal' => '2026-10-05', 'dosen_id' => $dosenFaisal->id, 'bab' => 'Pemeriksaan Akhir Kelayakan Sempro', 'rangkuman' => 'Pengecekan akhir berkas administrasi dan validasi lembar kendali bimbingan.', 'catatan' => 'Disetujui penuh untuk pendaftaran seminar proposal.', 'status' => 'delivered'],
                ],
            ],

            // 2. Reezqi: Faisal (P1), Koorprodi (P2 - 4 sesi, 1 menunggu paraf)
            [
                'mahasiswa' => $mhsReezqi,
                'judul' => 'Optimasi Alokasi Dosen Penguji Ujian Skripsi dengan Pendekatan Constraint Satisfaction Problem dan Algoritma Genetika',
                'bidang' => 'Computational Intelligence & Optimasi',
                'p1' => $dosenFaisal->id,
                'p2' => $koorprodi->id,
                'status' => 'revisi',
                'catatan_kaprodi' => 'Perjelas batas waktu optimasi penjadwalan dan parameter mutasi pada bab metodologi.',
                'sessions' => [
                    ['tanggal' => '2026-08-16', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 1 - Batasan Sistem', 'rangkuman' => 'Penetapan ruang lingkup seminar prodi IF.', 'catatan' => 'Disetujui.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-01', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 3 - Alur Komputasi AI', 'rangkuman' => 'Diagram alir optimasi penjadwalan GA.', 'catatan' => 'Bagus, lanjutkan.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-16', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 3 - Uji Coba Dataset', 'rangkuman' => 'Simulasi jadwal 20 mahasiswa dengan 12 dosen.', 'catatan' => 'Hasil uji coba awal menunjukkan fitness 0.98.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-30', 'dosen_id' => $koorprodi->id, 'bab' => 'Bab 3 - Draf Proposal Lengkap', 'rangkuman' => 'Pemeriksaan akhir dokumen proposal sebelum sempro.', 'catatan' => 'Periksa kembali sitasi daftar pustaka.', 'status' => 'process'],
                ],
            ],

            // 3. Akmal: Tejo (P1), Koorprodi (P2)
            [
                'mahasiswa' => $mhsAkmal,
                'judul' => 'Rancang Bangun Frontend Dashboard Monitoring Progres Bimbingan Tugas Akhir Menggunakan Progressive Web Apps',
                'bidang' => 'Software Engineering & PWA',
                'p1' => $dosenTejo->id,
                'p2' => $koorprodi->id,
                'status' => 'menunggu',
                'catatan_kaprodi' => 'Menunggu telaah awal calon pembimbing tugas akhir.',
                'sessions' => [
                    ['tanggal' => '2026-08-11', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 1 - Pendahuluan', 'rangkuman' => 'Latar belakang kendala monitoring bimbingan via chat informal.', 'catatan' => 'Perjelas fitur PWA yang akan diimplementasikan.', 'status' => 'delivered'],
                    ['tanggal' => '2026-08-27', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 2 - Service Worker & PWA', 'rangkuman' => 'Tinjauan teknologi caching Service Worker.', 'catatan' => 'Bagus.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-11', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Desain Integrasi REST API', 'rangkuman' => 'Koneksi state Vue dengan endpoint server.', 'catatan' => 'Gunakan Axios atau Fetch dengan interceptor token.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-25', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Penyusunan Instrumen UAT', 'rangkuman' => 'Kuesioner penerimaan pengguna untuk dosen dan mahasiswa.', 'catatan' => 'Disetujui.', 'status' => 'delivered'],
                    ['tanggal' => '2026-10-06', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 1-3 - Finalisasi Berkas Sempro', 'rangkuman' => 'Validasi lembar bimbingan 5 sesi lengkap dosen pembimbing 1.', 'catatan' => 'Acc maju seminar proposal.', 'status' => 'delivered'],
                ],
            ],

            // 4. Hari: Koorprodi (P1), Tejo (P2 - 4 sesi, 3 delivered, 1 process)
            [
                'mahasiswa' => $mhsHari,
                'judul' => 'Pengembangan Antarmuka Sistem Informasi Akademik Tugas Akhir Berbasis Desain Komponen Reusable dan Responsif',
                'bidang' => 'Human-Computer Interaction & Software Eng.',
                'p1' => $koorprodi->id,
                'p2' => $dosenTejo->id,
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Topik penelitian disetujui. Lanjutkan persiapan Bab 1-3.',
                'sessions' => [
                    ['tanggal' => '2026-08-20', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 1 - Rumusan Masalah', 'rangkuman' => 'Fokus responsivitas mobile dan dark mode.', 'catatan' => 'Revisi batasan browser target.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-05', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Wireframe & Design System', 'rangkuman' => 'Presentasi mockup dashboard dan halaman bimbingan.', 'catatan' => 'Tingkatkan kontras warna pada status badge.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-22', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Evaluasi Heuristik UI', 'rangkuman' => 'Kuesioner Nielsen Norman Group untuk antarmuka SIPTA.', 'catatan' => 'Rancangan kuesioner disetujui.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-29', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Draf Bab 1-3 Lengkap', 'rangkuman' => 'Pengecekan tata tulis dan sitasi pustaka IEEE.', 'catatan' => 'Perbaiki penulisan abstrak bahasa inggris.', 'status' => 'process'],
                ],
            ],

            // 5. Junnior: Budi (P1), Koorprodi (P2 - 3 sesi)
            [
                'mahasiswa' => $mhsJunnior,
                'judul' => 'Rancang Bangun Arsitektur Microservices dan Keamanan Data pada Portal Layanan Ujian Tugas Akhir',
                'bidang' => 'Cloud Computing & Cyber Security',
                'p1' => $dosenBudi->id,
                'p2' => $koorprodi->id,
                'status' => 'revisi',
                'catatan_kaprodi' => 'Perjelas studi kasus pembanding dan batasan pengujian keamanan sistem pada bagian latar belakang.',
                'sessions' => [
                    ['tanggal' => '2026-08-26', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 1 - Analisis Ancaman Keamanan', 'rangkuman' => 'Penerapan JWT dan otorisasi RBAC.', 'catatan' => 'Tambahkan mitigasi serangan CSRF & XSS.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-12', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Skema Komunikasi REST & Event', 'rangkuman' => 'Desain kontrak JSON API untuk backend.', 'catatan' => 'Validasi payload skema sudah bagus.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-27', 'dosen_id' => $dosenTejo->id, 'bab' => 'Bab 3 - Deployment Docker', 'rangkuman' => 'Containerization backend services.', 'catatan' => 'Tambahkan docker-compose di lampiran.', 'status' => 'process'],
                ],
            ],

            // 6. Bagus: Koorprodi (P1), Dian (P2)
            [
                'mahasiswa' => $mhsBagus,
                'judul' => 'Implementasi NLP dan Named Entity Recognition untuk Ekstraksi Otomatis Dokumen Skripsi',
                'bidang' => 'Natural Language Processing',
                'p1' => $koorprodi->id,
                'p2' => $dosenDian->id,
                'status' => 'menunggu',
                'catatan_kaprodi' => 'Dalam antrean telaah oleh calon pembimbing.',
                'sessions' => [
                    ['tanggal' => '2026-08-14', 'dosen_id' => $kaprodi->id, 'bab' => 'Bab 1 - Pendahuluan', 'rangkuman' => 'Latar belakang ekstraksi otomatis dokumen akademik.', 'catatan' => 'Perjelas dataset yang digunakan.', 'status' => 'delivered'],
                    ['tanggal' => '2026-08-30', 'dosen_id' => $kaprodi->id, 'bab' => 'Bab 2 - Tinjauan Pustaka Transformer', 'rangkuman' => 'Perbandingan IndoBERT vs RoBERTa.', 'catatan' => 'Kajian literatur sudah memadai.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-15', 'dosen_id' => $kaprodi->id, 'bab' => 'Bab 3 - Anotasi Korpus Data', 'rangkuman' => 'Proses pelabelan entitas dengan Doccano.', 'catatan' => 'Metodologi anotasi disetujui.', 'status' => 'delivered'],
                    ['tanggal' => '2026-09-26', 'dosen_id' => $kaprodi->id, 'bab' => 'Bab 3 - Skenario Pelatihan Model', 'rangkuman' => 'Parameter fine-tuning dan evaluasi F1-Score.', 'catatan' => 'Siapkan draf bab 1-3 lengkap.', 'status' => 'delivered'],
                    ['tanggal' => '2026-10-04', 'dosen_id' => $kaprodi->id, 'bab' => 'Bab 1-3 - Draf Proposal Final', 'rangkuman' => 'Pemeriksaan akhir naskah proposal skripsi.', 'catatan' => 'Acc maju seminar proposal.', 'status' => 'delivered'],
                ],
            ],

            // 7. Kevin: Koorprodi (P1), Rian (P2)
            [
                'mahasiswa' => $mhsKevin,
                'judul' => 'Pengembangan Sistem Deteksi Intrusi Jaringan Berbasis Deep Learning dan Packet Inspection',
                'bidang' => 'Keamanan Siber & Jaringan',
                'p1' => $koorprodi->id,
                'p2' => $dosenRian->id,
                'status' => 'menunggu',
                'catatan_kaprodi' => 'Pengajuan usulan judul baru dari mahasiswa, menunggu review kelayakan dan metodologi.',
                'sessions' => [],
            ],

            // 8. Putri: Dian (P1), Koorprodi (P2)
            [
                'mahasiswa' => $mhsPutri,
                'judul' => 'Sistem Rekomendasi Pemilihan Topik Skripsi Menggunakan Content-Based Filtering dan Analisis Minat Bakat',
                'bidang' => 'Sistem Cerdas & Rekomendasi',
                'p1' => $dosenDian->id,
                'p2' => $koorprodi->id,
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Topik penelitian sangat bagus dan metodologi filtering sesuai. Disetujui.',
                'sessions' => [],
            ],
        ];

        foreach ($titlesData as $data) {
            if (! $data['mahasiswa']) {
                continue;
            }

            PengajuanJudul::updateOrCreate(
                [
                    'mahasiswa_id' => $data['mahasiswa']->id,
                ],
                [
                    'judul_ta' => $data['judul'],
                    'bidang_penelitian' => $data['bidang'],
                    'pembimbing_1_id' => $data['p1'],
                    'pembimbing_2_id' => $data['p2'],
                    'telah_konsultasi' => true,
                    'status' => $data['status'] ?? 'disetujui',
                    'catatan_kaprodi' => $data['catatan_kaprodi'] ?? null,
                    'disetujui_oleh' => ($data['status'] ?? 'disetujui') === 'disetujui' ? $kaprodi->id : null,
                    'disetujui_at' => ($data['status'] ?? 'disetujui') === 'disetujui' ? now() : null,
                ]
            );

            foreach ($data['sessions'] as $sesi) {
                Bimbingan::updateOrCreate(
                    [
                        'mahasiswa_id' => $data['mahasiswa']->id,
                        'tanggal' => $sesi['tanggal'],
                        'keterangan' => $sesi['bab'],
                    ],
                    [
                        'dosen_id' => $sesi['dosen_id'],
                        'rangkuman' => $sesi['rangkuman'],
                        'catatan_dosen' => $sesi['catatan'],
                        'status' => $sesi['status'],
                    ]
                );
            }
        }
    }
}
