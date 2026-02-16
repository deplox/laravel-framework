# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is an **opinionated fork** of the Laravel Framework core repository, based on v12.x. It is NOT upstream Laravel — it layers ~14 custom commits with intentional divergences from official Laravel conventions. This is also NOT a Laravel application; it is the framework itself, distributed as a monorepo where individual Illuminate components are split into separate Composer packages.

### Git Workflow

- **GitButler** manages all branching and version control for this project
- Working branch: `gitbutler/workspace` (always; never switch away)
- Upstream base: `12.x`
- Do NOT make commits, push, create branches, or perform other git operations — GitButler handles all version control
- Do not rebase onto or merge from upstream without explicit instruction

## Fork-Specific Changes

These are the key differences from upstream Laravel 12.x. Be aware of these when working in the codebase — standard Laravel documentation may not apply in these areas.

**ID Strategy**: ULID primary keys are the default in migration stubs (`$table->ulid('id')->primary()` instead of `$table->id()`). Session IDs also use ULIDs.

**Migration Stubs**: Forward-only — `down()` methods are removed from generated stubs. Migration filenames use Unix timestamps (`1738934400_create_...`) instead of the standard `Y_m_d_His` format.

**Namespace Changes**: Flattened namespaces in generators — `App\Controllers` instead of `App\Http\Controllers`. This is a partial implementation (not all generators updated).

**Auth Changes**:
- `MustVerifyEmail` contract is on the base User model (upstream leaves it opt-in)
- Auth column names use constants instead of mutable properties (e.g., `const AUTH_PASSWORD = 'password'`, `const EMAIL = 'email'` with late static binding)
- `verified_at` column instead of upstream's `email_verified_at`
- Password reset and email verification use `Notification::send()` instead of `$this->notify()` (decoupled from Notifiable trait)

**Middleware**: `RedirectIfAuthenticated` throws `HttpException(403)` for JSON requests instead of redirecting.

**Policy**: `viewAny` resource ability renamed to `list`.

**Notification Provider**: ChannelManager bound under `'notification'` string alias.

**Broadcasting Provider**: BroadcastManager bound under `'broadcast'` string alias (matching other manager services like `'cache'`, `'db'`, `'queue'`).

**Other Fixes**: mb_substr for UTF-8 safe User-Agent truncation in DatabaseSessionHandler; Folio and Octane referenced with FQN to avoid import of optional dependencies.

See `CLAUDE_NOTES.md` for the full analysis of each change with risk assessments and recommendations.

## Common Commands

### Running Tests

```bash
# All tests
vendor/bin/phpunit

# Single test file
vendor/bin/phpunit tests/Database/EloquentModelTest.php

# Single test method
vendor/bin/phpunit tests/Database/EloquentModelTest.php --filter testUpdate

# Tests in a specific directory
vendor/bin/phpunit tests/Support/

# Docker-based testing (starts MySQL, Redis, Memcached, DynamoDB)
bin/test.sh --php 8.4 -- tests/Database/
```

### Code Formatting (Laravel Pint)

```bash
vendor/bin/pint              # Fix all files
vendor/bin/pint --test       # Check without fixing
```

### Static Analysis (PHPStan)

```bash
vendor/bin/phpstan --configuration=phpstan.src.neon.dist --no-progress   # Source (level 1)
vendor/bin/phpstan --configuration=phpstan.types.neon.dist --no-progress  # Types (level max)
```

### DDEV Local Development

DDEV is configured with PHP 8.4 and MariaDB 11.8 (`ddev/config.yaml`).

```bash
ddev start
ddev exec composer install
ddev exec vendor/bin/phpunit
```

## Architecture

### Monorepo Structure

- `src/Illuminate/` — ~37 framework components, each an independent Composer package (listed in `composer.json` `replace`)
- `tests/` — mirrors `src/` structure; test classes end with `Test.php`
- `types/` — PHPStan type definitions (checked at level max)
- `config/` — default configuration stubs shipped to applications

### Core Design Patterns

**Service Container** (`Illuminate\Container\Container`): Central IoC container managing all dependency injection. The `Application` class extends it.

**Service Providers** (`Illuminate\Support\ServiceProvider`): Register bindings and boot services. Each major component has one. The `register()` method binds into the container; `boot()` runs after all providers are registered.

**Contracts** (`Illuminate\Contracts\*`): Interfaces defining component APIs. Code should depend on contracts, not concrete implementations. Strictly enforced — all drivers implement their contract (e.g., every cache store implements `Contracts\Cache\Store`).

**Manager Pattern** (`Illuminate\Support\Manager`): Abstract base for pluggable driver systems. Subclasses implement `getDefaultDriver()` and provide `create{Name}Driver()` methods for each driver. Runtime extension via `extend($driver, Closure $callback)`. Used by CacheManager, SessionManager, HashManager, DatabaseManager, ChannelManager, etc.

**Facades** (`Illuminate\Support\Facades\Facade`): Static proxies to container-resolved services. Each facade implements `getFacadeAccessor()` returning a container binding name. The `__callStatic()` method resolves the service and forwards calls. Built-in test support: `shouldReceive()`, `spy()`, `fake()`, `swap()`.

**Pipeline** (`Illuminate\Pipeline\Pipeline`): Implements the middleware pattern used for HTTP request processing, job handling, and other pass-through chains.

**Composable Traits**: `Macroable` (runtime method extension via `macro()`/`mixin()`), `Conditionable` (fluent `when()`/`unless()` for method chains), `Tappable` (side effects without breaking chains). Used extensively across query builders, collections, HTTP client, Stringable, etc.

### Key Component Relationships

- **Foundation** bootstraps the `Application`, which extends **Container**
- **Routing** dispatches HTTP requests through **Pipeline** middleware to controllers
- **Database** provides the query builder; **Eloquent** builds the ORM on top of it
- **Queue** serializes and dispatches jobs; **Bus** provides the command dispatching layer
- **Events** provides the observer pattern used throughout (listeners, subscribers)
- **Cache**, **Session**, **Mail**, **Queue**, **Broadcasting** all use the Manager/driver pattern with pluggable backends

### Autoloading

PSR-4 namespaces: `Illuminate\` maps to `src/Illuminate/`. Some Support subnamespaces (`Macroable`, `Collections`, `Conditionable`, `Reflection`) also map under `Illuminate\Support\`. Helper function files are autoloaded via Composer `files` entries.

## Code Style

- PHP 8.2+ required; code must be compatible with 8.2 through 8.5
- Formatting enforced by Laravel Pint (config in `pint.json`); uses an `empty` preset with explicit rules
- Single quotes, short array syntax `[]`, `post` increment style (`$i++`)
- Opening braces on next line for classes/functions (unless signature ends with newline), same line for control structures
- Imports ordered alphabetically: `const`, `class`, `function`
- Blank line before `return` statements
- `not_operator_with_successor_space`: `! $value` not `!$value`
- PHPDoc: `@param`, `@return`, `@throws` order; left-aligned

## Testing Notes

- Tests use Orchestra Testbench (`orchestra/testbench-core`) for integration tests; pure unit tests extend `PHPUnit\Framework\TestCase` directly
- PHPUnit config: `phpunit.xml.dist` with 2048M memory limit, UTC timezone
- DB connection defaults to `testing` (SQLite in-memory) for unit tests
- CI services: MySQL 8, Redis 7.0, Memcached 1.6, DynamoDB Local for integration tests
- CI matrix: PHP 8.2-8.5, PHPUnit 10.5/11.5/12.x, both prefer-lowest and prefer-stable
- CI uses `--fail-on-deprecation` for prefer-stable runs; deprecation warnings must be resolved
- Tests also run on Windows (windows-2022) to ensure cross-platform compatibility

## Known Pitfalls

Hard-won lessons from previous work on this fork. Read these before making changes in these areas.

**Model defaults are untouchable**: Changing `Model::$keyType` or `Model::$incrementing` defaults breaks 250+ tests. The test suite has hundreds of ad-hoc model stubs that rely on the default values. ULID support is properly handled via the `HasUlids` trait and migration stubs — do not change the base Model.

**Trait method collisions in `Foundation\Auth\User`**: This class uses both `MustVerifyEmail` and `CanResetPassword` traits. Adding the same method (e.g., `routeNotificationForMail()`) to both causes a fatal error. Leave notification routing to the `Notifiable` trait.

**Forward-only migration stubs**: When removing `down()` from stubs, you must also update assertions in 8 test files that check for `Schema::dropIfExists`: `SessionTableCommandTest`, `CacheTableCommandTest`, `NotificationTableCommandTest`, `QueueTableCommandTest`, `QueueFailedTableCommandTest`, `QueueBatchesTableCommandTest`, `MigrateMakeCommandTest`, `ModelMakeCommandTest`.

**Namespace flattening is partial**: Not all generators are updated. Test `class_alias` calls and file paths in generator tests must match the flattened directory structure (e.g., `app/Controllers/` not `app/Http/Controllers/`). Check `TransformsToResource::guessResourceName()` and `ConsoleMakeCommand` which already use flat namespaces.

**Auth guard mock updates**: Adding null-safety guards (e.g., checking `getRememberTokenName()`) to auth components requires updating mocks in `tests/Auth/AuthGuardTest.php` that don't set up those expectations.
