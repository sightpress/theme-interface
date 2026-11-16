<?php

declare(strict_types=1);

namespace SightPress\Theme\Template;

/**
 * The Twig functions and filters core provides to every theme template, on top of Twig's built-ins. Core may add
 * functions in a minor release; it removes or changes one only in a major release.
 */
final class TemplateApi
{
    /**
     * @return non-empty-list<TemplateFunction>
     */
    public static function functions(): array
    {
        $function = TemplateFunctionKind::Function;
        $filter = TemplateFunctionKind::Filter;

        return [
            new TemplateFunction(
                'asset',
                $function,
                'asset(string path): string',
                'URL of a file in the theme\'s assets/ folder, with its version in the name so it can be cached. '
                . 'The generated design tokens are at asset(\'tokens.css\').',
            ),
            new TemplateFunction(
                't',
                $function,
                't(string message, array parameters = {}, string domain = \'messages\'): string',
                'The message translated to the page\'s language; write messages in British English.',
            ),
            new TemplateFunction(
                'can',
                $function,
                'can(string permission): bool',
                'Whether the person viewing the page has the permission on this site.',
            ),
            new TemplateFunction(
                'csp_nonce',
                $function,
                'csp_nonce(): string',
                'The Content Security Policy nonce for this response, for the rare inline <script> or <style>.',
            ),
            new TemplateFunction(
                'csrf_fields',
                $function,
                'csrf_fields(): string',
                'The hidden inputs that protect a POST form against cross-site request forgery (already escaped).',
            ),
            new TemplateFunction(
                'current_locale',
                $function,
                'current_locale(): string',
                'The BCP 47 tag of the language the page is shown in, for example en-GB.',
            ),
            new TemplateFunction(
                'text_direction',
                $function,
                'text_direction(string locale): string',
                'The writing direction of a language, "ltr" or "rtl", for the dir attribute.',
            ),
            new TemplateFunction(
                'user_datetime',
                $filter,
                'user_datetime(?DateTimeInterface value, string style = \'medium\'): string',
                'A date and time in the viewer\'s language and time zone; an empty string for null.',
            ),
            new TemplateFunction(
                'user_date',
                $filter,
                'user_date(?DateTimeInterface value, string style = \'long\'): string',
                'A date in the viewer\'s language and time zone; an empty string for null.',
            ),
        ];
    }

    public static function function(string $name): ?TemplateFunction
    {
        foreach (self::functions() as $function) {
            if ($function->name === $name) {
                return $function;
            }
        }

        return null;
    }
}
