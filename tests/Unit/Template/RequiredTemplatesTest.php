<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\LayoutDefinition;
use SightPress\Theme\Template\CorePartial;
use SightPress\Theme\Template\RequiredTemplates;
use SightPress\Theme\Template\TemplateKind;
use SightPress\Theme\Internal\Guard;
use SightPress\Theme\Template\TemplateDefinition;
use SightPress\Theme\Template\TemplateVariable;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(RequiredTemplates::class)]
#[CoversClass(CorePartial::class)]
#[UsesClass(Guard::class)]
#[UsesClass(LayoutDefinition::class)]
#[UsesClass(TemplateDefinition::class)]
#[UsesClass(TemplateVariable::class)]
final class RequiredTemplatesTest extends TestCase
{
    public function testTheShellEveryRequiredLayoutAndThePartials(): void
    {
        $expected = [
            RequiredTemplates::MASTER,
            ...array_map(static fn (LayoutDefinition $layout): string => $layout->template(), LayoutDefinition::required()),
            RequiredTemplates::MESSAGES,
            RequiredTemplates::SEARCH_FORM,
        ];

        self::assertSame($expected, RequiredTemplates::files());
    }

    public function testFindsADefinition(): void
    {
        $search = RequiredTemplates::find('layouts/search.html.twig');

        self::assertNotNull($search);
        self::assertSame(TemplateKind::Layout, $search->kind);
        self::assertSame(
            ['title', 'query', 'action', 'state', 'total', 'first', 'matched_all', 'results', 'pagination', 'language_name', 'retry_minutes'],
            $search->requiredVariables(),
        );
        self::assertSame(TemplateKind::Shell, RequiredTemplates::find(RequiredTemplates::MASTER)?->kind);
        self::assertSame(['action'], RequiredTemplates::find(RequiredTemplates::SEARCH_FORM)?->requiredVariables());
        self::assertNull(RequiredTemplates::find('layouts/landing.html.twig'));
    }

    public function testEveryLayoutHasADefinition(): void
    {
        foreach (LayoutDefinition::required() as $layout) {
            self::assertEquals(RequiredTemplates::find($layout->template()), RequiredTemplates::forLayout($layout));
        }

        [$landing, $sidebar, $fullWidth] = LayoutDefinition::standard();
        self::assertSame(['content', 'aside'], RequiredTemplates::forLayout($sidebar)->blocks);
        self::assertSame('layouts/landing.html.twig', RequiredTemplates::forLayout($landing)->file);
        self::assertStringContainsString('"Full width" layout', RequiredTemplates::forLayout($fullWidth)->description);

        $own = RequiredTemplates::forLayout(new LayoutDefinition('acme-gallery', 'Gallery'));
        self::assertSame(TemplateKind::Layout, $own->kind);
        self::assertSame(['content'], $own->blocks);
        self::assertSame(['title'], $own->requiredVariables());
    }

    public function testTheSearchPartialIsASearchLandmark(): void
    {
        $form = RequiredTemplates::find(RequiredTemplates::SEARCH_FORM);

        self::assertNotNull($form);
        self::assertStringContainsString('<search> landmark', $form->description);
        self::assertStringNotContainsString('role="search"', $form->description);
        self::assertNotNull($form->variable('id'));
        self::assertNotNull($form->variable('label'));
    }

    public function testSearchResultsAreAnnouncedThroughTheTitleNotALiveRegion(): void
    {
        $search = RequiredTemplates::find('layouts/search.html.twig');

        self::assertNotNull($search);
        self::assertStringContainsString('without a live region', $search->description);
        self::assertStringNotContainsString('polite live region', $search->description);
    }

    public function testTheShellDefinesTheBlocksLayoutsFill(): void
    {
        self::assertContains('main', RequiredTemplates::find(RequiredTemplates::MASTER)->blocks ?? []);
        self::assertContains('title', RequiredTemplates::find(RequiredTemplates::MASTER)->blocks ?? []);
    }

    public function testTheShellIncludesTheCoreHead(): void
    {
        self::assertSame('@seo/head.html.twig', CorePartial::Head->value);
        self::assertSame(RequiredTemplates::MASTER, CorePartial::Head->includedBy());
    }
}
