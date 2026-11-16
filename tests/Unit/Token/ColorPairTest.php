<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Token\ColorPair;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ColorPair::class)]
#[UsesClass(Guard::class)]
final class ColorPairTest extends TestCase
{
    public function testAPair(): void
    {
        $pair = new ColorPair('text', 'surface', ColorUse::Text);

        self::assertSame('text', $pair->foreground);
        self::assertSame('surface', $pair->background);
        self::assertSame(ColorUse::Text, $pair->use);
        self::assertSame('"text" on "surface"', $pair->label());
    }

    public function testRefusesInvalidNames(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Background colour of a contrast pair is not valid ("Surface")');
        new ColorPair('text', 'Surface', ColorUse::Text);
    }

    public function testRefusesAColourOnItself(): void
    {
        $this->expectExceptionObject(new InvalidValueException('A contrast pair needs two different colours, not "text" on itself.'));
        new ColorPair('text', 'text', ColorUse::Ui);
    }
}
