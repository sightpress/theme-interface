<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

use SightPress\Theme\Internal\Guard;

/**
 * A Twig function or filter core provides to theme templates. Templates must only use those listed in
 * {@see TemplateApi} and Twig's own built-ins.
 */
final readonly class TemplateFunction
{
    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $signature;

    /** @var non-empty-string */
    public string $description;

    /**
     * @param string $name        name in templates, for example `asset`
     * @param string $signature   parameters and return type, for example `asset(string path): string`
     * @param string $description what it does, in one or two sentences
     */
    public function __construct(
        string $name,
        public TemplateFunctionKind $kind,
        string $signature,
        string $description,
    ) {
        $this->name = Guard::matches($name, TemplateVariable::NAME_PATTERN, 'Template function name', 'use snake_case, starting with a letter');
        $this->signature = Guard::notBlank(trim($signature), sprintf('Signature of "%s"', $name));
        $this->description = Guard::notBlank(trim($description), sprintf('Description of "%s"', $name));
    }
}
