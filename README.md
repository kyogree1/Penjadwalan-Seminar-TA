# Penjadwalan Seminar TA

Laravel 13 + Inertia + Vue 3 + Tailwind 4 + MySQL.

## Syarat

- PHP ^8.3 + Composer + ekstensi `pdo_mysql`
- Node 22 + npm ≥10
- MySQL 8 berjalan lokal

## Setup pertama kali

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

composer setup
php artisan migrate:fresh --seed
```

`composer setup` = install deps + copy `.env` + key + migrate + build. `.env` sudah mengarah ke MySQL (`seminar_ta` / user `seminar` tanpa password, dev lokal saja).

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

| Role      | Username/NIM         | Password   |
| --------- | -------------------- | ---------- |
| Mahasiswa | `11231006`           | `password` |
| Dosen     | `198504122010121003` | `password` |
| Kaprodi   | `198003152005011002` | `password` |
| Koorprodi | `koorprodi`          | `password` |
| Tendik    | `199208192018032001` | `password` |

## Cek & test

```bash
npm run check && npm run types:check
php artisan test   # pakai DB seminar_ta_test
```
