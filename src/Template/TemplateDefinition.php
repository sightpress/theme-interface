<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

use SightPress\Theme\Internal\Guard;

/**
 * A template a theme must provide: its file, its role, the variables core passes to it, and the Twig blocks other
 * templates (core's or the theme's) may fill.
 */
final readonly class TemplateDefinition
{
    /**
     * A path relative to the theme's `templates/` folder, ending in `.html.twig`, without `..` parts.
     */
    public const string FILE_PATTERN = '/^(?!.*(?:^|\/)\.\.?(?:\/|$))[a-z0-9_-]+(?:\/[a-z0-9_-]+)*\.html\.twig$/';

    /** A Twig block name, such as `main` or `content`. */
    public const string BLOCK_PATTERN = '/^[a-z][a-z0-9_]*$/';

    /** @var non-empty-string */
    public string $file;

    /** @var non-empty-string */
    public string $description;

    /** @var list<TemplateVariable> */
    public array $variables;

    /** @var list<non-empty-string> */
    public array $blocks;

    /**
     * @param string                 $file        path relative to `templates/`, for example `layouts/home.html.twig`
     * @param string                 $description what the template is for and what it must contain
     * @param list<TemplateVariable> $variables   variables core passes
     * @param list<string>           $blocks      Twig blocks the template must define, so templates that extend
     *                                            it can fill them
     */
    public function __construct(
        string $file,
        public TemplateKind $kind,
        string $description,
        array $variables = [],
        array $blocks = [],
    ) {
        $this->file = Guard::matches(
            $file,
            self::FILE_PATTERN,
            'Template file',
            'use a lower-case path inside templates/ ending in .html.twig',
        );
        $this->description = Guard::notBlank(trim($description), sprintf('Description of template "%s"', $file));
        $this->variables = Guard::listOf($variables, TemplateVariable::class, sprintf('Variables of "%s"', $file));
        Guard::unique(
            array_map(static fn (TemplateVariable $variable): string => $variable->name, $this->variables),
            sprintf('Variables of "%s"', $file),
        );
        $this->blocks = Guard::listMatching(
            $blocks,
            self::BLOCK_PATTERN,
            sprintf('Blocks of "%s"', $file),
            'use snake_case, starting with a letter',
        );
        Guard::unique($this->blocks, sprintf('Blocks of "%s"', $file));
    }

    public function variable(string $name): ?TemplateVariable
    {
        foreach ($this->variables as $variable) {
            if ($variable->name === $name) {
                return $variable;
            }
        }

        return null;
    }

    /**
     * Names of the variables core always passes.
     *
     * @return list<non-empty-string>
     */
    public function requiredVariables(): array
    {
        return array_values(array_map(
            static fn (TemplateVariable $variable): string => $variable->name,
            array_filter($this->variables, static fn (TemplateVariable $variable): bool => $variable->required),
        ));
    }
}
