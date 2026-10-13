# Penjadwalan Seminar TA

Laravel 13 + Inertia + Vue 3 + Tailwind 4 + MySQL.

## Syarat

- PHP ^8.3 + Composer + ekstensi `pdo_mysql`
- Node 22 + npm ≥10
- MySQL 8 berjalan lokal

> Windows: pakai [Laragon](https://laragon.org) (sudah termasuk PHP + Composer + MySQL + HeidiSQL),
> atau install terpisah: PHP + Composer + Node LTS + MySQL Installer.

## Setup pertama kali

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

Lalu di terminal (CMD / PowerShell / Git Bash), dari folder proyek:

```bash
composer setup
php artisan migrate:fresh --seed
```

`composer setup` = install deps + copy `.env` + key + migrate + build. `.env` sudah mengarah ke MySQL (`seminar_ta` / user `seminar` tanpa password, dev lokal saja).

> Setelah setup pernah jalan, migrasi rutin bisa langsung: `php artisan migrate`.
> Di clone baru `migrate` langsung gagal karena: `vendor/` belum ada, `.env` belum ada,
> database belum dibuat, atau `pdo_mysql` belum aktif.

Reset database (hapus semua isi + seed ulang akun demo):

```bash
php artisan migrate:fresh --seed
```

> Awas: akun yang dibuat manual ikut terhapus — buat ulang setelahnya.

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

## Login

Wajib pakai akun (tombol quick-login sudah dihapus, login dibatasi 5x/menit).

| Role      | Username/NIM       | Password |
| --------- | ------------------ | -------- |
| Mahasiswa | `11231006`         | `password` |
| Dosen     | `198504122010121003` | `password` |
| Kaprodi   | `198003152005011002` | `password` |
| Tendik    | `199208192018032001` | `password` |

## Cek & test

```bash
npm run check && npm run types:check
php artisan test   # pakai DB seminar_ta_test
```
