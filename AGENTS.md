# AGENTS.md — dejwcake/craftable

The umbrella package of Craftable: `craftable:install` wires all sub-packages into a Laravel app,
a migration seeds the default admin user, role and permissions, and shared Eloquent traits.
Composer `dejwcake/craftable`, namespace `Brackets\Craftable` (fork of `brackets/craftable`).
README.md is also the user-facing Craftable documentation entry point.

## Layout

- `src/Console/Commands/CraftableInstall.php` — publishes vendor assets of every sub-package,
  runs their install commands, generates admin-user CRUD/profile (when admin-generator is
  present), scans translations, patches `bootstrap/app.php` and `config/logging.php`.
- `CraftableInitializeEnv` (`craftable:init-env`), `CraftableTestDBConnection`
  (`craftable:test-db-connection`).
- `src/Traits/` — `PublishableTrait`, `CreatedByAdminUserTrait`, `UpdatedByAdminUserTrait`.
- `database/migrations/fill_default_admin_user_and_permissions.php` — anonymous migration that
  seeds via raw queries (no Eloquent).

## Commands

Everything runs in Docker from the package root — never against a host PHP. The full,
copy-pasteable list (composer, every QA tool, both databases and
the "whole PHP suite" one-liner) is in **README.md → "How to develop this project"**.
The ones you need most:

```shell
docker compose run --rm test composer update
docker compose run --rm test ./vendor/bin/phpunit                         # MariaDB (default)
docker compose run --rm -e DB_CONNECTION=pgsql test ./vendor/bin/phpunit   # PostgreSQL
docker compose run --rm php-qa phpcs -s --colors --extensions=php
docker compose run --rm php-qa phpcbf -s --colors --extensions=php       # auto-fix style
docker compose run --rm php-qa phpstan analyse --configuration=phpstan.neon
docker compose run --rm php-qa phpmd ./database,./resources,./src,./tests ansi phpmd.xml --suffixes php --baseline-file phpmd.baseline.xml
docker compose run --rm php-qa phpcs --standard=.phpcs.compatibility.xml --cache=.phpcs.cache
docker compose run --rm php-qa composer normalize
```

A change is done when phpcs, phpstan, phpmd and the test suite are green.

## Code conventions

- PHP `^8.5`, Laravel 13. Every file starts with `declare(strict_types=1);`.
- **No Facades** — inject contracts through the constructor.
- **No helpers**, with these exceptions: `trans()` / `__()` are allowed everywhere; `app()` only in
  models, traits and places where DI is genuinely hard to provide.
- Constructor property promotion. `final` classes and `readonly` wherever possible — prefer a
  `final readonly class`, otherwise readonly properties. A readonly property is public rather than
  hidden behind a getter.
- Always import with `use`; never inline `\Fully\Qualified\Names`.
- Alias the colliding `Repository` contracts:
  `use Illuminate\Contracts\Config\Repository as Config;`,
  `use Illuminate\Contracts\Cache\Repository as Cache;`.
- Name a property after its type: `TranslationImportService $translationImportService`, not `$service`.
- Build strings with `sprintf()` — no `"{$var}"` interpolation and no `.` concatenation.
- Mark overrides with `#[Override]` — **except** a method that overrides a *trait* method
  (e.g. `HasFactory::newFactory()`): PHP 8.5.3 segfaults on that.
- Before adding a native type to an overriding property/parameter, check the parent. If the parent
  is untyped (Laravel's `$fillable`, `$hidden`, a command's `$description`, …) the child must stay
  untyped too.
- Fix new phpstan/phpmd findings in code. Baselines are for accepted, existing debt only — inspect
  the baseline diff before committing it.

## Testing conventions

- PHPUnit 13 + Orchestra Testbench 11. Test namespaces mirror `src/`.
- Several tested methods of one class → a directory named after the class with one
  `<Method>Test.php` per method.
- Feature tests when several real classes collaborate; Unit tests for isolated logic (mock the
  rest). Don't write tests for service providers or install commands.
- PHPUnit assertions are static: `self::assert*()`. Laravel's instance assertions
  (`$this->assertDatabaseHas()`, response asserts) stay on `$this`.
- Resolve services with `$this->app->make()`, never `app()`.
- Test-only models and stubs live in the `tests/` root.

## Package notes

- The seeder migration resolves its services through `app()->make()` in the constructor — an
  anonymous migration can't take constructor DI; that's the accepted helper exception.
- The default password in that migration is replaced during `craftable:install`; don't change the
  placeholder without updating the installer.
- Requires every other `dejwcake/*` package at `^2.0`; an install-flow change usually needs a
  matching change in the sub-package's own install command.

## Versioning

The package is on **2.x** and stays there through the Laravel 13 / PHP 8.5 upgrade — don't add
v3 upgrade sections or bump the `branch-alias`. User-facing changes go to `UPGRADE.md` when
consumers have to act.
