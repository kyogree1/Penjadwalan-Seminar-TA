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

- [ ] **Persistent Shell (AppLayout):** Extract default layout in `app.ts` to prevent remounting shell state across navigation.
- [ ] **Big File Refactoring:** Break down `AppLayout.vue` (>1400 lines) and `Login.vue` into modular components.
- [ ] **Global Design Tokens:** Migrate remaining arbitrary hex colors to Tailwind `@theme` variables.
- [ ] **Route Cleanliness:** Ensure all navigation links use Wayfinder route helpers.
- [ ] **Production Verification:** `bun run check:fix`, `bun run types:check`, `bun run build`, and `php artisan test`.
