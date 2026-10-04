# Frontend Refactor Plan

Scope: `resources/js`, `resources/css`, `resources/views/app.blade.php`. The backend is out of scope, except where a frontend step needs a contract (marked **Backend contract**).

Baseline (2026-10-04):

| Check                     | State                                            |
| ------------------------- | ------------------------------------------------ |
| `vue-tsc --noEmit`        | 0 errors, 26 `any` casts                         |
| `vp check`                | 23 files fail formatting                         |
| Wayfinder usage           | 0 imports, 73 hardcoded URLs                     |
| Arbitrary Tailwind values | 407 (147 inline hex colors)                      |
| Unused components         | 6                                                |
| Largest files             | AppLayout 1419, Dashboard 1335, Login 1286 lines |

Each phase ends green on `bun run types:check` and `bun run check`, and gets its own commit.

---

## Phase 0: Commit what's there

The working tree has 9 modified files and an untracked `resources/js/data/`.

- [ ] Commit or stash the current changes so the refactor starts from a clean tree.
- [ ] Add `resources/js/data/` to git (it's imported by `Login.vue`; a fresh clone breaks without it).

## Phase 1: Tooling green

Goal: CI passes, so every later phase gives a meaningful signal.

- [ ] Add to `.gitattributes`: `* text=auto eol=lf` (the modified files have CRLF, the rest LF).
- [ ] Run `git add --renormalize .`.
- [ ] Run `bun run check:fix` (fixes the quote style: code uses double quotes, config says `singleQuote: true`).
- [ ] Fix the `vite.config.ts` ignore paths: `resources/js/components/ui/*` should be `resources/js/Components/ui/*`, or remove them if unused.

**Verify:** `bun run check` and `bun run types:check` both exit 0.

## Phase 2: Persistent layout

Goal: stop remounting the shell on every navigation. Right now this resets sidebar state, notifications, the chatbot conversation, and re-runs theme init.

- [ ] In `app.ts`, set the default layout in `resolve`:
    ```ts
    resolve: async (name) => {
        const page = await resolvePageComponent(...);
        page.default.layout ??= name.startsWith('Auth/') ? undefined : AppLayout;
        return page;
    },
    ```
- [ ] Remove the `<AppLayout>` wrapper from all 22 pages.
- [ ] Replace the `title` prop on `AppLayout` with the page's `<Head>` title, or a shared prop.
- [ ] Move `initializeTheme()` out of `AppLayout`/`Login` `onMounted` into `app.ts` setup, so it runs once.

**Verify:** collapse the sidebar, open a chat, mark notifications read, then navigate between 3 pages. All of that state survives.

## Phase 3: Typed page props, centralized mocks

Goal: wiring the backend later only swaps the data source; templates stay unchanged.

- [ ] Create `resources/js/types/models.ts` with domain types: `User`, `Role`, `Dosen`, `Mahasiswa`, `PengajuanJudul`, `Bimbingan`, `JadwalSesi`, `Ruangan`, `Notification`, `ApplicationStatus`.
- [ ] Add `role`, `username`, `nim_nip`, `prodi`, `jabatan`, `avatar` to `types/auth.ts`, then remove the `page.props.auth as any` casts.
- [ ] Add a `useAuth()` composable returning a typed `user` and normalized `role` (`koordinator → kaprodi`). Use it in `AppLayout` and in the pages.
- [ ] Move all inline mock arrays (AppLayout notifications, `dosenList`, Monitoring cohort, Dosen advisees, etc.) into `resources/js/data/*.ts`, one file per domain, typed with `models.ts`.
- [ ] Each page declares its data as typed props, defaulting to the mocks: `withDefaults(defineProps<{ dosenList?: Dosen[] }>(), { dosenList: () => mockDosen })`. The backend sends nothing for now; when controllers exist, they pass real props and the defaults (then the `data/` files) get deleted. No template changes needed.
- [ ] For layout-level data (notifications), add a composable (`useNotifications()`) that reads from `data/` now and can switch to a shared Inertia prop later.
- [ ] Remove the hardcoded "Akmal Falah Maulana" fallback in `AppLayout.profileData`.
- [ ] Bring the `any` count to 0.

**Verify:** `grep -r "Mock" resources/js/pages resources/js/Layouts` returns nothing (mocks live only in `data/`); types pass.

## Phase 4: Honest forms and UI states

- [ ] Add a `lib/mockSubmit.ts` helper with the same callback shape as `form.post` (`onStart`, `onSuccess`, `onError`, `onFinish`), resolving after a short delay. Until the backend exists, all Pendaftaran forms and Profile submit through it instead of POSTing to routes that 405. Later, swapping to `form.post` is a one-line change per form.
- [ ] `Judul.vue`, `Sidang.vue`: set the status only in `onSuccess`, not before submitting.
- [ ] `Bimbingan.vue`, `Sempro.vue`, `Profile/Index.vue`: wire their existing `useForm` to the same helper.
    - **Backend contract (later):** `POST /pendaftaran/{judul,sidang,sempro,bimbingan}`, `PUT /profile`.
- [ ] Every submit button: `:disabled="form.processing"` plus a spinner. Show `form.errors.*` under each field.
- [ ] Every data view: empty, loading, and error states (`RiwayatTimelineCard` already has an empty state; use it as the pattern).
- [ ] Replace `alert()` in `Pendaftaran/Bimbingan.vue:145` with the existing `Modal` or an inline message.
- [ ] Hook the shared `flash.success/error/info` props into a toast in `AppLayout` (the backend already sends them; nothing renders them).

## Phase 5: Design audit (before tokens)

Goal: decide what the visual system should be _before_ encoding it into tokens, so Phase 6 doesn't lock in mistakes.

- [ ] Compare each portal against the `design-plan/` mockups (Dashboard, Pengajuan Judul, Bimbingan, Seminar Proposal, Sidang, Katalog, Panduan, Prosedur, landing page).
- [ ] Audit for generic AI-template patterns (antislop audit mode): purposeless gradients and glows, uniform card grids, contrast failures, dead controls, fabricated numbers in mocks shown as real data.
- [ ] Output: numbered findings list in `anti-slop/audit-001-2026-10-04.md`, each with a rule reference, priority (HIGH / MEDIUM / LOW) and a one-line reason.
- [ ] The user picks which numbers to fix. Approved visual decisions (palette, surfaces, radius, shadows) feed into Phase 6. Nothing gets changed in this phase.

## Phase 6: Design tokens

Goal: replace pasted hex values with named tokens, making dark mode maintainable.

- [ ] Inventory the distinct arbitrary colors: `#0E1626`, `#080D1A`, `#F4F6FA`, `#1E293B`, …
- [ ] Define them in `app.css` `@theme`, e.g. `--color-surface`, `--color-surface-dark`, `--color-canvas`, `--color-canvas-dark`, `--color-sidebar`, `--color-brand`.
- [ ] Optionally use CSS variables that flip under `.dark`, so one class (`bg-surface`) replaces a `bg-white dark:bg-[#0E1626]` pair.
- [ ] Replace usages file by file.
- [ ] Match `app.ts` progress color `#2563eb` to the brand token.

**Verify:** arbitrary-value count drops substantially; take visual before/after screenshots of each portal in light and dark.

## Phase 7: Component extraction and dedupe

- [ ] Make `Card.vue` the only card surface and replace the inline `rounded-2xl border ... dark:bg-[#0E1626]` blocks.
- [ ] Add `FormField.vue` (label, input slot, error, hint). Use it in every Pendaftaran form and in Profile.
- [ ] Move status→label/color mapping into `StatusBadge.vue` (or `lib/status.ts`) and delete the per-page `switch` blocks.
- [ ] Keep the 6 unused components (`AssessmentResultCard`, `FileTemplateLink`, `FileUpload`, `GaMetricCard`, `ReminderList`, `StatusTracker`) for upcoming features. Bring them onto the Phase 6 tokens so they don't rot; wire them in where a page already needs them (e.g. `FileUpload` for Sempro/Sidang uploads).
- [ ] Delete `Welcome.vue` if no route uses it.

## Phase 8: Split the big files

- [ ] `AppLayout.vue` (1419) → `Layouts/partials/Sidebar.vue`, `Topbar.vue`, `MobileNav.vue`, `NotificationPanel.vue`, plus a `navigation.ts` config per role.
- [ ] `Login.vue` (1286) and `Dashboard.vue` (1335): extract the shared academic calendar into `Components/calendar/AcademicCalendar.vue` (+ day modal, mobile week list). It already reuses `CompactCalendar`, `ScheduleDetailModal`, `WeekSelector`, `WeeklyAgendaList`.
- [ ] Target: no `.vue` file over ~400 lines.

## Phase 9: Routing via Wayfinder

- [ ] Replace hardcoded URLs with generated helpers: `import { judul } from '@/routes/pendaftaran'`, then `form.submit(judul.post())`, `<Link :href="dashboard()">`.
- [ ] Point notification and nav links at `kaprodi.*`, not the `/koordinator/*` redirect aliases.
- [ ] Derive active-nav detection from route names instead of `url.startsWith(...)`.

**Verify:** hardcoded-path grep (`"/pendaftaran`, `"/dosen`, `"/kaprodi`, `"/tendik`) returns 0 in `.vue` files.

## Phase 10: Security, accessibility, consistency

- [ ] Render the quick-login buttons in `Login.vue` only in local dev (`import.meta.env.DEV`) so seeded credentials don't ship in the production bundle.
- [ ] Login footer: remove the 3 `href="#"` links, or point them at real pages.
- [ ] Add `alt` to the `<img>` at `Index.vue:123`.
- [ ] Audit the 7 `outline-none` usages; each must have a `focus-visible:` replacement.
- [ ] Icon-only buttons (sidebar collapse, theme toggle, notifications, close) get `aria-label`.
- [ ] Modals: Escape closes them, focus is trapped, and focus returns to the trigger.
- [ ] Use **SIPTA IF** everywhere: `APP_NAME` / `VITE_APP_NAME` in `.env.example`, the `app.ts` fallback, the `<title>` fallback in `app.blade.php`, footer and sidebar. Remove any "SIDATA-FSTI" or "Enrolify" strings from the UI.
- [ ] Remove the `- SIPTA IF` suffix from every `<Head title>` (the `app.ts` title callback already appends it).
- [ ] Clean up "Sesuai Mockup" comments; pick one comment language.

---

## Decisions (2026-10-04)

1. **Product name:** SIPTA IF.
2. **Mock data:** stays in the frontend (`resources/js/data/*.ts`) for now. No backend work.
3. **Unused components:** kept for upcoming features.
4. **Design audit:** Phase 5, run before design tokens (Phase 6).
