<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests;

use Brackets\Craftable\Traits\UpdatedByAdminUserTrait;
use Illuminate\Database\Eloquent\Model;

class TestUpdatedByModel extends Model
{
    use UpdatedByAdminUserTrait;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $table = 'test_models';
}
