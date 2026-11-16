<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\ThemeRequirement;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ThemeRequirement::class)]
#[UsesClass(Guard::class)]
final class ThemeRequirementTest extends TestCase
{
    public function testKeepsTheTrimmedConstraint(): void
    {
        self::assertSame('^0.1', new ThemeRequirement(' ^0.1 ')->constraint);
    }

    public function testRefusesABlankConstraint(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Version constraint on sightpress/theme-interface must not be empty.'));
        new ThemeRequirement('  ');
    }
}
