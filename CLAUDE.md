# Laravel Framework — Opinionated Fork

This is `deplox/laravel-framework` (branch `13.x-opinionated`), a fork of `laravel/framework` with deliberate divergences. **Always read [OPINIONATED.md](./OPINIONATED.md) before making non-trivial changes** — it is the single source of truth for every fork divergence (flattened `App\` namespace, ULID keys, no migration `down()`, auth column constants, etc.).

Workflow:
- The fork tracks upstream via merge commits onto `13.x-opinionated`. Opinionated commits sit on top — preserve them when merging upstream.
- When adding a new divergence, update `OPINIONATED.md` in the same commit.
- PRs target `main` (which is the opinionated default branch for this fork), not upstream `laravel/framework`.
