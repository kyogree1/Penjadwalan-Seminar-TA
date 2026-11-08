# Backend Jobs — Penjadwalan Seminar TA

Status audit: 2026-11-08, `main` @ `89824ee`. Sumber: route/controller/Vue.
Legenda owner: **BE** = backend Laravel · **FE** = Vue/Inertia.

---

## 0. Ringkasan

Backend mayoritas sudah tersambung ke DB. Masalah inti ada dua:

1. **Gate GA terbalik.** `generateGa` dan `publish` menolak saat `is_open = false`, padahal pendaftaran juga butuh `is_open = true`. Akibatnya GA hanya bisa jalan **saat pendaftaran masih dibuka** — kebalikan dari kebutuhan.
2. **GA tidak pernah dipanggil UI.** Endpoint `POST /kaprodi/penjadwalan/generate-ga` dan `POST /kaprodi/penjadwalan/simpan` nol pemanggil di `resources/js`.

Sisanya: beberapa aksi UI masih lokal (tanpa request), notifikasi tidak pernah dibuat dari event, dan beberapa klaim UI tidak didukung backend.

---

## P0 — Perbaiki siklus hidup periode & gate GA

**Tujuan:** alur `pendaftaran dibuka → ditutup → generate GA → publish jadwal` berjalan.

### P0.1 Ubah gate `generateGa` — BE

File: `app/Http/Controllers/Kaprodi/PenjadwalanController.php:293`

```php
// sekarang
if (! $period->is_open) {
    throw ValidationException::withMessages(['period_id' => 'Periode ditutup.']);
}
```

Ganti menjadi:

```php
if ($period->is_open) {
    throw ValidationException::withMessages(['period_id' => 'Tutup pendaftaran terlebih dahulu.']);
}
if ($period->is_published) {
    throw ValidationException::withMessages(['period_id' => 'Jadwal sudah dipublikasikan. Buka publish untuk regenerasi.']);
}
```

Kriteria terima:

- `is_open = true` → 422 "Tutup pendaftaran terlebih dahulu."
- `is_open = false`, `is_published = false` → 200 JSON hasil GA
- `is_published = true` → 422
- Kandidat tetap difilter `status = verifikasi_tendik` + `status_akademik = disetujui` (`:301`)

### P0.2 Ubah gate publish — BE

File: `PenjadwalanController.php:549`

```php
if (! $period->is_open) {
    throw ValidationException::withMessages(['schedules' => 'Periode ditutup.']);
}
```

Hapus cek `is_open`; ganti `if ($period->is_open) throw 'Tutup pendaftaran sebelum menyimpan jadwal.'`.

Alasan: `publish()` dipakai oleh `simpan`, `update`, dan `updatePlotting`. Ketiganya harus tetap bisa setelah pendaftaran ditutup.

### P0.3 Cegah buka ulang periode yang sudah punya jadwal — BE

File: `PenjadwalanController.php:343` `storePeriod` + route `penjadwalan.periods.toggle/:id`.

Tambahkan validasi: tolak `is_open = true` bila periode sudah punya `Penjadwalan` berstatus `terpublikasi`/`selesai`.

```php
if ($period->is_open && Penjadwalan::where('period_id', $period->id)
        ->whereIn('status', ['terpublikasi', 'selesai'])->exists()) {
    throw ValidationException::withMessages(['is_open' => 'Periode sudah memiliki jadwal terpublikasi.']);
}
```

### P0.4 Update test — BE

File: `tests/Feature/R4SchedulingTest.php`

- `:209` dan `:249` saat ini mengharapkan GA **ditolak** ketika periode ditutup. Balik ekspektasinya.
- Tambah kasus: GA ditolak saat periode dibuka; publish ditolak saat periode masih dibuka; generate ditolak saat `is_published = true`.

### P0.5 (Opsional) Field state eksplisit — BE

Saat ini hanya `is_open` + `is_published`. Bila ingin audit trail:

Migrasi `schedule_periods.status`: `draft | open | closed | generated | published`.

**Jangan kerjakan dulu** kecuali dua boolean terbukti kurang. Dua boolean cukup untuk alur saat ini.

---

## P1 — Sambungkan GA ke UI

Backend sudah selesai; pekerjaan utama di FE. Dokumentasikan kontrak agar FE tidak menebak.

### P1.1 Kontrak `POST /kaprodi/penjadwalan/generate-ga` — BE (dokumentasi)

Request:

```json
{
    "period_id": 3,
    "popSize": 50,
    "maxGenerations": 100,
    "crossoverRate": 0.8,
    "mutationRate": 0.03
}
```

Response 200 (bentuk aktual dari `ScheduleOptimizer::optimize` + tambahan `:328-338`):

```json
{
    "schedule": [
        {
            "pendaftaran_id": 12,
            "tipe": "sempro",
            "mahasiswa_id": 9,
            "penguji_1_id": 5,
            "penguji_2_id": 7,
            "tanggal": "2026-11-20",
            "jam_mulai": "09:00:00",
            "jam_selesai": "10:00:00"
        }
    ],
    "unscheduled": [{ "pendaftaran_id": 13, "reason": "..." }],
    "fitness": 0.91,
    "kbk_match": 0.75,
    "generations": 100,
    "outcome": "partial",
    "success": false,
    "period_id": 3,
    "limitations": ["..."]
}
```

Catat: **`generateGa` tidak menyimpan apa pun.** Hasil harus dikirim balik lewat `simpan`.

### P1.2 `startGaOptimization` → request nyata — FE

File: `resources/js/pages/Kaprodi/PlottingSempro.vue:239`, `PlottingTA.vue:238`

- Hapus `setInterval` simulasi.
- `router.post('/kaprodi/penjadwalan/generate-ga', { period_id, ...gaParams }, { preserveScroll: true, onSuccess })`.
- Log progres: tampilkan tahap statis (bukan progres palsu per 10%). Tidak ada progress streaming; jangan pura-pura ada.
- Tampilkan `limitations[]` dan `unscheduled[]` di modal.

### P1.3 `applyGaResults` → simpan — FE

File: `PlottingSempro.vue:287`, `PlottingTA.vue:286`

- Kirim `{ period_id, schedules: result.schedule }` ke `POST /kaprodi/penjadwalan/simpan`.
- Tangani 422: tampilkan `errors.schedules` / `errors.period_id`.
- Setelah sukses, tutup modal dan reload props.

### P1.4 Tombol export — FE

`PlottingSempro.vue:299` (`exportExcel`) dan `:303` (`downloadJadwal`) hanya toast.
Arahkan ke endpoint yang ada:

- CSV jadwal: **belum ada** endpoint per-periode. Tambah `GET /kaprodi/penjadwalan/periods/{id}/export-csv` — BE, atau hapus tombol — BE/FE sepakati.
- ICS sudah ada: `GET /kaprodi/penjadwalan/periods/{id}/export-ics` (`routes/web.php:128`). Tinggal dipakai.

---

## P2 — Persetujuan Sempro/Sidang

File: `app/Http/Controllers/Kaprodi/PersetujuanController.php:173` (`updateSempro`), `:196` (`updateSidang`).

Saat ini hanya menerima `status_akademik` + `catatan_kaprodi`/`catatan`. Aksi di UI tidak punya endpoint:

| Aksi UI              | File                    | Endpoint dibutuhkan                                                                           |
| -------------------- | ----------------------- | --------------------------------------------------------------------------------------------- |
| Setujui berkas       | `SemproSection.vue:517` | `PATCH /kaprodi/persetujuan/sempro/{id}` field `status_berkas`                                |
| Simpan revisi berkas | `SemproSection.vue:525` | field `catatan_revisi`                                                                        |
| Unduh berkas         | `SemproSection.vue:534` | `GET /kaprodi/persetujuan/sempro/{id}/berkas` (stream/download)                               |
| Input nilai sempro   | `SemproSection.vue:569` | `POST /dosen/penilaian/{pendaftaran}` sudah ada — arahkan ke situ, jangan bikin endpoint baru |
| Reschedule           | `SemproSection.vue:600` | lihat P3                                                                                      |
| Konfirmasi hadir     | `SemproSection.vue:617` | `PATCH /kaprodi/persetujuan/sempro/{id}` field `konfirmasi_hadir`                             |

**Keputusan yang harus diambil sebelum coding:** apakah `status_berkas` dan `konfirmasi_hadir` perlu kolom DB? Cek dulu `database/migrations/*pendaftaran_sempro*`. Bila tidak ada, tambah migrasi; jangan simpan di state Vue.

### P2.1 Fallback demo — FE

`SemproSection.vue:117,268,325` dan `SidangSection.vue:113,204,249` memakai `defaultDemoList` saat props kosong. Hapus fallback; tampilkan empty state. Data demo di produksi menyesatkan.

---

## P3 — Reschedule

File: `resources/js/pages/Kaprodi/Partials/RescheduleSection.vue:374-405`. Semua aksi toast, nol request.

Backend belum punya endpoint reschedule. Perlu:

- Tabel `reschedule_requests` — atau pakai ulang `Penjadwalan` + flag. **Rekomendasi:** tabel terpisah, karena reschedule punya alur persetujuan.
    - `id, penjadwalan_id, diminta_oleh, alasan, tanggal_usulan, jam_usulan, status (menunggu|disetujui|ditolak), catatan, timestamps`
- `POST /pengajuan/reschedule` — mahasiswa/dosen
- `PATCH /kaprodi/persetujuan/reschedule/{id}` — setujui/tolak; bila disetujui panggil `PenjadwalanController::update` agar validasi konflik tetap satu sumber
- `GET` data reschedule disertakan di props `Kaprodi/Persetujuan`

---

## P4 — Notifikasi event-driven

Fakta: tidak ada `Notification::create` di `app/`. Notifikasi hanya dari seeder. `Topbar.vue` membaca nyata dari shared props (`HandleInertiaRequests.php:45-57`), jadi begitu event dibuat, UI langsung jalan.

Model: `app/Models/Notification.php` (`user_id, title, message, type, link, read_at`).

Titik pembuatan yang dibutuhkan:

| Event                              | Lokasi                                    | Penerima                           |
| ---------------------------------- | ----------------------------------------- | ---------------------------------- |
| Tendik verifikasi sempro/sidang    | `Tendik/VerifikasiController.php:52,68`   | Mahasiswa                          |
| Kaprodi setujui/tolak judul        | `Kaprodi/PersetujuanController.php:114`   | Mahasiswa + dosen pembimbing       |
| Jadwal dipublikasikan              | `PenjadwalanController::publish` (`:543`) | Mahasiswa + 2 penguji + pembimbing |
| Nilai diinput                      | `Dosen/PenilaianController.php:57`        | Mahasiswa                          |
| Bimbingan diparaf                  | `Dosen/BimbinganController.php:57`        | Mahasiswa                          |
| Reschedule diputuskan (setelah P3) | endpoint P3                               | Pemohon                            |

Implementasi minimal: satu helper `Notification::create([...])` di dalam transaksi yang sudah ada. Tidak perlu event/listener/queue.

---

## P5 — Kecil tapi menyesatkan

| Item                                                           | Lokasi                                                | Aksi                                                                                                            |
| -------------------------------------------------------------- | ----------------------------------------------------- | --------------------------------------------------------------------------------------------------------------- |
| `syncCalendar` hanya set flag, tidak memanggil Google Calendar | `PenjadwalanController.php:428`                       | Ganti label UI jadi "Tandai tersinkron" **atau** implementasi OAuth Calendar. Rekomendasi: ganti label (YAGNI). |
| `exportCsv` arsip tidak ditautkan                              | `Tendik/ArsipController.php:90`, route `arsip.export` | Tambah tombol unduh di `Tendik/Arsip.vue`. FE, 1 link.                                                          |
| `penguji1MatchScore` hardcoded `95`                            | `PenjadwalanController.php:687`                       | Hapus field dari response, atau hitung nyata. Lihat P6.                                                         |
| Chatbot hanya link ke `/panduan`                               | `Components/AcademicChatbot.vue`                      | Tidak ada backend. Hapus komponen atau beri label "Panduan".                                                    |
| `useNotifications.ts` masih impor mock                         | `resources/js/composables/useNotifications.ts`        | Tidak dipakai di mana pun. Hapus file + `resources/js/data/notifications.ts` bila tidak ada pemakai lain.       |

---

## P6 — Skor RF penguji (tunda)

`ScheduleOptimizer::optimize` sudah menerima parameter `$compatibility` (`ScheduleOptimizer.php:13`), tetapi `generateGa` tidak mengirimnya sehingga semua pasangan memakai `NEUTRAL_SCORE` (`:189`, diumumkan di `limitations` `PenjadwalanController.php:335`).

Untuk mengaktifkan:

- Muat model RF yang ada (`models/rf/paket_model_rf_runtime_private.zip`).
- Bentuk `compatibility[pendaftaran_id][dosen_id]` dari skor KBK.
- Kirim sebagai argumen ke `optimize` / `evaluateRows`.

**Jangan kerjakan** sebelum P0–P4 selesai. Fitur ini tidak memblokir alur utama dan menambah dependensi Python/runtime yang belum jelas.

---

## Urutan kerja

1. P0.1–P0.4 (BE) — 1 PR, kecil, test wajib
2. P1.2–P1.3 (FE) — bergantung P0
3. P2 (BE + FE) — butuh keputusan kolom DB
4. P3 (BE + FE) — fitur baru, tabel baru
5. P4 (BE) — setelah alur stabil
6. P5 (FE) — housekeeping, bisa paralel kapan saja
7. P6 — tunda

## Verifikasi

```bash
php artisan test --filter=R4SchedulingTest
php artisan test
npm run check
npm run types:check
./vendor/bin/pint --test
```

## Non-goals

- Antrian/queue untuk GA. Populasi 50 × 100 generasi berjalan < 1 detik; sinkron cukup.
- Progress streaming GA. Tidak ada dukungan SSE/WebSocket; jangan tampilkan progres palsu.
- Multi-periode paralel. Satu periode aktif per `tipe` sudah dijaga (`PenjadwalanController.php:389`).
