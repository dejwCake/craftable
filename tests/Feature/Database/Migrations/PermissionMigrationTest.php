<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Database\Migrations;

use Brackets\Craftable\Tests\TestPermissionMigration;

final class PermissionMigrationTest extends PermissionMigrationTestCase
{
    /** @var list<string> */
    private array $permissionNames = [
        'admin.post',
        'admin.post.index',
        'admin.post.create',
        'admin.post.show',
        'admin.post.edit',
        'admin.post.delete',
        'admin.post.bulk-delete',
    ];

    public function testMigrateUp(): void
    {
        $migration = new TestPermissionMigration();
        $migration->up();

        $db = $this->app['db']->connection();

        $permissions = $db->table('permissions')
            ->whereIn('name', $this->permissionNames)
            ->where('guard_name', 'admin')
            ->get();

        self::assertCount(7, $permissions);

        $administratorRoleId = $db->table('roles')
            ->where('name', 'Administrator')
            ->where('guard_name', 'admin')
            ->value('id');

        self::assertNotNull($administratorRoleId);

        $permissionIds = $permissions->pluck('id')->all();

        $rolePermissions = $db->table('role_has_permissions')
            ->where('role_id', $administratorRoleId)
            ->whereIn('permission_id', $permissionIds)
            ->get();

        self::assertCount(7, $rolePermissions);

        // Idempotency check: running up() again must not create duplicates
        $migration->up();

        $permissionsAfterSecondRun = $db->table('permissions')
            ->whereIn('name', $this->permissionNames)
            ->where('guard_name', 'admin')
            ->get();

        self::assertCount(7, $permissionsAfterSecondRun);
    }

    public function testMigrateDown(): void
    {
        $migration = new TestPermissionMigration();
        $migration->up();

        $db = $this->app['db']->connection();

        $permissionIds = $db->table('permissions')
            ->whereIn('name', $this->permissionNames)
            ->where('guard_name', 'admin')
            ->pluck('id')
            ->all();

        $migration->down();

        $remainingPermissions = $db->table('permissions')
            ->whereIn('name', $this->permissionNames)
            ->count();

        self::assertSame(0, $remainingPermissions);

        $remainingModelHasPermissions = $db->table('model_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->count();

        self::assertSame(0, $remainingModelHasPermissions);
    }
}
