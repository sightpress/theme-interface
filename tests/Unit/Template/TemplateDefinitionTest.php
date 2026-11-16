<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Template\TemplateDefinition;
use SightPress\Theme\Template\TemplateKind;
use SightPress\Theme\Template\TemplateVariable;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(TemplateDefinition::class)]
#[CoversClass(TemplateVariable::class)]
#[UsesClass(Guard::class)]
final class TemplateDefinitionTest extends TestCase
{
    public function testADefinition(): void
    {
        $title = new TemplateVariable('title', ' string ', true, ' The page title. ');
        $lead = new TemplateVariable('lead', 'string', false, 'A summary.');
        $template = new TemplateDefinition('layouts/landing.html.twig', TemplateKind::Layout, ' A landing page. ', [$title, $lead], ['content']);

        self::assertSame('layouts/landing.html.twig', $template->file);
        self::assertSame(TemplateKind::Layout, $template->kind);
        self::assertSame('A landing page.', $template->description);
        self::assertSame([$title, $lead], $template->variables);
        self::assertSame(['content'], $template->blocks);
        self::assertSame($lead, $template->variable('lead'));
        self::assertNull($template->variable('query'));
        self::assertSame(['title'], $template->requiredVariables());
        self::assertSame('string', $title->type);
        self::assertSame('The page title.', $title->description);
        self::assertTrue($title->required);
    }

    /**
     * @return iterable<string, array{callable(): mixed, string}>
     */
    public static function invalidDefinitions(): iterable
    {
        $kind = TemplateKind::Partial;

        yield 'file outside templates' => [
            static fn (): TemplateDefinition => new TemplateDefinition('../master.html.twig', $kind, 'x'),
            'Template file is not valid ("../master.html.twig")',
        ];
        yield 'file not Twig HTML' => [
            static fn (): TemplateDefinition => new TemplateDefinition('partials/feed.xml.twig', $kind, 'x'),
            'Template file is not valid ("partials/feed.xml.twig")',
        ];
        yield 'blank description' => [
            static fn (): TemplateDefinition => new TemplateDefinition('a.html.twig', $kind, ' '),
            'Description of template "a.html.twig" must not be empty.',
        ];
        yield 'variables not variables' => [
            /** @phpstan-ignore argument.type */
            static fn (): TemplateDefinition => new TemplateDefinition('a.html.twig', $kind, 'x', ['title']),
            'Variables of "a.html.twig"[0] must be an instance of',
        ];
        yield 'repeated variable' => [
            static fn (): TemplateDefinition => new TemplateDefinition('a.html.twig', $kind, 'x', [
                new TemplateVariable('title', 'string', true, 'x'),
                new TemplateVariable('title', 'string', false, 'y'),
            ]),
            'Variables of "a.html.twig" has duplicates: title.',
        ];
        yield 'invalid block' => [
            static fn (): TemplateDefinition => new TemplateDefinition('a.html.twig', $kind, 'x', [], ['main-content']),
            'Blocks of "a.html.twig"[0] is not valid ("main-content")',
        ];
        yield 'repeated block' => [
            static fn (): TemplateDefinition => new TemplateDefinition('a.html.twig', $kind, 'x', [], ['main', 'main']),
            'Blocks of "a.html.twig" has duplicates: main.',
        ];
        yield 'invalid variable name' => [
            static fn (): TemplateVariable => new TemplateVariable('siteName', 'string', true, 'x'),
            'Template variable name is not valid ("siteName"): use snake_case, starting with a letter.',
        ];
        yield 'blank variable type' => [
            static fn (): TemplateVariable => new TemplateVariable('title', '', true, 'x'),
            'Type of template variable "title" must not be empty.',
        ];
        yield 'blank variable description' => [
            static fn (): TemplateVariable => new TemplateVariable('title', 'string', true, ''),
            'Description of template variable "title" must not be empty.',
        ];
    }

    /**
     * @param callable(): mixed $build
     */
    #[DataProvider('invalidDefinitions')]
    public function testRefusesInvalidValues(callable $build, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $build();
    }
}
