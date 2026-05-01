<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Support;

final readonly class GitIgnoreEditor
{
    /** @var list<array{0: string, 1: string|null}> */
    private const array REQUIRED_ENTRIES = [
        ['/.fleet', '/.cursor/'],
        ['/.claude/settings.local.json', '/.zed'],
        ['/public/media', '/public/storage'],
        ['/storage/uploads', '/storage/pail'],
        ['/storage/debugbar', '/storage/uploads'],
        ['TODO.md', null],
    ];

    public function installCraftable(string $content): string
    {
        $hasTrailingNewline = $content === '' || str_ends_with($content, "\n");

        $lines = explode("\n", $content);
        if ($hasTrailingNewline && $content !== '') {
            array_pop($lines);
        }
        if ($lines === ['']) {
            $lines = [];
        }

        foreach (self::REQUIRED_ENTRIES as [$entry, $anchor]) {
            if (in_array($entry, $lines, true)) {
                continue;
            }

            if ($anchor === null) {
                $lines[] = $entry;

                continue;
            }

            $anchorIdx = array_search($anchor, $lines, true);
            if ($anchorIdx === false) {
                $lines[] = $entry;

                continue;
            }

            array_splice($lines, $anchorIdx + 1, 0, [$entry]);
        }

        return implode("\n", $lines) . "\n";
    }
}
