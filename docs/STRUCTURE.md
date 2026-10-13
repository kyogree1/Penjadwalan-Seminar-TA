# Struktur Proyek — Penjadwalan Seminar TA

Monolit Laravel + Inertia + Vue. Jangan pisah folder frontend/backend.

## Backend → Laravel (`app/`, `routes/`, `database/`)

- `app/Http/Controllers/` — baru `AuthController.php` + `Controller.php` base. Tambah controller per domain di sini (Bimbingan, Penjadwalan, Verifikasi), bukan folder baru.
- `app/Models/` — baru `User.php`. Model domain (`Period`, `ScheduleSession`, `BimbinganLog`, `AcademicSubmission`) belum ada.
- `app/Http/Middleware/` — `EnsureUserRole.php`, `HandleInertiaRequests.php` (share props ke frontend).
- `routes/web.php` — semua route `Route::inertia(...)` statis. Logic pindah ke controller saat tambah validasi/DB.
- `database/migrations/` — baru 3 default Laravel (users, cache, jobs).
- `config/`, `bootstrap/`, `storage/`, `public/` — standar Laravel.

## Frontend → Vue (`resources/js/`)

- `resources/js/pages/` — 24 halaman per role: `Mahasiswa/`, `Dosen/`, `Kaprodi/`, `Koordinator/`, `Tendik/`, `Pendaftaran/`, `Katalog/`, `Panduan/`, `Prosedur/`, `Profile/`, `Auth/`.
- `resources/js/Components/` — 25 komponen share (Modal, StatCard, StatusBadge, FileUpload, dst).
- `resources/js/Layouts/AppLayout.vue` — layout utama. `composables/useTheme.ts`, `lib/utils.ts` — helper.
- `resources/views/app.blade.php` — satu shell root Inertia, bukan template per halaman.

## Lem perekat

- `app.ts` bootstrap Inertia-Vue. `HandleInertiaRequests` share `authUser` ke semua `.vue`. Route `web.php` petakan URL → komponen `pages/`.
