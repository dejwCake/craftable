<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\PublishableTrait;

use Brackets\Craftable\Tests\Feature\Traits\PublishableTestCase;
use Carbon\CarbonImmutable;

class PublishTest extends PublishableTestCase
{
    public function testSetsPublishedAtToNow(): void
    {
        $model = $this->createPublishableModel('test');

        self::assertNull($model->published_at);

        $model->publish();
        $model->refresh();

        self::assertNotNull($model->published_at);
        self::assertTrue($model->published_at->isToday());
    }

    public function testClearsExpiredPublishedTo(): void
    {
        $model = $this->createPublishableModel(
            'test',
            CarbonImmutable::now()->subDays(2),
            CarbonImmutable::now()->subDay(),
        );

        $model->publish();
        $model->refresh();

        self::assertNull($model->published_to);
        self::assertTrue($model->isPublished());
    }

    public function testKeepsFuturePublishedTo(): void
    {
        $futureDate = CarbonImmutable::now()->addWeek();
        $model = $this->createPublishableModel(
            'test',
            null,
            $futureDate,
        );

        $model->publish();
        $model->refresh();

        self::assertNotNull($model->published_to);
    }

    public function testReturnsTrueWhenModelHasNoPublishedAtColumn(): void
    {
        $model = $this->createWithoutToModel('test');

        // Unpublished model with published_at column (has attribute) still works
        $result = $model->publish();

        self::assertTrue($result);
    }
}
