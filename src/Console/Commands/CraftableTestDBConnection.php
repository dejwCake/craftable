<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\DatabaseManager;
use Throwable;

class CraftableTestDBConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $signature = 'craftable:test-db-connection';

    /**
     * The console command description.
     *
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $description = 'Test the database connection';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseManager $db): int
    {
        $this->info('Testing the database connection...');

        try {
            $db->connection()->getPdo();
        } catch (Throwable $e) {
            $this->error(
                "Could not connect to the database. Please check your configuration. Error: " . $e->getMessage(),
            );

            return self::FAILURE;
        }

        $this->info('...connection OK');

        return self::SUCCESS;
    }
}
