<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class CraftableInitializeEnv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'craftable:init-env';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Initialize database environment variables';

    public function __construct(private readonly Filesystem $filesystem)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Initializing database environment variables...');

        $this->getDbSettings();

        $this->info('Database environment variables initialized.');

        $this->setApplicationName();
    }

    /**
     * Update .env setting
     */
    private function updateEnv(string $key, string $value, string $fileName = '.env'): bool|int
    {
        $fileName = base_path($fileName);
        $content = $this->filesystem->get($fileName);

        return $this->filesystem->put($fileName, preg_replace('/' . $key . '=.*/', $key . '=' . $value, $content));
    }

    /**
     * If default database values in .env are present and interaction mode is on,
     * asks for database settings. Values not provided will not be overwritten.
     */
    private function getDbSettings(): void
    {
        if ($this->isDefaultDatabaseEnv() && $this->input->isInteractive()) {
            $this->updateDbConnection();
            $this->updateDbHost();
            $this->updateDbPort();
            $this->updateDbName();
            $this->updateDbUser();
            $this->updateDbPassword();
        }
    }

    /**
     * Change default application name from Laravel to Craftable
     */
    private function setApplicationName(): void
    {
        if (env('APP_NAME') === 'Laravel') {
            $this->updateEnv('APP_NAME', 'Craftable');
            $this->updateEnv('APP_NAME', 'Craftable', '.env.example');
        }
    }

    /**
     * Determines if the .env file has default database settings
     */
    private function isDefaultDatabaseEnv(): bool
    {
        return
            version_compare(app()::VERSION, '5.8.35', '<') &&
                (env('DB_DATABASE') === 'homestead' &&
                    env('DB_USERNAME') === 'homestead') ||
            version_compare(app()::VERSION, '5.8.35', '>=') &&
                (env('DB_DATABASE') === 'laravel' &&
                    env('DB_USERNAME') === 'root')
        ;
    }

    private function updateDbConnection(): void
    {
        $connection = $this->choice('What database driver do you use?', ['mysql', 'pgsql'], 'mysql');
        if ($connection !== '') {
            $this->updateEnv('DB_CONNECTION', $connection);
        }
    }

    private function updateDbHost(): void
    {
        $host = $this->anticipate('What is your database host?', ['localhost', '127.0.0.1'], '127.0.0.1');
        if ($host !== null && $host !== '') {
            $this->updateEnv('DB_HOST', $host);
        }
    }

    private function updateDbPort(): void
    {
        $port = $this->anticipate(
            'What is your database port?',
            ['3306', '5432'],
            env('DB_CONNECTION') === 'mysql' ? '3306' : '5432',
        );
        if ($port !== null && $port !== '') {
            $this->updateEnv('DB_PORT', $port);
        }
    }

    private function updateDbName(): void
    {
        $name = $this->anticipate(
            'What is your database name?',
            ['laravel'],
            'laravel',
        );
        if ($name !== null && $name !== '') {
            $this->updateEnv('DB_DATABASE', $name);
        }
    }

    private function updateDbUser(): void
    {
        $user = $this->anticipate(
            'What is your database user name?',
            ['root'],
            'root',
        );
        if ($user !== null && $user !== '') {
            $this->updateEnv('DB_USERNAME', $user);
        }
    }

    private function updateDbPassword(): void
    {
        $password = $this->secret('What is your database user password?');
        if ($password !== null && $password !== '') {
            $this->updateEnv('DB_PASSWORD', $password);
        }
    }
}
