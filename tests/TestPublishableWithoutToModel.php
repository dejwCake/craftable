<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests;

use Brackets\Craftable\Traits\PublishableTrait;
use Illuminate\Database\Eloquent\Model;

class TestPublishableWithoutToModel extends Model
{
    use PublishableTrait;

    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $table = 'test_publishable_without_to_models';

    /**
     * @var array<string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $guarded = [];

    /**
     * @var bool
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
