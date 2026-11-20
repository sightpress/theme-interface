<?php

declare(strict_types=1);

namespace SightPress\Theme;

/**
 * The JSON Schema (draft 2020-12) of `theme.json`, shipped with this package. Core validates every theme's
 * `theme.json` against it before building the {@see Token\ThemeTokens}; editors can point `$schema` at {@see ID}.
 */
final class ThemeSchema
{
    /** The schema's `$id`; it changes with every minor release of this package that changes the schema. */
    public const string ID = 'https://sightpress.org/schema/theme-interface/0.2/theme.schema.json';

    /**
     * Absolute path of the schema file.
     *
     * @return non-empty-string
     */
    public static function file(): string
    {
        return dirname(__DIR__) . '/resources/theme.schema.json';
    }
}
