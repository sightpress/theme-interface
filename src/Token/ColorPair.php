<?php

declare(strict_types=1);

namespace SightPress\Theme\Token;

use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;

/**
 * A foreground colour the theme puts on a background colour, declared in `theme.json` under `contrast`
 * (`"text": [["text", "background"], …]`), so core can check its contrast in every {@see ColorMode}.
 */
final readonly class ColorPair
{
    /** @var non-empty-string */
    public string $foreground;

    /** @var non-empty-string */
    public string $background;

    /**
     * @param string $foreground colour name, for example `text`
     * @param string $background colour name, for example `surface`
     */
    public function __construct(string $foreground, string $background, public ColorUse $use)
    {
        $this->foreground = Guard::matches($foreground, Guard::NAME, 'Foreground colour of a contrast pair', Guard::NAME_RULE);
        $this->background = Guard::matches($background, Guard::NAME, 'Background colour of a contrast pair', Guard::NAME_RULE);
        if ($foreground === $background) {
            throw new InvalidValueException(sprintf(
                'A contrast pair needs two different colours, not "%s" on itself.',
                $foreground,
            ));
        }
    }

    /**
     * How the pair is named in messages, for example `"text" on "surface"`.
     */
    public function label(): string
    {
        return sprintf('"%s" on "%s"', $this->foreground, $this->background);
    }
}
