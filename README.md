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
  "require": { "sightpress/theme-interface": "^0.2" },
  "extra": { "sightpress": { "theme": "Acme\\Harbour\\HarbourTheme" } }
}
```

## What's in it

- `ThemeInterface`: what every theme implements: `manifest()` and `paths()`.
- `ThemeManifest`: the theme's id, name, description and version, the `ThemeRequirement` (the versions of this
  package it works with), the layouts it provides (`LayoutDefinition`; `pageLayouts()` are the ones editors can
  pick), the languages its own strings are
  translated to, whether it supports right-to-left scripts, suggested modules, author, licence and
  `previewDescription`: the alt text of the theme's screenshots, which describes the look in words. (`Screenshot`
  is deprecated since 0.2 and removed in 0.3: core ignores its path and warns.)
- `ThemePaths`: the theme folder and the standard places in it: `theme.json`, `templates/`, `assets/` and
  `translations/`.
- `ThemeSchema` and `resources/theme.schema.json`: the JSON Schema (draft 2020-12) of `theme.json`.
- `Token\ThemeTokens`: typed design tokens: `ColorTokens` for light and dark mode, each with "more contrast"
  overrides (`ColorMode`); the `TokenGroup`s typography, spacing, radius and focus; the `ColorPair`s whose
  contrast core checks (`ColorUse`: 4.5:1 for text, 3:1 for interface colours, WCAG 2.2 AA); and `ChartTokens`:
  every theme defines `chart-1` to `chart-4` (lines and bars), `chart-grid` and `chart-text`, and core always
  checks the series colours (3:1) and the chart text (4.5:1) on `background` and `surface` (`checkedPairs()`).
- `LayoutDefinition`: the required layouts `default`, `home` and `search`, and the standard optional ones
  `landing`, `sidebar` and `full-width` (fixed ids, so a page keeps its layout when the site changes theme). A page
  whose layout the theme doesn't provide is shown with `default`.
- `Template\RequiredTemplates`: the templates every theme must provide (`master.html.twig`, the layouts
  `default`, `home` and `search`, and the partials `messages` and `search-form`), each a `TemplateDefinition`
  with the variables core passes (`TemplateVariable`) and the blocks it must define. `forLayout()` gives the
  definition for any layout, including the standard and the theme's own ones. The search form is a `<search>`
  landmark; the search page says how many results there are in its `<title>` and `<h1>`, not in a live region.
- `Preview\PreviewPage` and `resources/preview/page.json`: the sample page every theme is previewed with, and its
  schema (`page.schema.json`). Core's `theme:preview` renders it with the theme applied to a static folder that
  opens offline: the top navbar, a hero with the theme's name, description and a Download button, one block of each
  core block type from the most essential to the most complex (`BLOCK_ORDER`), and the footer. Media are named by
  role (`@picture`, `@video`, `@captions`, `@audio`, `@document`); core supplies the files.
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

### No screenshot in a theme package

A theme package ships no screenshot or other preview output. Every picture of a theme, in the theme picker, the
documentation, a README or a release, and on every pull request, is made from its preview page, so it always shows
the theme as it is. Describe what the top of that page looks like in `previewDescription`, for example "A dark
blue top bar, then a large white heading on a pale blue band and a yellow Download button".

### A preview on every pull request

Every theme repo can show reviewers what a change looks like without installing anything. Add two small workflows
that call the reusable ones in this repo, pinned to a tag:

```yaml
# .github/workflows/theme-preview.yml
name: Theme preview
on: pull_request
permissions:
  contents: read
jobs:
  preview:
    uses: sightpress/theme-interface/.github/workflows/theme-preview.yml@0.2.1
```

```yaml
# .github/workflows/theme-preview-comment.yml
name: Theme preview comment
on:
  workflow_run:
    workflows: [Theme preview]
    types: [completed]
permissions:
  actions: read
  pull-requests: write
jobs:
  comment:
    uses: sightpress/theme-interface/.github/workflows/theme-preview-comment.yml@0.2.1
```

The first workflow installs the theme with core as a dev dependency (`sightpress/core` in `require-dev`), runs
`theme:validate` and `theme:preview --screenshot`, scans the preview page with axe in light, dark and forced-colors
mode, and uploads one artifact: the static preview (open `index.html`; it works offline), `screenshot.png`,
`screenshot-dark.png`, `page.png`, `axe.txt` and a `README.txt`. It has a read-only token and no secrets, so pull
requests from forks get it too. The second workflow never runs the pull request's code: it reads only a small
`preview.json` and keeps one comment on the pull request up to date, with the download link, the theme's
`previewDescription` and the axe result for each mode. Nothing is committed: no screenshot ever goes into the repo.

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

## Contributing

Everyone can help, not only developers. See [CONTRIBUTING.md](CONTRIBUTING.md) for the ways to take part, and
the [Code of Conduct](CODE_OF_CONDUCT.md) that everyone who takes part agrees to follow.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
