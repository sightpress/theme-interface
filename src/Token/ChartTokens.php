<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * The colours charts use (`theme.json` → `chart`), as names of the theme's colours. Core checks that each series
 * colour has at least 3:1 contrast with the background in every colour mode, and never uses colour alone to tell
 * series apart (charts also get patterns, labels and a data table).
 */
final readonly class ChartTokens
{
    /** Most series a chart shows in distinct colours; more series reuse them with different patterns. */
    public const int MAX_SERIES = 12;

    /** @var non-empty-list<non-empty-string> */
    public array $series;

    /**
     * @param list<string> $series colour names for the data series, in order, for example `["primary", "success"]`
     */
    public function __construct(array $series)
    {
        $series = Guard::listMatching($series, Guard::NAME, 'chart.series', Guard::NAME_RULE);
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
}
