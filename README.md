# SightPress theme interface

The contracts that every SightPress theme implements. SightPress is a powerful CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This package holds interfaces, small value objects and the `theme.json` schema only. It contains no implementation
code and has no runtime dependencies. The default theme and third-party themes use the same contracts.

It is versioned on its own, with strict semantic versioning: a new major version means a breaking change in a contract.

## What a theme is

A theme is presentation only: design tokens (`theme.json`), Twig templates, static assets and translations of its own
strings. It can't register services, routes, permissions, migrations, event subscribers or commands; anything with
behaviour is a module. A theme may suggest modules in its manifest, but never requires them.

Every theme, the default one included, is its own Composer package (type `sightpress-theme`) that requires this
package and names its theme class in `composer.json`:

```json
{
  "type": "sightpress-theme",
  "require": { "sightpress/theme-interface": "^0.1" },
  "extra": { "sightpress": { "theme": "Acme\\Harbour\\HarbourTheme" } }
}
```

## What's in it

- `ThemeInterface`: what every theme implements: `manifest()` and `paths()`.
- `ThemeManifest`: the theme's id, name, description and version, the `ThemeRequirement` (the versions of this
  package it works with), the layouts it provides (`LayoutDefinition`), the languages its own strings are
  translated to, whether it supports right-to-left scripts, a `Screenshot` with alt text for the theme picker,
  suggested modules, author and licence.
- `ThemePaths`: the theme folder and the standard places in it: `theme.json`, `templates/`, `assets/` and
  `translations/`.
- `ThemeSchema` and `resources/theme.schema.json`: the JSON Schema (draft 2020-12) of `theme.json`.
- `Token\ThemeTokens`: typed design tokens: `ColorTokens` for light and dark mode, each with "more contrast"
  overrides (`ColorMode`); the `TokenGroup`s typography, spacing, radius and focus; the `ColorPair`s whose
  contrast core checks (`ColorUse`: 4.5:1 for text, 3:1 for interface colours, WCAG 2.2 AA); and `ChartTokens`.
- `Template\RequiredTemplates`: the templates every theme must provide (`master.html.twig`, the layouts
  `default`, `home` and `search`, and the partials `messages` and `search-form`), each a `TemplateDefinition`
  with the variables core passes (`TemplateVariable`) and the blocks it must define.
- `Template\CorePartial`: the core partials a theme must include, such as the `<head>` partial with meta,
  canonical links and JSON-LD, so a theme can't drop them.
- `Template\TemplateApi`: the Twig functions and filters core provides to theme templates (`asset`, `t`, `can`,
  `csp_nonce`, `csrf_fields`, `current_locale`, `text_direction`, `user_datetime`, `user_date`). Templates use only
  these and Twig's built-ins, and include only their own templates and the core partials.

The value objects check their values when they are built and throw `Exception\InvalidValueException` with a
message that names the field and the rule.

This package describes; core enforces. Core does the colour maths and contrast checks, discovers and validates
installed themes, compiles the tokens to CSS custom properties and falls back to the default theme when a site's
theme is missing.

`ThemeInterface` changes only in a major release. Core may add optional template variables and template functions
in a minor release; removing or changing one is a major release.

## Requirements

- PHP 8.5 or later

## Development

This package has no dependencies on the other SightPress repos. To work on it:

```sh
composer install
composer check
```

Composer commands:

- `composer check`: everything that must pass before a commit. It validates `composer.json`, checks the code
  style, runs static analysis, runs the unit suite with the coverage gate, then runs the integration suite.
- `composer test`: both test suites. `composer test:unit` and `composer test:integration` run one suite each.
- `composer test:coverage`: the unit suite with coverage. It fails if unit tests cover less than 100% of the lines
  or methods in `src/`, and it lists what is not covered. `@codeCoverageIgnore` is not allowed. The HTML report
  is written to `build/coverage/html/`.
- `composer fix`: applies Rector refactorings, then fixes the code style.

Coverage needs the PCOV extension, or Xdebug in coverage mode. If your PHP has neither, use the
development image from the [core](https://github.com/sightpress/core) repo (`docker/dev-php/`), which has
PHP 8.5, PCOV and Composer. With `core` cloned next to this repo, run this from this folder:

```sh
docker build -t sightpress/dev-php:8.5 ../core/docker/dev-php
docker run --rm --user "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD:/workspace" -w /workspace \
  sightpress/dev-php:8.5 composer check
```

### Continuous integration

Every pull request and every push to `main` runs the `CI` workflow (`composer check`): `composer validate`, code
style, PHPStan, the unit suite with the 100% coverage gate, the integration suite and `composer audit`. The
coverage report is uploaded as an artifact.

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/); release-please uses them to
keep a release pull request and `CHANGELOG.md` up to date.

`main` is protected by the ruleset in `.github/rulesets/main.json`. Changes reach it only through a pull request that
passes the `composer check` check and has one approval. Pull requests are squash-merged. Admins can bypass the ruleset.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
