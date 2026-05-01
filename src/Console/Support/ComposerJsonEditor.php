<?php

declare(strict_types=1);

namespace Brackets\Craftable\Console\Support;

final readonly class ComposerJsonEditor
{
    private const array REQUIRED_REQUIRE = [
        'php' => '^8.5',
        'dejwcake/craftable' => '^2.0',
    ];

    private const array REQUIRED_REQUIRE_DEV = [
        'dejwcake/admin-generator' => '^2.0',
        'fruitcake/laravel-debugbar' => '^4.1',
        'nunomaduro/collision' => '^8.9',
        'phpunit/phpunit' => '^13.0',
    ];

    public function installCraftable(string $composerJson): string
    {
        /** @var array<string, mixed> $content */
        $content = json_decode($composerJson, true, flags: JSON_THROW_ON_ERROR);

        $content['require'] = $this->mergeRequire($content['require'] ?? []);
        $content['require-dev'] = $this->mergeRequireDev($content['require-dev'] ?? []);

        return json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
    }

    /**
     * @param array<string, string> $existing
     * @return array<string, string>
     */
    private function mergeRequire(array $existing): array
    {
        return $this->sortRequire($this->mergeConstraints($existing, self::REQUIRED_REQUIRE));
    }

    /**
     * @param array<string, string> $existing
     * @return array<string, string>
     */
    private function mergeRequireDev(array $existing): array
    {
        return $this->sortAlphabetical($this->mergeConstraints($existing, self::REQUIRED_REQUIRE_DEV));
    }

    /**
     * @param array<string, string> $existing
     * @param array<string, string> $required
     * @return array<string, string>
     */
    private function mergeConstraints(array $existing, array $required): array
    {
        foreach ($required as $name => $requiredVersion) {
            if (!isset($existing[$name]) || $this->isLowerVersion($existing[$name], $requiredVersion)) {
                $existing[$name] = $requiredVersion;
            }
        }

        return $existing;
    }

    private function isLowerVersion(string $existing, string $required): bool
    {
        return version_compare(
            $this->normalizeConstraint($existing),
            $this->normalizeConstraint($required),
            '<',
        );
    }

    private function normalizeConstraint(string $constraint): string
    {
        $first = trim(explode('||', $constraint)[0]);

        return ltrim($first, '^~>=< ');
    }

    /**
     * Composer-style sort for require: platform packages (php, ext-*) first, then alphabetical.
     *
     * @param array<string, string> $deps
     * @return array<string, string>
     */
    private function sortRequire(array $deps): array
    {
        uksort($deps, static function (string $a, string $b): int {
            $aIsPlatform = self::isPlatformPackage($a);
            $bIsPlatform = self::isPlatformPackage($b);

            if ($aIsPlatform !== $bIsPlatform) {
                return $aIsPlatform ? -1 : 1;
            }

            return strcmp($a, $b);
        });

        return $deps;
    }

    /**
     * @param array<string, string> $deps
     * @return array<string, string>
     */
    private function sortAlphabetical(array $deps): array
    {
        ksort($deps);

        return $deps;
    }

    private static function isPlatformPackage(string $name): bool
    {
        return $name === 'php' || str_starts_with($name, 'ext-') || str_starts_with($name, 'lib-');
    }
}
