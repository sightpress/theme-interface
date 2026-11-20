# 0001 — Theme architecture: presentation-only packages, a contract that describes, core that enforces

- Status: Proposed
- Date: 2026-11-20

## Context

Until now SightPress has had one theme, kept inside `core` (`resources/themes/default/`). The next milestone lets
an operator install more themes, apply a different one to each site, customise its colours, and lets anyone write a
theme from a template repo. A theme can change what every visitor sees, so it must not be able to break
accessibility, SEO or security, and a theme written today must keep working as core grows.

`theme-interface` 0.1 (2026-11-16) holds the first contracts: `ThemeInterface`, `ThemeManifest`, the `theme.json`
schema, typed tokens, the required templates and the Twig functions core provides. Core's ADR-0013 (framework and
library baseline) and the module architecture (core ADR-0009) set the patterns this follows.

## Decision

- **A theme is presentation only:** design tokens (`theme.json`), Twig templates, static assets and translations
  of its own strings. It can't register services, routes, permissions, migrations, event subscribers or commands.
  Anything with behaviour is a module; a theme may suggest modules, never require them.
- **Every theme is a Composer package** of type `sightpress-theme` that requires only this package and names its
  theme class in `extra.sightpress.theme`. The default theme moves out of core into `sightpress/theme-default`, so
  it follows the same rules as everyone else's.
- **Operators install themes with Composer.** There is no theme upload through the web interface: a theme is code
  that runs on every page, so installing one is a deployment step, not an admin action.
- **The contract describes, core enforces.** This package holds interfaces, value objects and JSON schemas only and
  depends on nothing but PHP. Colour maths, contrast checks, discovery, validation, token compilation and the
  fallback to the default theme stay in core.
- **Contrast is checked on install and on apply** (WCAG 2.2 AA: 4.5:1 for text, 3:1 for interface colours) in light
  and dark mode, each with and without more contrast, for the theme's declared pairs and always for the chart
  colours. A failing theme or token override is refused with a message that names the pair and the ratio.
- **Themes can't drop what every page needs:** the shell must include core's `<head>` partial (meta, canonical
  links, JSON-LD) and provide the `search-form` partial as a `<search>` landmark; required templates, their
  variables and their blocks are listed in `RequiredTemplates`. Templates use only the documented variables, the
  functions in `TemplateApi` and Twig's built-ins.
- **Layouts:** every theme provides `default`, `home` and `search`. `landing`, `sidebar` and `full-width` are
  standard optional layouts with fixed ids, so a page keeps its layout across themes that provide it; a theme may
  add its own. A page whose layout the theme doesn't provide is shown with `default`, and core lists those pages
  before a switch.
- **No screenshot in a theme package.** Every screenshot is generated from the theme's static preview page, built
  from a sample page this package ships, by CI or by the operator's install. The manifest carries the author's text
  description of the look instead (the alt text of the generated picture).
- **Versioning:** strict SemVer for this package. Core may add optional variables and functions in a minor release;
  removing or changing a field, variable or function is a major release (before 1.0: the next minor, with a
  deprecation in the one before). Contract tests pin the shapes. A theme states the versions it works with
  (`ThemeRequirement`); core lists a theme whose constraint isn't met as unavailable, with the reason.

## Consequences

- Theme authors need only this package, the template repo and core as a dev dependency to build and test a theme.
- Core grows a theme registry (reads installed packages), `theme:list`, `theme:validate`, `theme:preview`,
  `site:theme:apply`, an appearance screen and per-site token overrides.
- Every new core block type adds its sample to the preview page here, so previews always show every block.
- Themes can't add interactive features on their own; authors who need behaviour write a module next to the theme.
- Look at this again before 1.0, when the deprecated `Screenshot` is gone and the template variables have settled.
