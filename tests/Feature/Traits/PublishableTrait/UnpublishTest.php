<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\PublishableTrait;

use Brackets\Craftable\Tests\Feature\Traits\PublishableTestCase;
use Carbon\CarbonImmutable;

class UnpublishTest extends PublishableTestCase
{
    public function testSetsPublishedAtToNull(): void
    {
        $model = $this->createPublishableModel('test', CarbonImmutable::now()->subDay());

        self::assertTrue($model->isPublished());

        $model->unpublish();
        $model->refresh();

        self::assertNull($model->published_at);
        self::assertFalse($model->isPublished());
    }

    public function testUnpublishOnAlreadyUnpublishedModel(): void
    {
        $model = $this->createPublishableModel('test');

        self::assertNull($model->published_at);

        $result = $model->unpublish();

        self::assertTrue($result);
        self::assertNull($model->fresh()->published_at);
    }
}
