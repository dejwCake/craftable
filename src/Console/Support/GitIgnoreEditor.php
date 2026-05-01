<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Support;

final readonly class GitIgnoreEditor
{
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
        $lines = $this->getLines($content);

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

    /**
     * @return array<string>
     */
    public function getLines(string $content): array
    {
        $hasTrailingNewline = $content === '' || str_ends_with($content, "\n");

        $lines = explode("\n", $content);
        if ($hasTrailingNewline && $content !== '') {
            array_pop($lines);
        }
        if ($lines === ['']) {
            $lines = [];
        }

        return $lines;
    }
}
