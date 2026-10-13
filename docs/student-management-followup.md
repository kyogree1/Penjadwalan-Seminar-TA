# Student management follow-up

## Delivered

- Tendik student directory now provides real name/email create and edit forms, deletion confirmation, processing guards and visible validation/network/server feedback. Mutations use persisted account IDs and do not modify directory rows locally. Only a server success flash closes the management form.
- Deletion explains the existing server academic-history protection and displays rejected deletion errors inside the confirmation dialog.
- CSV selection uploads the actual file to a server preview endpoint. Preview does not persist records. The template download is a streamed UTF-8 CSV containing the exact `name,email` header.
- Preview and atomic create-only import share the existing strict CSV parser and validation. Import revalidates against current database contents inside its transaction. UTF-8, quoting, two columns, 1 MiB, 500 rows, duplicate email and existing account checks remain bounded.
- Preview is cleared on file changes; superseded requests are aborted and stale results ignored. Invalid previews cannot be imported. Import success requires server confirmation; errors remain visible.
- Existing username-based reset semantics and random undisclosed create/import credentials remain unchanged. No cohort, academic-stage, account-status, NIM or username provisioning fields were invented. Pagination still resets on search and clamps after refreshed data shrinks.

## Files changed by this follow-up

- `app/Http/Controllers/Tendik/MahasiswaController.php`
- `routes/web.php` (only the two student preview/template routes)
- `resources/js/pages/Tendik/Mahasiswa.vue`
- `tests/frontend/tendik-mahasiswa.test.js`
- `tests/Feature/StudentManagementFollowupTest.php` (new)
- `docs/student-management-followup.md` (new)

The existing student types already describe the supported read payload, so no type-file change was needed. Scheduling, `plan.md` and `.gitignore` were not edited. No commit or push was performed.

## Executed verification

- Backend RED: the new template/preview regression failed with HTTP 405 before implementation.
- Actual-SFC CRUD RED: the new create/edit/delete flow failed because the create action did not exist. GREEN: all four then-current directory tests passed, with 59 assertions.
- Actual-SFC CSV RED: the new import flow failed because no file input existed. GREEN: all five then-current directory tests passed, with 75 assertions.
- Final targeted SFC run: **6 passed, 79 assertions**. Tests compile and mount the real SFC and exercise model inputs, navigation callbacks, file preview, error feedback, stale responses and server-only mutation confirmation; only visual wrappers/navigation transport are substituted.
- Final targeted backend run (`StudentManagementFollowupTest.php` and `TendikMahasiswaTest.php`): **8 passed, 164 assertions**.
- Final full frontend run: **56 passed, 503 assertions** across 10 files.
- Final full PHPUnit run: **172 passed, 1854 assertions**.
- `bun run types:check`: passed.
- `bun run build`: passed; emitted the pre-existing optional-fontaine optimization warning.
- Targeted `bunx vp lint` for the student SFC/test: passed. Touched Vue/JS files formatted with `vp fmt`; touched PHP controller/test formatted with Pint. Targeted `git diff --check`: passed.
- Full `bun run check`: blocked by formatting in 15 concurrently supplied files under `docs/current-browser-evidence/` (JSON evidence and `harness.cjs`). These parent-owned browser evidence files were not modified.

PHPUnit uses the verified `phpunit.xml` SQLite `:memory:` database. No development database migration was run. These automated results do not claim authenticated browser acceptance or non-SQLite concurrent-write acceptance.
