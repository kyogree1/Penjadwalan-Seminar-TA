# Tendik rooms, dashboard, and archive contracts

## Rooms

`dbRuanganList` contains database IDs and only `nama_ruangan`, nullable `gedung`, nullable `lantai`, integer `kapasitas`, `status` (`tersedia` / `tidak_tersedia`), and nullable `keterangan`. The controller sorts by name then ID. Empty results remain empty; no fixtures, structured facilities, current occupancy, or synthetic agendas are rendered.

The actual page posts to `/tendik/ruangan`, puts to `/tendik/ruangan/{id}`, and deletes from `/tendik/ruangan/{id}`. Form state is local; rendered room data always follows server props. No optimistic list mutations occur. Requests are locked until finish; validation, network, HTTP, cancellation, flash errors, and missing confirmations are recoverable. Only a server success flash closes the dialog and reports success. Removing a selected row through refreshed props disables mutations.

Existing validation and role middleware remain authoritative. Deletion follows the existing schema's `penjadwalan.ruangan_id` null-on-delete policy: schedules remain but lose their room reference. The confirmation explains this; lifecycle changes are deferred to R3.

## Dashboard

Required typed `stats` props are rendered reactively. Waiting Sempro and Sidang registration counts are separate and summed for the verification queue, across all stored periods. These are registration counts, not unique student counts. Total rooms and administratively available rooms come from `ruangan`; account counts include only `role=mahasiswa`. Room availability is not current occupancy. No month/semester statistics, print counts, or fixture queue rows are claimed.

## Archive

`archiveCapabilities` explicitly reports `persistence=false` and `printing=false`. There is no archive model/table, issue history, print route, or official template service in this contract. The page distinguishes unsupported persistence from an empty stored archive. Fixture documents, print history, and pretend print/download buttons are removed. Real archive storage, document issuance, and printing remain unsupported, not implemented by R2.

## Verification

- `php artisan test --compact tests/Feature/TendikFacilitiesTest.php`
- `bun test tests/frontend/tendik-facilities.test.js`
- `bun run types:check`, `bun run check`, `bun run build`

Backend tests use the configured in-memory SQLite testing database. SFC tests compile all three actual pages using Vue's custom renderer, replace reactive server props, and exercise CRUD callbacks without stubbing the page logic. No development database resets or migrations are required.
