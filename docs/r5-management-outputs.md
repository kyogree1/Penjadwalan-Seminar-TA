# R5 management actions and real outputs

## Current-code audit and disposition

- `Kaprodi/Dosen.vue` / `DosenController`: R2 already uses live props and persisted POST/PUT/DELETE. Search/prodi/jabatan watchers already reset to page 1, and changed result counts clamp later pages. Preserved, not reimplemented. The actual remaining gap was disabled import, no parser/preview/template route.
- `Tendik/Mahasiswa.vue` / controller: R2 already posts the selected database ID to reset-password, gates duplicate requests, displays errors and waits for server flash. Actor middleware restricts Tendik; target lookup restricts mahasiswa. The existing default is **username, not NIM**; null/blank usernames fail. Password defaults are never echoed. Existing backend and actual-SFC tests remain acceptance evidence.
- `Tendik/Ruangan.vue` / controller: persisted create/edit/delete already implemented. Invalid capacities/status/fields fail without mutation. `tersedia` remains administrative availability, not occupancy. Deletion nulls schedule room references without canceling schedules. Added explicit unsupported hybrid-link message.
- `Tendik/Arsip.vue` / controller: already honestly unavailable. Capabilities remain persistence=false/printing=false; there is no archive table/model, issue history, official template service or print endpoint. No success-only action was reintroduced.
- `Koordinator/Penjadwalan.vue`: R4 replaced fake exports with an unavailable notice. Added a real CSV download; no XLSX/PDF dependencies are installed in composer.json, so those formats remain explicitly unavailable.

## CSV import contract

Real file input sends a UTF-8 CSV to server preview. Exact header: `name,email,nip,prodi,jabatan`; BOM, quoted commas/doubled quotes and multiline quoted fields are supported. Reject malformed quotes, incorrect column counts, invalid UTF-8/NUL, missing data, invalid field lengths/email, files larger than 1 MiB and more than 500 data rows. Preview shows parsed data and row/field errors. NIP is text, preserving leading zeroes.

Create-only, all-or-nothing transaction. Duplicate keys are NIP against users.username **and** users.nim_nip across all roles, plus email; repeated identifiers within the file are rejected. Existing accounts are never overwritten or skipped silently. Commit reparses/revalidates the uploaded file; client preview is not trusted. Preview never persists. Imported account passwords are random and never exposed; activation/provisioning is a separately unsupported workflow, not an emailed-password feature.

`GET /kaprodi/dosen/template` streams `template-dosen.csv` with actual header bytes and text/csv content type. Role-gated routes added minimally to the existing kaprodi/koordinator group: template, preview, import and schedule export. Existing unrelated route repairs preserved.

## Real schedule output

`GET /kaprodi/penjadwalan/export[?period_id=N]` streams `jadwal.csv`, reading persisted schedules, ordered by date/time/id. Optional period must exist and scopes the database query. Includes IDs, type, student name/NIM, time, supervisors/examiners, room, mode, stored online link and status. Empty results produce a valid header-only file. Formula-like cells are apostrophe-prefixed to prevent spreadsheet formula execution; CSV quoting preserves commas/newlines. Frontend uses a native download anchor with the selected period, not an Inertia toast or generated preview rows.

## Explicit unsupported scope

- XLSX import/export and generated PDF: no verified parser/writer/PDF library; CSV is never advertised as Excel, browser printing is not offered as PDF.
- Archive persistence, official SK issuance, print/reprint, document templates/download and graduation history: no confirmed persistence/output contract.
- Room-level hybrid-link management: room schema has no field. `penjadwalan.mode` supports offline/online/hybrid and `penjadwalan.link_online` is schedule-level storage; do not repurpose room notes as a dedicated link field. Existing links can be exported; link editing/provisioning is not implemented by R5.
- Lecturer KBK/calendar/quota and imported-account activation remain unsupported. Product requirements beyond the existing schema cannot be inferred from historical checked boxes.

## Verification

Final full gates: backend **106 passed / 1211 assertions**; frontend **41 passed / 383 assertions**. `bun run types:check`, `bun run check` (111 formatted files, no warnings/errors in 99 linted files), `bun run build`, and `git diff --check` passed. Build retains only the existing optional fontaine optimization warning.

RED: new R5 backend tests failed with missing import/template/export routes (404/405); revised actual-SFC import regression failed because Import CSV did not exist. GREEN: full backend and actual-SFC suites, type/lint/build and whitespace gates run after implementation. Tests isolate SQLite :memory: and use deterministic CSV and schedule fixtures. No dev database migration/reset, commits, pushes or plan.md edits performed. Authenticated full-browser/file-open acceptance and non-SQLite concurrency remain unverified.
