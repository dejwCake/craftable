<?php

declare(strict_types=1);

namespace Brackets\Craftable\Tests\Unit\Console\Support;

use Brackets\Craftable\Console\Support\GitIgnoreEditor;
use Override;
use PHPUnit\Framework\TestCase;

final class GitIgnoreEditorTest extends TestCase
{
    private GitIgnoreEditor $gitIgnoreEditor;

    private const string BASE_GITIGNORE = <<<'GITIGNORE'
        *.log
        .DS_Store
        .env
        .env.backup
        .env.production
        .phpactor.json
        .phpunit.result.cache
        /.codex
        /.cursor/
        /.idea
        /.nova
        /.phpunit.cache
        /.vscode
        /.zed
        /auth.json
        /node_modules
        /public/build
        /public/hot
        /public/storage
        /storage/*.key
        /storage/pail
        /vendor
        _ide_helper.php
        Homestead.json
        Homestead.yaml
        Thumbs.db
        .phpcs.cache
        .phpstan.cache
        docker-compose.override.yml
        npm-debug.log
        yarn-error.log

        GITIGNORE;

    private const string EXPECTED_GITIGNORE = <<<'GITIGNORE'
        *.log
        .DS_Store
        .env
        .env.backup
        .env.production
        .phpactor.json
        .phpunit.result.cache
        /.codex
        /.cursor/
        /.fleet
        /.idea
        /.nova
        /.phpunit.cache
        /.vscode
        /.zed
        /.claude/settings.local.json
        /auth.json
        /node_modules
        /public/build
        /public/hot
        /public/storage
        /public/media
        /storage/*.key
        /storage/pail
        /storage/uploads
        /storage/debugbar
        /vendor
        _ide_helper.php
        Homestead.json
        Homestead.yaml
        Thumbs.db
        .phpcs.cache
        .phpstan.cache
        docker-compose.override.yml
        npm-debug.log
        yarn-error.log
        TODO.md

        GITIGNORE;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->gitIgnoreEditor = new GitIgnoreEditor();
    }

    public function testInstallOnBaseGitIgnoreProducesExpectedContent(): void
    {
        $result = $this->gitIgnoreEditor->installCraftable(self::BASE_GITIGNORE);

        self::assertSame(self::EXPECTED_GITIGNORE, $result);
    }

    public function testInstallIsIdempotent(): void
    {
        $first = $this->gitIgnoreEditor->installCraftable(self::BASE_GITIGNORE);
        $second = $this->gitIgnoreEditor->installCraftable($first);

        self::assertSame($first, $second);
    }

    public function testEmptyInputAddsAllRequiredEntries(): void
    {
        $result = $this->gitIgnoreEditor->installCraftable('');

        $expected = "/.fleet\n"
            . "/.claude/settings.local.json\n"
            . "/public/media\n"
            . "/storage/uploads\n"
            . "/storage/debugbar\n"
            . "TODO.md\n";

        self::assertSame($expected, $result);
    }

    public function testEntryIsAppendedWhenAnchorIsMissing(): void
    {
        $input = "*.log\n/vendor\n";

        $result = $this->gitIgnoreEditor->installCraftable($input);

        self::assertStringContainsString('/.fleet', $result);
        self::assertStringContainsString('/.claude/settings.local.json', $result);
        self::assertStringEndsWith("\n", $result);
    }
}
