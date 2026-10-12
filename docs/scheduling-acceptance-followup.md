# Scheduling acceptance regression follow-up

## Outcomes

- Added successful manual examiner overrides for Sempro and Sidang, separately exercising both examiner positions. Assertions read persisted rows and the period-filtered Inertia response, retain the other examiner and schedule slot, preserve registration/student/type/period identity despite attempted identity changes, and confirm the registration remains scheduled with no duplicate schedule.
- Added successful manual date/start/end overrides for both exam types using a configured second period slot. Assertions verify persisted slot values, unchanged examiners/supervisors/identity/status, registration status, row count, and reloaded times.
- Added 44 invalid GA parameter datasets across every accepted request field: required/existing period identity, malformed scalar values, arrays/objects, booleans, fractional integer values and strings, and just-outside lower/upper bounds. Every invalid dataset requires HTTP 422 with the relevant field error and no schedule response or persisted schedule/registration mutation.
- Added missing-period and closed-period rejection, plus accepted numeric endpoints, nullable optional defaults and omitted optional defaults. Maximum endpoint checks deliberately use no eligible candidates to avoid expensive optimization; they establish request acceptance, not optimizer quality at maximum settings.

## Bug found and minimal fix

The initial targeted run returned 47 passing tests and one failing test (415 assertions): JSON `period_id: true` returned HTTP 200 rather than HTTP 422 when period ID 1 existed. Laravel's non-strict integer validation admits this boolean. Added a boolean rejection closure only to the GA period ID validation, preserving valid integer and HTML numeric-string compatibility. The next run passed all 48 then-present tests (421 assertions). Subsequent additions expanded malformed-value coverage.

Successful override tests and the other initially passing parameter tests are coverage of existing behavior, not manufactured RED evidence. The boolean-period regression is the actual failing-test-first fix.

## Verification

- `php vendor/bin/pint tests/Feature/R4SchedulingTest.php app/Http/Controllers/Kaprodi/PenjadwalanController.php`: passed after bounded formatting.
- `php artisan test --filter='R4SchedulingTest|R4OptimizerTest|ScheduleOptimizerTest|R5OutputsTest'`: 66 tests passed, 567 assertions.
- `php artisan test`: 167 tests passed, 1768 assertions in the current working tree. This total includes concurrently present work; it is not attributed entirely to this follow-up.
- Tests use the existing `phpunit.xml` SQLite `:memory:` configuration. No development database migration, commits or pushes were performed. Parent-owned `plan.md` and unrelated dirty files were not edited.

## Exact owned files

- Modified `tests/Feature/R4SchedulingTest.php`.
- Modified only the GA period ID validation in `app/Http/Controllers/Kaprodi/PenjadwalanController.php`, preserving its pre-existing edits.
- Created `docs/scheduling-acceptance-followup.md`.

These are authenticated Laravel feature regressions with isolated fixtures, not interactive browser acceptance or evidence of non-SQLite concurrency behavior.

## Scheduling review repairs

- Period updates now freeze name, type, wave, capacity, date range and slots once any schedule exists, while permitting `is_open` transitions. Closing leaves schedule definitions and registration state unchanged; subsequent GA and publication reject the closed period. Opening/creating an open period rejects another open period of the same exam type; another wave must be closed first. This is application validation, not a proven cross-engine concurrent uniqueness guarantee.
- Publication capacity now applies to cancelled-row reactivation as well as new rows. Both Sempro and Sidang regressions prove rejection without mutation when full, idempotent active-row publication at capacity, and reactivation of the same row after capacity is freed.
- Publication explicitly rejects boolean `period_id` values before integer coercion, preserving numeric-string submission compatibility.
- Structural-edit coverage checks each frozen field while attempting closure. The existing period-creation test now creates a closed second period rather than violating the single-open policy.

### Observed RED/GREEN evidence

Commands used `DB_CONNECTION=sqlite DB_DATABASE=:memory: DB_URL= php vendor/bin/phpunit` with the named `--filter`.

| Regression                                                               | RED output                                                                                                   | GREEN output                                          |
| ------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------ | ----------------------------------------------------- |
| `test_published_period_can_close_without_changing_schedule_definitions`  | failed: 1 test, 3 assertions; unexpected session error `Periode dengan jadwal tersimpan tidak dapat diubah.` | passed: 1 test, 20 assertions                         |
| `test_reopening_published_period_rejects_another_open_wave_of_same_type` | failed: session missing expected errors                                                                      | combined period checks passed: 4 tests, 63 assertions |
| `test_cancelled_schedule_reactivation_requires_free_capacity`            | failed: 1 test, 3 assertions; expected 422, received 302                                                     | passed: 1 test, 32 assertions                         |
| `test_publication_rejects_boolean_period_id_without_writes`              | failed: 1 test, 2 assertions; expected 422, received 302                                                     | passed: 1 test, 14 assertions                         |

Two test-fixture issues were corrected during development: persisted raw attributes must be read from a fresh period to avoid boolean/order differences, and a registration must be refreshed before restoring its pre-publication status because the original model is stale after the HTTP request. Neither was a production failure.

Final verification after bounded Pint formatting:

- `php vendor/bin/pint app/Http/Controllers/Kaprodi/PenjadwalanController.php tests/Feature/R4SchedulingTest.php`: `{"tool":"pint","result":"passed"}`.
- Targeted `--filter R4SchedulingTest`: `{"tool":"phpunit","result":"passed","tests":61,"passed":61,"assertions":580,"duration_ms":6762}`.
- Full backend: `{"tool":"phpunit","result":"passed","tests":183,"passed":183,"assertions":2015,"duration_ms":32275}`. Counts reflect the current dirty working tree, including concurrent work.

Only this controller, `tests/Feature/R4SchedulingTest.php`, and this document were modified for these repairs. No development migrations, commits, pushes or plan edits were performed.
