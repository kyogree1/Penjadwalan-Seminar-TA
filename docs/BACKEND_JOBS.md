# Daftar Job Backend per Halaman

Status: 2026-10-20, branch `frontend-merge-sim`. Sumber: audit route/controller/Vue.
Legenda: `OK` persist ke DB · `SEBAGIAN` sebagian data nyata · `MOCK` belum ada backend.

## Lintas portal

| Halaman / fitur | Status | Bukti | Job |
| --- | --- | --- | --- |
| Login, logout, throttle, reset password | OK | `routes/web.php:30-40` | — |
| Profil (nama, telepon, password) | OK | `PATCH profile` | — |
| Notifikasi (ikon lonceng) | MOCK | `Layouts/partials/Topbar.vue:133` tanpa props; `data/notifications.ts` | Tabel `notifications` + `NotificationController@index/read`, share via `HandleInertiaRequests` |
| Unduh dokumen pendaftaran | OK | `Dosen/ExamDocumentController` (private disk) | — |
| Upload berkas (sempro/sidang) | OK | `Mahasiswa/PendaftaranSemproController.php:119-137` | — |

## Mahasiswa

| Halaman | Status | Bukti | Job |
| --- | --- | --- | --- |
| `/dashboard` | OK | `Mahasiswa/DashboardController` | — |
| `/pendaftaran/judul` | OK | `POST pendaftaran/judul` | — |
| `/pendaftaran/bimbingan` | OK | `POST pendaftaran/bimbingan` | — |
| `/pendaftaran/sempro` | OK | `PendaftaranSemproController@store` (+4 PDF) | — |
| `/pendaftaran/sidang` | OK | `PendaftaranSidangController@store` | — |
| `/pendaftaran/jadwal` | OK | `PendaftaranJadwalController:49` | — |
| `/katalog` | SEBAGIAN | judul/dosen dikarang di `KatalogController.php:23-43`; fallback `mockKatalogList` (`Katalog/Index.vue:221`) | Query dari `pendaftaran_sidang`/`penjadwalan` status selesai + `users` role dosen; hapus mock |

## Dosen

| Halaman | Status | Bukti | Job |
| --- | --- | --- | --- |
| `/dosen/dashboard` | SEBAGIAN | controller hanya kirim `stats` (`Dosen/DashboardController.php:32-37`); `mySupervisedStudents`, `upcomingExams`, `adviseeStages` hardcoded (`Dosen/Dashboard.vue:41,78,110`) | Kirim daftar mahasiswa bimbingan, jadwal menguji terdekat, distribusi tahapan dari DB |
| `/dosen/bimbingan` | OK | `POST bimbingan/sesi`, `PATCH bimbingan/{id}/paraf` | — |
| `/dosen/penilaian` | OK | `POST dosen/penilaian/{id}` | — |
| `/dosen/persetujuan`, `/dosen/sempro`, `/dosen/sidang` | MOCK | render `Kaprodi/Persetujuan`; partial punya 7 aksi toast lokal tanpa request (lihat bagian Kaprodi) | Sama dengan job Kaprodi: sambungkan aksi ke endpoint |

## Kaprodi / Koordinator

| Halaman | Status | Bukti | Job |
| --- | --- | --- | --- |
| `/kaprodi/dashboard` | SEBAGIAN | `deadlines => null` (`Kaprodi/DashboardController.php:68`) | Sumber batas waktu periode: pakai `schedule_periods.start_date/end_date` |
| `/kaprodi/persetujuan`, `/sempro`, `/sidang` | MOCK | `PersetujuanController` hanya kirim pendaftaran; `Partials/SemproSection.vue` 7 aksi lokal: `approveBerkas:512`, `saveRevisi:520`, `downloadFile:529`, `submitNilaiSempro:564`, `submitReschedule:595`, `cancelReschedule:604`, `toggleKonfirmasiHadir:612`; `SidangSection.vue` 10 aksi serupa + nilai palsu `:224` | 1) Endpoint approve/revisi berkas (pakai `PATCH persetujuan/{sempro,sidang}/{id}`) 2) Endpoint konfirmasi hadir + reschedule 3) Unduh dokumen 4) Nilai dari tabel `penilaian` (P1.5) 5) Hapus semua default demo (nama, penguji, tanggal, ruang) |
| `/kaprodi/penjadwalan` + periode | OK | `storePeriod`, `togglePeriodStatus`, `togglePublish`, `destroyPeriod` | — |
| `/kaprodi/penjadwalan/plotting-*` | SEBAGIAN | GA nyata (`Services/GeneticAlgorithm/ScheduleOptimizer.php`); `penguji1MatchScore => 95` hardcoded (`PenjadwalanController.php:670`); `'nilai' => null` (`:679`) | Hitung skor match KBK nyata; isi nilai dari `penilaian` |
| `sync-calendar` | MOCK | `PenjadwalanController.php:419-429` hanya set flag | Integrasi Google Calendar API (OAuth + event insert) atau hapus tombolnya |
| `/kaprodi/monitoring` | MOCK | `Route::inertia` (`routes/web.php:130`), halaman "belum tersedia" | Endpoint agregat: per mahasiswa tahap, sesi bimbingan, Turnitin, status jadwal + ekspor |
| `/kaprodi/dosen` | OK | CRUD + CSV import/preview/template | — |
| `/kaprodi/mahasiswa` | OK | CRUD + CSV import/preview/template | — |
| Panduan, Prosedur | OK (statis) | `Route::inertia` | — |

## Tendik

| Halaman | Status | Bukti | Job |
| --- | --- | --- | --- |
| `/tendik/dashboard` | OK | `Tendik/DashboardController` | — |
| `/tendik/verifikasi` | OK | `PATCH verifikasi/{sempro,sidang}/{id}` | — |
| `/tendik/arsip` | MOCK | `ArsipController.php:13-18` kirim `persistence=false`, `printing=false` | Daftar arsip TA selesai + unduh/print berkas + filter tahun/prodi |
| `/tendik/mahasiswa` | OK | CRUD + CSV + reset password | — |

## Job untuk AI Engineer

| Job | Kondisi sekarang | Target |
| --- | --- | --- |
| Penjadwalan GA | `ScheduleOptimizer` sudah jalan (`generate-ga`), tapi `limitations` menyatakan KBK dosen, kuota, kalender ketersediaan belum jadi data nyata (`PenjadwalanController.php:326`) | Simpan bobot KBK + ketersediaan dosen ke DB, objective function pakai data nyata, evaluasi fitness/convergence + test |
| Skor match penguji | `penguji1MatchScore => 95` hardcoded | Skor kemiripan topik TA vs KBK dosen (embedding/TF-IDF), tersimpan + bisa diaudit |
| Chatbot akademik | `AcademicChatbot.vue` mock `setTimeout` | `POST /chatbot/ask` + RAG atas SOP/panduan (sumber di DB), rate limit, log pertanyaan |
| Rekomendasi pembimbing | Belum ada | Ranking dosen per judul (KBK + beban bimbingan + kuota) untuk kaprodi |
| Monitoring analitik | Halaman kosong | Agregasi progres TA + deteksi mahasiswa berisiko (bimbingan macet, Turnitin tinggi) |

## Catatan

- Roadmap lama: `docs/BACKEND_ROADMAP.md` (P0 selesai, P1 hampir selesai, P1.5 baru).
- Test suite frontend (`tests/frontend/*.test.js`) masih `bun:test`; `phpunit.xml` branch ini memakai sqlite `:memory:`.
