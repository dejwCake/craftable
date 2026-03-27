<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature;

use Brackets\Craftable\Traits\PublishableTrait;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $name
 * @property CarbonInterface $published_at
 */
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
