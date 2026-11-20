<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Internal\Guard;

/**
 * A page layout a theme provides, such as `default` or `home`, in `templates/layouts/<id>.html.twig`.
 *
 * The theme's manifest lists every layout it provides ({@see ThemeManifest::$layouts}). Every theme provides the
 * {@see REQUIRED} layouts; {@see STANDARD} names the optional ones core knows by name, so pages keep their layout
 * when the site switches between themes that both provide it. A theme may add layouts of its own. A page that uses a
 * layout the site's theme doesn't provide is shown with {@see FALLBACK}, and core lists those pages before a switch.
 *
 * Editors pick a page's layout from the theme's {@see isPageLayout() page layouts}; `search` is only for site search.
 */
final readonly class LayoutDefinition
{
    public const string DEFAULT = 'default';
    public const string HOME = 'home';
    public const string SEARCH = 'search';

    public const string LANDING = 'landing';
    public const string SIDEBAR = 'sidebar';
    public const string FULL_WIDTH = 'full-width';

    /** Layouts every theme must provide. */
    public const array REQUIRED = [self::DEFAULT, self::HOME, self::SEARCH];

    /** Optional layouts core knows by name; a theme that provides one uses this id for it. */
    public const array STANDARD = [self::LANDING, self::SIDEBAR, self::FULL_WIDTH];

    /** Layouts core uses for its own pages, which editors can't pick for a page. */
    public const array SYSTEM = [self::SEARCH];

    /** The layout used when a page's layout is not provided by the theme. */
    public const string FALLBACK = self::DEFAULT;

    /** @var non-empty-string */
    public string $id;

    /** @var non-empty-string */
    public string $label;

    /**
     * @param string $id          layout id (lower-case words joined by `-`); also the template file name
     * @param string $label       name shown to editors when they pick a layout, for example "Sidebar"
     * @param string $description one sentence for editors about when to use it
     */
    public function __construct(string $id, string $label, public string $description = '')
    {
        $this->id = Guard::matches($id, Guard::NAME, 'Layout id', Guard::NAME_RULE);
        $this->label = Guard::notBlank(trim($label), sprintf('Label of layout "%s"', $id));
    }

    /**
     * The layout's template, relative to the theme's `templates/` folder.
     *
     * @return non-empty-string
     */
    public function template(): string
    {
        return sprintf('layouts/%s.html.twig', $this->id);
    }

    public function isRequired(): bool
    {
        return in_array($this->id, self::REQUIRED, true);
    }

    /**
     * Whether editors can pick the layout for a page (every layout but the {@see SYSTEM} ones).
     */
    public function isPageLayout(): bool
    {
        return !in_array($this->id, self::SYSTEM, true);
    }

    /**
     * Whether the layout is one of the {@see STANDARD} optional layouts.
     */
    public function isStandard(): bool
    {
        return in_array($this->id, self::STANDARD, true);
    }

    /**
     * The {@see STANDARD} optional layouts with their standard labels, for themes that provide them.
     *
     * @return non-empty-list<self>
     */
    public static function standard(): array
    {
        return [
            new self(self::LANDING, 'Landing', 'A wide introduction band, then content in full-width sections.'),
            new self(self::SIDEBAR, 'Sidebar', 'Content with a side column for related links and notes.'),
            new self(self::FULL_WIDTH, 'Full width', 'Content across the whole page width, without a reading column.'),
        ];
    }

    /**
     * The required layouts with their standard labels, for themes that provide nothing more.
     *
     * @return non-empty-list<self>
     */
    public static function required(): array
    {
        return [
            new self(self::DEFAULT, 'Default', 'One column at a comfortable reading width.'),
            new self(self::HOME, 'Home', 'An introduction band, then content at full page width.'),
            new self(self::SEARCH, 'Search results', 'The search form and the list of results.'),
        ];
    }
}
