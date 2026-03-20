<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests;

use Brackets\Craftable\Traits\CreatedByAdminUserTrait;
use Illuminate\Database\Eloquent\Model;

class TestCreatedByModel extends Model
{
    use CreatedByAdminUserTrait;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $table = 'test_models';
}
