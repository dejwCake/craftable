<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Database\Migrations\Dtos;

use Brackets\Craftable\Database\Migrations\Dtos\Permission;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

final class PermissionTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $now = CarbonImmutable::now();

        $permission = new Permission(name: 'edit posts', guardName: 'web', createdAt: $now, updatedAt: $now);

        self::assertSame('edit posts', $permission->name);
        self::assertSame('web', $permission->guardName);
        self::assertSame($now, $permission->createdAt);
        self::assertSame($now, $permission->updatedAt);
    }

    public function testToArrayReturnsCorrectKeys(): void
    {
        $now = CarbonImmutable::now();

        $permission = new Permission(name: 'delete posts', guardName: 'api', createdAt: $now, updatedAt: $now);

        $result = $permission->toArray();

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('guard_name', $result);
        self::assertArrayHasKey('created_at', $result);
        self::assertArrayHasKey('updated_at', $result);
        self::assertSame('delete posts', $result['name']);
        self::assertSame('api', $result['guard_name']);
        self::assertSame($now, $result['created_at']);
        self::assertSame($now, $result['updated_at']);
    }
}
