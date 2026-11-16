<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ChartTokens::class)]
#[UsesClass(Guard::class)]
final class ChartTokensTest extends TestCase
{
    public function testSeries(): void
    {
        self::assertSame(['primary', 'success'], new ChartTokens(['primary', 'success'])->series);
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
