# Current authenticated browser acceptance

## Verdict

**Partial browser acceptance; not production signoff.** Real Chromium (installed Edge, Playwright) exercised actual controls against an isolated SQLite database on `127.0.0.1:8897`. A reproducible harness, DOM snapshots, screenshots, response/console logs, PDF bytes and downloaded CSV files are retained under `docs/current-browser-evidence/`. No commits or application-source edits were made. `bun run build` succeeded during this run, with the optional fontaine warning. Parent owns final formatting/gates.

## Isolation and authentication

Database: `C:/Users/Tenma/AppData/Local/hermes/profiles/redmess/cache/scratch/current-browser/acceptance.sqlite`. Only that new database was migrated and seeded. `router.php` overrides Vite's hot-file path without changing `public/hot`, and isolates document storage and file sessions under the same scratch directory. Initial sessions used Laravel's default file directory until the router override was added; no development database was used. SQLite is the sole database engine tested.

Supported local-only `demo_role` POST authenticates each role using the real login endpoint/session, with XSRF cookie headers. This setup POST is **not login-form/password acceptance**. No passwords were entered by this harness. All claimed business UI operations below used real buttons, selects, file inputs or links, not fetch mutations.

`fixture.php` explicitly asserts its database path before creating the examiner assignment, approved title, scheduled registration and private one-page PDF. Those are direct scratch fixtures, **not proof of title approval, scheduling generation/publication or student upload success**. `fixture.json` records the fixture identity and PDF hash.

## Verified scope

| Scope               | Actual interaction and readback                                                                                                                                               | Evidence prefix/files                                 |
| ------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| Room CRUD           | Add modal, fill, Save, full reload; Edit capacity 17 to 19, Save/reload; Delete confirmation warning, confirm/reload and absence assertion                                    | 02,05,18,26,29                                        |
| Lecturer CSV        | Open import UI, real file input, server preview, save/import and full reload; leading-zero NIPs remain `000889701` and `000889702`; duplicate email/NIP preview errors        | 08,12,15,41; lecturers-input.csv                      |
| Lecturer template   | Actual template link downloaded HTTP CSV; header `name,email,nip,prodi,jabatan`                                                                                               | 10; lecturer-template.csv                             |
| Tendik student CRUD | Add real name/email form; Save/reload; Edit name/reload; Delete confirmation and persisted absence; academic-history deletion rejected visibly inside retained modal          | 20,24,27,32,35-error,38,37                            |
| Student CSV         | Actual file input, server preview, save import/reload; duplicate email preview returns 422 and displays row error                                                             | 28 (loading only),31,34,36; students-input.csv        |
| Student template    | Actual link download, CSV header `name,email`                                                                                                                                 | 33; student-template.csv                              |
| Examiner document   | Click actual `Buka proposal file` link; response 200 application/pdf, no-store/private, nosniff; saved response bytes match fixture SHA256                                    | 19; examiner-proposal.pdf; fixture.json; events.jsonl |
| Examiner score      | Click `Isi Nilai`, fill 88.25 and note, click Save; full reload shows score/note and 1/2 count                                                                                | 23                                                    |
| Schedule export     | Select actual period, click CSV export; downloaded header plus fixture schedule row                                                                                           | 21,25; schedule.csv                                   |
| Keyboard            | Room dialog initial focus, Shift+Tab wraps to Save, Tab wraps to Close, Escape closes and restores Add focus; assertions recorded                                             | 22; events.jsonl                                      |
| Theme / viewport    | Tendik toggle dark then reload preserves dark; 390x844 directory and examiner page report document scrollWidth 390; mobile navigation opens and Escape restores trigger focus | 30,40                                                 |
| Network failure UI  | Offline real student file-input preview fails, shows `Failed to fetch`, retains existing directory, restores online state                                                     | 39                                                    |
| Logout              | Actual shell logout button returns login page; Sign in assertion                                                                                                              | 42                                                    |

Screenshots are captured artifacts, not human visual/accessibility signoff. No whole-site keyboard, contrast or screen-reader conformance claim.

## Findings and blockers

1. **Current title UI submission fails (release acceptance blocker).** Actual title form options use lecturer _names_ as values. Clicking submit sends that name where the controller requires an integer. `14-title-result.json/png` displays `The pembimbing 1 field must be an integer.` Input remains on validation return; reload is still empty. Prefixes 06/09/11 were diagnostic attempts, NOT successful saved-title evidence. The source must be fixed by its owner; this agent did not alter it. Student title-to-review happy path therefore was not verified.
2. **Document CSP header missing in observed runtime.** The clicked PDF response has private/no-store and nosniff, but no Content-Security-Policy header, despite the controller source supplying `sandbox; default-src 'none'`. Investigate middleware/header override before claiming that protection effective. Response byte hash matches fixture. Browser PDF viewer rendering and human file-content inspection remain unverified.
3. Examiner page renders duplicate `NIM: NIM: 11231006` (minor presentation finding, 16/23/40).
4. Production password login, new forgot/reset flow, second-examiner completion, real student upload/retention, review/verification/paraf buttons, scheduling GA generation/publication/edit controls, full cross-role document denial and all-portal responsive checks remain unverified in this run. Historical R8 results are not reused as fresh acceptance. Parent's backend scheduling tests are separate evidence.

## Logging and tooling qualifications

`events.jsonl` captures browser response status/method/URL, pageerror, console, requestfailed and keyboard assertions throughout each harness invocation. Local authentication APIRequestContext requests are setup and not counted as browser UI traffic. No pageerror events were observed. Expected console resource errors occur for intentional duplicate-preview 422 and offline simulation. Download navigations report `net::ERR_ABORTED` while successful CSV bytes are saved; these are browser download handoff, not failed exports. No zero-console-error claim.

Three diagnostic harness errors remain transparently logged: missing csrf meta (fixed to XSRF cookie), trying review control with no persisted title (blocked), and substring absence matching `student@acceptance.invalid` inside `csv.student@acceptance.invalid` (actual delete succeeded; corrected proof uses unique name in 38). Initial directory creation used a shell TMPDIR inconsistent with native PHP path; corrected explicit scratch path migration succeeded. Preserve these diagnostic artifacts rather than count them as passes.

## Replay

Install Playwright only under the scratch directory (`npm install --prefix <scratch> playwright`). Create a new SQLite file under scratch; run `php artisan migrate --seed --force --no-interaction` with explicit `DB_CONNECTION=sqlite`, `DB_DATABASE=<scratch>/acceptance.sqlite`, `APP_ENV=local`, `CACHE_STORE=array`. Start PHP on the separate port with `-t public docs/current-browser-evidence/router.php` and `ACCEPTANCE_SCRATCH=<scratch>`, then invoke `node docs/current-browser-evidence/harness.cjs <role> <route> <label> '<JSON actions>'`. The harness CLI accepts real click/fill/select/upload/download/keyboard/reload actions. Existing evidence logs contain the runtime sequence; do not overwrite historical evidence labels during replay. Examiner fixture requires first importing lecturers-input.csv through the actual UI, then explicitly running fixture.php with the isolated environment. Never use the development DB or reset it.

Server shutdown and disposable SQLite/session/private-document/browser-storage-state removal are required after each run; retained evidence contains no session storage-state files. Cleanup result is recorded in `cleanup.json`.
