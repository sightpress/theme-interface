<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * What core and the theme picker need to know about a theme: who it is, which contract versions it works with, the
 * layouts it provides, the languages and writing directions it supports, and the modules it suggests.
 */
final readonly class ThemeManifest
{
    /**
     * Lower-case words joined by `-`, starting with a letter; at most 64 characters. It must equal `name` in
     * `theme.json`. Third-party themes should start with their vendor name, for example `acme-harbour`.
     */
    public const string ID_PATTERN = '/^(?=.{1,64}$)[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/';

    /** @internal Human-readable form of {@see ID_PATTERN}, used in error messages. */
    public const string ID_RULE = 'use 1 to 64 lower-case letters and digits, starting with a letter, in words joined by "-"';

    /**
     * Semantic Versioning 2.0.0 (https://semver.org), without a leading "v".
     */
    public const string VERSION_PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)'
        . '(?:-((?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*))?'
        . '(?:\+([0-9a-zA-Z-]+(?:\.[0-9a-zA-Z-]+)*))?$/';

    /**
     * A BCP 47 language tag such as `en`, `en-GB`, `pt-BR` or `zh-Hant-TW`.
     */
    public const string LOCALE_PATTERN = '/^[a-z]{2,3}(?:-[A-Z][a-z]{3})?(?:-(?:[A-Z]{2}|\d{3}))?$/';

    /**
     * A module id, as `sightpress/module-interface` defines it.
     */
    public const string MODULE_ID_PATTERN = '/^(?=.{1,64}$)[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/';

    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $name;

    /** @var non-empty-string */
    public string $description;

    /** @var non-empty-string */
    public string $version;

    /** @var list<LayoutDefinition> always includes the required layouts */
    public array $layouts;

    /** @var list<non-empty-string> */
    public array $locales;

    /** @var list<non-empty-string> */
    public array $suggestedModules;

    /**
     * What the theme looks like, in words: the alt text of the screenshots core makes from the theme's preview page.
     *
     * @var non-empty-string|null
     */
    public ?string $previewDescription;

    /**
     * @param string                 $id               unique theme id (see {@see ID_PATTERN})
     * @param string                 $name             name shown in the theme picker, for example "Harbour"
     * @param string                 $description      one or two sentences for the theme picker
     * @param string                 $version          the theme's own version, SemVer 2.0.0
     * @param ThemeRequirement       $requires         versions of this contract the theme works with
     * @param list<LayoutDefinition> $layouts          layouts the theme provides; must include the
     *                                                 {@see LayoutDefinition::REQUIRED} ones
     * @param list<string>           $locales          BCP 47 tags of the languages the theme's own strings are
     *                                                 translated to; empty means every language core supports
     * @param bool                   $rightToLeft      whether the theme works in right-to-left scripts such as
     *                                                 Arabic and Hebrew (mirrored layout, logical CSS properties)
     * @param Screenshot|null        $screenshot       deprecated since 0.2 and removed in 0.3: core ignores the
     *                                                 path and warns, because a theme package ships no screenshot;
     *                                                 core makes them from the preview page (use
     *                                                 $previewDescription for the alt text)
     * @param list<string>           $suggestedModules ids of modules that go well with the theme; never required
     * @param string|null            $author           person or organisation, for example "Acme Ltd"
     * @param string|null            $licence          SPDX licence id, for example `GPL-3.0-or-later`
     * @param string|null            $previewDescription what the top of the theme's preview page looks like, for
     *                                                 people who can't see its screenshot, for example "Top bar in
     *                                                 dark blue, a large white heading on a teal band and a yellow
     *                                                 Download button"; describe the look, not the file. Optional in
     *                                                 0.2, required from 0.3
     */
    public function __construct(
        string $id,
        string $name,
        string $description,
        string $version,
        public ThemeRequirement $requires,
        array $layouts = [],
        array $locales = [],
        public bool $rightToLeft = true,
        /** @deprecated since 0.2, removed in 0.3: themes ship no screenshot; see $previewDescription */
        public ?Screenshot $screenshot = null,
        array $suggestedModules = [],
        public ?string $author = null,
        public ?string $licence = null,
        ?string $previewDescription = null,
    ) {
        $this->id = Guard::matches($id, self::ID_PATTERN, 'Theme id', self::ID_RULE);
        $this->name = Guard::notBlank(trim($name), sprintf('Name of theme "%s"', $id));
        $this->description = Guard::notBlank(trim($description), sprintf('Description of theme "%s"', $id));
        $this->version = Guard::matches(
            $version,
            self::VERSION_PATTERN,
            sprintf('Version of theme "%s"', $id),
            'use Semantic Versioning, for example 1.4.0',
        );

        $layouts = $layouts === []
            ? LayoutDefinition::required()
            : Guard::listOf($layouts, LayoutDefinition::class, sprintf('Layouts of theme "%s"', $id));
        $layoutIds = array_map(static fn (LayoutDefinition $layout): string => $layout->id, $layouts);
        Guard::unique($layoutIds, sprintf('Layouts of theme "%s"', $id));
        $missing = array_diff(LayoutDefinition::REQUIRED, $layoutIds);
        if ($missing !== []) {
            throw new InvalidValueException(sprintf(
                'Theme "%s" must provide the layouts "%s".',
                $id,
                implode('", "', $missing),
            ));
        }
        $this->layouts = $layouts;

        $this->locales = Guard::listMatching(
            $locales,
            self::LOCALE_PATTERN,
            sprintf('Locales of theme "%s"', $id),
            'use a BCP 47 language tag such as en-GB',
        );
        Guard::unique($this->locales, sprintf('Locales of theme "%s"', $id));

        $this->suggestedModules = Guard::listMatching(
            $suggestedModules,
            self::MODULE_ID_PATTERN,
            sprintf('Suggested modules of theme "%s"', $id),
            'use a module id such as acme.events',
        );
        Guard::unique($this->suggestedModules, sprintf('Suggested modules of theme "%s"', $id));

        foreach (['author' => $author, 'licence' => $licence] as $field => $value) {
            if ($value !== null) {
                Guard::notBlank($value, sprintf('The %s of theme "%s" (use null for none)', $field, $id));
            }
        }
        $this->previewDescription = $previewDescription === null
            ? null
            : Guard::notBlank(trim($previewDescription), sprintf('The preview description of theme "%s" (use null for none)', $id));
    }

    /**
     * The layout with this id, or null when the theme doesn't provide it.
     */
    public function layout(string $id): ?LayoutDefinition
    {
        foreach ($this->layouts as $layout) {
            if ($layout->id === $id) {
                return $layout;
            }
        }

        return null;
    }

    /**
     * Whether the theme provides the layout.
     */
    public function provides(string $id): bool
    {
        return $this->layout($id) !== null;
    }

    /**
     * The layouts editors can pick for a page, in the order the theme lists them.
     *
     * @return non-empty-list<LayoutDefinition>
     */
    public function pageLayouts(): array
    {
        $layouts = array_values(array_filter($this->layouts, static fn (LayoutDefinition $layout): bool => $layout->isPageLayout()));

        return $layouts === [] ? [LayoutDefinition::required()[0]] : $layouts;
    }

    /**
     * The layout core shows a page with: the page's own layout when the theme provides it, otherwise
     * {@see LayoutDefinition::FALLBACK}.
     */
    public function resolveLayout(string $id): LayoutDefinition
    {
        return $this->layout($id) ?? $this->layout(LayoutDefinition::FALLBACK) ?? LayoutDefinition::required()[0];
    }

    /**
     * Whether the theme's own strings are translated to this language (any language when {@see $locales} is empty).
     * Matches the language when the theme lists only the language: `en` covers `en-GB`.
     */
    public function supportsLocale(string $locale): bool
    {
        if ($this->locales === []) {
            return true;
        }

        $language = strtolower(explode('-', $locale)[0]);
        return array_any($this->locales, fn ($supported) => strcasecmp($supported, $locale) === 0 || $supported === $language);
    }
}
