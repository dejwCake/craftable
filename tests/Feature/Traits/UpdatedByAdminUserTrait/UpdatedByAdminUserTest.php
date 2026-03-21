<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\UpdatedByAdminUserTrait;

use Brackets\AdminAuth\Models\AdminUser;
use Brackets\Craftable\Tests\TestCase;
use Brackets\Craftable\Tests\TestUpdatedByModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpdatedByAdminUserTest extends TestCase
{
    public function testReturnsCorrectBelongsToRelation(): void
    {
        $model = new TestUpdatedByModel();

        $relation = $model->updatedByAdminUser();

        self::assertInstanceOf(BelongsTo::class, $relation);
        self::assertSame('updated_by_admin_user_id', $relation->getForeignKeyName());
        self::assertSame(AdminUser::class, $relation->getRelated()::class);
    }
}
