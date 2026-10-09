# SIPTA IF — Master Development & Integration Plan

Scope: Frontend (`resources/js`, `resources/css`) and Backend (`app/`, `routes/`, `database/`, `tests/`).

Baseline Status (2026-10-04):

- **Tooling:** `bun run check` clean, `bun run types:check` 0 errors, `bun run build` passes.
- **Backend Merge:** Cleanly merged `origin/main` (`dd6508f`). Mahasiswa controllers, models, and migrations active; PHPUnit test suite passing (`3 passed, 9 assertions`).
- **Tendik Slice Complete:** 5 pages typed with props, 43 unused icons pruned, Wayfinder routes linked, zero inline mock arrays, zero `any` casts.
- **External Asset:** `Front-End(1).zip` extracted to scratchpad, containing prototype implementations of AI Genetic Algorithm scheduling, Master Data Dosen, Dosen Bimbingan logbook, and Kaprodi review flows.

---

## Completed Phases

- [x] **Phase 0: Baseline Commit** — Working tree committed and tracked.
- [x] **Phase 1: Tooling Green** — `.gitattributes` LF, `check:fix` single-quote style, case-sensitive `Components/ui/*` ignore.
- [x] **Tendik Vertical Slice (Pass 1 & 2):**
    - [x] Pruned 43 dead icon imports across all 5 Tendik pages.
    - [x] Converted hardcoded `/tendik/*` URLs to Wayfinder route helpers.
    - [x] Created `resources/js/types/models.ts` with canonical `Pengajuan` lifecycle and role permissions.
    - [x] Created `resources/js/lib/status.ts` (single source of truth for status badges, `can()` rules, and `hasilEdit` re-check logic).
    - [x] Centralized mock data into `resources/js/data/tendik/`.
    - [x] Replaced inline status switches with `<StatusBadge>` and typed props.

---

## Track F: Frontend Integration (Kaprodi, Koordinator, Dosen)

Goal: Port the rich UI and features from `Front-End(1).zip` (HTML+Vue CDN) into proper compiled Inertia Vue 3 Single File Components.

### Phase F1: Koordinator Penjadwalan — AI Genetic Algorithm Engine

- [x] Create `resources/js/data/koordinator/jadwalGa.ts` with typed dummy datasets from `mockData.js` (`jadwalList`, `gaParams`, `dosenListAll`).
- [x] Port `penentuan-jadwal.html` into `resources/js/pages/Koordinator/Penjadwalan.vue`:
    - [x] Top KPI Banner: Fitness Score (0.985), 0 Bentrok Waktu, trigger button "✨ Jalankan Optimasi AI (GA)".
    - [x] AI GA Optimizer Modal: Parameter inputs (PopSize, MaxGenerations, Crossover Rate 85%, Mutation Rate 3%) + generational convergence logs.
    - [x] Scheduling Table: Student, Title, Pembimbing, Penguji 1 (with `% Match KBK` badge), Penguji 2, Date/Time (room-free per thesis specification).
    - [x] Manual Override Modal: Edit plotting penguji and exam time slots without schedule collisions.
    - [x] Action buttons: Export Excel, Download PDF, Riwayat Kelulusan.
- [x] Connect `jadwal-sempro.html` wave cards into the Period manager tabs.

### Phase F2: Master Data Dosen & Workload Quotas

- [x] Add `resources/js/pages/Kaprodi/Dosen.vue` (or an active tab in `Monitoring.vue`):
    - [x] 4 KPI Cards: Total Dosen, Dosen Informatika ITK, Dosen Lintas/Eksternal, Rata-rata Beban Uji GA.
    - [x] Master table with NIP, Jabatan Akademik, KBK (Bidang Keahlian), Role, and Quota progress bar (`currentQuota / maxQuota`).
    - [x] Modal Tambah / Edit Dosen.
    - [x] Modal Import CSV/Excel.
    - [x] Filters: Search query, Filter Prodi (Informatika vs Lintas), Filter Jabatan, Filter Role.

### Phase F3: Dosen Bimbingan Logbook & Digital Paraf

- [x] Upgrade `resources/js/pages/Dosen/Bimbingan.vue` using patterns from `bimbingan.html`:
    - [x] Advisee selection sidebar/cards with progress indicator and eligibility tag (`Memenuhi Syarat (Siap Sempro)` vs `Belum Cukup`).
    - [x] Detailed per-chapter session logbook (Bab 1-5, rangkuman, catatan dosen, status paraf).
    - [x] Modal Catat Sesi Bimbingan baru.
    - [x] One-click digital paraf action with confirmation feedback.

### Phase F4: Kaprodi Persetujuan & Review System

- [x] Align `resources/js/pages/Kaprodi/Persetujuan.vue` with `pengajuan-judul.html`, `sempro.html`, and `sidang.html`:
    - [x] Replace inline status switches with `StatusBadge` and `lib/status.ts`.
    - [x] Review notes textarea and explicit review actions (Approve, Minta Revisi, Tolak).
    - [x] Synchronize reviewer fields with backend `catatan_kaprodi`.

---

## Track B: Backend Architecture & API Contracts

Goal: Extend the merged backend (`origin/main`) to support all five portals with real persistence and business logic.

### Phase B1: Kaprodi & Koordinator Controllers

- [x] `app/Http/Controllers/Kaprodi/PenjadwalanController.php`:
    - `GET /kaprodi/penjadwalan`: Query pendaftar sempro & sidang ready for scheduling.
    - `PATCH /kaprodi/penjadwalan/{id}`: Manual examiner plotting override in `penjadwalan` table.
- [x] `app/Http/Controllers/Kaprodi/DosenController.php`:
    - `GET /kaprodi/dosen`: Return lecturer directory.
    - `POST /kaprodi/dosen`: Store new lecturer.
    - `PUT /kaprodi/dosen/{id}`: Update lecturer details.
    - `DELETE /kaprodi/dosen/{id}`: Delete lecturer.
- [x] `app/Http/Controllers/Kaprodi/PersetujuanController.php`:
    - Endpoints to approve, revise, or reject `PengajuanJudul`, `PendaftaranSempro`, and `PendaftaranSidang`.

### Phase B2: Dosen Controllers

- [x] `app/Http/Controllers/Dosen/BimbinganController.php`:
    - `GET /dosen/bimbingan`: List advisees and their logbooks.
    - `PATCH /dosen/bimbingan/{id}/paraf`: Sign/paraf logbook session.
    - `POST /dosen/bimbingan/sesi`: Create bimbingan session on behalf of student.
- [x] `app/Http/Controllers/Dosen/PenilaianController.php`:
    - Input scores and exam completion.

### Phase B3: Tendik Controllers

- [x] `app/Http/Controllers/Tendik/VerifikasiController.php`:
    - Verify administrative documents for Sempro & Sidang.
- [x] `app/Http/Controllers/Tendik/RuanganController.php`:
    - Manage physical rooms and hybrid links (CRUD).
- [x] `app/Http/Controllers/Tendik/ArsipController.php`:
    - Official documents archive page.
- [x] `app/Http/Controllers/Tendik/MahasiswaController.php`:
    - Student account directory and password reset to default NIM.

### Phase B4: Genetic Algorithm Engine in Laravel

- [x] Create `app/Services/GeneticAlgorithm/ScheduleOptimizer.php`:
    - Chromosome representation: `[MahasiswaID, TimeSlot, Examiner1ID, Examiner2ID]`.
    - Fitness function:
        - Hard constraints (disqualifying): No lecturer double-booked, examiners cannot be supervisors, examiner 1 != examiner 2.
        - Soft constraints: KBK topic matching percentage, equitable examiner workload distribution.
    - Genetic operators: Roulette wheel selection, two-point crossover, 3% bit-flip / swap mutation.
    - Wired into `Kaprodi\PenjadwalanController::generateGa` and `simpan`.

---

## Track G: Global Architecture & Quality Locks

- [x] **Persistent Shell (AppLayout):** Extract default layout in `app.ts` to prevent remounting shell state across navigation.
- [x] **Big File Refactoring:** Break down `AppLayout.vue` (>1400 lines) into modular partials (`Sidebar.vue`, `Topbar.vue`, `MobileNav.vue`).
- [x] **Security & Consistency:** Gated demo login buttons behind `import.meta.env.DEV`, converted dead `#` footer links to real routes, standardized `SIPTA IF` in `.env.example` and `app.blade.php`.
- [x] **Typed Auth & Notifications:** Added `useAuth.ts` and `useNotifications.ts`, removed untyped `any` casts from layout auth.
- [x] **Global Design Tokens:** Defined semantic palette tokens (`navy-surface`, `navy-bg`, `app-bg`, `accent-green`, `accent-rose`) in Tailwind v4 `@theme inline`.
- [x] **Route Cleanliness:** Ensured all navigation links and form submissions route to real backend controllers.
- [x] **Production Verification:** `bun run check:fix`, `bun run types:check`, and `bun run build`.

---

## Pre-H Recheck — Current Phase Status and Blocking Findings

Rechecked on branch `frontend` at commit `ca354ea40f756542c0dc5f361eab10f518248e50`. The checked boxes above describe historical implementation work, not current end-to-end acceptance. This recheck supersedes their blanket completion claims. Track H integration is blocked until the prerequisites below are resolved and retested.

### Verification results

- `bun run types:check`: passed.
- `bun run build`: passed; optional fontaine optimization warning, not a build failure.
- `php artisan test`: 24 passed, 80 assertions.
- Initial `bun run check`: failed on `plan.md` formatting only. After formatting only `plan.md`, `bun run check` passed: all 85 files formatted; no warnings or lint errors in 80 files. `git diff --check` also passed.
- Source audit: frontend props/handlers and backend routes/controllers/schema/optimizer were traced. Authenticated browser flows and database-specific enum behavior remain unverified. Passing existing tests does not demonstrate coverage of the defects below.

### Phase verdicts

| Phase             | Recheck verdict                                                                            | Required follow-up                                                                                                               |
| ----------------- | ------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| Phase 0           | Baseline commit exists; documentation changes remain uncommitted                           | Preserve current branch and local changes.                                                                                       |
| Phase 1           | Type check/build/tests pass; initial documentation formatting failure                      | Format only the edited plan and rerun check.                                                                                     |
| Tendik passes 1-2 | Typed UI/mock refactor present; live vertical slice incomplete                             | Reconcile props, verification transitions, reset action, and archive behavior; blanket Wayfinder conversion claim is inaccurate. |
| F1                | Partial: scheduling UI exists, but core actions are simulated/local                        | Wire real scheduling props, generate/save/override requests, periods, and downloads.                                             |
| F2                | Partial: lecturer directory UI exists; CRUD/import are local/mock                          | Reconcile props and persistence; implement actual import and pagination reset/clamping.                                          |
| F3                | Partial: logbook/paraf requests exist; mock IDs and premature success remain               | Hydrate real records and reconcile mutations only after server success.                                                          |
| F4                | Incomplete: review UI and StatusBadge exist, but contracts/actions disagree                | Align statuses, notes, supervisor IDs, and revise/reject actions with backend rules.                                             |
| B1                | Partial: controllers/routes exist; contracts and schedule publication incomplete           | Fix ready-candidate selection, status/schema agreement, validation, and transactional save.                                      |
| B2                | Blocked: authorization and score persistence defects                                       | Scope session creation/assessment writes and persist actual scores.                                                              |
| B3                | Partial: backend verification/room/reset actions exist; integration and archive incomplete | Wire actual data/actions; reconcile reset identifier; specify archive/hybrid scope.                                              |
| B4                | Blocked: optimizer feasibility and generate/save contract incorrect                        | Validate hard constraints and outputs; reconcile algorithm claims and implement missing fitness behavior.                        |
| G                 | Partial: modular shell/composables/tokens present; authentication blocker                  | Enforce demo gating server-side and repair disconnected forms before production acceptance.                                      |

### Prioritized prerequisite findings

- [ ] **P0 / G — Passwordless demo authentication.** `AuthController.php:32-40` accepts `demo_role` and logs in a matching account without a password or server-side environment restriction. Hiding buttons in DEV does not protect the endpoint. Restrict the server path to an explicitly supported development environment or remove it; add a test that production requests cannot bypass authentication.
- [ ] **P0 / B2 — Assessment write authorization and missing score persistence.** `Dosen/PenilaianController.php:37-48` loads any schedule by ID without assigned-examiner scope and validates `nilai` but never stores it. Scope writes to authorized examiners, establish persisted score fields/records, and verify unauthorized writes fail and valid scores survive reload.
- [ ] **P1 / F1-F4, B1-B3 — Widespread Inertia contract mismatch.** Controllers send `penjadwalanList/dosenOptions`, `dbDosenList`, `dbBimbinganList`, `dbJudul/dbSempro/dbSidang`, `dbStudents`, and `dbRuanganList`; pages consume different names and fall back to fixtures. Trace and normalize both names and shapes, including real IDs, empty results, and dashboard stats. Verify persisted records are rendered rather than mock substitutions.
- [ ] **P1 / F1, B4 — Scheduling UI remains simulated.** `Koordinator/Penjadwalan.vue:226-286` uses timer-driven logs and success-only apply; `:313-329` changes overrides locally; export/history handlers at `:602-646` show messages. Period changes are local and selected period does not scope results. Replace these with real requests and supported exports; verify reload persistence and period isolation.
- [ ] **P1 / B4 — Generated schedules cannot be saved directly.** `Kaprodi/PenjadwalanController.php:54-99` and `ScheduleOptimizer.php:285-295` confuse registration IDs with user IDs, omit registration/type fields, and disagree on examiner field names. Supervisors are empty, KBK is fixed, and time slots use fixed September dates. Define one validated candidate/result/save contract with real relationships and selected-period dates.
- [ ] **P1 / B1-B4 — Schedule feasibility/publication validation.** Save/override handlers lack role, distinct-examiner, supervisor-exclusion, overlap, registration-readiness, and publication consistency checks (`Kaprodi/PenjadwalanController.php:89-127`). Use transactional publication and validate all constraints against existing schedules before success; test partial failure and duplicate publication.
- [ ] **P1 / B4 — Optimizer claims exceed implementation.** `ScheduleOptimizer.php:179-269` uses finite hard-constraint penalties and identical start-key comparisons, not interval feasibility; KBK bonus does not affect returned fitness and workload balancing is absent. Selection is tournament, not roulette; mutation replaces slot/examiner 1, not the documented bit-flip/swap. Reconcile the intended algorithm with executable behavior and test infeasible inputs; never claim conflict-free without validation.
- [ ] **P1 / F4, B1 — Status and review-note mismatch.** `Kaprodi/Persetujuan.vue:125-130` sends `catatan_kaprodi`; controller `:65-86` persists `catatan`. Revision statuses disagree with migration enums. Align the domain contract and schema, validate allowed transitions, and test notes/revisions with the configured database engine.
- [ ] **P1 / F3, B2 — Logbook ownership and mutation reconciliation.** Session creation accepts an existing user without student-role/supervisee checks (`Dosen/BimbinganController.php:58-74`). UI mutates before confirmation, ignores errors, and retains synthetic session IDs (`Dosen/Bimbingan.vue:104-226`). Enforce relationship scope and refresh persisted IDs; test denial, validation failure, and subsequent paraf.
- [ ] **P1 / F4 — Missing review actions and supervisor IDs.** Title approval sends no supervisor IDs (`Kaprodi/Persetujuan.vue:157-178`); title review lacks revise/reject/note controls and academic review lacks reject (`:564-681`). Reconcile permitted actions with the business rules, then wire validated requests and confirmation feedback.
- [ ] **P1 / F2 — Lecturer CRUD/import/pagination.** `Kaprodi/Dosen.vue:245-315` mutates local records or reports simulated import success; `:1076-1097` has no real file import. Filtering does not reset/clamp pagination (`:47-85`). Wire persistence/import and verify filtering from a later page does not hide matching rows.
- [ ] **P1 / Tendik, B3 — Revision and reset disagreement.** Verification shows local `revisi` but sends `ditolak` (`Tendik/Verifikasi.vue:64-113`). Reset UI does not call its endpoint (`Tendik/Mahasiswa.vue:61-65`); backend resets to username rather than the documented NIM and exposes that value in flash. Agree on intended transitions/reset semantics and test real requests without success-before-confirmation.
- [ ] **P2 / B3 — Archive/hybrid requirements not implemented.** Archive controller only renders mock-backed UI; printing lacks a handler. Room CRUD has no dedicated hybrid-link field in the inspected schema/model/controller. Confirm requirements before implementing or revising the completion claim.

### Positive findings and limits

Persistent default layout and modular Sidebar/Topbar/MobileNav, typed auth/notification composables, semantic theme tokens, and real guide/procedure footer routes are present. Notification data remains mock-backed. The historical exact removal count of 43 icons was not re-proven from current source. Existing tests cover some backend behavior but do not establish live browser or complete integration acceptance.

## Track R: Pre-H Repairs, ANTISLOP-UI, and Anti-Slop Code Quality

Planning scope: no application repairs or design changes have been performed by this update. Use the prerequisite findings above as the initial backlog; verify exact current code before each change. Preserve the existing working tree and do not commit/push automatically.

### R0 — Audit handoff and test coverage

- [ ] Confirm each source-level finding against current code and capture reproducible tests; mark browser/database checks not performed as unverified.
- [ ] Record affected routes, props, models, schema, and files for each repair. Audit active Mahasiswa, dashboard, profile, catalogue, and shared shell call paths as well as the reviewed F/B slices so sibling contract defects are not missed.
- [ ] Add failing regression tests for authentication bypass, unauthorized assessment/session writes, ignored scores, contract mismatches, invalid review transitions, and infeasible schedules before their fixes.
- [ ] Keep tests isolated from production data; run migrations against a disposable test database and avoid destructive development-database resets.

Acceptance: each repair has a confirmed defect, a scoped implementation task, and an executable acceptance check. Existing passing tests are not substitutes for new regression coverage.

### R1 — Authentication and write authorization (first fixes)

Implementation verified: demo-role login now requires the server's `local` environment; assessment writes require an assigned examiner; session creation requires a student with an approved title assigning the current supervisor. Regression tests exercised failing behavior before repairs and successful/denied cases afterward. Parent verification: `php artisan test` passed 47 tests and 139 assertions; `git diff --check` passed. Score persistence remains pending in R3; authenticated browser acceptance remains pending.

- [x] Remove or strictly server-gate demo login; reject production demo-role requests without authenticating an account. Retain normal password login and session regeneration.
- [x] Scope assessment writes to assigned examiners; scope logbook creation to student-role users with a valid supervisee relationship.
- [x] Test allowed writes, unassigned lecturers, wrong-role users, guessed record IDs, and production demo requests.

Acceptance: prohibited requests make no database changes; legitimate login, paraf, assessment, and session flows continue working. Complete R1 before exposing additional live mutation UI.

### R2 — Live data contracts and mutation behavior

First slice implemented: Kaprodi dashboard now consumes required typed server props for registered account totals, pending submission counts, approved-title supervisor assignments, and pending title records. Removed fabricated quota, graduation, fitness, deadline, and lifecycle claims. Supervisor workload counts distinct students and remains separate from examiner duties; query-count regression guards against N+1 behavior. Parent reran backend tests (50 passed, 228 assertions), mounted-SFC frontend tests (2 passed, 14 assertions), type check, lint/format, and whitespace checks successfully. Other portal contracts and authenticated browser acceptance remain pending; this does not complete R2.

- [ ] Inventory controller/page contracts and choose one canonical name/shape per portal; map persisted IDs, relations, dates, and optional fields explicitly.
- [ ] Repair scheduling, lecturer, bimbingan, review, Tendik, and dashboard props; ensure empty server arrays remain empty rather than activating fixtures.
- [ ] Hydrate props reactively after Inertia navigation/reload. Replace fake client IDs with returned persisted IDs.
- [ ] Show mutation success only after server confirmation; expose validation/request errors and either avoid optimistic writes or implement rollback.
- [ ] Add contract tests with nonempty and empty records and browser refresh checks for each repaired page.

Acceptance: live records and options reach the correct UI, failed saves never announce success, and reloads preserve server-confirmed state.

### R3 — Review lifecycle and assessment persistence

- [ ] Reconcile statuses across domain types, controllers, models, and schema. Separate administrative verification, academic review, and thesis lifecycle where their meanings differ.
- [ ] Add non-destructive migrations only where the confirmed business contract requires them; test enum/transition behavior on the supported database engine.
- [ ] Standardize review-note fields, supervisor ID selection, and permitted approve/revise/reject actions without silently broadening role permissions.
- [ ] Define score storage per examiner and exam, duplicate-submission behavior, completion rules, and aggregation before implementing assessment persistence.
- [ ] Test score reloads, notes, supervisor assignment, rejected transitions, and re-verification of changed approved fields.

Acceptance: all persisted review/score values match displayed state; permissions and schema enforce the same lifecycle rules.

### R4 — Scheduling and GA correctness

- [ ] Define typed candidate, optimizer-result, and publication contracts using distinct student/registration IDs, exam type, supervisor IDs, and selected-period slots.
- [ ] Include eligible Sempro and Sidang candidates according to confirmed readiness rules; use actual lecturer metadata and availability.
- [ ] Validate distinct examiners, supervisor exclusion, interval overlaps, existing bookings, and feasible capacity. Return explicit infeasible/partial outcomes instead of fixed conflict-free success.
- [ ] Implement and test the agreed KBK/workload fitness objectives and reconcile tournament/roulette, crossover, and mutation documentation with the chosen implementation.
- [ ] Make publication transactional and duplicate-safe; revalidate manual overrides and persisted bookings. Define period storage and filtering before wiring period actions.
- [ ] Wire frontend generate/preview/apply/save requests and actual computed metrics; clear timers/listeners when applicable.
- [ ] Test infeasible inputs, overlapping intervals, repeated publish, rollback, supervisor conflicts, and reloads with deterministic fixtures/seeds where supported.

Acceptance: generated results satisfy the save contract, invalid schedules are rejected, and every success claim has computed evidence and persisted results.

### R5 — Management actions and real outputs

- [ ] Wire lecturer CRUD, student reset, room actions, and available archive actions; normalize pagination when filters change.
- [ ] Implement CSV selection/parsing/preview, row-level validation, duplicate policy, and an actual template download. Add XLSX only if separately supported and tested.
- [ ] Produce real schedule exports/downloads in their advertised formats; do not label CSV as Excel or a toast as a download.
- [ ] Confirm archive/hybrid-link requirements and list unsupported features explicitly instead of shipping success-only controls.
- [ ] Verify malformed imports, invalid saves, reset authorization, file contents, and refresh persistence.

Acceptance: each active control performs its advertised action; unsupported actions are clearly unavailable and never report false completion.

### R6 — ANTISLOP-UI audit and implementation

Run the UI audit after the functional audit; apply its changes after relevant repairs stabilize, before Track H acceptance. Audit the existing app and record source/screenshots, concrete usability cost, priority, and target files before changing visual style. Use Impeccable guidance if its full bundle is available; the installed catalog stub is not evidence that its audit/detector has run. Do not install tools or change Hermes configuration as part of this plan update.

- [ ] Establish an academic workflow design baseline using existing SIPTA references and semantic tokens; preserve useful identity rather than imposing a generic landing-page redesign.
- [ ] Audit hierarchy and density: prioritize pending decisions, student progress, schedules, and documents. Remove decorative KPI blocks, repetitive nested cards, oversized banners, and gratuitous gradients/glows where they impair these tasks.
- [ ] Replace invented AI badges, fixed fitness/match claims, fabricated activity, and decorative charts with computed data or honest empty/unavailable states.
- [ ] Standardize typography, spacing, table columns, form layout, icon use, status meaning, and action priority across portals. Do not encode state with color alone.
- [ ] Add coherent loading/empty/error/disabled/success states; avoid bouncing alerts, noisy animation, and notifications that conceal failures.
- [ ] Check keyboard focus, modal trapping/restoration, accessible labels, contrast, reduced motion, and light/dark themes. Review representative 360px, 768px, and 1440px viewports and narrow-table overflow.
- [ ] Compare screenshots before/after and walk through login, review, logbook, management, assessment, and scheduling tasks. Record evidence rather than declaring the UI polished from source alone.

Acceptance: important actions have clear hierarchy, no invented telemetry remains, all audited workflows work with keyboard and target viewports, and findings are fixed or explicitly deferred. Preserve Indonesian application terminology where already part of the product; write audit reports in English.

### R7 — Anti-slop code review and cleanup

Perform behavior-preserving cleanup after regression tests and functional fixes are green, with a focused review after each repair slice and a consolidated pass before Track H. Scope reviews to changed code and necessary sibling paths, not a repository-wide rewrite.

- [ ] Review four angles: reuse, quality/readability, efficiency, and root-cause depth. Require path/line evidence, concrete maintenance/runtime cost, confidence, and change risk for each finding.
- [ ] Reuse existing status, authorization, validation, serialization, and UI utilities where semantically correct; avoid introducing parallel registries or one-off wrappers.
- [ ] Remove dead imports, stale commented code, explanatory-noise comments, duplicate state, redundant logic, fixture leakage, and type escapes that bypass a real contract.
- [ ] Replace silent error swallowing and success-before-response patterns with consistent error handling. Bound requests/listeners/timers and inspect avoidable N+1 queries with actual evidence.
- [ ] Inspect history before removing unclear logic. Preserve public routes, schema fields, exports, and business behavior unless a separately tested repair explicitly changes the contract.
- [ ] Apply low-risk cleanup first; verify careful refactors with targeted tests. Record high-risk changes as separate tasks rather than hiding them in polish.
- [ ] Re-run targeted regression tests and types/lint/build after changes; document skipped low-confidence suggestions and remaining debt.

Acceptance: code is simpler without contract regressions, duplicate bug patterns are addressed at their shared cause, and no broad refactor is justified solely by stylistic preference.

### R8 — Pre-H release gate

- [ ] Close P0/P1 prerequisite defects, or explicitly document a scope decision for any deferral; do not silently bypass authentication, permission, persistence, or feasibility blockers.
- [ ] Run `bun run types:check`, `bun run check`, `bun run build`, and `php artisan test`; run additional targeted frontend/GA checks already supported by the repository.
- [ ] Exercise authenticated role workflows in a browser with an isolated dataset and verify save/reload, denied requests, empty/error states, and real downloads.
- [ ] Complete ANTISLOP-UI and anti-slop code finding registers, including before/after evidence and test results.
- [ ] Update historical phase acceptance labels accurately. Then begin Track H's friend-branch comparison and apply the same functional/UI/code gates to newly integrated features.

Execution order: R0 audit/tests → R1 access control → R2 contracts → R3 lifecycle/scores → R4 scheduling → R5 management/outputs → R6 UI implementation → R7 consolidated code cleanup → R8 acceptance → Track H. UI audit can begin after R0; scoped code review follows each repair, but neither substitutes for functional fixes.

### Remediation order before Track H

1. Fix server-side demo authentication and assessment/session authorization first, with regression tests.
2. Normalize live data contracts and persisted IDs across all affected pages; separate fixtures from legitimate empty server results.
3. Align review statuses/schema/notes and persist assessment scores; test authorized transitions and rejected requests.
4. Repair scheduling candidate/output/save contracts, feasibility, and transactional publication; replace simulated UI actions.
5. Complete CRUD/import/reset/download/archive interactions according to confirmed scope.
6. Re-run type, lint, build, backend tests, then authenticated browser acceptance. Reopen historical boxes or mark them accepted only with evidence. Begin Track H only after blockers are closed or explicitly deferred with a documented scope decision.

---

## Track H: Friend Branch Integration (frontend_dosenkooer) — Corrected Analysis & Execution Plan

### H.0 — Source and verified findings

Source inspected: `origin/frontend_dosenkooer`, commit `b86bf70` by Acewaners, dated 2026-10-08. The commit date does not establish the push date. This branch has no merge base with `frontend` and contains only `Front-End/`: 10 HTML pages, global Vue CDN components, shared utilities, mock data, CSS, and a logo. Its features overlap with the prototype previously used in Track F; byte-for-byte identity with the original ZIP has not been verified.

Verified dataset sizes from evaluating `mockData.js`: 21 master students, 12 master lecturers, 6 dashboard lecturers, 9 lecturer-name options, 5 pipeline stages, 6 study fields, 7 title submissions, 5 bimbingan students, and 8 catalogue records. The earlier claim of 80+ students was incorrect.

Verified corrections to the initial analysis:

- `resources/js/pages/Kaprodi/Dashboard.vue:30` already defines lecturer workload data; `:74` defines pipeline stages, and `:215` renders the segmented tracker. Do not rebuild these as missing features.
- `app/Http/Controllers/Kaprodi/DashboardController.php:23` sends `stats`, while the inspected dashboard script uses hardcoded data without declaring those props. Data-contract reconciliation is the first implementation priority.
- Existing page coverage is not proof of working persistence, imports, exports, or permission enforcement. Historical Track F completion records remain historical; Track H requires fresh verification.
- The friend's scheduling prototype uses timer-driven simulated optimization and fixed metrics. Applying results and exporting files show alerts rather than persist schedules or create downloads (`penentuan-jadwal.html:711-783`). Do not port these as working backend functionality.
- The friend's lecturer import modal has no actual file input in the inspected markup (`data-dosen.html:636-661`); its template link only shows a toast. It is a UX reference, not a completed import implementation.
- Catalogue similarity UI is a candidate addition, not evidence of a functioning similarity service. Define its algorithm, inputs, and backend contract before implementation.

### H.1 — Integration inventory

| Prototype source                     | Compiled equivalent                                                | Current evidence and next check                                                                                       |
| ------------------------------------ | ------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------- |
| `index.html`                         | `Kaprodi/Dashboard.vue`                                            | Pipeline and lecturer workload UI present; reconcile controller props and remove hardcoded production totals.         |
| `penentuan-jadwal.html`              | `Koordinator/Penjadwalan.vue`                                      | UI present; verify real generation, overrides, saving, conflicts, and downloads.                                      |
| `jadwal-sempro.html`                 | Period management in `Koordinator/Penjadwalan.vue`                 | UI present; verify period persistence and active-wave behavior.                                                       |
| `bimbingan.html`                     | `Dosen/Bimbingan.vue`, `Pendaftaran/Bimbingan.vue`                 | Logbook/paraf UI present; verify permissions, persistence, and validated-session eligibility.                         |
| `data-dosen.html`                    | `Kaprodi/Dosen.vue`                                                | Directory UI present; audit CRUD, pagination, CSV import, and template download.                                      |
| `data-mahasiswa.html`                | `Tendik/Mahasiswa.vue`                                             | Directory UI present; compare missing interactions and audit persistence/import requirements.                         |
| `katalog-ta.html`                    | `Katalog/Index.vue`                                                | Search/filter/detail UI present; compare useful presentation changes and scope similarity capability.                 |
| `pengajuan-judul/sempro/sidang.html` | `Pendaftaran/{Judul,Sempro,Sidang}.vue`, `Kaprodi/Persetujuan.vue` | Role-specific equivalents present; verify review actions and data contracts rather than mark complete from filenames. |
| Global sidebar/navbar/chatbot        | `AppLayout.vue`, layout partials, `AcademicChatbot.vue`            | Keep the compiled shell; selectively adopt useful UX without duplicate global components.                             |

### H.2 — Phase 1: Correct and complete the inventory

- [x] Correct student counts, dashboard coverage, and unsupported completion claims in Track H.
- [ ] Compare every prototype page against its compiled equivalent at interaction level.
- [ ] Classify each difference as already present, visual improvement, missing interaction, or backend dependency.
- [ ] Record actual endpoints, props, and acceptance checks for each selected feature.
- [ ] Establish current type-check, lint, build, and relevant test baselines before application changes.

Acceptance: no feature is classified as missing solely from a keyword search; each selected task has source/target paths and an observable success condition.

#### H.2.1 — Mandatory integration audit before implementation

Audit both `origin/frontend_dosenkooer` and the current compiled frontend. The earlier source inspection is partial; no full browser audit has been completed. The inventory above is a starting point, not a completed audit report.

- [ ] Record the exact source and target commit IDs plus local modifications so findings remain reproducible.
- [ ] Inspect all 10 prototype pages and their compiled counterparts, including shared sidebar, navbar, theme behavior, and chatbot. Read handlers and dependencies, not just markup or keyword matches.
- [ ] List each visible action and trace it to its handler, request endpoint, controller, validation, persistence, and response. Label purely local or simulated behavior explicitly.
- [ ] Compare props, field names, identifiers, status values, permission rules, and date/time formats across frontend and backend.
- [ ] Run both interfaces in a browser where available. Check primary interactions, navigation, console errors, failed requests, mobile layout, light/dark mode, keyboard access, and modal focus behavior.
- [ ] Test empty datasets, invalid input, duplicate imports, rejected requests, zero quotas, and refresh-after-save behavior using isolated test data.
- [ ] Establish type-check, lint, build, and backend-test baselines. Record actual failures without claiming they were introduced by integration.
- [ ] Produce a per-page comparison and a prioritized finding register in this plan before choosing application changes.

Finding register format:

| ID      | Source / target paths and lines | Observed evidence                         | Classification                                                                  | Priority | Proposed change           | Verification                | State                                            |
| ------- | ------------------------------- | ----------------------------------------- | ------------------------------------------------------------------------------- | -------- | ------------------------- | --------------------------- | ------------------------------------------------ |
| Pending | Populate during the audit       | Include actual source or runtime evidence | Already present / visual improvement / missing interaction / backend dependency | P0-P3    | Specific integration task | Observable acceptance check | Confirmed / needs verification / blocked / fixed |

Priority definitions: P0 = broken core flow or data/permission defect; P1 = important missing or non-persistent interaction; P2 = meaningful UX/accessibility improvement; P3 = optional polish or fixture cleanup. Assess actual impact before assigning priority.

Audit completion gate: every prototype page has a comparison entry; confirmed findings have evidence and an acceptance check; runtime checks not performed are marked blocked or unverified with a reason. Do not label a feature absent from the compiled app based only on its presence in the prototype. Reorder Phases 2-6 if confirmed higher-priority defects justify it.

### H.3 — Phase 2: Dashboard data wiring (first implementation slice)

- [ ] Trace dashboard controller queries, model fields, existing props, and rendered KPI values.
- [ ] Define a typed contract for stats, lecturer workload, pipeline stages, and deadlines where supported by backend data.
- [ ] Connect existing dashboard UI to controller data; keep fixtures separate from production props.
- [ ] Keep thesis lifecycle stages distinct from submission approval statuses; do not map them blindly through `StatusPengajuan`.
- [ ] Calculate percentages from counts, handle empty datasets and zero quota, and distinguish supervision workload from examiner workload.
- [ ] Verify status vocabulary against model/controller values before counting pending submissions.
- [ ] Add controller/contract tests and check the rendered dashboard with populated and empty data.

Acceptance: displayed totals match server data, tracker segments reflect supported lifecycle definitions, and no fixture replaces a legitimate empty result.

### H.4 — Phase 3: Lecturer and student management

- [ ] Audit `Kaprodi/Dosen.vue` and `Tendik/Mahasiswa.vue`: add/edit actions, permissions, validation, filters, pagination, and persistence.
- [ ] Implement or repair CSV import with a real file input, parsing, preview, row-level errors, duplicate handling, and a real template download.
- [ ] Define duplicate keys using existing domain identifiers and verify backend validation before import writes.
- [ ] Treat XLSX support as a separate task with a verified parser/dependency; do not label CSV-only behavior as Excel support.
- [ ] Verify save/import results after refresh and exercise malformed files and empty/filter-reset states.

Acceptance: supported files create/update the intended records; invalid rows have actionable errors; download buttons generate files rather than success-only alerts.

### H.5 — Phase 4: Review and bimbingan workflows

- [ ] Compare prototype review screens with role-specific compiled pages without combining student and reviewer permissions.
- [ ] Reconcile canonical submission statuses, notes, and allowed transitions with backend enforcement.
- [ ] Verify title/sempro/sidang review actions and paraf survive refreshes.
- [ ] Count validated sessions for seminar eligibility, not all recorded sessions; confirm the required threshold with the existing business rules.
- [ ] Verify that editing verified fields triggers the appropriate re-check flow and that audit records persist.

Acceptance: unauthorized transitions fail; permitted actions persist; pending sessions do not satisfy validated-session requirements.

### H.6 — Phase 5: Catalogue integration

- [ ] Preserve existing search, filters, sorting, and bilingual detail presentation.
- [ ] Compare the friend's detail layout and selectively adopt missing useful fields or interactions.
- [ ] Move fixtures into a typed dataset where needed; deduplicate records rather than bulk-import more dummy rows.
- [ ] Define similarity inputs, comparison corpus, scoring method, endpoint, and loading/error behavior before adding its modal.
- [ ] Separate title/topic similarity from plagiarism checking; display only metrics the implementation actually computes.
- [ ] Keep mock DOI, view/download counts, and academic records as fixtures; verify real document links before exposing downloads.

Acceptance: catalogue interactions remain functional, empty/error states are explicit, and similarity results come from a defined computation rather than fixed prototype values.

### H.7 — Phase 6: Scheduling verification and visual alignment

- [ ] Exercise real GA generation, parameter validation, manual examiner/time overrides, conflicts, saving, and reload behavior.
- [ ] Verify exports and schedule downloads produce the advertised formats; distinguish CSV from XLSX and browser printing from generated PDF.
- [ ] Remove fixed fitness, match, and conflict claims where not supplied by computed results.
- [ ] Reconcile typography, spacing, semantic theme tokens, mobile table behavior, and modal accessibility across selected pages.
- [ ] Test keyboard navigation, focus restoration, light/dark themes, and representative mobile/desktop widths.

Acceptance: scheduling outcomes are persisted and validated, downloaded files open, and integrated pages remain usable across target viewports.

### H.8 — Integration decisions and collaboration

- Keep Laravel/Inertia/Vue SFCs on `frontend` as the application base; preserve `origin/frontend_dosenkooer` as source history.
- Selectively port features instead of merging unrelated histories or copying CDN globals into the compiled app. A Git merge is technically possible with unrelated-history handling, but it does not perform application integration.
- Retain the existing shared layout and chatbot unless comparison identifies a specific worthwhile improvement.
- Suggested ownership: one developer handles lecturer/student management; the other handles dashboard/catalogue integration. Coordinate shared layout, domain types, and status utilities.
- Use feature branches based on `frontend`, with one coherent slice per PR. Do not commit, push, or change team branches automatically.

### H.9 — Execution order and completion gates

1. Complete Phase 1 inventory and record baseline results.
2. Implement Phase 2 dashboard data wiring as the first small, testable integration slice.
3. Complete management interactions in Phase 3, then review/bimbingan in Phase 4.
4. Integrate catalogue presentation and explicitly scoped similarity in Phase 5.
5. Verify scheduling and complete visual/browser QA in Phase 6.
6. Read `package.json` to confirm available scripts; run `bun run types:check`, `bun run check`, and `bun run build`, plus relevant backend tests. Apply targeted formatting fixes as needed rather than unrelated repository-wide edits.
7. Record actual results and outstanding blockers. Mark tasks complete only when their acceptance checks pass, not when markup or a modal exists.

Current scope: this revision corrects the plan only. Application integration tasks above remain unchecked and have not been implemented by this documentation update.
