# R3 lifecycle and assessment contract

## Inspected disagreements

- Dosen/PenilaianController accepted `nilai` without storage, replaced the schedule's `catatan`, and marked an exam complete after one examiner wrote.
- Kaprodi/PersetujuanController accepted administrative verification, scheduling and completion statuses, and shared registration `catatan` with Tendik. Title rejection also stamped `disetujui_oleh` / `disetujui_at`.
- Title schema did not allow `revisi`; mock-only StatusPengajuan vocabulary (`diajukan`, `menunggu_ulang`) was not the persisted registration vocabulary.
- Student title selection accepted lecturer names as well as IDs; approved title resubmission left approval stamps intact.

## Defined persisted contract

- Title status: draft, menunggu, disetujui, revisi, ditolak. Kaprodi/koordinator may approve/revise/reject submitted titles, not manufacture draft/pending submissions. Only approval carries approval stamps. Null supervisor selections preserve existing assignments; selected IDs must be lecturers and positions must differ.
- Registration `status` retains menunggu/verifikasi_tendik/terjadwal/selesai/ditolak. Tendik alone controls verification/rejection/reset before scheduling. Neither Tendik nor student resubmission can reopen scheduled/completed registrations.
- Separate registration `status_akademik`: menunggu/disetujui/revisi/ditolak. Kaprodi changes this, not administrative/exam lifecycle; decisions freeze when scheduled/completed. This does not implement scheduling eligibility (R4).
- Academic notes use `catatan_kaprodi` for all three review kinds. Registration `catatan` remains administrative. Legacy Kaprodi request `catatan` is accepted as an alias for academic notes without overwriting administrative notes. Existing ambiguous historical notes are not copied or relabeled.
- Student registration resubmission returns administrative and academic state to menunggu. Approved title resubmission clears approval stamps. Existing review notes remain historical context.
- `penilaian` stores one immutable 0–100 score (up to two decimals) and examiner note per (penjadwalan_id, dosen_id), with FK and database uniqueness. SQLite range/precision triggers protect direct SQL writes. Sequential duplicates return validation errors; unique constraint races are translated to the same error rather than success or overwriting.
- Only assigned examiners can score, not supervisors-only or other roles. A published exam requires two distinct non-null examiner positions. After both submit, schedule becomes selesai and only its matching type/student registration in terjadwal becomes selesai. Final score is the equal-weight mean, rounded to two decimals; incomplete aggregate is null. This is not a graduation/pass rule.
- UI reads server-confirmed own score, notes, count and aggregate after reload. Other examiners' individual notes/scores are not exposed. Rubrics, grade corrections, certificates, recommendations and scheduling changes are not implemented.

## Engine and verification

Configured default engine confirmed with `php artisan config:show database`: sqlite, database/database.sqlite, foreign keys enabled. PHPUnit uses sqlite :memory:, exercising migrations and actual SQLite enum checks, uniqueness and range triggers. No dev database reset or migration was run; deployment must apply the two additive migrations with `php artisan migrate` before live use. Other engines and concurrent SQLite busy-lock retries were not load-tested. Row-lock transaction plus unique constraint protects duplicate overwrites; SQLite ignores SELECT FOR UPDATE, so high-contention lock handling remains an operational limitation, not a verified concurrency guarantee.

Final full gates: `php artisan test` returned {"tool":"phpunit","result":"passed","tests":91,"passed":91,"assertions":1068,"duration_ms":6020}; `bun test tests/frontend` returned 36 pass / 0 fail / 336 expect() calls across 8 files. `bun run types:check`, `bun run check`, `bun run build` and `git diff --check` exited 0. Check reported 106 files correctly formatted and no warnings/lint errors in 96 files; build transformed 2376 modules and completed in 6.69s. Build reports the pre-existing optional fontaine warning.

No changes to plan.md, routes/web.php, lib/status.ts, scheduling/GA controllers or migrations from earlier phases. All existing uncommitted R2 repairs preserved; no commit/push.
