<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class CraftableInstall extends Command
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

    public function __construct(private readonly Filesystem $filesystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Installing Craftable...');

        $this->publishAllVendors();

        $this->addHashToLogging();

        $this->call('admin-ui:install');

        $this->call('admin-auth:install', ['--dont-install-admin-ui' => true]);

        $this->generateUserStuff();

        $this->call('admin-translations:install', ['--dont-install-admin-ui' => true]);

        $this->scanAndSaveTranslations();

        $this->call('admin-listing:install');

        if ($this->password) {
            $this->comment('Admin password is: ' . $this->password);
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
        $alreadyMigrated = false;
        $files = $this->filesystem->allFiles(database_path('migrations'));
        foreach ($files as $file) {
            if (strpos($file->getFilename(), 'fill_default_admin_user_and_permissions.php') !== false) {
                $alreadyMigrated = true;

                break;
            }
        }
        if (!$alreadyMigrated) {
            $this->call('vendor:publish', [
                '--provider' => "Brackets\\Craftable\\CraftableServiceProvider",
            ]);

            $this->generatePasswordAndUpdateMigration();
        }
    }

    private function publishSpatieMediaLibrary(): void
    {
        $alreadyMigrated = false;
        $files = $this->filesystem->allFiles(database_path('migrations'));
        foreach ($files as $file) {
            if (strpos($file->getFilename(), 'create_media_table.php') !== false) {
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

    /**
     * Generate new password and change default password in migration to use new password
     */
    private function generatePasswordAndUpdateMigration(): void
    {
        $this->password = Str::random(10);

        $files = $this->filesystem->allFiles(database_path('migrations'));
        foreach ($files as $file) {
            if (strpos($file->getFilename(), 'fill_default_admin_user_and_permissions.php') !== false) {
                //change database/migrations/*fill_default_user_and_permissions.php to use new password
                $this->strReplaceInFile(
                    database_path('migrations/' . $file->getFilename()),
                    'best package ever',
                    '' . $this->password . '',
                );

                break;
            }
        }
    }

    /**
     * Generate user administration and profile
     */
    private function generateUserStuff(): void
    {
        // TODO this is probably redundant?
        // Migrate
        $this->call('migrate');

        // Generate User CRUD (with new model)
        $this->call('admin:generate:admin-user', [
            '--force' => true,
        ]);

        // Generate user profile
        $this->call('admin:generate:admin-user:profile');
    }

    /**
     * Prepare translations config and rescan
     */
    private function scanAndSaveTranslations(): void
    {
        // Scan translations
        $this->info('Scanning codebase and storing all translations');

        $this->strReplaceInFile(
            config_path('admin-translations.php'),
            '// here you can add your own directories',
            '// here you can add your own directories
        // base_path(\'routes\'), // uncomment if you have translations in your routes i.e. you have localized URLs
        base_path(\'vendor/dejwcake/admin-auth/src\'),
        base_path(\'vendor/dejwcake/admin-auth/resources\'),
        base_path(\'vendor/dejwcake/admin-ui/resources\'),
        base_path(\'vendor/dejwcake/admin-translations/resources\'),',
        );

        $this->call('admin-translations:scan-and-save', [
            'paths' => array_merge(
                config('admin-translations.scanned_directories'),
                ['vendor/dejwcake/admin-auth/src', 'vendor/dejwcake/admin-auth/resources'],
            ),
        ]);
    }

    /**
     * Change logging to add hash to logs
     */
    private function addHashToLogging(): void
    {
        $this->strReplaceInFile(
            config_path('logging.php'),
            '\'days\' => env(\'LOG_DAILY_DAYS\', 14),',
            '\'days\' => env(\'LOG_DAILY_DAYS\', 14),
            \'tap\' => [Brackets\AdvancedLogger\LogCustomizers\HashLogCustomizer::class],',
        );
    }
}
