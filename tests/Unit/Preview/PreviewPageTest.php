<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Preview;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\Internal\Guard;
use SightPress\Theme\Preview\PreviewPage;

#[CoversClass(PreviewPage::class)]
#[UsesClass(Guard::class)]
final class PreviewPageTest extends TestCase
{
    public function testShipsThePageAndItsSchema(): void
    {
        self::assertFileExists(PreviewPage::file());
        self::assertFileExists(PreviewPage::schemaFile());

        /** @var array{'$id': string} $schema */
        $schema = json_decode((string) file_get_contents(PreviewPage::schemaFile()), true, 64, JSON_THROW_ON_ERROR);
        self::assertSame(PreviewPage::SCHEMA_ID, $schema['$id']);
    }

    public function testTheBlockOrderNamesEveryTypeOnce(): void
    {
        self::assertSame(array_values(array_unique(PreviewPage::BLOCK_ORDER)), PreviewPage::BLOCK_ORDER);
        self::assertSame([], array_diff(PreviewPage::CONTAINERS, PreviewPage::BLOCK_ORDER));
    }

    public function testTheDownloadLinkIsTheHomepage(): void
    {
        self::assertSame('https://acme.example/harbour', PreviewPage::downloadUrl('acme/theme-harbour', ' https://acme.example/harbour '));
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function noHomepage(): iterable
    {
        yield 'none' => [null];
        yield 'empty' => [''];
        yield 'not a web address' => ['ftp://acme.example'];
    }

    #[DataProvider('noHomepage')]
    public function testWithoutAHomepageTheDownloadLinkIsThePackagistPage(?string $homepage): void
    {
        self::assertSame(
            'https://packagist.org/packages/sightpress/theme-default',
            PreviewPage::downloadUrl('sightpress/theme-default', $homepage),
        );
    }

    public function testRefusesAPackageNameThatIsNotOne(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Package name');
        PreviewPage::downloadUrl('Acme Harbour');
    }
}
