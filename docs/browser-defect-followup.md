# Browser defect follow-up

## Outcome

Fixed the browser-discovered student title lecturer-name payload defect in `resources/js/pages/Pendaftaran/Judul.vue`. Both native selects now bind stringified lecturer IDs, retaining names solely as display labels. Initial and refreshed records hydrate supervisor foreign-key IDs (or related-object IDs), never lecturer names. Duplicate lecturer names remain distinguishable by ID. No auth, scheduling, plan, or examiner controller changes; no commits or development migrations.

## Regression evidence

`tests/frontend/r2-student-dosen-contract.test.js` now mounts the actual compiled title SFC/template with Vue's native select v-model directive and captures its submitted form values. Only visual wrappers and Inertia transport are stubbed. Selecting two equally named lecturers reproduced the bug: expected `41`/`72`, received the lecturer name for both. After the select binding repair it passes. A separate actual-script hydration regression initially failed (expected `41`, received the lecturer name); hydration and server-prop replacement now pass with IDs.

The first mounted-test attempt lacked static-node support; disabling compiler static hoisting corrected that test-harness error before the genuine payload RED result. This is automated actual-SFC proof, not a new authenticated title-to-review browser acceptance run. Historical failed-browser evidence remains unchanged.

## Examiner PDF CSP investigation

Inspected `docs/current-browser-evidence/router.php` and `harness.cjs`: neither strips CSP. The router only overrides hot-file, private-storage, and session paths; the harness explicitly includes CSP in its retained response-header allowlist. Laravel's filesystem response adapter retains supplied headers. No app middleware/bootstrap header removal was found.

Added `tests/Feature/ExamDocumentHeadersTest.php`: authorized private PDF delivery through the HTTP kernel already passes exact `Content-Security-Policy: sandbox; default-src 'none'`, PDF type, nosniff, private/no-store, and streamed-byte assertions. This is additional coverage, not a previously failing production regression. Therefore the examiner controller was not changed.

A disposable native PHP listener on `127.0.0.1:8903`, using a newly migrated in-memory SQLite connection and scratch-only document storage, also returned HTTP 200 with the exact CSP, private/no-store, nosniff and PDF bytes through `Application::handleRequest`. The first scratch probe failed because its authentication guard lacked a request binding; binding the captured request corrected this harness setup error. No development database was migrated. Cookie headers are not retained here. The earlier browser CSP omission remains unexplained and should not be called fixed: the current controller/kernel/native HTTP contract is correct, while the historical browser viewer/navigation path still needs fresh capture.

## Executed checks

- Target actual-SFC suite: **23 passed, 88 assertions**.
- Full frontend suite at execution: **61 passed, 518 assertions** (includes concurrent agents' changes).
- Full PHP suite, isolated SQLite `:memory:` from `phpunit.xml`: **184 passed, 2029 assertions**.
- Dedicated examiner header test: **1 passed, 10 assertions**, no controller modification.
- `bun run types:check`: passed.
- `bun run build`: passed; optional fontaine fallback warning remains.
- Scoped Vue/JS formatting and dedicated PHP Pint: passed.
- `bun run check`: blocked by formatting in 11 out-of-scope browser evidence/readiness/scheduling files; left unchanged. Parent must rerun consolidated gates after concurrent work.

Scratch listener was terminated after the HTTP probe. This slice modified the title SFC and its existing frontend test, and created the dedicated examiner-header test and this report.
