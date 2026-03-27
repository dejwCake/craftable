<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests;

use Brackets\Craftable\Database\Migrations\PermissionMigration;
use Illuminate\Support\Collection;

final class TestPermissionMigration extends PermissionMigration
{
    public function up(): void
    {
        $this->setPermissionsAndRoles(
            new Collection([
                'admin.post',
                'admin.post.index',
                'admin.post.create',
                'admin.post.show',
                'admin.post.edit',
                'admin.post.delete',
                'admin.post.bulk-delete',
            ]),
            new Collection(),
        );
        $this->migrateUp();
    }

    public function down(): void
    {
        $this->setPermissionsAndRoles(
            new Collection([
                'admin.post',
                'admin.post.index',
                'admin.post.create',
                'admin.post.show',
                'admin.post.edit',
                'admin.post.delete',
                'admin.post.bulk-delete',
            ]),
            new Collection(),
        );
        $this->migrateDown();
    }
}
