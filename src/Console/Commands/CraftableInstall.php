<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Commands;

use Brackets\Craftable\CraftableServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

final class CraftableInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'craftable:install';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Install a Craftable (brackets/craftable) instance';

    protected string $password = '';

    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly Application $app,
        private readonly Repository $config,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing Craftable...');

        $this->publishAllVendors();

        $this->addAdminRoutes();
        $this->addHashToLogging();

        $this->addGitIgnoreToPublic();

        $this->call('admin-ui:install');

        $this->call('admin-auth:install', ['--dont-install-admin-ui' => true]);

        $this->generateUserStuff();

        $this->call('admin-translations:install', ['--dont-install-admin-ui' => true]);

        $this->scanAndSaveTranslations();

        $this->call('admin-listing:install');

        if ($this->password) {
            $this->comment(sprintf('Admin password is: %s', $this->password));
        }

        $this->info('Craftable installed.');
    }

    private function strReplaceInFile(
        string $filePath,
        string $find,
        string $replaceWith,
        ?string $ifRegexNotExists = null,
    ): bool|int {
        $content = $this->filesystem->get($filePath);
        if ($ifRegexNotExists !== null && preg_match($ifRegexNotExists, $content)) {
            return false;
        }

        return $this->filesystem->put($filePath, str_replace($find, $replaceWith, $content));
    }

    /**
     * Publishing all publishable files from all craftable packages
     */
    private function publishAllVendors(): void
    {
        //Spatie Permission
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\\Permission\\PermissionServiceProvider',
            '--tag' => 'permission-migrations',
        ]);
        $this->call('vendor:publish', [
            '--provider' => 'Spatie\\Permission\\PermissionServiceProvider',
            '--tag' => 'permission-config',
        ]);

        //Spatie Backup
        $this->call('vendor:publish', [
            '--provider' => "Spatie\\Backup\\BackupServiceProvider",
        ]);

        $this->publishSpatieMediaLibrary();

        $this->call('vendor:publish', [
            '--provider' => "Brackets\\Media\\MediaServiceProvider",
        ]);

        //Advanced logger
        $this->call('vendor:publish', [
            '--provider' => "Brackets\\AdvancedLogger\\AdvancedLoggerServiceProvider",
        ]);

        $this->publishCraftable();
    }

    private function publishCraftable(): void
    {
        $this->call('vendor:publish', [
            '--provider' => CraftableServiceProvider::class,
        ]);

        $this->generatePasswordAndUpdateMigration();
    }

    private function publishSpatieMediaLibrary(): void
    {
        $alreadyMigrated = false;
        $files = $this->filesystem->allFiles($this->app->databasePath('migrations'));
        foreach ($files as $file) {
            if (str_contains($file->getFilename(), 'create_media_table.php')) {
                $alreadyMigrated = true;

                break;
            }
        }
        if (!$alreadyMigrated) {
            $this->call('vendor:publish', [
                '--provider' => 'Spatie\\MediaLibrary\\MediaLibraryServiceProvider',
                '--tag' => 'medialibrary-migrations',
            ]);
        }
    }

    private function generatePasswordAndUpdateMigration(): void
    {
        $files = $this->filesystem->allFiles($this->app->databasePath('migrations'));
        foreach ($files as $file) {
            if (!str_contains($file->getFilename(), 'fill_default_admin_user_and_permissions.php')) {
                continue;
            }

            $filePath = $this->app->databasePath(sprintf('migrations/%s', $file->getFilename()));
            $content = $this->filesystem->get($filePath);

            if (str_contains($content, "'best package ever'")) {
                $this->password = Str::random(10);
                $this->filesystem->put($filePath, str_replace('best package ever', $this->password, $content));
            } elseif (preg_match("/protected string \\\$password = '(.+)';/", $content, $matches)) {
                $this->password = $matches[1];
            }

            break;
        }
    }

    /**
     * Generate user administration and profile
     */
    private function generateUserStuff(): void
    {
        $this->call('migrate');

        $application = $this->getApplication();

        if ($application !== null && $application->has('admin:generate:admin-user')) {
            $this->call('admin:generate:admin-user', [
                '--force' => true,
            ]);
        }

        if ($application !== null && $application->has('admin:generate:admin-user:profile')) {
            $this->call('admin:generate:admin-user:profile');
        }
    }

    /**
     * Prepare translations config and rescan
     */
    private function scanAndSaveTranslations(): void
    {
        // Scan translations
        $this->info('Scanning codebase and storing all translations');

        $configPath = $this->app->configPath('admin-translations.php');
        $vendorPaths = [
            'vendor/dejwcake/admin-auth/src',
            'vendor/dejwcake/admin-auth/resources',
            'vendor/dejwcake/admin-ui/resources',
            'vendor/dejwcake/admin-translations/resources',
            'vendor/dejwcake/craftable-media/src',
        ];

        $this->strReplaceInFile(
            $configPath,
            '// here you can add your own directories',
            '// here you can add your own directories
        // base_path(\'routes\'), // uncomment if you have translations in your routes i.e. you have localized URLs',
            '|base_path\(\'routes\'\)|',
        );

        foreach ($vendorPaths as $vendorPath) {
            $escapedPath = preg_quote($vendorPath, '|');
            $this->strReplaceInFile(
                $configPath,
                '// here you can add your own directories',
                '// here you can add your own directories
        base_path(\'' . $vendorPath . '\'),',
                '|' . $escapedPath . '|',
            );
        }

        $this->call('admin-translations:scan-and-save', [
            'paths' => array_merge(
                $this->config->get('admin-translations.scanned_directories'),
                $vendorPaths,
            ),
        ]);
    }

    /**
     * Change logging to add hash to logs
     */
    private function addHashToLogging(): void
    {
        $this->strReplaceInFile(
            $this->app->configPath('logging.php'),
            '\'days\' => env(\'LOG_DAILY_DAYS\', 14),',
            '\'days\' => env(\'LOG_DAILY_DAYS\', 14),
            \'tap\' => [Brackets\AdvancedLogger\LogCustomizers\HashLogCustomizer::class],',
        );
    }

    private function addGitIgnoreToPublic(): void
    {
        $gitignorePath = $this->app->publicPath('.gitignore');

        if ($this->filesystem->exists($gitignorePath)) {
            $content = $this->filesystem->get($gitignorePath);
            if (! str_contains($content, '/build')) {
                $this->filesystem->put($gitignorePath, rtrim($content, "\n") . "\n/build\n");
            }

            return;
        }

        $this->filesystem->put($gitignorePath, "/build\n");
    }

    private function addAdminRoutes(): void
    {
        $this->strReplaceInFile(
            $this->app->basePath('bootstrap/app.php'),
            'web: __DIR__.\'/../routes/web.php\',',
            'web: __DIR__.\'/../routes/web.php\',
        then: function () {
            Route::middleware(\'web\')
                ->group(base_path(\'routes/admin.php\'));
        },',
            '|base_path(\'routes/admin.php\')|',
        );
    }
}
