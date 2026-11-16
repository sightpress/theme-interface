<?php

declare(strict_types=1);

namespace SightPress\Theme;

use SightPress\Theme\Internal\Guard;

/**
 * A page layout a theme provides, such as `default` or `home`, in `templates/layouts/<id>.html.twig`.
 *
 * Every theme provides the {@see REQUIRED} layouts. A page that uses a layout the site's theme doesn't provide is
 * shown with {@see FALLBACK}.
 */
final readonly class LayoutDefinition
{
    public const string DEFAULT = 'default';
    public const string HOME = 'home';
    public const string SEARCH = 'search';

    /** Layouts every theme must provide. */
    public const array REQUIRED = [self::DEFAULT, self::HOME, self::SEARCH];

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
