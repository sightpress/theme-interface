<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * The design tokens of a theme, as `theme.json` holds them (see `resources/theme.schema.json`): colours per mode,
 * the other {@see TokenGroup}s, the colour pairs whose contrast core checks, and the chart colours.
 *
 * Building this object checks the shape and the references between tokens. Whether the pairs have enough contrast
 * is core's job: the contract describes, core enforces.
 */
final readonly class ThemeTokens
{
    /** Token names in the groups other than colours: lower-case words or numbers joined by `-`. */
    public const string TOKEN_PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    /**
     * A plain CSS value: letters, digits, spaces and `. , % # ( ) + * / ' " -`, and no comment. Anything else
     * (`;`, `{`, `}`, `<` …) could inject rules into the stylesheet core generates.
     */
    public const string VALUE_PATTERN = '/^(?!.*\/\*)[A-Za-z0-9 .,%#()+*\/\'"-]+$/';

    private const string TOKEN_RULE = 'use lower-case words or numbers joined by "-"';

    private const string VALUE_RULE = 'use a plain CSS value (letters, digits, spaces and . , % # ( ) + * / \' " -)';

    /** @var array<value-of<TokenGroup>, array<non-empty-string, non-empty-string>> */
    public array $values;

    /** @var list<ColorPair> */
    public array $contrast;

    public ChartTokens $chart;

    /**
     * @param array<value-of<TokenGroup>, array<int|string, string>> $values   other tokens by group, for example
     *                                                                         `['typography' => ['size-base' => '1rem']]`
     * @param list<ColorPair>                                        $contrast colour pairs core checks for contrast
     * @param ChartTokens|null                                       $chart    the series colours from
     *                                                                         `chart.series`, or null for the
     *                                                                         {@see ChartTokens::MARKS}
     */
    public function __construct(
        public ColorTokens $colors,
        array $values = [],
        array $contrast = [],
        ?ChartTokens $chart = null,
    ) {
        $this->chart = $chart ?? new ChartTokens();
        $groups = [];
        foreach ($values as $group => $tokens) {
            $kind = TokenGroup::tryFrom((string) $group);
            if ($kind === null) {
                throw new InvalidValueException(sprintf(
                    'Unknown token group "%s": use one of "%s".',
                    $group,
                    implode('", "', array_column(TokenGroup::cases(), 'value')),
                ));
            }
            $groups[$kind->value] = Guard::mapMatching(
                $tokens,
                self::TOKEN_PATTERN,
                self::TOKEN_RULE,
                self::VALUE_PATTERN,
                self::VALUE_RULE,
                $kind->value,
            );
        }

        foreach (TokenGroup::cases() as $group) {
            foreach ($group->requiredTokens() as $token) {
                if (!isset($groups[$group->value][$token])) {
                    throw new InvalidValueException(sprintf('%s must define "%s".', $group->value, $token));
                }
            }
        }
        $this->values = $groups;

        $this->contrast = Guard::listOf($contrast, ColorPair::class, 'contrast');
        foreach ($this->contrast as $pair) {
            foreach ([$pair->foreground, $pair->background] as $name) {
                $this->known($name, sprintf('The %s pair %s', $pair->use->label(), $pair->label()));
            }
        }

        foreach ($this->chart->series as $name) {
            $this->known($name, 'chart.series');
        }
    }

    /**
     * Every token of a group, by name.
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public function group(TokenGroup $group): array
    {
        return $this->values[$group->value] ?? [];
    }

    /**
     * @return non-empty-string|null
     */
    public function value(TokenGroup $group, string $name): ?string
    {
        return $this->values[$group->value][$name] ?? null;
    }

    /**
     * The contrast pairs for one use.
     *
     * @return list<ColorPair>
     */
    public function pairs(ColorUse $use): array
    {
        return array_values(array_filter($this->contrast, static fn (ColorPair $pair): bool => $pair->use === $use));
    }

    /**
     * Every pair core checks for contrast: the theme's own `contrast` pairs, then the chart pairs
     * ({@see ChartTokens::contrastPairs()}).
     *
     * @return list<ColorPair>
     */
    public function checkedPairs(): array
    {
        return [...$this->contrast, ...$this->chart->contrastPairs($this->colors)];
    }

    private function known(string $name, string $where): void
    {
        if (!$this->colors->has($name)) {
            throw new InvalidValueException(sprintf(
                '%s names the colour "%s", which colors.light doesn\'t define.',
                $where,
                $name,
            ));
        }
    }
}
