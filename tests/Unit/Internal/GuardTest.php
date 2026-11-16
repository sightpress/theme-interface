<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;
use stdClass;

#[CoversClass(Guard::class)]
final class GuardTest extends TestCase
{
    public function testNotBlank(): void
    {
        self::assertSame('x', Guard::notBlank('x', 'Field'));

        $this->expectExceptionObject(new InvalidValueException('Field must not be empty.'));
        Guard::notBlank(" \n", 'Field');
    }

    public function testMatches(): void
    {
        self::assertSame('text-muted', Guard::matches('text-muted', Guard::NAME, 'Name', Guard::NAME_RULE));

        $this->expectExceptionObject(new InvalidValueException('Name is not valid ("Text"): rule.'));
        Guard::matches('Text', Guard::NAME, 'Name', 'rule');
    }

    public function testMatchesRefusesAnEmptyString(): void
    {
        $this->expectException(InvalidValueException::class);
        Guard::matches('', '/.*/', 'Name', 'rule');
    }

    public function testUnique(): void
    {
        Guard::unique(['a', 'b'], 'Things');
        $this->expectExceptionObject(new InvalidValueException('Things has duplicates: a.'));
        Guard::unique(['a', 'b', 'a'], 'Things');
    }

    public function testListOf(): void
    {
        $object = new stdClass();
        self::assertSame([$object], Guard::listOf([$object], stdClass::class, 'Things'));
    }

    public function testListOfRefusesAMap(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Things must be a list.'));
        Guard::listOf(['a' => new stdClass()], stdClass::class, 'Things');
    }

    public function testListOfRefusesOtherObjects(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Things[1] must be an instance of stdClass.'));
        Guard::listOf([new stdClass(), 'x'], stdClass::class, 'Things');
    }

    public function testListMatching(): void
    {
        self::assertSame(['a', 'b'], Guard::listMatching(['a', 'b'], Guard::NAME, 'Things', 'rule'));
    }

    public function testListMatchingRefusesAMap(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Things must be a list.'));
        Guard::listMatching(['a' => 'a'], Guard::NAME, 'Things', 'rule');
    }

    public function testListMatchingRefusesNonStrings(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Things[0] must be a string.'));
        Guard::listMatching([1], Guard::NAME, 'Things', 'rule');
    }

    public function testListMatchingNamesTheIndex(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Things[1] is not valid ("B"): rule.'));
        Guard::listMatching(['a', 'B'], Guard::NAME, 'Things', 'rule');
    }

    public function testMapMatching(): void
    {
        self::assertSame(['a' => 'x'], Guard::mapMatching(['a' => 'x'], Guard::NAME, 'n', '/^x$/', 'v', 'map'));
    }

    public function testMapMatchingChecksNames(): void
    {
        $this->expectExceptionObject(new InvalidValueException('A name in map is not valid ("A"): n.'));
        Guard::mapMatching(['A' => 'x'], Guard::NAME, 'n', '/^x$/', 'v', 'map');
    }

    public function testMapMatchingRefusesNonStrings(): void
    {
        $this->expectExceptionObject(new InvalidValueException('map.a must be a string.'));
        Guard::mapMatching(['a' => 1], Guard::NAME, 'n', '/^x$/', 'v', 'map');
    }

    public function testMapMatchingChecksValues(): void
    {
        $this->expectExceptionObject(new InvalidValueException('map.a is not valid ("y"): v.'));
        Guard::mapMatching(['a' => 'y'], Guard::NAME, 'n', '/^x$/', 'v', 'map');
    }
}
