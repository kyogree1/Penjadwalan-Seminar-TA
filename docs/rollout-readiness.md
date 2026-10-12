# Rollout readiness — isolated evidence, not production approval

## Scope and engine discovery

Audited the dirty `frontend` worktree, including all twelve migration files. Existing controller/routes/UI/migrations and parent-owned `plan.md` were not edited by this rollout slice. No production or development database migrations, file moves, account provisioning or password resets were performed.

Nonsecret engine evidence: `.env` and `.env.example` both declare `DB_CONNECTION=sqlite`; `config/database.php` defaults to SQLite. No `bootstrap/cache/config.php` existed during discovery. Bootstrapping the application and printing **only** `config('database.default')` returned `DB_CONNECTION=sqlite` without opening a database connection. This identifies the supplied local runtime, **not the production engine**. No production deployment configuration/service was supplied; production engine/version, existing schema state and non-SQLite execution/concurrency remain **unverified**. Do not infer production SQLite from local evidence.

Safe deployment-host discovery (do not use `config:show database`, dump `.env`, or expose URLs/credentials):

```sh
php -r 'require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); print "DB_CONNECTION=".config("database.default").PHP_EOL;'
```

A configured DB URL/custom connection can influence the actual driver; verify that privately with the deployment operator. Before rollout, obtain engine/version and a sanitized schema/migration inventory, then exercise an anonymized disposable clone of that engine. Never treat `migrate --pretend` as permission to point at production: setup/repository checks may still connect/write.

## Migration compatibility inventory

| Files (ordered)                                         | Contracts / review points                                                                                                                                         |
| ------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `0001_01_01_000000_create_users_table.php`              | users, reset-token table, sessions; nullable unique username, unique email. Token table alone is not a password-reset flow.                                       |
| `0001_01_01_000001_create_cache_table.php`              | cache and locks; string primary keys and integer expiry.                                                                                                          |
| `0001_01_01_000002_create_jobs_table.php`               | jobs, batches, failed jobs; composite queue/date index.                                                                                                           |
| `2025_01_01_000010_create_ruangan_table.php`            | administrative availability enum.                                                                                                                                 |
| `2025_01_01_000011_create_pengajuan_judul_table.php`    | title decisions and nullable supervisor foreign keys.                                                                                                             |
| `2025_01_01_000012_create_bimbingan_table.php`          | student/lecturer cascade foreign keys; consultation status enum.                                                                                                  |
| `2025_01_01_000013_create_pendaftaran_sempro_table.php` | four nullable document-path columns; administrative status enum.                                                                                                  |
| `2025_01_01_000014_create_pendaftaran_sidang_table.php` | three nullable document-path columns; administrative status enum.                                                                                                 |
| `2025_01_01_000015_create_penjadwalan_table.php`        | dynamic registration reference has no FK; typed schedules, nullable room/examiner FKs.                                                                            |
| `2026_10_11_000001_create_penilaian_table.php`          | unique schedule/lecturer pair; decimal(5,2); SQLite INSERT/UPDATE range/precision triggers, other drivers raw `ALTER TABLE ... ADD CONSTRAINT ... CHECK`.         |
| `2026_10_11_000002_add_academic_review.php`             | changes title enum to include revision; adds separate academic status and notes. Down intentionally keeps expanded title enum but drops academic decisions/notes. |
| `2026_10_11_000003_create_schedule_periods.php`         | JSON slots, date bounds/capacity; nullable schedule period FK with restricted deletion. Existing schedules stay null; no historical backfill.                     |

SQLite enum/check representation and decimal affinity differ from MySQL/PostgreSQL. SQLite proof is not native ENUM alteration, enforced MySQL CHECK/version, JSON, decimal-rounding, lock or transactional-DDL proof. In particular non-SQLite range CHECK does not assert two-decimal input before decimal coercion; old MySQL versions may ignore CHECK. Validate exact intended engine/version. Laravel generates enum alteration SQL by grammar: exercise it rather than assuming portability. Rollback destroys assessment/academic/period data; backup restore is the production recovery strategy, not a casual three-step rollback. Restrict/cascade/null FK semantics need populated-engine rehearsal.

## Legacy public-document staging command

New command: `app/Console/Commands/RelocateLegacyDocuments.php`.

```sh
php artisan documents:relocate-legacy
# Only after operator backups, review and actual writer quiescence:
php artisan documents:relocate-legacy --execute --quiesced
```

Default is read-only inventory. Both modes read only registration document references, never scan arbitrary public files. Historical HEAD upload controllers stored the same relative `dokumen/{sempro|sidang}/{mahasiswa_id}/{filename}.pdf` paths on the `public` disk; repaired upload controllers use `local`. Therefore private copies preserve paths and require **no DB updates**. Allowlist is shared with `ExamDocumentController::fields` (four sempro and three sidang fields). Paths must match type/student and a conservative PDF basename; missing files, unexpected paths and different destination hashes block the entire preflight. Symlink roots/path components and non-local disks are refused. Identical existing private copies are idempotent. Execution requires explicit `--execute --quiesced`, uses exclusive destination creation, compares SHA-256 before/after copy and never overwrites/deletes originals. Output contains aggregate counts, not student IDs/file paths/credentials.

**This is non-destructive staging, not completed confidentiality relocation.** Public originals remain exposed until an operator retires them after backups, per-file verification and authenticated examiner acceptance. No automatic public deletion is included. Disable public document serving during this maintenance window; stop HTTP uploads, workers and all other storage writers, snapshot DB + public/private storage, run inventory, resolve every blocked reference, stage copies, test authorized/unauthorized document reads, then retire verified public originals with an independently approved manifest. Audit orphan public files, web-server aliases/CDN/cache copies and historical URLs separately: this command covers only currently referenced documents. `--quiesced` is operator attestation, not a lock. It does not remove caches or enforce maintenance itself. Local filesystem ancestors must be trusted and not writable by adversaries; symlink checks are not a hostile concurrent filesystem sandbox.

On copy/verification failure, some private copies may remain; originals and DB paths remain unchanged. Investigate partial private copies manually and repeat dry-run; never blindly delete a conflicting destination. Catch output intentionally suppresses raw DB/storage exception text to avoid leaking connection details. Unknown/noncanonical historical paths require operator review, not automatic normalization.

## Executed evidence

- RED: `php vendor/bin/phpunit tests/Feature/LegacyDocumentRolloutTest.php` failed because `documents:relocate-legacy` did not exist.
- GREEN initial slice: 1 test / 9 assertions passed using SQLite `:memory:` and `Storage::fake('public')` / `Storage::fake('local')` only.
- Conflict/missing/traversal preflight coverage: targeted suite passed 2 tests / 13 assertions, before the additional idempotent rerun assertion.
- Final full backend suite: `php vendor/bin/phpunit` **172 tests / 1854 assertions passed** (34.275 seconds). Includes final relocation tests and sibling changes present during execution.
- `php vendor/bin/pint --test app/Console/Commands/RelocateLegacyDocuments.php tests/Feature/LegacyDocumentRolloutTest.php` passed. Bounded diff whitespace check returned exit 0.
- Separate file-backed SQLite rehearsal used `C:/Users/Tenma/AppData/Local/hermes/profiles/redmess/cache/scratch/enrolify-rollout-isolated.sqlite`, newly created by `enrolify-rollout-probe.php` in that same scratch directory. Bootstrap config was overridden before any DB work (`database.default=sqlite`, scratch database path, connection URL null, cache array). Probe refuses an existing scratch DB. `migrate --force`, `migrate:rollback --step=3 --force`, then `migrate --force` all returned exit 0. Final readback: **12 migration rows**, **2 range triggers**, `penilaian` and `schedule_periods` present, `penjadwalan.period_id` and sempro academic status present. No seeders run. Scratch artifact is disposable, not a repository database.

Tests demonstrate unchanged registration paths/public originals, byte-identical private copies, default no-copy, missing quiescence refusal, conflict preservation, missing/unsafe reference rejection and rerun success. They do not establish production file-open authorization, populated upgrade reversibility, storage ACLs or hostile concurrency. No frontend gate is claimed for this PHP/docs-only slice.

## Random-account access provisioning: precise blocker

Current student create/import and lecturer create/import generate random passwords; no cleartext secret is returned. `User` uses Laravel's hashed cast/Notifiable, and the reset-token table exists. However `routes/web.php` contains login/logout, authenticated profile password change and Tendik student reset, **no guest forgot-password or reset-token completion routes**; no `routes/auth.php` exists. The profile password endpoint requires the current password, so newly random provisioned users cannot use it. The Tendik reset workflow is an existing predictable-default reset, not a secure invitation mechanism; no default value is disclosed here or recommended as rollout provisioning.

Consequently secure self-service first access is **blocked**, not exercised or marked delivered. Sending Laravel's default reset notification without a valid named password-reset route is not an end-to-end solution. Required next slice: identity-verified, per-user invitation/reset-token issuance; audited privileged authorization; expiring single-use tokens with throttling; guest reset completion route with confirmed strong password and token consumption; configured trustworthy application URL + mail delivery; no tokens/passwords in flash/logs/CLI; existing-session/remember-token invalidation on reset; disposable tests for expiry, replay, unauthorized issuance and successful login. Confirm institutional identity/mail ownership before enabling invitations. No production mail, account mutations, shared secrets or default credentials were generated/disclosed in this slice.
