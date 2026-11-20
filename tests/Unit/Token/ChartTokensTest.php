<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Token\ColorPair;
use SightPress\Theme\Token\ColorTokens;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ChartTokens::class)]
#[UsesClass(Guard::class)]
#[UsesClass(ColorPair::class)]
#[UsesClass(ColorTokens::class)]
final class ChartTokensTest extends TestCase
{
    public function testSeries(): void
    {
        self::assertSame(['primary', 'success'], new ChartTokens(['primary', 'success'])->series);
    }

    public function testSeriesDefaultToTheMarks(): void
    {
        self::assertSame(['chart-1', 'chart-2', 'chart-3', 'chart-4'], new ChartTokens()->series);
    }

    public function testContrastPairsOnEveryBackgroundTheThemeDefines(): void
    {
        $palette = ColorTokensTest::palette();
        $withSurface = new ColorTokens($palette + ['surface' => '#f3f4f6'], $palette + ['surface' => '#1e1f25']);
        $chart = new ChartTokens(['chart-1', 'surface']);

        self::assertEquals(
            [
                new ColorPair('chart-1', 'background', ColorUse::Ui),
                new ColorPair('surface', 'background', ColorUse::Ui),
                new ColorPair('chart-text', 'background', ColorUse::Text),
                new ColorPair('chart-1', 'surface', ColorUse::Ui),
                new ColorPair('chart-text', 'surface', ColorUse::Text),
            ],
            $chart->contrastPairs($withSurface),
        );
        self::assertEquals(
            [
                new ColorPair('chart-1', 'background', ColorUse::Ui),
                new ColorPair('chart-2', 'background', ColorUse::Ui),
                new ColorPair('chart-3', 'background', ColorUse::Ui),
                new ColorPair('chart-4', 'background', ColorUse::Ui),
                new ColorPair('chart-text', 'background', ColorUse::Text),
            ],
            new ChartTokens()->contrastPairs(new ColorTokens($palette, $palette)),
        );
    }

    public function testRefusesNoSeries(): void
    {
        $this->expectExceptionObject(new InvalidValueException('chart.series must name 1 to 12 colours, not 0.'));
        new ChartTokens([]);
    }

    public function testRefusesTooManySeries(): void
    {
        $this->expectExceptionObject(new InvalidValueException('chart.series must name 1 to 12 colours, not 13.'));
        new ChartTokens(array_map(static fn (int $i): string => 'chart-' . $i, range(1, 13)));
    }

    public function testRefusesRepeatedColours(): void
    {
        $this->expectExceptionObject(new InvalidValueException('chart.series has duplicates: primary.'));
        new ChartTokens(['primary', 'primary']);
    }
}
