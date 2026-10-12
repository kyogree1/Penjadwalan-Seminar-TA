# Tendik verification live-data slice

`VerifikasiController::index` serializes `pengajuan` from persisted Sempro and Sidang registrations. Each row carries its database ID and `sempro`/`sidang` discriminator, nullable student name/NIM, title, creation timestamp, persisted status/notes, and document labels/paths. IDs can overlap between tables; the UI keys and selects rows by both type and ID. Empty results remain empty and replacement Inertia props update the list and selected record.

The existing PATCH endpoints remain unchanged. `Verifikasi Berkas` submits `verifikasi_tendik`; `Tolak Berkas` submits `ditolak`. Notes are optional, matching existing server validation. Requests do not mutate props or close the modal optimistically. A success flash in the response confirms completion; validation, denial flash, HTTP, network, cancellation and missing confirmation errors remain visible in the modal. Loading guards prevent duplicate submissions, switching the selected registration and closing/editing the form while pending.

## Deferred lifecycle limitations (R3)

The database supports `menunggu`, `verifikasi_tendik`, `terjadwal`, `selesai`, and `ditolak`, not `revisi` or `menunggu_ulang`. The existing verification endpoints accept `menunggu`, `verifikasi_tendik`, and `ditolak` without transition restrictions. This slice neither migrates statuses nor changes role permissions or lifecycle policy. It does not promise scheduling, enforce resubmission/re-verification rules, or introduce audit metadata.

Stored document paths do not prove existence, completeness, authenticity, PDF format, or inspection. Missing paths are shown as `Belum tersedia`; no fabricated checklist passes, prefilled approval claim or nonfunctional PDF preview is displayed. Secure document delivery/preview remains pending.

Regression coverage: `tests/Feature/TendikVerifikasiTest.php` (isolated SQLite) and `tests/frontend/tendik-verifikasi.test.js` (compiled actual Vue SFC with visual/navigation stubs).
