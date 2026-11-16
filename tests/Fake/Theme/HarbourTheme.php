<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Fake\Theme;

use SightPress\Theme\LayoutDefinition;
use SightPress\Theme\Screenshot;
use SightPress\Theme\ThemeInterface;
use SightPress\Theme\ThemeManifest;
use SightPress\Theme\ThemePaths;
use SightPress\Theme\ThemeRequirement;

/**
 * A third-party theme written only against this package, as a theme author would write it.
 */
final class HarbourTheme implements ThemeInterface
{
    public function manifest(): ThemeManifest
    {
        return new ThemeManifest(
            id: 'acme-harbour',
            name: 'Harbour',
            description: 'Calm blues with large type, for organisations that publish a lot of reading.',
            version: '1.2.0',
            requires: new ThemeRequirement('^0.1'),
            layouts: [...LayoutDefinition::required(), new LayoutDefinition('sidebar', 'Sidebar', 'Content with a side column.')],
            locales: ['en-GB', 'pt'],
            screenshot: new Screenshot('assets/screenshot.png', 'Home page with a dark blue header and three cards.'),
            suggestedModules: ['acme.events'],
            author: 'Acme Ltd',
            licence: 'GPL-3.0-or-later',
        );
    }

    public function paths(): ThemePaths
    {
        return new ThemePaths(__DIR__ . '/files');
    }
}
