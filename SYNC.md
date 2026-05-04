# Upstream Sync Playbook

This document is the conflict-resolution companion to [`OPINIONATED.md`](./OPINIONATED.md). It exists on the `13.x-opinionated` branch only and is consumed by humans resolving merge conflicts surfaced by the automated `upstream-sync.yml` workflow.

## How sync works

`.github/workflows/upstream-sync.yml` runs on a 6-hour cron and on `workflow_dispatch`. For each of the 3 fork branches (`13.x`, `13.x-development`, `13.x-opinionated`) it picks the **oldest** upstream `v13.*` tag (prereleases excluded) for which the corresponding fork tag does not yet exist, then takes one of two paths.

### Mirror branch — `13.x` (fully unattended)

Because `13.x` has no fork-only commits, every upstream tag is reachable by fast-forward. The workflow:

1. Fast-forwards the local checkout to the upstream tag.
2. Pushes `13.x` directly to `origin` (no PR, no CI gate — the result is byte-identical to upstream which is already tested).
3. Creates the fork tag `vXX.YY.ZZ` (same name, same SHA as upstream).
4. Publishes the GitHub Release inline.

If the mirror cannot fast-forward, the workflow fails loudly so a human can clean up `origin/13.x`.

### Divergent branches — `13.x-development` and `13.x-opinionated` (PR for human review)

These branches always need a real merge commit, so syncing is **not** unattended. The workflow:

1. Creates `sync/upstream-<tag>-into-<branch>`, runs `git merge --no-ff <tag>`, and embeds `UPSTREAM_TAG=<tag>` and `FORK_TAG=<tag><suffix>` markers in the merge commit body.
2. Pushes the sync branch and opens a PR labeled `ready-to-merge` (clean merge) or `conflicts` (manual resolution required).
3. **You review and merge** with **Create a merge commit** once CI is green. To switch to fully unattended divergent merges later, enable branch protection requiring `tests.yml` status checks on the divergent branches and add `gh pr merge --auto --merge` to the workflow — both pieces are needed; without status checks, `--auto` merges before tests finish.

After the PR merges with a **merge commit** (squash-merge is disabled because it would destroy the markers), `upstream-release.yml` reads the markers from the last 10 commits (handles GitHub's "Create a merge commit" wrapper), bumps `Application::VERSION`, creates the fork tag, and publishes a GitHub Release.

## Resolving a `conflicts`-labelled PR

1. Check out the sync branch locally:
   ```sh
   git fetch origin
   git checkout sync/upstream-vXX.YY.ZZ-into-13.x-opinionated
   ```
2. Re-run the merge — the bot's WIP commit has a clean tree, so the conflict markers are gone. To get them back:
   ```sh
   git reset --hard HEAD~1
   git merge --no-ff vXX.YY.ZZ
   ```
3. Resolve using the **hotspot defaults below**. When in doubt, run the fork's tests:
   ```sh
   bin/test.sh
   ```
4. Commit with the markers preserved:
   ```sh
   git commit -m "merge: upstream vXX.YY.ZZ into 13.x-opinionated

   UPSTREAM_TAG=vXX.YY.ZZ
   FORK_TAG=vXX.YY.ZZ-opinionated"
   git push --force-with-lease
   ```
5. Remove the `conflicts` label, add `auto-merge`, and merge with a **merge commit** (not squash, not rebase).

## Hotspots and default resolutions

Each hotspot below names the fork-divergent files most likely to conflict on an upstream change. The default resolution is what to choose when the conflict is purely structural; semantic changes from upstream still need to be ported.

### 1. Generator stubs and `make:*` commands — keep flattened `App\` namespace

Source of divergence: [`OPINIONATED.md` §1](./OPINIONATED.md#1-flattened-app-namespace-layout).

Files most likely to conflict:
- `src/Illuminate/Routing/Console/ControllerMakeCommand.php`
- `src/Illuminate/Routing/Console/MiddlewareMakeCommand.php`
- `src/Illuminate/Foundation/Console/RequestMakeCommand.php`
- `src/Illuminate/Foundation/Console/ResourceMakeCommand.php`
- `src/Illuminate/Foundation/Console/ConsoleMakeCommand.php`
- `src/Illuminate/Routing/Console/stubs/controller*.stub`

**Default**: keep the flattened namespace (`App\Controllers\`, `App\Middleware\`, etc.). If upstream adds new behavior (e.g. a new `--api` flag), port it on top.

### 2. Migration stubs — keep ULID PKs and stripped `down()`

Source of divergence: [`OPINIONATED.md` §2](./OPINIONATED.md#2-migrations-filenames-ulid-keys-no-down).

Files most likely to conflict:
- `src/Illuminate/Database/Migrations/stubs/*.stub`
- `src/Illuminate/Cache/Console/stubs/cache.stub`
- `src/Illuminate/Notifications/Console/stubs/notifications.stub`
- `src/Illuminate/Queue/Console/stubs/{batches,failed_jobs,jobs}.stub`
- `src/Illuminate/Session/Console/stubs/database.stub`

**Default**: ULID primary keys and no `down()`. Re-apply our `MigrationCreator`/`Migrator` dual-format prefix detection.

### 3. Auth traits and contracts — keep column constants

Source of divergence: [`OPINIONATED.md` §3, §4](./OPINIONATED.md#3-authentication-column-constants).

Files most likely to conflict:
- `src/Illuminate/Auth/Authenticatable.php`
- `src/Illuminate/Auth/MustVerifyEmail.php`
- `src/Illuminate/Auth/GenericUser.php`
- `src/Illuminate/Foundation/Auth/User.php`
- `src/Illuminate/Contracts/Auth/MustVerifyEmail.php`

**Default**: keep `EMAIL`, `AUTH_PASSWORD`, `VERIFIED_AT` constants and `verified_at` column name. If upstream adds a new property, convert to a constant.

### 4. Authorization map — keep `'index' => 'list'`

Source of divergence: [`OPINIONATED.md` §7](./OPINIONATED.md#7-authorization-viewany-renamed-to-list).

Files most likely to conflict:
- `src/Illuminate/Foundation/Auth/Access/AuthorizesRequests.php`
- `src/Illuminate/Auth/Access/Gate.php`
- `src/Illuminate/Foundation/Console/stubs/policy.stub`

**Default**: keep `list()` policy method; resource map continues to translate `index → list`.

### 5. Sessions — keep ULID generator

Source of divergence: [`OPINIONATED.md` §5](./OPINIONATED.md#5-sessions-ulid-ids).

Files most likely to conflict:
- `src/Illuminate/Session/Store.php`
- `src/Illuminate/Session/Console/SessionTableCommand.php`

**Default**: ULID session IDs. If upstream changes the ID generator's API, port the API but keep ULID.

### 6. Notifications & broadcasting — keep facade-based send

Source of divergence: [`OPINIONATED.md` §8, §9](./OPINIONATED.md#8-notifications-via-the-facade).

Files most likely to conflict:
- `src/Illuminate/Auth/Passwords/CanResetPassword.php`
- `src/Illuminate/Notifications/NotificationServiceProvider.php`
- `src/Illuminate/Broadcasting/BroadcastServiceProvider.php`
- `src/Illuminate/Foundation/Application.php` (aliases array)

**Default**: keep `Notification::send()` flow and `'notification'` / `'broadcast'` container aliases.

### 7. Migrator internals — keep dual-format prefix detection

Files most likely to conflict:
- `src/Illuminate/Database/Migrations/Migrator.php`
- `src/Illuminate/Database/Migrations/MigrationCreator.php`
- `src/Illuminate/Database/Migrations/DatabaseMigrationRepository.php`

**Default**: keep our prefix detection that handles both `Y_m_d_His` and Unix timestamps; keep the ULID `migrations` repository table schema.

### 8. Folio/Octane FQCN inlining — keep inline FQCN

Source of divergence: [`OPINIONATED.md` §10](./OPINIONATED.md#10-soft-import-removal-folio-octane).

Files most likely to conflict:
- `src/Illuminate/Foundation/Configuration/ApplicationBuilder.php`
- `src/Illuminate/Foundation/Exceptions/Renderer/Listener.php`

**Default**: keep inline FQCN; do not re-introduce soft imports.

### 9. `VendorPublishCommand` & `ApiInstallCommand` regexes

Files most likely to conflict:
- `src/Illuminate/Foundation/Console/VendorPublishCommand.php`
- `src/Illuminate/Foundation/Console/ApiInstallCommand.php`

**Default**: keep the dual-format regexes that accept both legacy `Y_m_d_His` and Unix-timestamp migration filenames.

## After resolving

Always run the full suite locally before pushing:

```sh
bin/test.sh
```

If a divergent test starts failing because upstream changed a fixture you depend on, update the fixture rather than reverting the divergence.

## When the mirror branch (`13.x`) cannot fast-forward

This should not happen — `13.x` is a passive mirror with no fork-only commits. If the workflow reports a non-fast-forward, someone has pushed to `origin/13.x` directly. Investigate `git log origin/13.x ^upstream/13.x` to find the offending commits and either revert them on the mirror branch or move them to a divergent branch.
