# Penjadwalan Seminar TA

Sistem Informasi Tugas Akhir & Penjadwalan Seminar / Sidang Informatika ITK.
Stack: Laravel 13 + Inertia + Vue 3 + Tailwind 4 + MySQL + Python (GA & RF).

## Syarat

- PHP ^8.3 + Composer + ekstensi `pdo_mysql`
- Node 22 + npm ≥10
- MySQL 8 berjalan lokal
- Python ≥3.10 / [uv](https://docs.astral.sh/uv/) (opsional: inferensi bobot Random Forest untuk GA; fallback netral bila tidak ada)

> Windows: pakai [Laragon](https://laragon.org) (sudah termasuk PHP + Composer + MySQL + HeidiSQL),
> atau install terpisah: PHP + Composer + Node LTS + MySQL Installer.

## Setup pertama kali

> Kalau database `seminar_ta` + `seminar_ta_test` dan user `seminar` sudah ada,
> lewati bagian buat-DB di bawah — langsung ke `composer setup`.

### Linux

```bash
sudo mysql <<'SQL'
CREATE DATABASE IF NOT EXISTS seminar_ta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS seminar_ta_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'seminar'@'%' IDENTIFIED BY '';
CREATE USER IF NOT EXISTS 'seminar'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON seminar_ta.* TO 'seminar'@'%', 'seminar'@'localhost';
GRANT ALL PRIVILEGES ON seminar_ta_test.* TO 'seminar'@'%', 'seminar'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### Windows (Laragon / MySQL)

Buka HeidiSQL (Laragon) atau MySQL Workbench, jalankan:

```sql
CREATE DATABASE IF NOT EXISTS seminar_ta CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS seminar_ta_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'seminar'@'%' IDENTIFIED BY '';
CREATE USER IF NOT EXISTS 'seminar'@'localhost' IDENTIFIED BY '';
GRANT ALL PRIVILEGES ON seminar_ta.* TO 'seminar'@'%', 'seminar'@'localhost';
GRANT ALL PRIVILEGES ON seminar_ta_test.* TO 'seminar'@'%', 'seminar'@'localhost';
FLUSH PRIVILEGES;
```

Kalau MySQL pakai user `root` tanpa password, cukup buat 2 database di atas dan ubah `.env`:

```ini
DB_USERNAME=root
DB_PASSWORD=
```

> Catatan Windows: pastikan ekstensi `pdo_mysql` aktif di `php.ini` Laragon (Menu > PHP > Extensions >
> centang `pdo_mysql`), lalu restart Apache/PHP.

Terminal dari folder proyek:

```bash
composer setup
php artisan migrate:fresh --seed
```

`composer setup` = install deps + copy `.env` + key + migrate + build.

Reset database (hapus semua isi + seed ulang akun demo):

```bash
php artisan migrate:fresh --seed
```

## Jalan harian

```bash
composer dev
```

Buka http://localhost:8000 (server + queue + Vite HMR sekaligus).

Habis `git pull`:

```bash
npm install && php artisan migrate
composer dev
```

## Akses & Fitur Utama

- **Jadwal Publik (`/jadwal`)**: Kalender dan agenda terpublikasi tanpa perlu login. Filter Sempro / Sidang, pencarian mahasiswa & dosen, indikator mode Online/Offline.
- **Portal Koorprodi / Kaprodi (`/kaprodi/jadwal`)**: Monitoring jadwal terpublikasi langsung dari sidebar koordinator, manajemen periode pendaftaran, dan plotting jadwal.
- **Optimasi GA + Random Forest**: Penjadwalan otomatis via Genetic Algorithm dengan skoring kecocokan kepakaran penguji 1 berbasis model Random Forest (`scripts/rf_compatibility.py`).

## Akun Demo

Login dibatasi 5x/menit.

| Role      | Username/NIM         | Password   | Keterangan                    |
| --------- | -------------------- | ---------- | ----------------------------- |
| Mahasiswa | `11231006`           | `password` | Mahasiswa S1 Informatika      |
| Dosen     | `198504122010121003` | `password` | Dosen Pembimbing & Penguji    |
| Dosen 2   | `198803152018012001` | `password` | Dosen Penguji                 |
| Kaprodi   | `198003152005011002` | `password` | Koordinator Program Studi     |
| Koorprodi | `koorprodi`          | `password` | Akun alias koordinator        |
| Tendik    | `199208192018032001` | `password` | Verifikasi berkas pendaftaran |

## Cek & Test

```bash
npm run check          # Formatter & linter check (vp check)
npm run types:check    # TypeScript verification (vue-tsc)
php artisan test       # 219 Pest feature & unit tests (DB seminar_ta_test)
```
