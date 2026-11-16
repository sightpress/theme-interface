<?php

declare(strict_types=1);

namespace SightPress\Theme\Internal;

use SightPress\Theme\Exception\InvalidValueException;

/**
 * Shared checks for the value objects in this package.
 *
 * @internal Not part of the contract: it may change in any release. Themes must not use it.
 */
final class Guard
{
    /**
     * Lower-case words joined by `-`, starting with a letter, such as `text-muted` or `default`.
     */
    public const string NAME = '/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*$/';

    /** Human-readable form of {@see NAME}, used in error messages. */
    public const string NAME_RULE = 'use lower-case words joined by "-", starting with a letter';

    /**
     * @return non-empty-string
     */
    public static function notBlank(string $value, string $field): string
    {
        if (trim($value) === '') {
            throw new InvalidValueException(sprintf('%s must not be empty.', $field));
        }

        return $value;
    }

    /**
     * @param non-empty-string $pattern
     *
     * @return non-empty-string
     */
    public static function matches(string $value, string $pattern, string $field, string $rule): string
    {
        if ($value === '' || preg_match($pattern, $value) !== 1) {
            throw new InvalidValueException(sprintf('%s is not valid ("%s"): %s.', $field, $value, $rule));
        }

        return $value;
    }

    /**
     * @param list<string> $values
     */
    public static function unique(array $values, string $field): void
    {
        $duplicates = array_keys(array_filter(array_count_values($values), static fn (int $count): bool => $count > 1));
        if ($duplicates !== []) {
            throw new InvalidValueException(sprintf('%s has duplicates: %s.', $field, implode(', ', $duplicates)));
        }
    }

    /**
     * Checks at run time what the `list<T>` type of a parameter promises, for callers without static analysis.
     *
     * @template T of object
     *
     * @param array<mixed>    $values
     * @param class-string<T> $class
     *
     * @return list<T>
     */
    public static function listOf(array $values, string $class, string $field): array
    {
        if (!array_is_list($values)) {
            throw new InvalidValueException(sprintf('%s must be a list.', $field));
        }

        foreach ($values as $index => $value) {
            if (!$value instanceof $class) {
                throw new InvalidValueException(sprintf('%s[%d] must be an instance of %s.', $field, $index, $class));
            }
        }

        /** @var list<T> $values */
        return $values;
    }

    /**
     * A list of strings that each match a pattern.
     *
     * @param array<mixed>     $values
     * @param non-empty-string $pattern
     *
     * @return list<non-empty-string>
     */
    public static function listMatching(array $values, string $pattern, string $field, string $rule): array
    {
        if (!array_is_list($values)) {
            throw new InvalidValueException(sprintf('%s must be a list.', $field));
        }

        $checked = [];
        foreach ($values as $index => $value) {
            if (!is_string($value)) {
                throw new InvalidValueException(sprintf('%s[%d] must be a string.', $field, $index));
            }
            $checked[] = self::matches($value, $pattern, sprintf('%s[%d]', $field, $index), $rule);
        }

        return $checked;
    }

    /**
     * A map of names to strings: every name matches `$namePattern` and every value matches `$valuePattern`.
     *
     * @param array<mixed>     $values
     * @param non-empty-string $namePattern
     * @param non-empty-string $valuePattern
     *
     * @return array<non-empty-string, non-empty-string>
     */
    public static function mapMatching(
        array $values,
        string $namePattern,
        string $nameRule,
        string $valuePattern,
        string $valueRule,
        string $field,
    ): array {
        $checked = [];
        foreach ($values as $name => $value) {
            $name = self::matches((string) $name, $namePattern, sprintf('A name in %s', $field), $nameRule);
            if (!is_string($value)) {
                throw new InvalidValueException(sprintf('%s.%s must be a string.', $field, $name));
            }
            $checked[$name] = self::matches($value, $valuePattern, sprintf('%s.%s', $field, $name), $valueRule);
        }

        return $checked;
    }
}
