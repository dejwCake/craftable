<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\PublishableTrait;

use Brackets\Craftable\Tests\Feature\TestPublishableModel;
use Brackets\Craftable\Tests\Feature\TestPublishableWithoutToModel;
use Brackets\Craftable\Tests\Feature\Traits\PublishableTestCase;
use Carbon\CarbonImmutable;

class ScopeUnpublishedTest extends PublishableTestCase
{
    public function testReturnsModelsWithFuturePublishedAt(): void
    {
        $this->createPublishableModel('future', CarbonImmutable::now()->addDay());
        $this->createPublishableModel('published', CarbonImmutable::now()->subDay());

        $result = TestPublishableModel::unpublished()->get();

        self::assertCount(1, $result);
        self::assertSame('future', $result->first()->name);
    }

    public function testReturnsModelsWithNullPublishedAt(): void
    {
        $this->createPublishableModel('null published_at');

        $result = TestPublishableModel::unpublished()->get();

        self::assertCount(1, $result);
    }

    public function testReturnsModelsWithExpiredPublishedTo(): void
    {
        $this->createPublishableModel(
            'expired',
            CarbonImmutable::now()->subDays(2),
            CarbonImmutable::now()->subDay(),
        );

        $result = TestPublishableModel::unpublished()->get();

        self::assertCount(1, $result);
        self::assertSame('expired', $result->first()->name);
    }

    public function testWorksWithModelWithoutPublishedToColumn(): void
    {
        $this->createWithoutToModel('not published');
        $this->createWithoutToModel('published', CarbonImmutable::now()->subDay());

        $result = TestPublishableWithoutToModel::unpublished()->get();

        self::assertCount(1, $result);
        self::assertSame('not published', $result->first()->name);
    }
}
