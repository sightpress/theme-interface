<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Fake\Theme;

use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Token\ColorPair;
use SightPress\Theme\Token\ColorTokens;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Token\ThemeTokens;
use SightPress\Theme\Token\TokenGroup;

/**
 * Builds the token value objects from a decoded `theme.json` that already passed the schema, the way core does.
 */
final class TokensFromJson
{
    /**
     * @param array<string, mixed> $json
     */
    public static function build(array $json): ThemeTokens
    {
        /** @var array{light: array<string, string>, dark: array<string, string>, more?: array{light?: array<string, string>, dark?: array<string, string>}} $colors */
        $colors = $json['colors'];

        $values = [];
        foreach (TokenGroup::cases() as $group) {
            /** @var array<string, string|int|float> $tokens */
            $tokens = $json[$group->value] ?? [];
            $values[$group->value] = array_map(static fn (string|int|float $value): string => (string) $value, $tokens);
        }

        $pairs = [];
        /** @var array<string, list<array{string, string}>> $contrast */
        $contrast = $json['contrast'] ?? [];
        foreach ($contrast as $use => $list) {
            foreach ($list as [$foreground, $background]) {
                $pairs[] = new ColorPair($foreground, $background, ColorUse::from($use));
            }
        }

        /** @var array{series: list<string>}|null $chart */
        $chart = $json['chart'] ?? null;

        return new ThemeTokens(
            new ColorTokens($colors['light'], $colors['dark'], $colors['more']['light'] ?? [], $colors['more']['dark'] ?? []),
            $values,
            $pairs,
            $chart === null ? null : new ChartTokens($chart['series']),
        );
    }
}
