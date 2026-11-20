# Theme contracts

What it does: the contracts every SightPress theme implements (`ThemeInterface`, `ThemeManifest`), the
`theme.json` schema and its typed token value objects, the required templates with the variables core passes to
them, and the Twig functions templates may use.

Who uses it: theme authors (developers and designers); core uses it to discover, validate and apply themes.

- Package: `sightpress/theme-interface`, namespace `SightPress\Theme\`. No runtime dependencies.
- A theme is a Composer package of type `sightpress-theme` with `extra.sightpress.theme` set to its theme class.
- Files in a theme: `theme.json`, `templates/` (at least `RequiredTemplates::files()`), `assets/`, `translations/`.
- Required layouts: `default`, `home`, `search`; standard optional layouts: `landing`, `sidebar` (blocks
  `content` + `aside`), `full-width`; a page whose layout the theme doesn't provide uses `default`. Editors pick
  from `ThemeManifest::pageLayouts()` (all but `search`).
- Required colours include the chart colours `chart-1` … `chart-4`, `chart-grid`, `chart-text` (0.2).
- `theme.json` schema: `resources/theme.schema.json` (`ThemeSchema::ID`), draft 2020-12.

Accessibility:

- Contrast pairs declared under `contrast` are checked by core in light, dark and both "more contrast" modes:
  4.5:1 for `text` pairs, 3:1 for `ui` pairs (WCAG 2.2 AA). Chart series colours need 3:1 and `chart-text` 4.5:1
  on `background` and `surface`, always checked (`ThemeTokens::checkedPairs()`).
- The shell must set `lang` and `dir`, have a skip link and landmarks, show messages in a polite live region, and
  include the core `<head>` partial. The search form is a `<search>` landmark named by its visible label; the
  search page announces the number of results through `<title>` and `<h1>`, not a live region.
- A theme ships no screenshot (0.2): core makes every screenshot from the preview page
  (`resources/preview/page.json`, `Preview\PreviewPage`); its alt text is the manifest's `previewDescription`,
  which describes the look in words. `Screenshot` is deprecated in 0.2 and removed in 0.3.
- The preview page holds one block of each core block type, in `PreviewPage::BLOCK_ORDER`, after the hero (the
  theme's name as `<h1>`, its description and a Download button whose accessible name starts with its label).

Code: `src/`; contract tests with a fake third-party theme in `tests/Unit/Contract/` and `tests/Fake/Theme/`.
