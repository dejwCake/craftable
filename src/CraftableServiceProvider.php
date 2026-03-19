<?php

declare(strict_types=1);

namespace Brackets\Craftable;

use Brackets\Craftable\Console\Commands\CraftableInitializeEnv;
use Brackets\Craftable\Console\Commands\CraftableInstall;
use Brackets\Craftable\Console\Commands\CraftableTestDBConnection;
use Illuminate\Support\ServiceProvider;

final class CraftableServiceProvider extends ServiceProvider
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
        $timestamp = date('Y_m_d_His', time() + 5);

        if (!glob($this->app->databasePath('migrations/*_fill_default_admin_user_and_permissions.php'))) {
            $this->publishes([
                __DIR__ . '/../database/migrations/fill_default_admin_user_and_permissions.php'
                => sprintf(
                    '%s/%s_fill_default_admin_user_and_permissions.php',
                    $this->app->databasePath('migrations'),
                    $timestamp,
                ),
            ], 'migrations');
        }

        $avatarPath = sprintf('%s/images/avatar.png', $this->app->storagePath());
        if (!file_exists($avatarPath)) {
            $this->publishes([
                __DIR__ . '/../resources/images/avatar.png' => $avatarPath,
            ], 'images');
        }
    }
}
