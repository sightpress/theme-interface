<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

use SightPress\Theme\LayoutDefinition;

/**
 * The templates every theme must provide, with the variables core passes to each and the blocks each must define.
 *
 * Core refuses a theme that misses one of them, or whose shell doesn't include the {@see CorePartial}s. A theme may
 * add more layouts (declared in its manifest) and more partials. Core may add optional variables in a minor release.
 */
final class RequiredTemplates
{
    public const string MASTER = 'master.html.twig';
    public const string MESSAGES = 'partials/messages.html.twig';
    public const string SEARCH_FORM = 'partials/search-form.html.twig';

    /**
     * @return non-empty-list<TemplateDefinition>
     */
    public static function all(): array
    {
        $menu = 'list<array{label: string, url: string, current?: bool, children?: list<array<string, mixed>>}>';

        return [
            new TemplateDefinition(
                self::MASTER,
                TemplateKind::Shell,
                'Page shell every page extends. Sets lang and dir on <html> from the page\'s language, includes '
                . 'the core <head> partial, makes "Skip to main content" the first thing to tab to, and has the '
                . 'landmarks <header>, <nav aria-label> for each menu and the language switcher, the site\'s '
                . '<search> form (partials/search-form.html.twig, when search_action is set), <main id="content"> '
                . 'and <footer>. Shows messages at the top of <main> through partials/messages.html.twig. The '
                . '<title> is document_title when set, else the title block and the site name, with "Error: " in '
                . 'front when the form has errors. Main menu items may have children, up to three levels, shown as '
                . 'nested lists that work without JavaScript.',
                [
                    new TemplateVariable('site_name', 'string', false, 'The site\'s name.'),
                    new TemplateVariable('locale', 'string', false, 'BCP 47 tag of the page\'s language; else current_locale().'),
                    new TemplateVariable('document_title', 'string', false, 'The whole <title>, when core sets it (SEO title, search results).'),
                    new TemplateVariable('home_url', 'string', false, 'The home page in the page\'s language; else "/".'),
                    new TemplateVariable(
                        'branding_logo',
                        'array{url: string, alt: string, width: int, height: int}',
                        false,
                        'The uploaded logo, shown instead of the site\'s name in the header link.',
                    ),
                    new TemplateVariable('main_menu', $menu, false, 'Items of the main menu; current marks the page shown.'),
                    new TemplateVariable('footer_menu', $menu, false, 'Items of the footer menu.'),
                    new TemplateVariable(
                        'breadcrumbs',
                        'list<array{label: ?string, url: string}>',
                        false,
                        'Where the page is on the site, the last item the page itself; a null label is the home page.',
                    ),
                    new TemplateVariable(
                        'languages',
                        'list<array{code: string, name: string, url: string, current: bool}>',
                        false,
                        'The page in each published language, each named in its own language, for the language switcher; '
                        . 'empty when there is only one.',
                    ),
                    new TemplateVariable('search_action', 'string', false, 'Where the header\'s search form searches, while site search is on.'),
                    new TemplateVariable('messages', 'list<string>', false, 'Messages to show once, such as "Page saved".'),
                    new TemplateVariable('form', 'array{errors: array<string, list<string>>}', false, 'The form on the page, if any.'),
                ],
                ['title', 'head', 'brand', 'breadcrumbs', 'main', 'footer', 'scripts'],
            ),
            self::forLayout(new LayoutDefinition(LayoutDefinition::DEFAULT, 'Default')),
            self::forLayout(new LayoutDefinition(LayoutDefinition::HOME, 'Home')),
            self::forLayout(new LayoutDefinition(LayoutDefinition::SEARCH, 'Search results')),
            new TemplateDefinition(
                self::MESSAGES,
                TemplateKind::Partial,
                'Messages in a polite live region (role="status") that is always there, even when empty, so '
                . 'screen readers know it. Core\'s own views include it too.',
                [new TemplateVariable('messages', 'list<string>', true, 'The messages; may be empty.')],
            ),
            new TemplateDefinition(
                self::SEARCH_FORM,
                TemplateKind::Partial,
                'A search form in a <search> landmark named by its visible label (aria-labelledby), with a search '
                . 'input named "q" and a visible "Search" button, sent with GET. Works the same without JavaScript. '
                . 'The shell and the search layout both include it, so its ids start with id.',
                [
                    new TemplateVariable('action', 'string', true, 'URL the form is sent to with GET.'),
                    new TemplateVariable('query', 'string', false, 'What was searched for, to fill the input.'),
                    new TemplateVariable('id', 'string', false, 'Start of the form\'s ids, "search" by default, so a page can have two forms.'),
                    new TemplateVariable('label', 'string', false, 'The visible label, "Search this site" by default.'),
                ],
            ),
        ];
    }

    /**
     * What core passes to a layout and what the layout must contain: the definition of a required layout, or the
     * common definition every other layout (standard or the theme's own) follows. Every layout extends the shell;
     * core fills its content block with the page's blocks and the shell's head block with the page's meta.
     */
    public static function forLayout(LayoutDefinition $layout): TemplateDefinition
    {
        $title = new TemplateVariable('title', 'string', true, 'The page title, shown as the only <h1>.');
        $lead = new TemplateVariable('lead', 'string', false, 'A short summary shown under the title.');
        $updated = new TemplateVariable(
            'updated_at',
            'DateTimeInterface',
            false,
            'When the page last changed (UTC), shown as "Last updated" with user_date in a <time datetime>.',
        );

        return match ($layout->id) {
            LayoutDefinition::DEFAULT => new TemplateDefinition(
                $layout->template(),
                TemplateKind::Layout,
                'One column at a comfortable reading width: the title, the lead, then the content.',
                [$title, $lead, $updated],
                ['content'],
            ),
            LayoutDefinition::HOME => new TemplateDefinition(
                $layout->template(),
                TemplateKind::Layout,
                'An introduction band with the title and lead, then the content at full page width.',
                [$title, $lead, $updated],
                ['hero', 'content'],
            ),
            LayoutDefinition::SEARCH => self::search($layout),
            LayoutDefinition::SIDEBAR => new TemplateDefinition(
                $layout->template(),
                TemplateKind::Layout,
                'The title and lead, then the content with a side column in an <aside> named by its heading. On '
                . 'narrow screens and at 200% zoom the side column follows the content.',
                [$title, $lead, $updated],
                ['content', 'aside'],
            ),
            default => new TemplateDefinition(
                $layout->template(),
                TemplateKind::Layout,
                sprintf('The "%s" layout: the title as the only <h1>, the lead, then the content.', $layout->label),
                [$title, $lead, $updated],
                ['content'],
            ),
        };
    }

    private static function search(LayoutDefinition $layout): TemplateDefinition
    {
        return new TemplateDefinition(
            $layout->template(),
            TemplateKind::Layout,
            'Site search: the title as the only <h1> (what was searched for and how many results there are; core '
            . 'also puts it in <title>, so screen readers announce both when the page loads, without a live region), '
            . 'the search form, then the results as a numbered list (<ol start="first">), each with its title as a '
            . 'link in an <h2>, its trail and its extract with the matched words in <mark>. When nothing was found, '
            . 'nothing was searched for yet, or there were too many searches, the page says so in words, with tips.',
            [
                new TemplateVariable('title', 'string', true, 'The heading, for example "3 results for “river”".'),
                new TemplateVariable('query', 'string', true, 'What was searched for; empty before a search.'),
                new TemplateVariable('action', 'string', true, 'Where the search form searches.'),
                new TemplateVariable(
                    'state',
                    "'results'|'none'|'empty'|'busy'",
                    true,
                    'results; none found; empty (nothing searched for yet); busy (too many searches, see retry_minutes).',
                ),
                new TemplateVariable('total', 'int', true, 'How many results there are in all.'),
                new TemplateVariable('first', 'int', true, 'The number of the first result on this page.'),
                new TemplateVariable('matched_all', 'bool', true, 'False when no page has every word, so results have some of them.'),
                new TemplateVariable(
                    'results',
                    'list<array{title: string, url: string, excerpt: string, locale: string, trail: string, '
                    . 'extract: list<array{text: string, mark: bool}>}>',
                    true,
                    'The results on this page, best first; excerpt is plain text, extract the same text in pieces, '
                    . 'mark true for matched words; trail is where the page is on the site.',
                ),
                new TemplateVariable(
                    'pagination',
                    '?array{current: int, pages: int, previous: ?string, next: ?string, pattern: string}',
                    true,
                    'Links to the other pages of results, or null; pattern is a sprintf() URL with %d for the page.',
                ),
                new TemplateVariable('language_name', 'string', true, 'The name of the language searched, in that language.'),
                new TemplateVariable('retry_minutes', '?int', true, 'Minutes to wait before searching again, when busy.'),
            ],
            ['content'],
        );
    }

    /**
     * File names of the required templates, relative to `templates/`.
     *
     * @return non-empty-list<non-empty-string>
     */
    public static function files(): array
    {
        return array_map(static fn (TemplateDefinition $template): string => $template->file, self::all());
    }

    public static function find(string $file): ?TemplateDefinition
    {
        foreach (self::all() as $template) {
            if ($template->file === $file) {
                return $template;
            }
        }

        return null;
    }
}
