# Merge scope: Mahasiswa and Tendik

## Active ownership

This contributor's active work is limited to the Mahasiswa (student) and Tendik (administrative staff) portals. When integrating this work, focus review and conflict resolution on these two areas.

Leave Dosen and Kaprodi/Coordinator portal pages, controllers, scheduling generation/publication logic, assessment workflows, and role-specific business rules as they are. Do not overwrite a collaborator's implementations with prototype files, older snapshots, or unrelated portal changes. Existing history on `frontend` contains earlier cross-portal work; that history is not authorization to replace another contributor's current work.

## Shared changes explicitly requested with this slice

- Remove the redundant role badge beside the shared dashboard/page title; retain account identity and normal navigation.
- Remove all four quick-login buttons and passwordless role-login behavior. Use the existing username/email and password authentication path.
- Render real published Sempro and Sidang schedules publicly on the guest home/login page, without requiring authentication. Public display reads existing published schedule records; it does not change coordinator generation, publication, or editing workflows.
- Public schedule payloads must not include account contact information, uploaded documents, scores, private notes, or meeting access links.

These shared entry-point/header changes intentionally affect all roles visually, but do not constitute Dosen or Kaprodi/Coordinator portal refactoring. Review shared-file conflicts by individual change rather than accepting an entire side.

## Login for the current local seeded accounts

Use each account's existing username. The current local seeded accounts use the temporary password `password`; do not add a password bypass or hardcode this value in authentication logic. Do not reset unrelated accounts, rerun the database seeder, or migrate/reset a collaborator's development database as part of the merge.

## Integration checks

1. Review the exact diff and retain unrelated collaborator changes.
2. Verify Mahasiswa and Tendik navigation and their affected forms after integration.
3. Verify guests can see published seminar/defense dates and times, while unpublished/cancelled records are excluded.
4. Verify login requires the account's actual credentials and no role shortcut remains.
5. Run `bun run types:check`, `bun run check`, `bun run build`, `bun test tests/frontend`, and isolated backend tests.
6. Distinguish pre-existing formatting failures from failures introduced by this slice. Do not run a repository-wide automatic formatter to resolve a bounded merge.

No automatic commit, push, unrelated portal rewrite, or database reset is part of this scope.
