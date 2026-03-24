# Upgrade Guide: v1 to v2

## Requirements

| Dependency | v1 | v2 |
|---|---|---|
| PHP | ^8.2 | ^8.5 |
| Laravel | ^12.0 | ^13.0 |
| dejwcake/admin-auth | ^1.0 | ^2.0 |
| dejwcake/admin-listing | ^1.0 | ^2.0 |
| dejwcake/admin-translations | ^1.0 | ^2.0 |
| dejwcake/admin-ui | ^1.0 | ^2.0 |
| dejwcake/advanced-logger | ^1.1 | ^2.0 |
| dejwcake/craftable-media | ^1.0 | ^2.0 |
| dejwcake/craftable-translatable | ^1.0 | ^2.0 |
| spatie/laravel-backup | ^9.2.7 | ^10.2 |
| spatie/laravel-permission | ^6.15 | ^7.2 |
| phpunit/phpunit | ^11.5 | ^13.0 |
| orchestra/testbench | ^10.0 | ^11.0 |

Update your `composer.json`:

```json
"dejwcake/craftable": "^2.0"
```

## Breaking Changes

### 1. `install-stubs/` Removed — Migration Moved

The `install-stubs/` directory has been removed. The seeder migration is now located directly inside the package:

| v1 path | v2 path |
|---|---|
| `install-stubs/database/migrations/fill_default_admin_user_and_permissions.php` | `database/migrations/fill_default_admin_user_and_permissions.php` |

The service provider now uses `glob()` to check whether the migration has already been published, instead of `class_exists('FillDefaultAdminUserAndPermissions')`.

**Action required:** If you already have the published migration from v1, no action needed — the provider skips publishing when the file exists.

### 2. Migration Rewritten — Facades Replaced with DI

The `fill_default_admin_user_and_permissions` migration has been rewritten:

- `DB` facade replaced with `ConnectionInterface` resolved via `$this->app->make(ConnectionInterface::class)`
- All dependencies (`ConnectionInterface`, `Config`, `Cache`, `HashManager`) resolved via `$this->app->make()` where `$this->app` is obtained from the `app()` helper in the constructor
- String concatenation replaced with `sprintf()`
- Added `use RuntimeException;` and `use Throwable;` imports (previously used unqualified)
- Added `protected Application $app` property populated from the `app()` helper for container access

**Action required:** If you have customized the published migration, compare with the new version and port your changes.

### 3. `CraftableServiceProvider` Made `final`

```php
// v1
class CraftableServiceProvider extends ServiceProvider

// v2
final class CraftableServiceProvider extends ServiceProvider
```

**Action required:** If you extend this class, refactor to use composition or decoration.

### 4. `CraftableInstall` — Dependency Injection Replaces Helpers

The install command now uses constructor-injected dependencies instead of Laravel helpers:

| v1 | v2 |
|---|---|
| `database_path()` | `$this->app->databasePath()` |
| `config_path()` | `$this->app->configPath()` |
| `base_path()` | `$this->app->basePath()` |
| `public_path()` | `$this->app->publicPath()` |
| `config()` | `$this->config->get()` |
| String concatenation | `sprintf()` |

Additional changes:
- `$password` visibility changed from `protected` to `private`
- `publishCraftable()` simplified — no longer checks for existing migration (provider handles it)
- `generatePasswordAndUpdateMigration()` rewritten — reads existing password if already set, only generates new one if placeholder found
- `generateUserStuff()` now checks if generator commands exist before calling them (no longer crashes without `admin-generator`)
- `scanAndSaveTranslations()` rewritten — adds vendor paths individually with idempotent regex guards, adds `craftable-media/src` to scanned paths
- `addGitIgnoreToPublic()` rewritten — appends `/build` to existing `.gitignore` instead of overwriting; default content changed from webpack outputs (`/css`, `/fonts`, `/images`, `/js`, `mix-manifest.json`) to Vite output (`/build`)

### 5. `CraftableInitializeEnv` — Modernized

- Constructor now accepts `Application` via DI (replaces `base_path()` helper)
- `env()` helper replaced with `Illuminate\Support\Env::get()`
- Removed legacy Laravel 5.x version check in `isDefaultDatabaseEnv()` — now only checks for the modern defaults (`laravel`/`root`)
- `preg_replace()` pattern uses `sprintf()` instead of concatenation

### 6. `CraftableTestDBConnection` Made `final`

```php
// v1
class CraftableTestDBConnection extends Command

// v2
final class CraftableTestDBConnection extends Command
```

### 7. `PublishableTrait` — `$dates` and `$casts` Checks Replaced

```php
// v1
private function hasPublishedAt(): bool
{
    if ($this instanceof Model) {
        return $this->hasAttribute('published_at')
            || ($this->dates !== null && in_array('published_at', $this->dates, true))
            || ($this->casts !== null && in_array('published_at', $this->casts, true));
    }
    return false;
}

// v2
private function hasPublishedAt(): bool
{
    if ($this instanceof Model) {
        return $this->hasAttribute('published_at')
            || $this->hasCast('published_at');
    }
    return false;
}
```

The deprecated `$dates` property (removed in Laravel 13) and the incorrect `in_array()` check on `$casts` (was checking cast values, not keys) have been replaced with `Model::hasCast()`.

**Action required:** If you relied on `$dates` to make `PublishableTrait` detect your publish columns, use `casts()` method instead:

```php
protected function casts(): array
{
    return [
        'published_at' => 'datetime',
        'published_to' => 'datetime',
    ];
}
```

### 8. `.gitignore` Default Content Changed

The `addGitIgnoreToPublic()` method now writes `/build` (Vite output) instead of the webpack-era defaults:

```diff
-/css
-/fonts
-/images
-/js
-mix-manifest.json
+/build
```

**Action required:** If you have a custom `public/.gitignore`, the installer will append `/build` to it. If starting fresh, review the generated `.gitignore`.

## CI Pipeline

GitHub Actions workflow added (new in v2):
- `dejwcake/phpqa8.5:1` for code analysis (PHPCS, PHPCS Compatibility, PHPStan, PHPMD)
- `dejwcake/php8.5:1` for tests
- `dejwcake/postgres18:1` and `dejwcake/mariadb12.1:1` for database tests

## Migration Steps Summary

1. Update `composer.json` requirements (PHP ^8.5, all `dejwcake/*` ^2.0, spatie packages bumped)
2. Run `composer update`
3. If you customized the seeder migration, compare with the new version in `database/migrations/`
4. If you relied on `$dates` for `PublishableTrait`, switch to `casts()` method
5. If you extended `CraftableServiceProvider`, refactor (now `final`)
6. Review `public/.gitignore` — now targets Vite (`/build`) instead of webpack outputs
7. All sub-packages must also be upgraded to v2 — see their individual upgrade guides
