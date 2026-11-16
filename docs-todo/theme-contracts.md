# Theme contracts

What it does: the contracts every SightPress theme implements (`ThemeInterface`, `ThemeManifest`), the
`theme.json` schema and its typed token value objects, the required templates with the variables core passes to
them, and the Twig functions templates may use.

Who uses it: theme authors (developers and designers); core uses it to discover, validate and apply themes.

- Package: `sightpress/theme-interface`, namespace `SightPress\Theme\`. No runtime dependencies.
- A theme is a Composer package of type `sightpress-theme` with `extra.sightpress.theme` set to its theme class.
- Files in a theme: `theme.json`, `templates/` (at least `RequiredTemplates::files()`), `assets/`, `translations/`.
- Required layouts: `default`, `home`, `search`; a page whose layout the theme doesn't provide uses `default`.
- `theme.json` schema: `resources/theme.schema.json` (`ThemeSchema::ID`), draft 2020-12.

Accessibility:

- Contrast pairs declared under `contrast` are checked by core in light, dark and both "more contrast" modes:
  4.5:1 for `text` pairs, 3:1 for `ui` pairs (WCAG 2.2 AA). Chart series colours need 3:1 against the background.
- The shell must set `lang` and `dir`, have a skip link and landmarks, show messages in a polite live region, and
  include the core `<head>` partial.
- Screenshots need alt text that describes the look of the theme.

Code: `src/`; contract tests with a fake third-party theme in `tests/Unit/Contract/` and `tests/Fake/Theme/`.
