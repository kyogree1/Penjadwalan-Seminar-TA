# Tendik student directory contract

The page consumes the controller's `dbStudents` array reactively. Rows contain persisted `id`, `name`, `email`, nullable `username` and `nim_nip`, `prodi`, and `created_at`. Empty results remain empty. NIM and username are separate fields; cohort, thesis stage and account status are not supplied by the User schema and are not inferred.

The existing Tendik-only POST `/tendik/mahasiswa/{id}/reset-password` targets a persisted mahasiswa ID. Its historical implementation resets the password to the existing **username**, despite previously calling that value “default NIM”. This slice preserves that policy and does not substitute `nim_nip`, invent identifiers, create accounts or change provisioning. Null, empty and whitespace-only usernames now fail validation without changing the password. A nonblank existing username is used exactly as stored.

The endpoint flashes only a generic success message, never the default password. The UI does not label any displayed identifier as a password, does not preview a password, and waits for server success feedback. Validation, network, HTTP and cancellation failures remain visible in the modal. Loading guards prevent duplicate submissions and modal switching during a request. Refreshed props update both rows and the selected student by ID; removed students cannot be reset from a stale modal.

The username-based default remains a predictable legacy credential policy. Replacing it with a secure reset-token flow and reviewing session revocation are intentionally outside this bounded live-data slice. Tests use SQLite in memory, not the development database.
