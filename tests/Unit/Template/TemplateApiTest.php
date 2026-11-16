<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Template;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Template\TemplateApi;
use SightPress\Theme\Template\TemplateFunction;
use SightPress\Theme\Template\TemplateFunctionKind;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(TemplateApi::class)]
#[CoversClass(TemplateFunction::class)]
#[UsesClass(Guard::class)]
final class TemplateApiTest extends TestCase
{
    public function testTheDocumentedFunctionsAndFilters(): void
    {
        $names = array_map(static fn (TemplateFunction $function): string => $function->name, TemplateApi::functions());

        self::assertSame(
            ['asset', 't', 'can', 'csp_nonce', 'csrf_fields', 'current_locale', 'text_direction', 'user_datetime', 'user_date'],
            $names,
        );
        self::assertSame(TemplateFunctionKind::Filter, TemplateApi::function('user_date')?->kind);
        self::assertSame(TemplateFunctionKind::Function, TemplateApi::function('asset')?->kind);
        self::assertSame('can(string permission): bool', TemplateApi::function('can')?->signature);
        self::assertNull(TemplateApi::function('dump_everything'));
    }

    public function testAFunctionNeedsANameSignatureAndDescription(): void
    {
        $function = new TemplateFunction('nav', TemplateFunctionKind::Function, ' nav(): string ', ' The menu. ');
        self::assertSame('nav(): string', $function->signature);
        self::assertSame('The menu.', $function->description);

        $this->expectException(InvalidValueException::class);
        new TemplateFunction('Nav', TemplateFunctionKind::Function, 'x', 'y');
    }

    public function testRefusesABlankSignature(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Signature of "nav" must not be empty.'));
        new TemplateFunction('nav', TemplateFunctionKind::Function, '', 'y');
    }

    public function testRefusesABlankDescription(): void
    {
        $this->expectExceptionObject(new InvalidValueException('Description of "nav" must not be empty.'));
        new TemplateFunction('nav', TemplateFunctionKind::Function, 'nav(): string', ' ');
    }
}
