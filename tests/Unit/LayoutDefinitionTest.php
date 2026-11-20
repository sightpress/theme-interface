<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\LayoutDefinition;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(LayoutDefinition::class)]
#[UsesClass(Guard::class)]
final class LayoutDefinitionTest extends TestCase
{
    public function testALayout(): void
    {
        $layout = new LayoutDefinition('full-width', ' Full width ', 'Content across the whole page.');

        self::assertSame('full-width', $layout->id);
        self::assertSame('Full width', $layout->label);
        self::assertSame('Content across the whole page.', $layout->description);
        self::assertSame('layouts/full-width.html.twig', $layout->template());
        self::assertFalse($layout->isRequired());
    }

    public function testTheRequiredLayouts(): void
    {
        $layouts = LayoutDefinition::required();

        self::assertSame(LayoutDefinition::REQUIRED, array_map(static fn (LayoutDefinition $layout): string => $layout->id, $layouts));
        foreach ($layouts as $layout) {
            self::assertTrue($layout->isRequired());
            self::assertNotSame('', $layout->description);
        }
        self::assertContains(LayoutDefinition::FALLBACK, LayoutDefinition::REQUIRED);
    }

    public function testTheStandardLayouts(): void
    {
        $layouts = LayoutDefinition::standard();

        self::assertSame(LayoutDefinition::STANDARD, array_map(static fn (LayoutDefinition $layout): string => $layout->id, $layouts));
        foreach ($layouts as $layout) {
            self::assertTrue($layout->isStandard());
            self::assertFalse($layout->isRequired());
            self::assertTrue($layout->isPageLayout());
            self::assertNotSame('', $layout->description);
        }
        self::assertSame([], array_intersect(LayoutDefinition::STANDARD, LayoutDefinition::REQUIRED));
    }

    public function testOnlyTheSearchLayoutIsNotForPages(): void
    {
        $pages = array_map(static fn (LayoutDefinition $layout): bool => $layout->isPageLayout(), LayoutDefinition::required());

        self::assertSame([true, true, false], $pages);
        self::assertFalse(LayoutDefinition::required()[0]->isStandard());
        self::assertTrue(new LayoutDefinition('acme-gallery', 'Gallery')->isPageLayout());
    }

    public function testRefusesAnInvalidId(): void
    {
        $this->expectExceptionObject(new InvalidValueException(
            'Layout id is not valid ("Side_bar"): use lower-case words joined by "-", starting with a letter.',
        ));
        new LayoutDefinition('Side_bar', 'Sidebar');
    }

    public function testRequiresALabel(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Label of layout "sidebar" must not be empty.'));
        new LayoutDefinition('sidebar', '');
    }
}
