<?php

declare(strict_types=1);

namespace SightPress\Theme\Tests\Unit\Contract;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SightPress\Theme\LayoutDefinition;
use SightPress\Theme\Template\CorePartial;
use SightPress\Theme\Template\RequiredTemplates;
use SightPress\Theme\Template\TemplateApi;
use SightPress\Theme\Template\TemplateDefinition;
use SightPress\Theme\Tests\Fake\SchemaValidator;
use SightPress\Theme\Tests\Fake\Theme\HarbourTheme;
use SightPress\Theme\Tests\Fake\Theme\TokensFromJson;
use SightPress\Theme\ThemeManifest;
use SightPress\Theme\ThemePaths;
use SightPress\Theme\ThemeSchema;
use SightPress\Theme\Token\ColorMode;
use SightPress\Theme\Token\ColorUse;
use SightPress\Theme\Token\TokenGroup;
use SightPress\Theme\Internal\Guard;
use SightPress\Theme\Template\TemplateFunction;
use SightPress\Theme\Template\TemplateVariable;
use SightPress\Theme\ThemeRequirement;
use SightPress\Theme\Token\ChartTokens;
use SightPress\Theme\Token\ColorPair;
use SightPress\Theme\Token\ColorTokens;
use SightPress\Theme\Token\ThemeTokens;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * A theme written only against this package fits together: its manifest, its files, the schema and the token
 * value objects, the required templates and the template API.
 */
#[CoversClass(ThemeManifest::class)]
#[CoversClass(ThemePaths::class)]
#[CoversClass(ThemeSchema::class)]
#[CoversClass(RequiredTemplates::class)]
#[CoversClass(TemplateDefinition::class)]
#[CoversClass(TemplateApi::class)]
#[CoversClass(CorePartial::class)]
#[UsesClass(Guard::class)]
#[UsesClass(LayoutDefinition::class)]
#[UsesClass(TemplateFunction::class)]
#[UsesClass(TemplateVariable::class)]
#[UsesClass(ThemeRequirement::class)]
#[UsesClass(ChartTokens::class)]
#[UsesClass(ColorPair::class)]
#[UsesClass(ColorTokens::class)]
#[UsesClass(ColorUse::class)]
#[UsesClass(ThemeTokens::class)]
#[UsesClass(TokenGroup::class)]
final class HarbourThemeTest extends TestCase
{
    private HarbourTheme $theme;

    protected function setUp(): void
    {
        $this->theme = new HarbourTheme();
    }

    public function testTheManifestIsStableAndProvidesTheRequiredLayouts(): void
    {
        $manifest = $this->theme->manifest();

        self::assertEquals($manifest, $this->theme->manifest());
        foreach (LayoutDefinition::REQUIRED as $id) {
            self::assertNotNull($manifest->layout($id));
        }
        self::assertSame('sidebar', $manifest->resolveLayout('sidebar')->id);
        self::assertSame(LayoutDefinition::FALLBACK, $manifest->resolveLayout('landing')->id);
    }

    public function testDescribesItsPreviewAndShipsNoScreenshot(): void
    {
        $manifest = $this->theme->manifest();

        self::assertNotNull($manifest->previewDescription);
        self::assertNull($manifest->screenshot);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->theme->paths()->root, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            self::assertInstanceOf(\SplFileInfo::class, $file);
            self::assertDoesNotMatchRegularExpression('/^screenshot\./i', $file->getFilename());
        }
    }

    public function testThemeJsonMatchesTheSchemaAndTheManifest(): void
    {
        $json = $this->themeJson();

        self::assertSame([], SchemaValidator::errors($json));

        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        self::assertSame($this->theme->manifest()->id, $data['name']);
        self::assertSame($this->theme->manifest()->version, $data['version']);
    }

    public function testThemeJsonBuildsTheTokenValueObjects(): void
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($this->themeJson(), true, 32, JSON_THROW_ON_ERROR);
        $tokens = TokensFromJson::build($data);

        self::assertSame('#ffffff', $tokens->colors->color('background', ColorMode::Light));
        self::assertSame('#000000', $tokens->colors->color('background', ColorMode::DarkMore));
        self::assertSame('1.125rem', $tokens->value(TokenGroup::Typography, 'size-base'));
        self::assertNotEmpty($tokens->pairs(ColorUse::Text));
        self::assertNotEmpty($tokens->pairs(ColorUse::Ui));
        self::assertSame(['primary', 'success', 'warning', 'error'], $tokens->chart->series);
        self::assertCount(count($tokens->contrast) + 10, $tokens->checkedPairs());
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function requiredTemplates(): iterable
    {
        foreach (RequiredTemplates::files() as $file) {
            yield $file => [$file];
        }
    }

    #[DataProvider('requiredTemplates')]
    public function testEveryRequiredTemplateExistsAndDefinesItsBlocks(string $file): void
    {
        $template = RequiredTemplates::find($file);
        self::assertNotNull($template);

        $path = $this->theme->paths()->templates() . '/' . $file;
        self::assertFileExists($path);
        $source = (string) file_get_contents($path);
        foreach ($template->blocks as $block) {
            self::assertMatchesRegularExpression(sprintf('/\{%%-?\s*block\s+%s\b/', $block), $source, $block);
        }
    }

    public function testEveryLayoutOfTheManifestExistsAndDefinesItsBlocks(): void
    {
        foreach ($this->theme->manifest()->layouts as $layout) {
            $path = $this->theme->paths()->templates() . '/' . $layout->template();
            self::assertFileExists($path);
            $source = (string) file_get_contents($path);
            self::assertStringContainsString("{% extends 'master.html.twig' %}", $source);
            foreach (RequiredTemplates::forLayout($layout)->blocks as $block) {
                self::assertMatchesRegularExpression(sprintf('/\{%%-?\s*block\s+%s\b/', $block), $source, $block);
            }
        }
    }

    public function testTheSearchFormIsASearchLandmarkAndTheResultsHaveNoLiveRegion(): void
    {
        $templates = $this->theme->paths()->templates();

        self::assertStringContainsString('<search ', (string) file_get_contents($templates . '/' . RequiredTemplates::SEARCH_FORM));
        self::assertStringNotContainsString('role=', (string) file_get_contents($templates . '/layouts/search.html.twig'));
    }

    public function testTheShellIncludesTheCorePartials(): void
    {
        foreach (CorePartial::cases() as $partial) {
            $source = (string) file_get_contents($this->theme->paths()->templates() . '/' . $partial->includedBy());

            self::assertStringContainsString(sprintf("include '%s'", $partial->value), $source);
        }
    }

    public function testTemplatesUseOnlyDocumentedFunctions(): void
    {
        $builtIns = ['block', 'include', 'source', 'range', 'constant', 'parent', 'attribute', 'max', 'min', 'random', 'date', 'cycle', 'dump'];
        foreach (RequiredTemplates::files() as $file) {
            $source = (string) file_get_contents($this->theme->paths()->templates() . '/' . $file);
            // Function calls only: names right after "|" are filters and after "." are attribute calls.
            preg_match_all('/(?<![|.\w])([a-z_]+)\(/', $source, $matches);
            foreach ($matches[1] as $function) {
                self::assertTrue(
                    in_array($function, $builtIns, true) || TemplateApi::function($function) !== null,
                    sprintf('%s calls %s(), which core does not document.', $file, $function),
                );
            }
        }
    }

    public function testTheSchemaFileIsShippedWithItsId(): void
    {
        /** @var array{'$id': string} $schema */
        $schema = json_decode((string) file_get_contents(ThemeSchema::file()), true, 64, JSON_THROW_ON_ERROR);

        self::assertSame(ThemeSchema::ID, $schema['$id']);
    }

    private function themeJson(): string
    {
        return (string) file_get_contents($this->theme->paths()->tokensFile());
    }
}
