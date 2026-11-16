<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

/**
 * The token groups other than colours. Each becomes CSS custom properties named `--<prefix>-<token>`, for example
 * `typography.size-base` becomes `--font-size-base`.
 */
enum TokenGroup: string
{
    case Typography = 'typography';
    case Spacing = 'spacing';
    case Radius = 'radius';
    case Focus = 'focus';

    /**
     * Prefix of the group's CSS custom properties, without the leading `--`.
     */
    public function cssPrefix(): string
    {
        return match ($this) {
            self::Typography => 'font',
            self::Spacing => 'space',
            self::Radius => 'radius',
            self::Focus => 'focus',
        };
    }

    /**
     * Tokens every theme must define in this group.
     *
     * @return list<non-empty-string>
     */
    public function requiredTokens(): array
    {
        return match ($this) {
            self::Typography => ['size-base'],
            self::Spacing, self::Radius, self::Focus => [],
        };
    }
}
