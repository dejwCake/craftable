<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Database\Migrations;

use Brackets\Craftable\Tests\TestAdminUserModel;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Override;

/**
 * The default admin user, role and permissions of a fresh install.
 */
class FillDefaultAdminUserAndPermissionsTest extends PermissionMigrationTestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->app['config']->set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admin_users']);
        $this->app['config']->set('auth.providers.admin_users', [
            'driver' => 'eloquent',
            'model' => TestAdminUserModel::class,
        ]);

        $schemaBuilder = $this->db()->connection()->getSchemaBuilder();
        $schemaBuilder->dropIfExists('test_admin_users');
        $schemaBuilder->create('test_admin_users', static function (Blueprint $table): void {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
            $table->boolean('activated')->default(false);
            $table->timestamps();
        });
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db()->connection()->getSchemaBuilder()->dropIfExists('test_admin_users');

        parent::tearDown();
    }

    public function testAdministratorHasTheSecurityResetPermission(): void
    {
        $this->migrate('up');

        self::assertContains('admin.admin-user.security-reset', $this->administratorPermissions());
    }

    public function testAdministratorStillLacksImpersonalLogin(): void
    {
        $this->migrate('up');

        $permissions = $this->administratorPermissions();
        self::assertNotContains('admin.admin-user.impersonal-login', $permissions);
        self::assertContains('admin.admin-user.index', $permissions);
    }

    /**
     * @return array<int, string>
     */
    private function administratorPermissions(): array
    {
        return $this->db()->table('role_has_permissions')
            ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->where('roles.name', 'Administrator')
            ->where('roles.guard_name', 'admin')
            ->pluck('permissions.name')
            ->all();
    }

    private function migrate(string $direction): void
    {
        $migration = require __DIR__ . '/../../../../database/migrations/fill_default_admin_user_and_permissions.php';
        \assert($migration instanceof Migration);
        // Anonymous migration: the Migration base class declares neither up() nor down().
        $migration->{$direction}();
    }

    private function db(): DatabaseManager
    {
        return $this->app->make(DatabaseManager::class);
    }
}
