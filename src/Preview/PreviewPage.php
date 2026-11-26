<?php

declare(strict_types=1);

namespace SightPress\Theme\Preview;

use SightPress\Theme\Internal\Guard;

/**
 * The sample page every theme is previewed with (`resources/preview/page.json`), shipped with this package.
 *
 * Core's `theme:preview` renders it through the real renderer with a theme applied and writes a static folder:
 * the theme's header with the top navbar (skip link, site name, one link per section), the hero (the theme's name,
 * its description and a Download button), the sections with one block of each core block type, from the most
 * essential to the most complex ({@see BLOCK_ORDER}), and the theme's footer. Every screenshot of a theme (the
 * theme picker, the docs gallery, the README, the preview of a pull request) is made from that page, so a theme
 * package ships no screenshot.
 *
 * Media are named by role ({@see MEDIA}) and core supplies the files, so this package ships none. Text may use the
 * {@see VARIABLES}, which are valid only in this file.
 */
final class PreviewPage
{
    /** The schema's `$id`; it changes with every minor release of this package that changes the page. */
    public const string SCHEMA_ID = 'https://sightpress.org/schema/theme-interface/0.3/preview/page.schema.json';

    /**
     * Every block type the page can hold, in the order the page shows them: the essential ones first, the complex
     * ones last. The page holds only the types core has at this version of the contract; a task that adds a block
     * type to core adds its sample to the page in its place here (a minor release).
     */
    public const array BLOCK_ORDER = [
        'hero',
        'heading',
        'rich_text',
        'list',
        'quote',
        'callout',
        'call_to_action',
        'button_group',
        'divider',
        'spacer',
        'image',
        'table',
        'file_download',
        'accordion',
        'faq',
        'columns',
        'gallery',
        'audio',
        'video',
        'code',
        'contact_details',
        'contact_form',
        'social_links',
        'page_list',
        'embed',
        'sign_language_video',
    ];

    /** Block types whose blocks hold other blocks; the blocks inside them don't count as the type's sample. */
    public const array CONTAINERS = ['columns'];

    /**
     * Media roles a block's data may name instead of a media id, as `@<role>`; core puts in the id of its own sample
     * file (a picture, a video with its WebVTT captions, a recording and a PDF).
     */
    public const array MEDIA = ['picture', 'video', 'captions', 'audio', 'document'];

    /** Variables the page's text may use, written `{{ theme.<name> }}`. */
    public const array VARIABLES = ['name', 'description', 'version', 'download_url'];

    /**
     * Absolute path of `page.json`.
     *
     * @return non-empty-string
     */
    public static function file(): string
    {
        return dirname(__DIR__, 2) . '/resources/preview/page.json';
    }

    /**
     * Absolute path of the page's JSON Schema (draft 2020-12).
     *
     * @return non-empty-string
     */
    public static function schemaFile(): string
    {
        return dirname(__DIR__, 2) . '/resources/preview/page.schema.json';
    }

    /**
     * Where the Download button of a theme's preview leads: the `homepage` of the theme's `composer.json` when it
     * has one, otherwise the package's page on Packagist.
     *
     * @param string      $package  the Composer package name, for example `acme/theme-harbour`
     * @param string|null $homepage `homepage` from the theme's `composer.json`
     *
     * @return non-empty-string
     */
    public static function downloadUrl(string $package, ?string $homepage = null): string
    {
        $homepage = trim((string) $homepage);
        if (preg_match('#^https?://\S+$#', $homepage) === 1) {
            return $homepage;
        }

        return 'https://packagist.org/packages/' . Guard::matches(
            $package,
            '#^[a-z0-9](?:[_.-]?[a-z0-9]+)*/[a-z0-9](?:(?:[_.]|-{1,2})?[a-z0-9]+)*$#',
            'Package name',
            'use a Composer package name such as acme/theme-harbour',
        );
    }
}
