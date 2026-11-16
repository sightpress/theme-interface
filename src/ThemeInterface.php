<?php

declare(strict_types=1);

namespace SightPress\Theme;

/**
 * A SightPress theme: the default one and third-party ones alike.
 *
 * A theme is presentation only. It supplies design tokens (`theme.json`), Twig templates, static assets and
 * translations for its own strings, all inside the folder {@see paths()} points to. It can't register services,
 * routes, permissions, migrations, event subscribers or commands; anything with behaviour belongs in a module.
 *
 * Core finds a theme through `extra.sightpress.theme` in the package's `composer.json` (the class name of the
 * implementation), checks {@see ThemeManifest::$requires} against the installed version of this package, then
 * validates the folder: the `theme.json` schema, contrast, and the {@see Template\RequiredTemplates}.
 *
 * Implementations must be cheap to build (no I/O in the constructor), have a constructor without required
 * parameters, and return the same values every time.
 */
interface ThemeInterface
{
    /**
     * Who the theme is, what it provides and which versions of this contract it works with.
     */
    public function manifest(): ThemeManifest;

    /**
     * Where the theme's files are.
     */
    public function paths(): ThemePaths;
}
