<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Screenshot;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Screenshot::class)]
#[UsesClass(Guard::class)]
final class ScreenshotTest extends TestCase
{
    public function testKeepsPathAndTrimmedAltText(): void
    {
        $screenshot = new Screenshot('assets/screenshot.webp', ' Home page with three cards. ');

        self::assertSame('assets/screenshot.webp', $screenshot->path);
        self::assertSame('Home page with three cards.', $screenshot->alt);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidPaths(): iterable
    {
        yield 'absolute' => ['/etc/screenshot.png'];
        yield 'parent folder' => ['assets/../../secret.png'];
        yield 'current folder' => ['./screenshot.png'];
        yield 'not an image' => ['assets/screenshot.svg'];
        yield 'empty' => [''];
    }

    #[DataProvider('invalidPaths')]
    public function testRefusesPathsOutsideTheThemeOrNotImages(string $path): void
    {
        $this->expectException(InvalidValueException::class);
        new Screenshot($path, 'A picture.');
    }

    public function testRequiresAltText(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Screenshot alt text must not be empty.'));
        new Screenshot('screenshot.png', ' ');
    }
}
