<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Tests\Fake\SchemaValidator;
use SightPress\Theme\ThemeSchema;

#[CoversClass(ThemeSchema::class)]
final class ThemeSchemaTest extends TestCase
{
    public function testAMinimalThemeIsValid(): void
    {
        self::assertSame([], SchemaValidator::errors((string) json_encode(self::minimal())));
    }

    /**
     * @return iterable<string, array{array<string, mixed>}>
     */
    public static function invalidThemes(): iterable
    {
        $theme = self::minimal();

        yield 'no name' => [array_diff_key($theme, ['name' => true])];
        yield 'name not kebab case' => [['name' => 'My Theme'] + $theme];
        yield 'blank label' => [['label' => ' '] + $theme];
        yield 'version not SemVer' => [['version' => 'v1'] + $theme];
        yield 'unknown top-level key' => [['scripts' => []] + $theme];
        yield 'required colour missing' => [self::with($theme, ['colors', 'light'], ['background' => '#fff'])];
        yield 'colour not hex' => [self::with($theme, ['colors', 'dark', 'text'], 'red')];
        yield 'colour name not kebab case' => [self::with($theme, ['colors', 'dark', 'Text'], '#000')];
        yield 'unknown colour mode' => [self::with($theme, ['colors', 'sepia'], [])];
        yield 'no base font size' => [self::with($theme, ['typography'], ['line-height' => '1.5'])];
        yield 'token injects CSS' => [self::with($theme, ['spacing', '1'], '1rem; } body { display: none')];
        yield 'token with a comment' => [self::with($theme, ['radius', 'small'], '1px /* x */')];
        yield 'pair with one colour' => [self::with($theme, ['contrast', 'text'], [['text']])];
        yield 'unknown pair use' => [self::with($theme, ['contrast', 'large'], [])];
        yield 'empty chart series' => [self::with($theme, ['chart', 'series'], [])];
        yield 'repeated chart colour' => [self::with($theme, ['chart', 'series'], ['primary', 'primary'])];
    }

    /**
     * @param array<string, mixed> $theme
     */
    #[DataProvider('invalidThemes')]
    public function testInvalidThemesAreRefused(array $theme): void
    {
        self::assertNotSame([], SchemaValidator::errors((string) json_encode($theme)));
    }

    /**
     * @return array<string, mixed>
     */
    private static function minimal(): array
    {
        $colors = [
            'background' => '#ffffff',
            'text' => '#1a1a1f',
            'link' => '#0a57c2',
            'primary' => '#0a57c2',
            'on-primary' => '#fff',
            'border' => '#6b6b78',
            'focus' => '#1a1a1f',
        ];

        return [
            'name' => 'minimal',
            'label' => 'Minimal',
            'version' => '0.1.0',
            'colors' => ['light' => $colors, 'dark' => $colors, 'more' => ['dark' => ['text' => '#000']]],
            'typography' => ['size-base' => '1rem', 'line-height' => 1.5],
            'spacing' => ['1' => '0.25rem'],
            'contrast' => ['text' => [['text', 'background']], 'ui' => [['border', 'background']]],
            'chart' => ['series' => ['primary']],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param non-empty-list<string> $path
     *
     * @return array<string, mixed>
     */
    private static function with(array $data, array $path, mixed $value): array
    {
        $key = array_shift($path);
        if ($path === []) {
            $data[$key] = $value;

            return $data;
        }
        /** @var array<string, mixed> $child */
        $child = is_array($data[$key] ?? null) ? $data[$key] : [];
        $data[$key] = self::with($child, $path, $value);

        return $data;
    }
}
