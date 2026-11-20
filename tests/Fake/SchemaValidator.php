<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Fake;

use Opis\JsonSchema\Errors\ErrorFormatter;
use Opis\JsonSchema\Validator;
use SightPress\Theme\Preview\PreviewPage;
use SightPress\Theme\ThemeSchema;

/**
 * Validates `theme.json` data, or a preview page, against the schema this package ships, as core does.
 */
final class SchemaValidator
{
    /**
     * @return list<string> one message per problem; empty when the data is valid
     */
    public static function errors(string $json, string $schema = ThemeSchema::ID): array
    {
        $validator = new Validator();
        $validator->setMaxErrors(20);
        $validator->resolver()?->registerFile($schema, $schema === ThemeSchema::ID ? ThemeSchema::file() : PreviewPage::schemaFile());

        $result = $validator->validate(json_decode($json, false, 64, JSON_THROW_ON_ERROR), $schema);
        $error = $result->error();
        if ($error === null) {
            return [];
        }

        $messages = [];
        foreach (new ErrorFormatter()->format($error, true) as $pointer => $list) {
            foreach ((array) $list as $message) {
                $messages[] = sprintf('%s: %s', $pointer, is_string($message) ? $message : 'invalid');
            }
        }

        return $messages;
    }
}
