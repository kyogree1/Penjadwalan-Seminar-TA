<?php

namespace Database\Seeders;

use App\Models\PengajuanJudul;
use App\Models\PendaftaranSempro;
use App\Models\PendaftaranSidang;
use App\Models\Penjadwalan;
use App\Models\Ruangan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CsvRealDataSeeder extends Seeder
{
    private array $lecturerCache = [];
    private array $studentCache = [];
    private array $roomCache = [];

    public function run(): void
    {
        $this->seedFixedRoles();

        $semproPath = base_path('sempro-ta.csv');
        $sidangPath = base_path('sidang-ta.csv');

        if (file_exists($semproPath)) {
            $this->seedFromCsv($semproPath, 'sempro');
        }

        if (file_exists($sidangPath)) {
            $this->seedFromCsv($sidangPath, 'sidang');
        }

        $this->seedAkmalData();
        $this->balanceSemproData();
    }

    private function balanceSemproData(): void
    {
        // CSV sempro asli hanya berisi 1 record, sedangkan sidang berisi 48 record.
        // Kita jadikan separuh (sekitar 24 peserta) berstatus Sempro agar kalender berimbang antara Sempro (biru) & Sidang (ungu).
        $sidangSchedules = Penjadwalan::where('tipe', 'sidang')
            ->where('mahasiswa_id', '!=', $this->studentCache['11231006']?->id)
            ->get();

        foreach ($sidangSchedules as $idx => $s) {
            if ($idx % 2 === 0) { // Genap kita jadikan Sempro
                // 1. Update tipe jadwal
                $s->update(['tipe' => 'sempro']);

                // 2. Buat record PendaftaranSempro
                $pendaftaranSempro = PendaftaranSempro::updateOrCreate(
                    [
                        'mahasiswa_id' => $s->mahasiswa_id,
                    ],
                    [
                        'judul_ta' => PengajuanJudul::where('mahasiswa_id', $s->mahasiswa_id)->value('judul_ta') ?: 'Seminar Proposal Tugas Akhir',
                        'bentuk_ta' => 'Skripsi',
                        'status' => 'terjadwal',
                        'gelombang' => 1,
                    ]
                );

                // Update foreign ID di penjadwalan
                $s->update(['pendaftaran_id' => $pendaftaranSempro->id]);
            }
        }
    }

    private function seedAkmalData(): void
    {
        $akmal = $this->studentCache['11231006'] ?? User::where('username', '11231006')->first();
        if (!$akmal) return;

        // Dosen pembimbing Tejo & Penguji
        $p1 = User::where('username', '198504122010121003')->first();
        $p2 = User::where('role', 'dosen')->where('id', '!=', $p1?->id)->first();
        $u1 = User::where('role', 'dosen')->whereNotIn('id', array_filter([$p1?->id, $p2?->id]))->first();
        $u2 = User::where('role', 'dosen')->whereNotIn('id', array_filter([$p1?->id, $p2?->id, $u1?->id]))->first();

        $judulStr = 'Rancang Bangun Sistem Informasi Pengelolaan Seminar dan Tugas Akhir Berbasis Web Terintegrasi';

        // 1. Pengajuan Judul
        PengajuanJudul::updateOrCreate(
            ['mahasiswa_id' => $akmal->id],
            [
                'judul_ta' => $judulStr,
                'bidang_penelitian' => 'Rekayasa Perangkat Lunak',
                'pembimbing_1_id' => $p1?->id,
                'pembimbing_2_id' => $p2?->id,
                'telah_konsultasi' => true,
                'status' => 'disetujui',
                'catatan_kaprodi' => 'Judul dan metodologi telah disetujui.',
                'disetujui_at' => now(),
            ]
        );

        // 2. Pendaftaran Sempro
        $sempro = PendaftaranSempro::updateOrCreate(
            ['mahasiswa_id' => $akmal->id],
            [
                'judul_ta' => $judulStr,
                'bentuk_ta' => 'Skripsi',
                'lokasi_mitra' => 'Institut Teknologi Kalimantan',
                'status' => 'terjadwal',
                'gelombang' => 1,
            ]
        );

        // 3. Pendaftaran Sidang
        $sidang = PendaftaranSidang::updateOrCreate(
            ['mahasiswa_id' => $akmal->id],
            [
                'judul_ta' => $judulStr,
                'status' => 'verifikasi_tendik',
                'gelombang' => 1,
            ]
        );

        // 4. Jadwal Sempro Akmal (Terjadwal & Terpublikasi)
        $ruang = Ruangan::where('nama_ruangan', 'B-102')->first() ?? Ruangan::first();
        Penjadwalan::updateOrCreate(
            [
                'tipe' => 'sempro',
                'pendaftaran_id' => $sempro->id,
                'mahasiswa_id' => $akmal->id,
            ],
            [
                'ruangan_id' => $ruang?->id,
                'pembimbing_1_id' => $p1?->id,
                'pembimbing_2_id' => $p2?->id,
                'penguji_1_id' => $u1?->id,
                'penguji_2_id' => $u2?->id,
                'tanggal' => now()->addDays(5)->toDateString(),
                'jam_mulai' => '09:00:00',
                'jam_selesai' => '10:30:00',
                'mode' => 'offline',
                'gelombang' => 1,
                'status' => 'terpublikasi',
                'catatan' => 'Seminar proposal dijadwalkan secara resmi.',
            ]
        );
    }

    private function seedFixedRoles(): void
    {
        // 1. Akun Mahasiswa Utama (Akmal Falah Maulana - 11231006)
        $akmal = User::updateOrCreate(
            ['username' => '11231006'],
            [
                'name' => 'Akmal Falah Maulana',
                'email' => 'akmal.falah@student.itk.ac.id',
                'role' => 'mahasiswa',
                'nim_nip' => 'NIM: 11231006',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Mahasiswa S1 Informatika',
                'password' => Hash::make('password'),
            ]
        );
        $this->studentCache['11231006'] = $akmal;

        // 2. Akun Kaprodi (Prof. Dr. Eng. Muhammad Ridwan)
        User::updateOrCreate(
            ['username' => '198003152005011002'],
            [
                'name' => 'Prof. Dr. Eng. Muhammad Ridwan, S.T., M.T.',
                'email' => 'kaprodi.if@itk.ac.id',
                'role' => 'kaprodi',
                'nim_nip' => 'NIP: 198003152005011002',
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Ketua Program Studi Informatika',
                'password' => Hash::make('password'),
            ]
        );

        // 3. Akun Tendik (Siti Nurhaliza, S.Kom.)
        User::updateOrCreate(
            ['username' => '199208192018032001'],
            [
                'name' => 'Siti Nurhaliza, S.Kom.',
                'email' => 'tendik.fsti@itk.ac.id',
                'role' => 'tendik',
                'nim_nip' => 'NIP: 199208192018032001',
                'prodi' => 'Fakultas Sains & Teknologi Informasi',
                'jabatan' => 'Tenaga Kependidikan / Staf Akademik',
                'password' => Hash::make('password'),
            ]
        );

        // 4. Default Seed Ruangan
        $defaultRooms = [
            'B-102' => ['gedung' => 'Gedung B', 'lantai' => 'Lantai 1'],
            'B-103' => ['gedung' => 'Gedung B', 'lantai' => 'Lantai 1'],
            'B-201' => ['gedung' => 'Gedung B', 'lantai' => 'Lantai 2'],
            'B-207' => ['gedung' => 'Gedung B', 'lantai' => 'Lantai 2'],
            'G-103' => ['gedung' => 'Gedung G', 'lantai' => 'Lantai 1'],
            'Ruang Rapat A2 (A-308)' => ['gedung' => 'Gedung A', 'lantai' => 'Lantai 3'],
            'Ruang Lab Riset Multimedia' => ['gedung' => 'Gedung A', 'lantai' => 'Lantai 2'],
            'Ruang Lab Jaringan & Keamanan Siber' => ['gedung' => 'Gedung B', 'lantai' => 'Lantai 3'],
            'Ruang Sidang Utama Informatika' => ['gedung' => 'Gedung A', 'lantai' => 'Lantai 3'],
        ];

        foreach ($defaultRooms as $name => $meta) {
            $this->roomCache[$name] = Ruangan::updateOrCreate(
                ['nama_ruangan' => $name],
                [
                    'gedung' => $meta['gedung'],
                    'lantai' => $meta['lantai'],
                    'kapasitas' => 30,
                    'status' => 'tersedia',
                ]
            );
        }

        // 5. Seed Periode Pendaftaran (Sempro & Sidang)
        // Tanggal pendaftaran awal bulan (contoh: tanggal 3 s.d. 10 bulan berjalan)
        $startPendaftaran = now()->startOfMonth()->addDays(2)->toDateString(); // Tanggal 3
        $endPendaftaran = now()->startOfMonth()->addDays(9)->toDateString();   // Tanggal 10

        \Illuminate\Support\Facades\DB::table('schedule_periods')->updateOrInsert(
            ['name' => 'Seminar Proposal TA Gelombang 1'],
            [
                'tipe' => 'sempro',
                'gelombang' => 1,
                'capacity' => 50,
                'start_date' => $startPendaftaran,
                'end_date' => $endPendaftaran,
                'slots' => json_encode([]),
                'is_open' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        \Illuminate\Support\Facades\DB::table('schedule_periods')->updateOrInsert(
            ['name' => 'Sidang Tugas Akhir Gelombang 1'],
            [
                'tipe' => 'sidang',
                'gelombang' => 1,
                'capacity' => 40,
                'start_date' => $startPendaftaran,
                'end_date' => $endPendaftaran,
                'slots' => json_encode([]),
                'is_open' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function seedFromCsv(string $path, string $type): void
    {
        $handle = fopen($path, 'r');
        if ($handle === false) return;

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            return;
        }

        // Clean headers
        $cleanHeaders = array_map(fn($h) => trim($h), $headers);
        $headerMap = array_flip($cleanHeaders);

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) continue;

            $nim = trim($row[$headerMap['NIM'] ?? 3] ?? '');
            $namaMhs = trim($row[$headerMap['Nama Mahasiswa'] ?? 4] ?? '');
            $judul = trim($row[$headerMap['Judul TA'] ?? 6] ?? '');
            $bentukTa = trim($row[$headerMap['Jenis TA'] ?? 5] ?? 'Skripsi');
            $pembimbing1Name = trim($row[$headerMap['Pembimbing Utama'] ?? 7] ?? '');
            $pembimbing2Name = trim($row[$headerMap['Pembimbing Pendamping'] ?? 8] ?? '');
            $penguji1Name = trim($row[$headerMap['Penguji 1'] ?? 9] ?? '');
            $penguji2Name = trim($row[$headerMap['Penguji 2'] ?? 10] ?? '');
            $tglStr = trim($row[$headerMap['Tanggal'] ?? 12] ?? '');
            $waktuStr = trim($row[$headerMap['Waktu'] ?? 13] ?? '');
            $tempatStr = trim($row[$headerMap['Tempat'] ?? 14] ?? '');

            if (!$nim || !$namaMhs) continue;

            // 1. Get or create Mahasiswa User
            $mhs = $this->getOrCreateMahasiswa($nim, $namaMhs);

            // 2. Get or create Lecturers
            $p1 = $pembimbing1Name ? $this->getOrCreateDosen($pembimbing1Name) : null;
            $p2 = $pembimbing2Name ? $this->getOrCreateDosen($pembimbing2Name) : null;
            $u1 = $penguji1Name ? $this->getOrCreateDosen($penguji1Name) : null;
            $u2 = $penguji2Name ? $this->getOrCreateDosen($penguji2Name) : null;

            // 3. Create or update Pengajuan Judul (Status: disetujui)
            if ($judul) {
                PengajuanJudul::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhs->id,
                        'judul_ta' => $judul,
                    ],
                    [
                        'bidang_penelitian' => 'Rekayasa Perangkat Lunak & Sistem Cerdas',
                        'pembimbing_1_id' => $p1?->id,
                        'pembimbing_2_id' => $p2?->id,
                        'telah_konsultasi' => true,
                        'status' => 'disetujui',
                        'catatan_kaprodi' => 'Disetujui untuk pelaksanaan TA.',
                        'disetujui_at' => now(),
                    ]
                );
            }

            // 4. Create Pendaftaran (Sempro / Sidang)
            $pendaftaranId = null;
            if ($type === 'sempro') {
                $pendaftaran = PendaftaranSempro::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhs->id,
                        'judul_ta' => $judul ?: 'Seminar Proposal TA',
                    ],
                    [
                        'bentuk_ta' => $bentukTa ?: 'Skripsi',
                        'status' => 'terjadwal',
                        'gelombang' => 1,
                    ]
                );
                $pendaftaranId = $pendaftaran->id;
            } else {
                $pendaftaran = PendaftaranSidang::updateOrCreate(
                    [
                        'mahasiswa_id' => $mhs->id,
                        'judul_ta' => $judul ?: 'Sidang Tugas Akhir',
                    ],
                    [
                        'status' => 'terjadwal',
                        'gelombang' => 1,
                    ]
                );
                $pendaftaranId = $pendaftaran->id;
            }

            // 5. Ruangan
            $ruangan = $this->getOrCreateRuangan($tempatStr);

            // 6. Tanggal & Jam
            $tanggal = $this->parseIndonesianDate($tglStr);
            [$jamMulai, $jamSelesai] = $this->parseTimeRange($waktuStr);

            // 7. Penjadwalan
            Penjadwalan::updateOrCreate(
                [
                    'tipe' => $type,
                    'pendaftaran_id' => $pendaftaranId,
                    'mahasiswa_id' => $mhs->id,
                ],
                [
                    'ruangan_id' => $ruangan?->id,
                    'pembimbing_1_id' => $p1?->id,
                    'pembimbing_2_id' => $p2?->id,
                    'penguji_1_id' => $u1?->id,
                    'penguji_2_id' => $u2?->id,
                    'tanggal' => $tanggal,
                    'jam_mulai' => $jamMulai,
                    'jam_selesai' => $jamSelesai,
                    'mode' => 'offline',
                    'gelombang' => 1,
                    'status' => 'terpublikasi',
                    'catatan' => "Jadwal resmi {$type} hasil sinkronisasi data akademik.",
                ]
            );
        }

        fclose($handle);
    }

    private function getOrCreateMahasiswa(string $nim, string $name): User
    {
        if (isset($this->studentCache[$nim])) {
            return $this->studentCache[$nim];
        }

        $email = strtolower(preg_replace('/[^a-zA-Z0-9]/', '.', $name)) . '.' . substr($nim, -4) . '@student.itk.ac.id';

        $user = User::updateOrCreate(
            ['username' => $nim],
            [
                'name' => $name,
                'email' => $email,
                'role' => 'mahasiswa',
                'nim_nip' => "NIM: {$nim}",
                'prodi' => 'S1 Informatika',
                'jabatan' => 'Mahasiswa S1 Informatika',
                'password' => Hash::make('password'),
            ]
        );

        $this->studentCache[$nim] = $user;
        return $user;
    }

    private function getOrCreateDosen(string $name): User
    {
        $cleanName = trim(preg_replace('/\s+/', ' ', $name));
        if (isset($this->lecturerCache[$cleanName])) {
            return $this->lecturerCache[$cleanName];
        }

        // Cari by name
        $existing = User::where('role', 'dosen')->where('name', $cleanName)->first();
        if ($existing) {
            $this->lecturerCache[$cleanName] = $existing;
            return $existing;
        }

        // Generate synthetic NIP based on crc32
        $nipCode = '198' . sprintf('%015d', abs(crc32($cleanName)));
        $nip = substr($nipCode, 0, 18);
        $email = strtolower(explode(' ', trim(preg_replace('/[^a-zA-Z\s]/', '', $cleanName)))[0] ?? 'dosen') . '.' . substr($nip, -4) . '@lecturer.itk.ac.id';

        $user = User::create([
            'name' => $cleanName,
            'username' => $nip,
            'email' => $email,
            'role' => 'dosen',
            'nim_nip' => "NIP: {$nip}",
            'prodi' => 'S1 Informatika',
            'jabatan' => 'Dosen Pembimbing & Penguji',
            'password' => Hash::make('password'),
        ]);

        $this->lecturerCache[$cleanName] = $user;
        return $user;
    }

    private function getOrCreateRuangan(?string $roomName): ?Ruangan
    {
        if (!$roomName) return null;
        $clean = trim($roomName);
        if (!$clean) return null;

        if (isset($this->roomCache[$clean])) {
            return $this->roomCache[$clean];
        }

        $existing = Ruangan::where('nama_ruangan', $clean)->first();
        if ($existing) {
            $this->roomCache[$clean] = $existing;
            return $existing;
        }

        $gedung = 'Gedung A';
        if (str_starts_with($clean, 'B-') || str_contains($clean, 'B')) {
            $gedung = 'Gedung B';
        } elseif (str_starts_with($clean, 'G-') || str_contains($clean, 'G')) {
            $gedung = 'Gedung G';
        }

        $ruangan = Ruangan::create([
            'nama_ruangan' => $clean,
            'gedung' => $gedung,
            'lantai' => 'Lantai 2',
            'kapasitas' => 30,
            'status' => 'tersedia',
        ]);

        $this->roomCache[$clean] = $ruangan;
        return $ruangan;
    }

    private function parseIndonesianDate(string $raw): string
    {
        $months = [
            'januari' => '01',
            'februari' => '02',
            'maret' => '03',
            'april' => '04',
            'mei' => '05',
            'juni' => '06',
            'juli' => '07',
            'agustus' => '08',
            'september' => '09',
            'oktober' => '10',
            'november' => '11',
            'desember' => '12',
        ];

        // Format example: "26 Januari 2026" or "Senin, 26 Januari 2026"
        $cleaned = trim(preg_replace('/^[a-zA-Z]+,\s*/', '', $raw));
        $parts = explode(' ', $cleaned);

        if (count($parts) >= 3) {
            $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
            $monthName = strtolower($parts[1]);
            $year = $parts[2];
            $month = $months[$monthName] ?? '01';

            // Fix typo tahun jika ada seperti 206 -> 2026
            if (strlen($year) === 3) {
                $year = '20' . substr($year, 1);
            }

            // Normalisasi agar jadwal tampil di bulan aktif saat ini (Oktober 2026) dan bulan depan (November 2026)
            // agar kalender publik & dashboard langsung terisi agenda yang relevan saat ditest
            $currentYear = now()->year;
            $currentMonth = now()->month; // 10 (Oktober)

            // Jika sempro/sidang di CSV tertulis bulan lalu (Januari - September),
            // kita proyeksikan ke bulan berjalan (Oktober) dan bulan depan (November)
            $mInt = (int) $month;
            if ($mInt < $currentMonth) {
                // Sebagian kita petakan ke bulan Oktober (minggu ke-2 sampai ke-4) dan November
                $targetMonth = ($mInt % 2 === 0) ? $currentMonth : ($currentMonth + 1);
                $targetDay = min(max((int)$day, 12), 28); // Tanggal 12-28 agar setelah periode pendaftaran tanggal 3-10
                return sprintf('%04d-%02d-%02d', $currentYear, $targetMonth, $targetDay);
            }

            return "{$year}-{$month}-{$day}";
        }

        return now()->toDateString();
    }

    private function parseTimeRange(string $raw): array
    {
        // Example: "09:30 - 10:30" or "10:00 - 12:00"
        $parts = explode('-', $raw);
        $start = trim($parts[0] ?? '09:00');
        $end = trim($parts[1] ?? '11:00');

        $startFormatted = str_contains($start, ':') ? (strlen($start) === 5 ? "{$start}:00" : $start) : '09:00:00';
        $endFormatted = str_contains($end, ':') ? (strlen($end) === 5 ? "{$end}:00" : $end) : '11:00:00';

        return [$startFormatted, $endFormatted];
    }
}
