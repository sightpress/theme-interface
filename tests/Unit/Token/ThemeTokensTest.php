<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Token\ColorPair;
use SightPress\Theme\Token\ColorTokens;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Token\ThemeTokens;
use SightPress\Theme\Token\TokenGroup;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ThemeTokens::class)]
#[UsesClass(Guard::class)]
#[UsesClass(ChartTokens::class)]
#[UsesClass(ColorPair::class)]
#[UsesClass(ColorTokens::class)]
#[UsesClass(ColorUse::class)]
#[UsesClass(TokenGroup::class)]
final class ThemeTokensTest extends TestCase
{
    public function testTokens(): void
    {
        $text = new ColorPair('text', 'background', ColorUse::Text);
        $border = new ColorPair('border', 'background', ColorUse::Ui);
        $chart = new ChartTokens(['primary']);
        $tokens = new ThemeTokens(
            self::colors(),
            [
                'typography' => ['size-base' => '1.125rem', 'family-base' => 'system-ui, "Segoe UI", sans-serif'],
                'spacing' => [1 => '0.25rem', 'page' => 'calc(100% - 2rem)'],
            ],
            [$text, $border],
            $chart,
        );

        self::assertSame('1.125rem', $tokens->value(TokenGroup::Typography, 'size-base'));
        self::assertSame(['1' => '0.25rem', 'page' => 'calc(100% - 2rem)'], $tokens->group(TokenGroup::Spacing));
        self::assertSame([], $tokens->group(TokenGroup::Radius));
        self::assertNull($tokens->value(TokenGroup::Focus, 'width'));
        self::assertSame([$text], $tokens->pairs(ColorUse::Text));
        self::assertSame([$border], $tokens->pairs(ColorUse::Ui));
        self::assertSame([$text, $border], $tokens->contrast);
        self::assertSame($chart, $tokens->chart);
    }

    public function testChartAndContrastAreOptional(): void
    {
        $tokens = new ThemeTokens(self::colors(), ['typography' => ['size-base' => '1rem']]);

        self::assertSame([], $tokens->contrast);
        self::assertSame(ChartTokens::MARKS, $tokens->chart->series);
    }

    public function testCheckedPairsAreTheThemesThenTheCharts(): void
    {
        $text = new ColorPair('text', 'background', ColorUse::Text);
        $tokens = new ThemeTokens(self::colors(), ['typography' => ['size-base' => '1rem']], [$text], new ChartTokens(['primary']));

        self::assertEquals(
            [$text, new ColorPair('primary', 'background', ColorUse::Ui), new ColorPair('chart-text', 'background', ColorUse::Text)],
            $tokens->checkedPairs(),
        );
    }

    /**
     * @return iterable<string, array{callable(): ThemeTokens, string}>
     */
    public static function invalidTokens(): iterable
    {
        $typography = ['typography' => ['size-base' => '1rem']];

        yield 'unknown group' => [
            /** @phpstan-ignore argument.type */
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography + ['shadows' => []]),
            'Unknown token group "shadows": use one of "typography", "spacing", "radius", "focus".',
        ];
        yield 'no base font size' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), ['typography' => ['line-height' => '1.5']]),
            'typography must define "size-base".',
        ];
        yield 'no typography at all' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors()),
            'typography must define "size-base".',
        ];
        yield 'invalid token name' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography + ['radius' => ['Small' => '1px']]),
            'A name in radius is not valid ("Small"): use lower-case words or numbers joined by "-".',
        ];
        yield 'value that injects CSS' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography + ['focus' => ['width' => '3px; } a { color: red']]),
            'focus.width is not valid ("3px; } a { color: red")',
        ];
        yield 'value with a comment' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography + ['focus' => ['width' => '3px /* x */']]),
            'focus.width is not valid ("3px /* x */")',
        ];
        yield 'contrast not pairs' => [
            /** @phpstan-ignore argument.type */
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography, [['text', 'background']]),
            'contrast[0] must be an instance of SightPress\Theme\Token\ColorPair.',
        ];
        yield 'pair with an unknown colour' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography, [new ColorPair('text', 'surface', ColorUse::Text)]),
            'The text pair "text" on "surface" names the colour "surface", which colors.light doesn\'t define.',
        ];
        yield 'chart with an unknown colour' => [
            static fn (): ThemeTokens => new ThemeTokens(self::colors(), $typography, [], new ChartTokens(['primary', 'chart-9'])),
            'chart.series names the colour "chart-9", which colors.light doesn\'t define.',
        ];
    }

    /**
     * @param callable(): ThemeTokens $build
     */
    #[DataProvider('invalidTokens')]
    public function testRefusesInvalidTokens(callable $build, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $build();
    }

    private static function colors(): ColorTokens
    {
        return new ColorTokens(ColorTokensTest::palette(), ColorTokensTest::palette('#ececf1', '#121216'));
    }
}
