<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Preview\PreviewPage;
use SightPress\Theme\Tests\Fake\SchemaValidator;

/**
 * Pins the sample page: it validates against its schema, opens with the hero, shows each block type once outside
 * containers in {@see PreviewPage::BLOCK_ORDER}, names media only by role and uses only the preview variables.
 */
#[CoversClass(PreviewPage::class)]
final class PreviewPageContractTest extends TestCase
{
    /** The core block types at this version of the contract; a block task adds its type here and to the page. */
    private const string REMOVE = "\0remove";

    private const array CORE_TYPES = [
        'heading',
        'rich_text',
        'call_to_action',
        'image',
        'file_download',
        'columns',
        'gallery',
        'audio',
        'video',
        'contact_form',
    ];

    public function testThePageMatchesItsSchema(): void
    {
        self::assertSame([], SchemaValidator::errors(self::json(), PreviewPage::SCHEMA_ID));
    }

    public function testEveryCoreBlockTypeIsShownOnceInOrder(): void
    {
        $types = [];
        foreach (self::page()['sections'] as $section) {
            foreach ($section['blocks'] as $block) {
                $types[] = $block['type'];
            }
        }

        self::assertSame(self::CORE_TYPES, $types);
        $order = array_flip(PreviewPage::BLOCK_ORDER);
        $ranks = array_map(static fn (string $type): int => $order[$type] ?? -1, $types);
        $sorted = $ranks;
        sort($sorted);
        self::assertSame($sorted, $ranks, 'The blocks must follow PreviewPage::BLOCK_ORDER.');
        self::assertNotContains(-1, $ranks);
    }

    public function testTheNavbarTargetsAreUnique(): void
    {
        $ids = array_column(self::page()['sections'], 'id');

        self::assertSame(array_values(array_unique($ids)), $ids);
        self::assertContains('contact', $ids);
    }

    public function testUsesOnlyThePreviewVariables(): void
    {
        preg_match_all('/\{\{\s*(.*?)\s*\}\}/', self::json(), $matches);
        $allowed = array_map(static fn (string $name): string => 'theme.' . $name, PreviewPage::VARIABLES);

        self::assertNotSame([], $matches[1]);
        self::assertSame([], array_diff($matches[1], $allowed));
    }

    public function testNamesMediaOnlyByRole(): void
    {
        preg_match_all('/"@([a-z_]+)"/', self::json(), $matches);

        self::assertSame([], array_diff($matches[1], PreviewPage::MEDIA));
        self::assertSame([], array_diff(PreviewPage::MEDIA, $matches[1]), 'Every sample file is used.');
    }

    /**
     * @return iterable<string, array{non-empty-list<string|int>, mixed}>
     */
    public static function brokenPages(): iterable
    {
        yield 'no hero' => [['hero'], self::REMOVE];
        yield 'hero heading not the theme name' => [['hero', 'heading'], 'Welcome'];
        yield 'button label not Download' => [['hero', 'button', 'label'], 'Get it'];
        yield 'button name without its label' => [['hero', 'button', 'accessible_name'], 'Get {{ theme.name }}'];
        yield 'section without blocks' => [['sections', 0, 'blocks'], []];
        yield 'section id with a space' => [['sections', 0, 'id'], 'my text'];
        yield 'block without a version' => [['sections', 0, 'blocks', 0, 'version'], self::REMOVE];
        yield 'unknown key' => [['footer'], 'Mine'];
    }

    /**
     * @param non-empty-list<string|int> $path
     */
    #[DataProvider('brokenPages')]
    public function testTheSchemaRefusesABrokenPage(array $path, mixed $value): void
    {
        $page = self::with(self::page(), $path, $value);

        self::assertNotSame([], SchemaValidator::errors((string) json_encode($page), PreviewPage::SCHEMA_ID));
    }

    /**
     * @param array<mixed>               $data
     * @param non-empty-list<string|int> $path
     *
     * @return array<mixed>
     */
    private static function with(array $data, array $path, mixed $value): array
    {
        $key = array_shift($path);
        if ($path === []) {
            $data[$key] = $value;
            if ($value === self::REMOVE) {
                unset($data[$key]);
            }

            return $data;
        }
        $data[$key] = self::with(is_array($data[$key] ?? null) ? $data[$key] : [], $path, $value);

        return $data;
    }

    private static function json(): string
    {
        return (string) file_get_contents(PreviewPage::file());
    }

    /**
     * @return array{sections: list<array{id: string, blocks: list<array{type: string}>}>}&array<string, mixed>
     */
    private static function page(): array
    {
        /** @var array{sections: list<array{id: string, blocks: list<array{type: string}>}>}&array<string, mixed> */
        return json_decode(self::json(), true, 64, JSON_THROW_ON_ERROR);
    }
}
