<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * The colours of a theme (`theme.json` → `colors`): every colour for light and for dark mode, and the colours each
 * mode changes when people ask for more contrast. They become CSS custom properties named `--color-<name>`.
 */
final readonly class ColorTokens
{
    /**
     * Colours every theme must define: core uses them for generated images, the web manifest and focus rings. Every
     * theme also defines the {@see ChartTokens::COLORS}.
     */
    public const array REQUIRED = ['background', 'text', 'link', 'primary', 'on-primary', 'border', 'focus'];

    /** `#rgb` or `#rrggbb`, upper- or lower-case. */
    public const string HEX_PATTERN = '/^#(?:[0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/';

    private const string HEX_RULE = 'write colours as #rgb or #rrggbb, for example #1a1a1f';

    /** @var array<non-empty-string, non-empty-string> */
    public array $light;

    /** @var array<non-empty-string, non-empty-string> */
    public array $dark;

    /** @var array<non-empty-string, non-empty-string> */
    public array $lightMore;

    /** @var array<non-empty-string, non-empty-string> */
    public array $darkMore;

    /**
     * @param array<string, string> $light     every colour in light mode, by name
     * @param array<string, string> $dark      the same colours in dark mode
     * @param array<string, string> $lightMore light-mode colours that change with more contrast
     * @param array<string, string> $darkMore  dark-mode colours that change with more contrast
     */
    public function __construct(array $light, array $dark, array $lightMore = [], array $darkMore = [])
    {
        $this->light = self::palette($light, 'colors.light');
        $this->dark = self::palette($dark, 'colors.dark');
        $this->lightMore = self::palette($lightMore, 'colors.more.light');
        $this->darkMore = self::palette($darkMore, 'colors.more.dark');

        $missing = array_diff(self::REQUIRED, array_keys($this->light));
        if ($missing !== []) {
            throw new InvalidValueException(sprintf(
                'colors.light must define the colours "%s".',
                implode('", "', $missing),
            ));
        }

        $missingChart = array_diff(ChartTokens::COLORS, array_keys($this->light));
        if ($missingChart !== []) {
            throw new InvalidValueException(sprintf(
                'colors.light must define the chart colours "%s": chart-1 to chart-4 for lines and bars, chart-grid and chart-text.',
                implode('", "', $missingChart),
            ));
        }

        $different = array_keys(array_diff_key($this->light, $this->dark) + array_diff_key($this->dark, $this->light));
        if ($different !== []) {
            throw new InvalidValueException(sprintf(
                'colors.light and colors.dark must define the same colours; different: "%s".',
                implode('", "', $different),
            ));
        }

        $unknown = array_keys(array_diff_key($this->lightMore + $this->darkMore, $this->light));
        if ($unknown !== []) {
            throw new InvalidValueException(sprintf(
                'colors.more can only change colours that colors.light defines; unknown: "%s".',
                implode('", "', $unknown),
            ));
        }
    }

    /**
     * Every colour name the theme defines.
     *
     * @return list<non-empty-string>
     */
    public function names(): array
    {
        return array_keys($this->light);
    }

    public function has(string $name): bool
    {
        return isset($this->light[$name]);
    }

    /**
     * Every colour as it is in the given mode.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function colors(ColorMode $mode): array
    {
        return match ($mode) {
            ColorMode::Light => $this->light,
            ColorMode::Dark => $this->dark,
            ColorMode::LightMore => array_replace($this->light, $this->lightMore),
            ColorMode::DarkMore => array_replace($this->dark, $this->darkMore),
        };
    }

    /**
     * @return non-empty-string|null
     */
    public function color(string $name, ColorMode $mode): ?string
    {
        return $this->colors($mode)[$name] ?? null;
    }

    /**
     * @param array<mixed> $colors
     *
     * @return array<non-empty-string, non-empty-string>
     */
    private static function palette(array $colors, string $field): array
    {
        return Guard::mapMatching($colors, Guard::NAME, Guard::NAME_RULE, self::HEX_PATTERN, self::HEX_RULE, $field);
    }
}
