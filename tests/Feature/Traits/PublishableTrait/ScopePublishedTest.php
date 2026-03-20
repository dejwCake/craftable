<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\PublishableTrait;

use Brackets\Craftable\Tests\Feature\Traits\PublishableTestCase;
use Brackets\Craftable\Tests\TestPublishableModel;
use Brackets\Craftable\Tests\TestPublishableWithoutToModel;
use Carbon\CarbonImmutable;

class ScopePublishedTest extends PublishableTestCase
{
    public function testReturnsModelsWithPublishedAtInThePast(): void
    {
        $this->createPublishableModel('published', CarbonImmutable::now()->subDay());
        $this->createPublishableModel('not published', CarbonImmutable::now()->addDay());

        $result = TestPublishableModel::published()->get();

        self::assertCount(1, $result);
        self::assertSame('published', $result->first()->name);
    }

    public function testExcludesModelsWithNullPublishedAt(): void
    {
        $this->createPublishableModel('null published_at');
        $this->createPublishableModel('published', CarbonImmutable::now()->subDay());

        $result = TestPublishableModel::published()->get();

        self::assertCount(1, $result);
        self::assertSame('published', $result->first()->name);
    }

    public function testExcludesModelsWithExpiredPublishedTo(): void
    {
        $this->createPublishableModel(
            'expired',
            CarbonImmutable::now()->subDays(2),
            CarbonImmutable::now()->subDay(),
        );

        $result = TestPublishableModel::published()->get();

        self::assertCount(0, $result);
    }

    public function testIncludesModelsWithNullPublishedTo(): void
    {
        $this->createPublishableModel(
            'no end date',
            CarbonImmutable::now()->subDay(),
            null,
        );

        $result = TestPublishableModel::published()->get();

        self::assertCount(1, $result);
    }

    public function testIncludesModelsWithFuturePublishedTo(): void
    {
        $this->createPublishableModel(
            'still active',
            CarbonImmutable::now()->subDay(),
            CarbonImmutable::now()->addDay(),
        );

        $result = TestPublishableModel::published()->get();

        self::assertCount(1, $result);
    }

    public function testWorksWithModelWithoutPublishedToColumn(): void
    {
        $this->createWithoutToModel('published', CarbonImmutable::now()->subDay());
        $this->createWithoutToModel('not published', CarbonImmutable::now()->addDay());

        $result = TestPublishableWithoutToModel::published()->get();

        self::assertCount(1, $result);
        self::assertSame('published', $result->first()->name);
    }
}
