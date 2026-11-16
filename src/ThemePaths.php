<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Internal\Guard;

/**
 * The folder of a theme and the standard places inside it:
 *
 * - `theme.json`: the design tokens ({@see Token\ThemeTokens}, schema in `resources/theme.schema.json`);
 * - `templates/`: Twig templates, at least the {@see Template\RequiredTemplates};
 * - `assets/`: CSS, scripts, fonts, images and an SVG icon sprite, published per theme version;
 * - `translations/`: translations of the theme's own strings, one file per locale.
 *
 * Build it from the package root, for example `new ThemePaths(dirname(__DIR__))` in a class in `src/`.
 */
final readonly class ThemePaths
{
    public const string TOKENS_FILE = 'theme.json';
    public const string TEMPLATES_DIRECTORY = 'templates';
    public const string ASSETS_DIRECTORY = 'assets';
    public const string TRANSLATIONS_DIRECTORY = 'translations';

    /** @var non-empty-string */
    public string $root;

    /**
     * @param string $root absolute path of the theme folder; a trailing `/` is removed
     */
    public function __construct(string $root)
    {
        $trimmed = rtrim($root, '/\\');
        $this->root = Guard::notBlank($trimmed === '' && $root !== '' ? '/' : $trimmed, 'Theme folder');
    }

    /**
     * Absolute path of a file or folder inside the theme.
     *
     * @return non-empty-string
     */
    public function path(string $relative): string
    {
        $relative = ltrim($relative, '/\\');

        return $relative === '' ? $this->root : rtrim($this->root, '/') . '/' . $relative;
    }

    /**
     * @return non-empty-string
     */
    public function tokensFile(): string
    {
        return $this->path(self::TOKENS_FILE);
    }

    /**
     * @return non-empty-string
     */
    public function templates(): string
    {
        return $this->path(self::TEMPLATES_DIRECTORY);
    }

    /**
     * @return non-empty-string
     */
    public function assets(): string
    {
        return $this->path(self::ASSETS_DIRECTORY);
    }

    /**
     * @return non-empty-string
     */
    public function translations(): string
    {
        return $this->path(self::TRANSLATIONS_DIRECTORY);
    }
}
