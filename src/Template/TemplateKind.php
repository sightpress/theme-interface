<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

/**
 * The role a template plays in a theme.
 */
enum TemplateKind: string
{
    /** The page shell every page extends (`master.html.twig`). */
    case Shell = 'shell';

    /** A page layout in `layouts/`, which extends the shell. */
    case Layout = 'layout';

    /** A piece included by other templates, in `partials/`. Core's own views include some of them too. */
    case Partial = 'partial';
}
