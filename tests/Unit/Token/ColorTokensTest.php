<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Token\ColorMode;
use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Token\ColorTokens;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ColorTokens::class)]
#[UsesClass(Guard::class)]
final class ColorTokensTest extends TestCase
{
    /**
     * @return array<string, string>
     */
    public static function palette(string $text = '#1a1a1f', string $background = '#ffffff'): array
    {
        return [
            'background' => $background,
            'text' => $text,
            'link' => '#0a57c2',
            'primary' => '#0A57C2',
            'on-primary' => '#fff',
            'border' => '#6b6b78',
            'focus' => $text,
            'chart-1' => '#0a57c2',
            'chart-2' => '#a14800',
            'chart-3' => '#1d6b33',
            'chart-4' => '#7b2c94',
            'chart-grid' => '#d4d4dc',
            'chart-text' => $text,
        ];
    }

    public function testColoursPerMode(): void
    {
        $colors = new ColorTokens(
            self::palette(),
            self::palette('#ececf1', '#121216'),
            ['text' => '#000000'],
            ['background' => '#000'],
        );

        self::assertSame([...ColorTokens::REQUIRED, ...ChartTokens::COLORS], $colors->names());
        self::assertTrue($colors->has('link'));
        self::assertFalse($colors->has('mark'));
        self::assertSame('#1a1a1f', $colors->color('text', ColorMode::Light));
        self::assertSame('#ececf1', $colors->color('text', ColorMode::Dark));
        self::assertSame('#000000', $colors->color('text', ColorMode::LightMore));
        self::assertSame('#0a57c2', $colors->color('link', ColorMode::LightMore));
        self::assertSame('#000', $colors->color('background', ColorMode::DarkMore));
        self::assertSame('#ececf1', $colors->color('text', ColorMode::DarkMore));
        self::assertSame('#0A57C2', $colors->colors(ColorMode::Light)['primary']);
        self::assertNull($colors->color('mark', ColorMode::Dark));
    }

    public function testRefusesMissingChartColours(): void
    {
        $light = self::palette();
        unset($light['chart-2'], $light['chart-text']);

        $this->expectExceptionObject(new InvalidValueException(
            'colors.light must define the chart colours "chart-2", "chart-text": chart-1 to chart-4 for lines and bars, chart-grid and chart-text.',
        ));
        new ColorTokens($light, $light);
    }

    public function testRefusesAMissingRequiredColour(): void
    {
        $light = self::palette();
        unset($light['focus'], $light['link']);

        $this->expectExceptionObject(new InvalidValueException('colors.light must define the colours "link", "focus".'));
        new ColorTokens($light, $light);
    }

    public function testRefusesDifferentColoursInLightAndDark(): void
    {
        $this->expectExceptionObject(new InvalidValueException(
            'colors.light and colors.dark must define the same colours; different: "mark", "surface".',
        ));
        new ColorTokens(self::palette() + ['mark' => '#ff0'], self::palette() + ['surface' => '#eee']);
    }

    public function testRefusesMoreContrastForUnknownColours(): void
    {
        $this->expectExceptionObject(new InvalidValueException(
            'colors.more can only change colours that colors.light defines; unknown: "mark".',
        ));
        new ColorTokens(self::palette(), self::palette(), [], ['mark' => '#ff0']);
    }

    public function testRefusesAColourThatIsNotHex(): void
    {
        $this->expectExceptionObject(new InvalidValueException(
            'colors.dark.text is not valid ("rgb(0 0 0)"): write colours as #rgb or #rrggbb, for example #1a1a1f.',
        ));
        new ColorTokens(self::palette(), self::palette('rgb(0 0 0)'));
    }

    public function testRefusesAnInvalidColourName(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('A name in colors.more.light is not valid ("Text")');
        new ColorTokens(self::palette(), self::palette(), ['Text' => '#000']);
    }
}
