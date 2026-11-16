<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

use SightPress\Theme\Internal\Guard;

/**
 * A variable core passes to a template. Templates must only rely on the variables documented here: core may add
 * variables in a minor release, but removes or changes one only in a major release.
 */
final readonly class TemplateVariable
{
    /** A Twig variable name in snake case, such as `site_name`. */
    public const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $type;

    /** @var non-empty-string */
    public string $description;

    /**
     * @param string $name        variable name, for example `main_menu`
     * @param string $type        its shape, in PHPStan notation, for example
     *                            `list<array{label: string, url: string, current: bool}>`
     * @param bool   $required    true when core always passes it; templates must handle optional ones being absent
     * @param string $description what it holds, in one sentence
     */
    public function __construct(string $name, string $type, public bool $required, string $description)
    {
        $this->name = Guard::matches($name, self::NAME_PATTERN, 'Template variable name', 'use snake_case, starting with a letter');
        $this->type = Guard::notBlank(trim($type), sprintf('Type of template variable "%s"', $name));
        $this->description = Guard::notBlank(trim($description), sprintf('Description of template variable "%s"', $name));
    }
}
