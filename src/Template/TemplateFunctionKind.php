<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

/**
 * Whether a {@see TemplateFunction} is called as a Twig function (`asset('css/site.css')`) or a filter
 * (`published_at|user_date`).
 */
enum TemplateFunctionKind: string
{
    case Function = 'function';
    case Filter = 'filter';
}
