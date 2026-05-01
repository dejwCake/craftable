<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Console\Support;

use Brackets\Craftable\Console\Support\ComposerJsonEditor;
use Override;
use PHPUnit\Framework\TestCase;

final class ComposerJsonEditorTest extends TestCase
{
    private ComposerJsonEditor $composerJsonEditor;

    private const string BASE_COMPOSER_JSON = <<<'JSON'
        {
            "name": "test/project",
            "require": {
                "php": "^8.3",
                "laravel/framework": "^13.0",
                "laravel/tinker": "^3.0"
            },
            "require-dev": {
                "fakerphp/faker": "^1.23",
                "larastan/larastan": "^3.9",
                "laravel/pail": "^1.2.5",
                "laravel/pao": "^1.0.6",
                "laravel/pint": "^1.27",
                "mockery/mockery": "^1.6",
                "nunomaduro/collision": "^8.6",
                "phpunit/phpunit": "^12.5.12"
            }
        }
        JSON;

    private const string EXPECTED_COMPOSER_JSON = <<<'JSON'
        {
            "name": "test/project",
            "require": {
                "php": "^8.5",
                "dejwcake/craftable": "^2.0",
                "laravel/framework": "^13.0",
                "laravel/tinker": "^3.0"
            },
            "require-dev": {
                "dejwcake/admin-generator": "^2.0",
                "fakerphp/faker": "^1.23",
                "fruitcake/laravel-debugbar": "^4.1",
                "larastan/larastan": "^3.9",
                "laravel/pail": "^1.2.5",
                "laravel/pao": "^1.0.6",
                "laravel/pint": "^1.27",
                "mockery/mockery": "^1.6",
                "nunomaduro/collision": "^8.9",
                "phpunit/phpunit": "^13.0"
            }
        }

        JSON;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->composerJsonEditor = new ComposerJsonEditor();
    }

    public function testInstallOnBaseComposerJsonProducesExpectedContent(): void
    {
        $result = $this->composerJsonEditor->installCraftable(self::BASE_COMPOSER_JSON);

        self::assertSame(self::EXPECTED_COMPOSER_JSON, $result);
    }

    public function testInstallIsIdempotent(): void
    {
        $first = $this->composerJsonEditor->installCraftable(self::BASE_COMPOSER_JSON);
        $second = $this->composerJsonEditor->installCraftable($first);

        self::assertSame($first, $second);
    }

    public function testHigherPhpVersionIsPreserved(): void
    {
        $input = json_encode([
            'require' => ['php' => '^9.0'],
            'require-dev' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->composerJsonEditor->installCraftable($input);
        $decoded = json_decode($result, true);

        self::assertSame('^9.0', $decoded['require']['php']);
    }

    public function testHigherPhpunitVersionIsPreserved(): void
    {
        $input = json_encode([
            'require' => [],
            'require-dev' => ['phpunit/phpunit' => '^13.5'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->composerJsonEditor->installCraftable($input);
        $decoded = json_decode($result, true);

        self::assertSame('^13.5', $decoded['require-dev']['phpunit/phpunit']);
    }

    public function testLowerPhpVersionIsBumped(): void
    {
        $input = json_encode([
            'require' => ['php' => '^8.0'],
            'require-dev' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->composerJsonEditor->installCraftable($input);
        $decoded = json_decode($result, true);

        self::assertSame('^8.5', $decoded['require']['php']);
    }

    public function testMissingRequiredKeyIsAdded(): void
    {
        $input = json_encode([
            'require' => [],
            'require-dev' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        $result = $this->composerJsonEditor->installCraftable($input);
        $decoded = json_decode($result, true);

        self::assertSame('^8.5', $decoded['require']['php']);
        self::assertSame('^2.0', $decoded['require']['dejwcake/craftable']);
        self::assertSame('^2.0', $decoded['require-dev']['dejwcake/admin-generator']);
        self::assertSame('^4.1', $decoded['require-dev']['fruitcake/laravel-debugbar']);
        self::assertSame('^8.9', $decoded['require-dev']['nunomaduro/collision']);
        self::assertSame('^13.0', $decoded['require-dev']['phpunit/phpunit']);
    }

    public function testRequireIsSortedWithPhpFirstThenAlphabetical(): void
    {
        $result = $this->composerJsonEditor->installCraftable(self::BASE_COMPOSER_JSON);
        $decoded = json_decode($result, true);

        self::assertSame(
            ['php', 'dejwcake/craftable', 'laravel/framework', 'laravel/tinker'],
            array_keys($decoded['require']),
        );
    }

    public function testRequireDevIsSortedAlphabetically(): void
    {
        $result = $this->composerJsonEditor->installCraftable(self::BASE_COMPOSER_JSON);
        $decoded = json_decode($result, true);

        self::assertSame(
            [
                'dejwcake/admin-generator',
                'fakerphp/faker',
                'fruitcake/laravel-debugbar',
                'larastan/larastan',
                'laravel/pail',
                'laravel/pao',
                'laravel/pint',
                'mockery/mockery',
                'nunomaduro/collision',
                'phpunit/phpunit',
            ],
            array_keys($decoded['require-dev']),
        );
    }

    public function testForwardSlashesAreNotEscaped(): void
    {
        $result = $this->composerJsonEditor->installCraftable(self::BASE_COMPOSER_JSON);

        self::assertStringNotContainsString('\/', $result);
    }
}
