<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Internal\Guard;

/**
 * A picture of the theme for the theme picker, with the text alternative people using a screen reader get instead.
 */
final readonly class Screenshot
{
    /**
     * A relative path inside the theme folder, without `..` parts, to a PNG, JPEG, WebP or AVIF file.
     */
    public const string PATH_PATTERN = '/^(?!.*(?:^|\/)\.\.?(?:\/|$))[A-Za-z0-9._-]+(?:\/[A-Za-z0-9._-]+)*\.(?:png|jpe?g|webp|avif)$/';

    /** @var non-empty-string */
    public string $path;

    /** @var non-empty-string */
    public string $alt;

    /**
     * @param string $path relative to the theme folder, for example `assets/screenshot.png`
     * @param string $alt  what the screenshot shows, for example "Home page with a dark blue header, a large
     *                     heading and three cards"; describe the look, not the file
     */
    public function __construct(string $path, string $alt)
    {
        $this->path = Guard::matches(
            $path,
            self::PATH_PATTERN,
            'Screenshot path',
            'use a path inside the theme folder, without "..", to a .png, .jpg, .jpeg, .webp or .avif file',
        );
        $this->alt = Guard::notBlank(trim($alt), 'Screenshot alt text');
    }
}
