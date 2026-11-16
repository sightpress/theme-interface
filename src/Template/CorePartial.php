<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

/**
 * Templates core provides that a theme must include, so a theme can't drop what every page needs.
 * A theme's templates may include only their own templates and these partials.
 */
enum CorePartial: string
{
    /**
     * Everything core puts in `<head>`: description, canonical and alternate-language links, Open Graph and
     * Twitter card meta, JSON-LD, icons, the web manifest and `theme-color`. The shell includes it inside `<head>`:
     * `{% include '@seo/head.html.twig' %}`.
     */
    case Head = '@seo/head.html.twig';

    /**
     * Which required template must include the partial.
     */
    public function includedBy(): string
    {
        return match ($this) {
            self::Head => RequiredTemplates::MASTER,
        };
    }
}
