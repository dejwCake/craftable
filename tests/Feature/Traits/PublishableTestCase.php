<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Traits;

use Brackets\Craftable\Tests\TestCase;
use Brackets\Craftable\Tests\TestPublishableModel;
use Brackets\Craftable\Tests\TestPublishableWithoutToModel;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Override;

abstract class PublishableTestCase extends TestCase
{
    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $schemaBuilder = $this->app['db']->connection()->getSchemaBuilder();

        $schemaBuilder->create('test_publishable_models', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('published_to')->nullable();
        });

        $schemaBuilder->create('test_publishable_without_to_models', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->dateTime('published_at')->nullable();
        });
    }

    protected function createPublishableModel(
        string $name,
        ?CarbonImmutable $publishedAt = null,
        ?CarbonImmutable $publishedTo = null,
    ): TestPublishableModel {
        return TestPublishableModel::create([
            'name' => $name,
            'published_at' => $publishedAt,
            'published_to' => $publishedTo,
        ]);
    }

    protected function createWithoutToModel(
        string $name,
        ?CarbonImmutable $publishedAt = null,
    ): TestPublishableWithoutToModel {
        return TestPublishableWithoutToModel::create([
            'name' => $name,
            'published_at' => $publishedAt,
        ]);
    }
}
