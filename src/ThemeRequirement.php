<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Internal\Guard;

/**
 * The versions of this package (`sightpress/theme-interface`) a theme works with. Core lists a theme whose
 * constraint the installed version doesn't satisfy as unavailable, with the reason, and never applies it.
 */
final readonly class ThemeRequirement
{
    /** The Composer package the constraint is about. */
    public const string PACKAGE = 'sightpress/theme-interface';

    /** @var non-empty-string */
    public string $constraint;

    /**
     * @param string $constraint Composer-style version constraint, for example `^0.1` or `^1.2`; it should match
     *                           the `sightpress/theme-interface` constraint in the theme's `composer.json`
     */
    public function __construct(string $constraint)
    {
        $this->constraint = Guard::notBlank(trim($constraint), sprintf('Version constraint on %s', self::PACKAGE));
    }
}
