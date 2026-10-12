# Kaprodi review live-data contract (R2)

`Kaprodi/Persetujuan` consumes required reactive `dbJudul`, `dbSempro`, `dbSidang` and `dosenOptions` props from `PersetujuanController::index`. No fixture fallback or local record mutation remains. Database IDs may overlap: modal selection and requests retain the selected table discriminator independently of the currently visible tab.

Title records use persisted `pembimbing_1_id`, `pembimbing_2_id` and `catatan_kaprodi`. Options contain only existing `role=dosen` users (ID/name); assignment validates integer IDs, existence and lecturer role before any write. Null assignments retain the endpoint's existing no-change semantics, not removal. Title approval uses the existing title endpoint and its existing approval authority; no new role or policy is introduced.

Academic records retain the database statuses `menunggu`, `verifikasi_tendik`, `terjadwal`, `selesai`, `ditolak` and note field `catatan`. The UI saves notes with the current persisted status, and never represents Kaprodi review as Tendik verification. Existing backend endpoints still accept the supported database states; transition policy, concurrency and approval audit semantics remain R3 work. Invalid states now fail validation before SQL writes. Title statuses remain `draft`, `menunggu`, `disetujui`, `ditolak`.

`revisi` is not a database status: Minta Revisi is disabled with an explicit R3 explanation. SK issuance, cohort/angkatan filters, invented eligibility checks and unsourced supervisor/schedule facts have been removed. SK generation, document preview, scheduling and eligibility are explicitly unavailable on this page.

Requests prevent duplicate submission and closing/reopening the modal while saving. Validation, network, HTTP exception, cancellation, flash-error and missing-confirmation responses retain the modal and show errors. Success and modal closure require the returned server success flash; refreshed Inertia props remain the only source of record changes.

Regression coverage: `tests/Feature/KaprodiPersetujuanTest.php` (SQLite in-memory) and `tests/frontend/kaprodi-persetujuan.test.js` (compiled actual SFC with Vue custom renderer, visual/Inertia stubs only). No migration or development database reset is needed.
