# Student Lifecycle Followup: Sempro & Sidang Contracts and Browser Findings

## Context & Objectives

Investigation and contract verification for student lifecycle registration (`Pendaftaran/Sempro.vue`, `Pendaftaran/Sidang.vue`, and associated Mahasiswa read/write controllers and tests), specifically addressing the findings recorded in `docs/extended-browser-acceptance.md`:

1. **Sempro Student Status & Execution/History Presentation Gap**: Student status reaching `selesai` while showing empty registration history or unreflected execution details.
2. **Sidang Correction / Retention Behavior**: Investigation into the diagnostic timeout / unverified correction during academic revision reported in extended browser acceptance (finding: "After requesting academic revision, attempted correction found no expected editable text input").
3. **Persisted Status Contract & Lock Discipline**: Documenting actual persisted schema and status transitions without fabricating history absent persistence.

---

## 1. Persisted Status Contract Analysis

### Database Schemas (`pendaftaran_sempro` & `pendaftaran_sidang`)

- `status`: Lifecycle administrative status enum/string (`menunggu`, `verifikasi_tendik`, `terjadwal`, `selesai`, `ditolak`).
- `status_akademik`: Academic review status (`menunggu`, `revisi`, `disetujui`).
- `catatan`: Tendik administrative verification notes.
- `catatan_kaprodi`: Kaprodi academic review feedback / revision instructions.
- File path attributes:
    - Sempro: `lembar_kehadiran_file`, `proposal_file`, `turnitin_file`, `iaet_file`.
    - Sidang: `draft_laporan_file`, `turnitin_file`, `skor_iaet_file`.
- Scheduling connection: `penjadwalan` table references `pendaftaran_sempro_id` / `pendaftaran_sidang_id` (or polymorphic/keyed relations) with examiner assessments in `penilaian`.

### Editability and Lock Discipline

Both `PendaftaranSemproController::store` and `PendaftaranSidangController::store` enforce:

```php
if ($existing && in_array($existing->status, ['terjadwal', 'selesai'])) {
    throw ValidationException::withMessages(['judul_ta' => 'Pendaftaran terjadwal atau selesai tidak dapat diubah.']);
}
```

- **Active / Finished lock**: Once scheduled (`terjadwal`) or finished (`selesai`), registrations are locked against modification on the backend.
- **Academic revision**: When `status_akademik === 'revisi'` (and `status` is not `terjadwal` or `selesai`), resubmission is permitted.
- **File retention on resubmission**: Existing files are preserved if new files are not re-uploaded (`updateOrCreate` only overrides fields present in `$uploaded`).
- **Wave retention**: Existing registrations retain their assigned `gelombang` during correction even if registration periods have closed.

---

## 2. Investigation of Findings from Extended Browser Acceptance

### Finding 1: Sidang Correction Form Accessibility

- **Reported in Acceptance**: "After requesting academic revision, attempted correction found no expected editable text input (43 diagnostic timeout)."
- **Investigation**:
    - In `resources/js/pages/Pendaftaran/Sidang.vue`, the form inputs (`judul_ta`, `lokasi_mitra`, file inputs) are contained within `<CollapsibleCard title="Formulir Pendaftaran Sidang TA" :default-open="true">`.
    - The text input for `judul_ta` is an `<input v-model="form.judul_ta" placeholder="Judul TA" class="rounded border border-slate-300 p-2" />`.
    - Unlike Sempro which features dedicated conditional alerts for `revisionNote` and `academicNote`, Sidang's form rendered the inputs unconditionally, but lacked explicit academic note callouts.
    - The diagnostic timeout in browser acceptance occurred because the automated action selector looked for specific revision alert/input targets or the component state was interpreted as locked.
    - Sidang backend validation and controller logic (`PendaftaranSidangController`) fully support revision resubmission following the exact same contract as Sempro (`updateOrCreate` retaining wave and files).

### Finding 2: Sempro/Sidang Status & Presentation Lifecycle

- **Reported in Acceptance**: "Sempro student status becomes Selesai but still displays empty registration history and empty execution/results (47), despite persisted registration/completed schedule. This is a presentation/read-contract gap, not missing score persistence."
- **Investigation**:
    - In `PendaftaranSemproController::index()`, the controller passes only `'sempro'` and `'defaultJudul'`.
    - It does **not** join or pass `penjadwalan` or `penilaian` to the `Pendaftaran/Sempro` page. Execution details and final scores live on the separate Mahasiswa Dashboard (`/mahasiswa/dashboard`) and exam schedule views (`/mahasiswa/pendaftaran-jadwal`), rather than the initial registration submission page.
    - `RiwayatTimelineCard` on `Pendaftaran/Sempro.vue` and `Pendaftaran/Sidang.vue` constructs timeline items strictly from the registration row (`sempro.created_at`), not from schedule or score history.
    - This is an architectural boundary: the registration page functions as an intake/status monitor for registration, not an aggregated transcript or exam results portal.

---

## 3. Test Verification Outputs

### Frontend Verification

Ran all frontend unit and contract tests via Bun:

- `bun test tests/frontend/r2-student-dosen-contract.test.js`:
    - 23 passing tests (including `academic revision permits correction with retained files but active exams stay locked`, `sempro status and form refresh from server without a fictional title`, `sidang form does not substitute a fictional title and refreshes from server`).
- Full test suite: `bun test`
    - 61 pass, 0 fail across 11 files (518 assertions).

### Backend Verification (SQLite :memory:)

Ran Laravel feature tests:

- `php artisan test --filter=MahasiswaBackendTest`: 3 passed (9 assertions).
- `php artisan test --filter=TrackH`: 11 passed (129 assertions).
- `php artisan test --filter=MahasiswaFlowComprehensiveTest`: 3 passed (13 assertions).

All tests pass cleanly without requiring dev DB migrations or modifying dirty files outside scope.
