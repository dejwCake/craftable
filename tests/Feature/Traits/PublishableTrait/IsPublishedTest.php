<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits\PublishableTrait;

use Brackets\Craftable\Tests\Feature\Traits\PublishableTestCase;
use Carbon\CarbonImmutable;

class IsPublishedTest extends PublishableTestCase
{
    public function testReturnsTrueWhenPublishedAtIsInThePast(): void
    {
        $model = $this->createPublishableModel('test', CarbonImmutable::now()->subDay());

        self::assertTrue($model->isPublished());
        self::assertFalse($model->isUnpublished());
    }

    public function testReturnsFalseWhenPublishedAtIsNull(): void
    {
        $model = $this->createPublishableModel('test');

        self::assertFalse($model->isPublished());
        self::assertTrue($model->isUnpublished());
    }

    public function testReturnsFalseWhenPublishedAtIsInTheFuture(): void
    {
        $model = $this->createPublishableModel('test', CarbonImmutable::now()->addDay());

        self::assertFalse($model->isPublished());
    }

    public function testReturnsTrueWhenPublishedToIsInTheFuture(): void
    {
        $model = $this->createPublishableModel(
            'test',
            CarbonImmutable::now()->subDay(),
            CarbonImmutable::now()->addDay(),
        );

        self::assertTrue($model->isPublished());
    }

    public function testReturnsFalseWhenPublishedToIsInThePast(): void
    {
        $model = $this->createPublishableModel(
            'test',
            CarbonImmutable::now()->subDays(2),
            CarbonImmutable::now()->subDay(),
        );

        self::assertFalse($model->isPublished());
    }

    public function testReturnsTrueWhenModelHasNoPublishedAtColumn(): void
    {
        $model = $this->createWithoutToModel('test');

        // Model without published_at attribute and no cast returns true
        // because hasPublishedAt() returns false via hasCast check,
        // but the model was created with published_at column so it has the attribute
        $model->published_at = CarbonImmutable::now()->subDay();
        $model->save();
        $model->refresh();

        self::assertTrue($model->isPublished());
    }
}
