<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Traits;

use Brackets\AdminAuth\Models\AdminUser;
use Brackets\Craftable\Tests\TestUpdatedByModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use PHPUnit\Framework\TestCase;

class UpdatedByAdminUserTraitTest extends TestCase
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
