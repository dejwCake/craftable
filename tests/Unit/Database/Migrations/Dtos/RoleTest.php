<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Database\Migrations\Dtos;

use Brackets\Craftable\Database\Migrations\Dtos\Role;
use PHPUnit\Framework\TestCase;

final class RoleTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $role = new Role(name: 'administrator', guardName: 'web');

        self::assertSame('administrator', $role->name);
        self::assertSame('web', $role->guardName);
    }

    public function testToArrayReturnsCorrectKeys(): void
    {
        $role = new Role(name: 'editor', guardName: 'api');

        $result = $role->toArray();

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('guard_name', $result);
        self::assertSame('editor', $result['name']);
        self::assertSame('api', $result['guard_name']);
    }
}
