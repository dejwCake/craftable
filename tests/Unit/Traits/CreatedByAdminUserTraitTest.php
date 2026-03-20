<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Traits;

use Brackets\AdminAuth\Models\AdminUser;
use Brackets\Craftable\Tests\TestCreatedByModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\TestCase;

class CreatedByAdminUserTraitTest extends TestCase
{
    public function testReturnsCorrectBelongsToRelation(): void
    {
        $model = new TestCreatedByModel();

        $relation = $model->createdByAdminUser();

        self::assertInstanceOf(BelongsTo::class, $relation);
        self::assertSame('created_by_admin_user_id', $relation->getForeignKeyName());
        self::assertSame(AdminUser::class, $relation->getRelated()::class);
    }
}
