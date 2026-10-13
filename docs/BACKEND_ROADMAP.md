# Roadmap Backend — Penjadwalan Seminar TA

## Kondisi sekarang

- `app/` hanya: `AuthController.php`, `User.php`, 2 middleware. `database/migrations/` masih default Laravel.
- `routes/web.php` 100% `Route::inertia()` statis — tidak ada POST/PUT/validasi. Padahal frontend sudah `form.post('/pendaftaran/judul')` (`Pendaftaran/Judul.vue:34`) → 404.
- Semua data halaman (`Penjadwalan.vue`, `Pendaftaran/Bimbingan.vue`, `Judul.vue`) masih `ref()` hardcoded di `.vue`.
- ~~Lubang keamanan: `AuthController@login` menerima `demo_role` → login tanpa password. Hapus sebelum deploy.~~ SELESAI 2026-10-13: blok `demo_role` + tombol quick login dihapus, throttle login aktif.

## Urutan kerja (P0 → P3)

### P0 — Amankan auth (1 hari)

1. ~~Hapus blok `demo_role` di `AuthController.php:33-42`.~~ SELESAI 2026-10-13 (+ hapus tombol quick login `Login.vue`).
2. ~~Tambah rate-limit login: `Route::post('/login', ...)->middleware('throttle:5,1')`.~~ SELESAI 2026-10-13.
3. Lengkapi `users` migration: `username unique`, `role enum(mahasiswa,dosen,kaprodi,koordinator,tendik)`, `nim_nip`, `prodi`, `jabatan`, `avatar`, `signature_path`.
4. Seeder role + `Pest.php` test login per role (sudah ada `RoleAuthenticationTest`, tinggal tambah throttle).

### P1 — Persistensi inti (inti capstone)

Buat migrasi + model dalam urutan ini (tiap tabel langsung ganti satu `ref()` frontend):

| #   | Tabel                                                                                              | Ganti mock di                          | Controller                                                                                         |
| --- | -------------------------------------------------------------------------------------------------- | -------------------------------------- | -------------------------------------------------------------------------------------------------- |
| 1   | `exam_periods` (type, academic_year, semester, wave unique, quota, start_date, end_date, is_open)  | `Penjadwalan.vue:99`                   | `PeriodeController@index/store/update/toggle`                                                      |
| 2   | `thesis_titles` (user_id, judul, bidang, pembimbing1_id, pembimbing2_id, status, decision_note)    | `Judul.vue:18` + `dosenList` hardcoded | `JudulController@index/store` — list dosen dari `users where role=dosen`, bukan array statis       |
| 3   | `guidance_logs` (user_id, dosen_id, tanggal, rangkuman, keterangan, status)                        | `Pendaftaran/Bimbingan.vue:54`         | `BimbinganLogController@index/store/destroy` — validasi min. 8x bimbingan di server, bukan di teks |
| 4   | `exam_registrations` (user_id, period_id, type sempro/sidang, berkas json, turnitin_score, status) | `Sempro.vue`, `Sidang.vue`             | `RegistrasiController@store` — validasi Turnitin ≤20, cek Form TA-01A/02/03D/04                    |
| 5   | `schedule_sessions` (period_id, student_id, room_id, date, time_start/end, status) + `rooms`       | `Penjadwalan.vue:146`                  | `JadwalController@index/updateExaminer` — pindahkan `validateExaminerOverride` ke FormRequest      |
| 6   | `assessments` (session_id, dosen_id, rubrik json, nilai, revisi_note)                              | `Dosen/Penilaian.vue`                  | `PenilaianController@store`                                                                        |

Relasi: `User hasMany ThesisTitle/GuidanceLog/ExamRegistration`; `ExamPeriod hasMany ScheduleSession`; `ScheduleSession belongsToMany User (examiners)` via pivot `examiner_session`.

### P2 — Otorisasi + alur peran

1. Policy per model: mahasiswa hanya CRUD miliknya; dosen hanya advisee-nya; tendik verifikasi berkas; kaprodi buka/tutup periode + override penguji.
2. Ganti `Route::inertia()` menjadi `Route::get(..., [Controller::class,'index'])` + `Route::post(...)` per portal. Pertahankan prefix/role middleware yang ada.
3. `HandleInertiaRequests::share` tambah `activePeriod`, `unreadNotifications` — hilangkan `usePage().props.auth as any` berulang di 17 file.
4. Upload berkas (`FileUpload.vue`) → `Storage::disk('private')` + validasi mime/size di FormRequest. E-TTD (`SignaturePadModal`) simpan `signature_path`, bukan base64 di DB.

### P3 — Otomasi + chatbot

1. Penjadwalan genetika jadi `app/Services/SchedulerService.php` + `php artisan schedule:run` setelah periode ditutup. Jangan taruh algoritma di controller.
2. `AcademicChatbot.vue:81` `setTimeout` mock → `ChatbotController@ask` (endpoint POST, rate-limit ketat, log pertanyaan). Prompt SOP dari DB/config, bukan string hardcoded.
3. Pest feature test per alur: daftar judul → bimbingan 8x → daftar sempro → terjadwal → nilai. Sekarang hanya `ExampleTest`.

## Aturan main

- Validasi selalu di FormRequest (frontend `validatePeriodInput`/`validateExaminerOverride` cukup jadi UX, bukan kebenaran).
- Satu domain = satu controller + satu FormRequest + satu policy. Jangan tambah folder `backend/` — monolit Inertia wajib begitu.
- Tiap migrasi disertai seeder demo agar `npm run dev` langsung ada isi periode + sesi seperti mock sekarang.
