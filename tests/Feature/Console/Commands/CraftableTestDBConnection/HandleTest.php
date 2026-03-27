<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Feature\Console\Commands\CraftableTestDBConnection;

use Brackets\Craftable\Tests\Feature\TestCase;
use Illuminate\Database\DatabaseManager;
use Mockery;
use PDOException;

class HandleTest extends TestCase
{
    public function testReturnsSuccessOnValidConnection(): void
    {
        $this->artisan('craftable:test-db-connection')
            ->expectsOutput('Testing the database connection...')
            ->expectsOutput('...connection OK')
            ->assertExitCode(0);
    }

    public function testReturnsFailureOnInvalidConnection(): void
    {
        $dbManager = Mockery::mock(DatabaseManager::class);
        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->andThrow(new PDOException('Connection refused'));
        $dbManager->shouldReceive('connection')->andReturn($connection);

        $this->app->instance(DatabaseManager::class, $dbManager);

        $this->artisan('craftable:test-db-connection')
            ->expectsOutput('Testing the database connection...')
            ->expectsOutputToContain('Could not connect to the database')
            ->assertExitCode(1);
    }
}
