<?php

declare(strict_types=1);

namespace SightPress\Theme\Exception;

use InvalidArgumentException;

/**
 * Thrown when a value object of this package is built with a value that breaks one of its rules.
 *
 * The message names the field and the rule, so a theme author can fix the theme without reading this package.
 */
final class InvalidValueException extends InvalidArgumentException
{
}
