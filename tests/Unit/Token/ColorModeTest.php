<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Token;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Token\ColorMode;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Token\TokenGroup;

#[CoversClass(ColorMode::class)]
#[CoversClass(ColorUse::class)]
#[CoversClass(TokenGroup::class)]
final class ColorModeTest extends TestCase
{
    public function testColorModes(): void
    {
        self::assertSame('light mode', ColorMode::Light->label());
        self::assertSame('dark mode', ColorMode::Dark->label());
        self::assertSame('light mode with more contrast', ColorMode::LightMore->label());
        self::assertSame('dark mode with more contrast', ColorMode::DarkMore->label());
        self::assertSame(ColorMode::Light, ColorMode::Light->base());
        self::assertSame(ColorMode::Light, ColorMode::LightMore->base());
        self::assertSame(ColorMode::Dark, ColorMode::Dark->base());
        self::assertSame(ColorMode::Dark, ColorMode::DarkMore->base());
    }

    public function testColorUses(): void
    {
        self::assertSame(4.5, ColorUse::Text->minimumRatio());
        self::assertSame(3.0, ColorUse::Ui->minimumRatio());
        self::assertSame('text', ColorUse::Text->label());
        self::assertSame('interface colour', ColorUse::Ui->label());
    }

    public function testTokenGroups(): void
    {
        self::assertSame(
            ['font', 'space', 'radius', 'focus'],
            array_map(static fn (TokenGroup $group): string => $group->cssPrefix(), TokenGroup::cases()),
        );
        self::assertSame(['size-base'], TokenGroup::Typography->requiredTokens());
        self::assertSame([], TokenGroup::Spacing->requiredTokens());
    }
}
