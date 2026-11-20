<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\Exception\InvalidValueException;
use SightPress\Theme\LayoutDefinition;
use SightPress\Theme\Screenshot;
use SightPress\Theme\ThemeManifest;
use SightPress\Theme\ThemeRequirement;
use SightPress\Theme\Internal\Guard;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(ThemeManifest::class)]
#[UsesClass(Guard::class)]
#[UsesClass(LayoutDefinition::class)]
#[UsesClass(Screenshot::class)]
#[UsesClass(ThemeRequirement::class)]
final class ThemeManifestTest extends TestCase
{
    public function testDefaults(): void
    {
        $manifest = self::manifest();

        self::assertSame('acme-harbour', $manifest->id);
        self::assertSame('Harbour', $manifest->name);
        self::assertSame('Calm blues.', $manifest->description);
        self::assertSame('1.0.0', $manifest->version);
        self::assertSame('^0.1', $manifest->requires->constraint);
        self::assertEquals(LayoutDefinition::required(), $manifest->layouts);
        self::assertSame([], $manifest->locales);
        self::assertTrue($manifest->rightToLeft);
        self::assertNull($manifest->screenshot);
        self::assertSame([], $manifest->suggestedModules);
        self::assertNull($manifest->author);
        self::assertNull($manifest->licence);
    }

    public function testEverything(): void
    {
        $sidebar = new LayoutDefinition('sidebar', 'Sidebar');
        $screenshot = new Screenshot('screenshot.png', 'The home page.');
        $manifest = new ThemeManifest(
            'default',
            ' SightPress Default ',
            ' The default theme. ',
            '0.1.0-beta.1+build.5',
            new ThemeRequirement('^0.1'),
            [...LayoutDefinition::required(), $sidebar],
            ['en-GB', 'pt', 'zh-Hant-TW', 'es-419'],
            false,
            $screenshot,
            ['acme.events', 'blog'],
            'SightPress',
            'GPL-3.0-or-later',
        );

        self::assertSame('SightPress Default', $manifest->name);
        self::assertSame('The default theme.', $manifest->description);
        self::assertSame($sidebar, $manifest->layout('sidebar'));
        self::assertSame(['en-GB', 'pt', 'zh-Hant-TW', 'es-419'], $manifest->locales);
        self::assertFalse($manifest->rightToLeft);
        self::assertSame($screenshot, $manifest->screenshot);
        self::assertSame(['acme.events', 'blog'], $manifest->suggestedModules);
        self::assertSame('SightPress', $manifest->author);
        self::assertSame('GPL-3.0-or-later', $manifest->licence);
    }

    public function testResolvesLayoutsWithTheFallback(): void
    {
        $manifest = self::manifest();

        self::assertSame('home', $manifest->resolveLayout('home')->id);
        self::assertSame('default', $manifest->resolveLayout('landing')->id);
        self::assertNull($manifest->layout('landing'));
        self::assertTrue($manifest->provides('search'));
        self::assertFalse($manifest->provides('landing'));
    }

    public function testPageLayoutsLeaveOutTheSearchLayout(): void
    {
        $manifest = new ThemeManifest(
            'harbour',
            'Harbour',
            'Calm blues.',
            '1.0.0',
            new ThemeRequirement('^0.2'),
            [...LayoutDefinition::required(), ...LayoutDefinition::standard()],
        );

        self::assertSame(
            ['default', 'home', 'landing', 'sidebar', 'full-width'],
            array_map(static fn (LayoutDefinition $layout): string => $layout->id, $manifest->pageLayouts()),
        );
    }

    public function testSupportsEveryLocaleWhenNoneAreListed(): void
    {
        self::assertTrue(self::manifest()->supportsLocale('ar'));
    }

    public function testSupportsListedLocalesAndTheirRegions(): void
    {
        $manifest = self::manifest(locales: ['en', 'pt-BR']);

        self::assertTrue($manifest->supportsLocale('en-GB'));
        self::assertTrue($manifest->supportsLocale('EN'));
        self::assertTrue($manifest->supportsLocale('pt-br'));
        self::assertFalse($manifest->supportsLocale('pt-PT'));
        self::assertFalse($manifest->supportsLocale('fr'));
    }

    /**
     * @return iterable<string, array{callable(): ThemeManifest, string}>
     */
    public static function invalidManifests(): iterable
    {
        yield 'id with capitals' => [
            static fn (): ThemeManifest => self::manifest(id: 'Acme'),
            'Theme id is not valid ("Acme"): use 1 to 64 lower-case letters and digits, starting with a letter, in words joined by "-".',
        ];
        yield 'id too long' => [
            static fn (): ThemeManifest => self::manifest(id: str_repeat('a', 65)),
            sprintf('Theme id is not valid ("%s")', str_repeat('a', 65)),
        ];
        yield 'blank name' => [
            static fn (): ThemeManifest => self::manifest(name: ' '),
            'Name of theme "acme-harbour" must not be empty.',
        ];
        yield 'blank description' => [
            static fn (): ThemeManifest => self::manifest(description: ''),
            'Description of theme "acme-harbour" must not be empty.',
        ];
        yield 'version not SemVer' => [
            static fn (): ThemeManifest => self::manifest(version: 'v1.0'),
            'Version of theme "acme-harbour" is not valid ("v1.0"): use Semantic Versioning, for example 1.4.0.',
        ];
        yield 'layouts not layout definitions' => [
            static fn (): ThemeManifest => self::manifest(layouts: ['default']),
            'Layouts of theme "acme-harbour"[0] must be an instance of SightPress\Theme\LayoutDefinition.',
        ];
        yield 'required layout missing' => [
            static fn (): ThemeManifest => self::manifest(layouts: [new LayoutDefinition('default', 'Default')]),
            'Theme "acme-harbour" must provide the layouts "home", "search".',
        ];
        yield 'repeated layout' => [
            static fn (): ThemeManifest => self::manifest(layouts: [...LayoutDefinition::required(), new LayoutDefinition('home', 'Home')]),
            'Layouts of theme "acme-harbour" has duplicates: home.',
        ];
        yield 'invalid locale' => [
            static fn (): ThemeManifest => self::manifest(locales: ['en_GB']),
            'Locales of theme "acme-harbour"[0] is not valid ("en_GB"): use a BCP 47 language tag such as en-GB.',
        ];
        yield 'repeated locale' => [
            static fn (): ThemeManifest => self::manifest(locales: ['en', 'en']),
            'Locales of theme "acme-harbour" has duplicates: en.',
        ];
        yield 'invalid module id' => [
            static fn (): ThemeManifest => self::manifest(suggestedModules: ['Acme Events']),
            'Suggested modules of theme "acme-harbour"[0] is not valid ("Acme Events"): use a module id such as acme.events.',
        ];
        yield 'repeated module' => [
            static fn (): ThemeManifest => self::manifest(suggestedModules: ['blog', 'blog']),
            'Suggested modules of theme "acme-harbour" has duplicates: blog.',
        ];
        yield 'blank author' => [
            static fn (): ThemeManifest => self::manifest(author: ' '),
            'The author of theme "acme-harbour" (use null for none) must not be empty.',
        ];
        yield 'blank licence' => [
            static fn (): ThemeManifest => self::manifest(licence: ''),
            'The licence of theme "acme-harbour" (use null for none) must not be empty.',
        ];
    }

    /**
     * @param callable(): ThemeManifest $build
     */
    #[DataProvider('invalidManifests')]
    public function testRefusesInvalidValues(callable $build, string $message): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage($message);
        $build();
    }

    /**
     * @param list<mixed>  $layouts
     * @param list<string> $locales
     * @param list<string> $suggestedModules
     */
    private static function manifest(
        string $id = 'acme-harbour',
        string $name = 'Harbour',
        string $description = 'Calm blues.',
        string $version = '1.0.0',
        array $layouts = [],
        array $locales = [],
        array $suggestedModules = [],
        ?string $author = null,
        ?string $licence = null,
    ): ThemeManifest {
        /** @var list<LayoutDefinition> $layouts */
        return new ThemeManifest(
            $id,
            $name,
            $description,
            $version,
            new ThemeRequirement('^0.1'),
            $layouts,
            $locales,
            suggestedModules: $suggestedModules,
            author: $author,
            licence: $licence,
        );
    }
}
