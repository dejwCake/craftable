<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\CreatedByAdminUserTrait;

use Brackets\AdminAuth\Models\AdminUser;
use Brackets\Craftable\Tests\Feature\TestCase;
use Brackets\Craftable\Tests\Feature\TestCreatedByModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreatedByAdminUserTest extends TestCase
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
