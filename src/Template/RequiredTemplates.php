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
        $menu = 'list<array{label: string, url: string, current?: bool}>';
        $pageTitle = new TemplateVariable('title', 'string', true, 'The page title, shown as the only <h1>.');
        $lead = new TemplateVariable('lead', 'string', false, 'A short summary shown under the title.');

        return [
            new TemplateDefinition(
                self::MASTER,
                TemplateKind::Shell,
                'Page shell every page extends. Sets lang and dir on <html> from the page\'s language, includes '
                . 'the core <head> partial, makes "Skip to main content" the first thing to tab to, and has the '
                . 'landmarks <header>, <nav aria-label> for each menu, <main id="content"> and <footer>. Shows '
                . 'messages at the top of <main> through partials/messages.html.twig, and puts "Error: " in front '
                . 'of the <title> when the form has errors.',
                [
                    new TemplateVariable('site_name', 'string', false, 'The site\'s name.'),
                    new TemplateVariable('locale', 'string', false, 'BCP 47 tag of the page\'s language; else current_locale().'),
                    new TemplateVariable('main_menu', $menu, false, 'Items of the main menu; current marks the page shown.'),
                    new TemplateVariable('footer_menu', $menu, false, 'Items of the footer menu.'),
                    new TemplateVariable('messages', 'list<string>', false, 'Messages to show once, such as "Page saved".'),
                    new TemplateVariable('form', 'array{errors: array<string, list<string>>}', false, 'The form on the page, if any.'),
                ],
                ['title', 'head', 'brand', 'breadcrumbs', 'main', 'footer', 'scripts'],
            ),
            new TemplateDefinition(
                new LayoutDefinition(LayoutDefinition::DEFAULT, 'Default')->template(),
                TemplateKind::Layout,
                'One column at a comfortable reading width: the title, the lead, then the content.',
                [$pageTitle, $lead],
                ['content'],
            ),
            new TemplateDefinition(
                new LayoutDefinition(LayoutDefinition::HOME, 'Home')->template(),
                TemplateKind::Layout,
                'An introduction band with the title and lead, then the content at full page width.',
                [$pageTitle, $lead],
                ['hero', 'content'],
            ),
            new TemplateDefinition(
                new LayoutDefinition(LayoutDefinition::SEARCH, 'Search results')->template(),
                TemplateKind::Layout,
                'The search form, the number of results in a polite live region, then the results as a list of '
                . 'links with their excerpts. Says plainly when nothing was found.',
                [
                    $pageTitle,
                    new TemplateVariable('query', 'string', true, 'What was searched for; empty before a search.'),
                    new TemplateVariable('total', 'int', true, 'How many results there are in all.'),
                    new TemplateVariable(
                        'results',
                        'list<array{title: string, url: string, excerpt: string, locale: string}>',
                        true,
                        'The results on this page, best first; excerpt is plain text.',
                    ),
                    new TemplateVariable(
                        'pagination',
                        'array{current: int, pages: int, previous: ?string, next: ?string}',
                        false,
                        'Links to the other pages of results, when there is more than one.',
                    ),
                ],
                ['content'],
            ),
            new TemplateDefinition(
                self::MESSAGES,
                TemplateKind::Partial,
                'Messages in a polite live region (role="status"). Core\'s own views include it too.',
                [new TemplateVariable('messages', 'list<string>', true, 'The messages; may be empty.')],
            ),
            new TemplateDefinition(
                self::SEARCH_FORM,
                TemplateKind::Partial,
                'A search form with role="search", a visible label, a search input named "q" and a submit button.',
                [
                    new TemplateVariable('action', 'string', true, 'URL the form is sent to with GET.'),
                    new TemplateVariable('query', 'string', false, 'What was searched for, to fill the input.'),
                ],
            ),
        ];
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
