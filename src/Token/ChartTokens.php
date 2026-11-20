<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * The colours charts use (core's dashboard charts, AN-2).
 *
 * Every theme defines the {@see COLORS} in `colors.light` and `colors.dark`: {@see MARKS} for lines and bars,
 * {@see GRID} for gridlines and {@see TEXT} for labels. `theme.json` → `chart.series` may list, in order, which
 * colours the data series use; without it they use the {@see MARKS}. Core checks every series colour for 3:1 and the
 * chart text for 4.5:1 against the {@see BACKGROUNDS} in every colour mode ({@see contrastPairs()}), and never uses
 * colour alone to tell series apart (charts also get patterns, labels and a data table).
 */
final readonly class ChartTokens
{
    /** Most series a chart shows in distinct colours; more series reuse them with different patterns. */
    public const int MAX_SERIES = 12;

    /** Colours of chart lines and bars, in order; the series colours when `chart.series` is not set. */
    public const array MARKS = ['chart-1', 'chart-2', 'chart-3', 'chart-4'];

    /** Colour of chart gridlines; decorative, so it has no contrast rule. */
    public const string GRID = 'chart-grid';

    /** Colour of the text in charts (axis labels, values). */
    public const string TEXT = 'chart-text';

    /** Chart colours every theme must define. */
    public const array COLORS = [...self::MARKS, self::GRID, self::TEXT];

    /** The colours charts are drawn on; `surface` is checked only when the theme defines it. */
    public const array BACKGROUNDS = ['background', 'surface'];

    /** @var non-empty-list<non-empty-string> */
    public array $series;

    /**
     * @param list<string>|null $series colour names for the data series, in order, for example
     *                                  `["primary", "success"]`; null for the {@see MARKS}
     */
    public function __construct(?array $series = null)
    {
        $series = $series === null
            ? self::MARKS
            : Guard::listMatching($series, Guard::NAME, 'chart.series', Guard::NAME_RULE);
        if ($series === [] || count($series) > self::MAX_SERIES) {
            throw new InvalidValueException(sprintf(
                'chart.series must name 1 to %d colours, not %d.',
                self::MAX_SERIES,
                count($series),
            ));
        }
        Guard::unique($series, 'chart.series');
        $this->series = $series;
    }

    /**
     * The pairs core checks for charts on top of the theme's own `contrast` pairs: each series colour (interface
     * colour, 3:1) and the chart text (text, 4.5:1) on each of the {@see BACKGROUNDS} the theme defines.
     *
     * @return list<ColorPair>
     */
    public function contrastPairs(ColorTokens $colors): array
    {
        $pairs = [];
        foreach (self::BACKGROUNDS as $background) {
            if (!$colors->has($background)) {
                continue;
            }
            foreach ($this->series as $series) {
                if ($series !== $background) {
                    $pairs[] = new ColorPair($series, $background, ColorUse::Ui);
                }
            }
            $pairs[] = new ColorPair(self::TEXT, $background, ColorUse::Text);
        }

        return $pairs;
    }
}
