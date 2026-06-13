# Laravel Framework — Opinionated Fork

This is `deplox/laravel-framework` (branch `13.x-opinionated`), a fork of `laravel/framework` with deliberate divergences. **Always read [OPINIONATED.md](./OPINIONATED.md) before making non-trivial changes** — it is the single source of truth for every fork divergence (flattened `App\` namespace, ULID keys, no migration `down()`, auth column constants, etc.).

Workflow:
- The fork tracks upstream via merge commits onto `13.x-opinionated`. Opinionated commits sit on top — preserve them when merging upstream.
- When adding a new divergence, update `OPINIONATED.md` in the same commit.
- PRs target `main` (which is the opinionated default branch for this fork), not upstream `laravel/framework`.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
