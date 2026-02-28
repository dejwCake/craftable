<?php

declare(strict_types=1);

namespace Brackets\Craftable;

use Brackets\Craftable\Console\Commands\CraftableInitializeEnv;
use Brackets\Craftable\Console\Commands\CraftableInstall;
use Brackets\Craftable\Console\Commands\CraftableTestDBConnection;
use Illuminate\Support\ServiceProvider;

class CraftableServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publish();
        }
    }

    public function register(): void
    {
        $this->commands([
            CraftableInitializeEnv::class,
            CraftableInstall::class,
            CraftableTestDBConnection::class,
        ]);
    }

    private function publish(): void
    {
        if (!class_exists('FillDefaultAdminUserAndPermissions')) {
            $timestamp = date('Y_m_d_His', time() + 5);

            $this->publishes([
                __DIR__ . '/../install-stubs/database/migrations/fill_default_admin_user_and_permissions.php' =>
                    $this->app->databasePath('migrations')
                    . '/' . $timestamp . '_fill_default_admin_user_and_permissions.php',
            ], 'migrations');
        }

        if (!file_exists(storage_path() . '/images/avatar.png')) {
            $this->publishes([
                __DIR__ . '/../resources/images/avatar.png' => $this->app->storagePath() . '/images/avatar.png',
            ], 'images');
        }
    }
}
