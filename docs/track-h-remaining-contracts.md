# Track H remaining contract decisions

## H-A05 — assigned-supervisor logbook

Implemented: student GET options and POST authorization use the same approved `PengajuanJudul` supervisor positions as the lecturer logbook. An unassigned lecturer, a waiting title, or no approved title rejects creation without writes. Pre-assignment consultation is not this endpoint's contract. Existing stored sessions remain visible to their student; historical orphan consultation cleanup is not performed. `TrackHBoundaryTest` proves the decision and successful assigned creation.

## H-A07 — bounded account-management persistence

Implemented Tendik-only routes: POST `/tendik/mahasiswa`, PUT and DELETE `/tendik/mahasiswa/{id}`, POST `/tendik/mahasiswa/import`. Only name/email and fixed role=mahasiswa are supported; cross-role targets return 404. User migration `0001_01_01_000000_create_users_table.php` supports these fields and nullable username. Passwords are random undisclosed secrets; these accounts require separate access provisioning, not an invented invitation or default credential. Email edits clear email verification. Password reset remains existing policy and rejects missing usernames.

CSV contract: UTF-8, exact `name,email` header, CSV extension, <=1 MiB, 1–500 data rows, create-only transaction, all-role unique email and within-file case-insensitive duplicate rejection. Invalid rows roll back earlier writes. No cohort, phone, academic stage, identity assignment, role escalation or XLSX support. Deletion rejects accounts with academic history to avoid migration-defined cascades. Concurrent import uniqueness is ultimately enforced by the database; production concurrency is not browser-verified.

**Partial delivery:** routes/persistence tested; directory create/edit/delete/import UI and CSV preview/template are deferred, not claimed working. Existing directory detail/reset controls remain. The schema supports bounded writes; there is no schema-based excuse to defer the entire backend.

## H-A08 — directory pagination

Implemented eight-row client pagination over authoritative refreshed `dbStudents`. Search resets page, shrinking/empty server results clamp to page 1, both boundaries disable navigation. Selected detail/reset resolves by persisted ID against refreshed props. Mounted actual-SFC regression exercises populated, filtered, shrinking and empty results. This bounds rendering, not database query size; server pagination is a future scale concern.

## H-A09 / H-A12 — publication source explicitly deferred

Evidence: migrations `2025_01_01_000013_create_pendaftaran_sempro_table.php` and `2025_01_01_000014_create_pendaftaran_sidang_table.php` store submitted titles, draft/proposal paths and administrative review status. Academic review is separate. `2025_01_01_000015_create_penjadwalan_table.php` publishes **schedules**, not manuscripts. `Dosen/PenilaianController::store` sets examination/registration `selesai` after two examiner scores; it does not approve manuscript publication. No publication consent, final approved manuscript, publication timestamp or catalogue publication model exists. A submitted/approved/completed defense is not evidence of submitted-published thesis permission.

Therefore no real publication corpus is confirmed. Do not reinterpret `terpublikasi` schedule status as public thesis permission or expose private draft/Turnitin/IAET documents auth-wide. Catalogue remains honest empty state. `TrackHBoundaryTest::test_completed_approved_defense_is_not_manuscript_publication` locks the decision. H-A12 remains dependent on H-A09: no similarity endpoint, fake records or plagiarism claim added. Next prerequisite is an approved publication eligibility/consent and final-document persistence contract.

## H-A15 — secure examiner delivery, bounded rollout

Real paths exist: Sempro `proposal_file`, `lembar_kehadiran_file`, `turnitin_file`, `iaet_file`; Sidang `draft_laporan_file`, `turnitin_file`, `skor_iaet_file`. Existing upload controllers originally used the public disk (`config/filesystems.php` links `/storage` to it); an authorized wrapper alone cannot make those existing URLs private.

Implemented new uploads to existing **local/private** storage, plus GET `/dosen/penilaian/{scheduleId}/dokumen/{field}`. Only assigned examiners on terpublikasi/selesai schedules may read; supervisor-only assignment is insufficient. Endpoint binds exact exam type, registration ID and student ID; allowlists fields; requires student-owned path prefix and an existing private file; rejects traversal, missing files, wrong type/owner and draft schedules with 404. Private/no-store, PDF, nosniff and sandbox headers accompany actual streamed bytes. Penilaian props provide links only for existing authorized private files, and the actual page renders links/unavailable state. Document access is not a new academic approval action and does not modify assessment lifecycle.

**Legacy rollout deferred:** existing public uploads remain untouched under the no-development-data-change constraint. They are deliberately not exposed by the secure endpoint. Operators must inventory, transfer and verify legacy files, remove publicly served copies and disable any public exposure before claiming all historical documents confidential. No migration/reset/file movement was performed. Browser PDF opening remains unverified.

## Preservation

No commit, push, development migration/reset or plan.md edit. Existing R1–R8 and Track H slice 1 work retained. Tests use phpunit.xml SQLite :memory: and fake filesystem disks. See `track-h-browser-acceptance.md` for the release gate rather than interpreting executable tests as browser acceptance.
