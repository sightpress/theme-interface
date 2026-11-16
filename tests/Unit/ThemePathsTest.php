<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\ThemePaths;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ThemePaths::class)]
#[UsesClass(Guard::class)]
final class ThemePathsTest extends TestCase
{
    public function testStandardPlaces(): void
    {
        $paths = new ThemePaths('/srv/vendor/acme/harbour/');

        self::assertSame('/srv/vendor/acme/harbour', $paths->root);
        self::assertSame('/srv/vendor/acme/harbour/theme.json', $paths->tokensFile());
        self::assertSame('/srv/vendor/acme/harbour/templates', $paths->templates());
        self::assertSame('/srv/vendor/acme/harbour/assets', $paths->assets());
        self::assertSame('/srv/vendor/acme/harbour/translations', $paths->translations());
        self::assertSame('/srv/vendor/acme/harbour/assets/css/site.css', $paths->path('/assets/css/site.css'));
        self::assertSame('/srv/vendor/acme/harbour', $paths->path(''));
    }

    public function testTheFileSystemRoot(): void
    {
        $paths = new ThemePaths('/');

        self::assertSame('/', $paths->root);
        self::assertSame('/theme.json', $paths->tokensFile());
    }

    public function testRefusesAnEmptyFolder(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Theme folder must not be empty.'));
        new ThemePaths('');
    }
}
