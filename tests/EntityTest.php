<?php

declare(strict_types=1);

namespace Tests;

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Michalsn\CodeIgniterTranslatable\Exceptions\TranslatableException;
use Tests\Support\Database\Seeds\SeedTests;
use Tests\Support\Models\ArticleModel;

/**
 * @internal
 */
final class EntityTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $namespace;
    protected $seed = SeedTests::class;

    protected function setUp(): void
    {
        parent::setUp();

        $config                   = config('App');
        $config->supportedLocales = ['en', 'pl'];
        Factories::injectMock('config', 'App', $config);
    }

    public function testTranslation(): void
    {
        $result = (new ArticleModel())->find(1);

        $this->assertSame('Test User 1', $result->author);
        $this->assertSame('Sample title 1', $result->translate()->title);
    }

    public function testTranslationAll(): void
    {
        $config                = config('App');
        $config->defaultLocale = 'pl';
        Factories::injectMock('config', 'App', $config);

        $results = (new ArticleModel())->setFallbackLocale('en')->findAll();

        $this->assertSame('Test User 1', $results[0]->author);
        $this->assertSame('Przykładowy tytuł 1', $results[0]->translate()->title);

        $this->assertSame('Test User 3', $results[2]->author);
        $this->assertSame('Sample title 3', $results[2]->translate()->title);
    }

    public function testTranslationWithAllTranslations(): void
    {
        $result = (new ArticleModel())->withAllTranslations()->find(1);

        $this->assertSame('Test User 1', $result->author);
        $this->assertSame('Sample title 1', $result->translate()->title);
        $this->assertSame('Przykładowy tytuł 1', $result->translate('pl')->title);
        $this->assertSame(['en', 'pl'], $result->getTranslationKeys());
    }

    public function testHasTranslation(): void
    {
        $result = (new ArticleModel())->find(1);

        $this->assertSame('Test User 1', $result->author);
        $this->assertTrue($result->hasTranslation('en'));
        $this->assertFalse($result->hasTranslation('pl'));
    }

    public function testTranslateException(): void
    {
        $this->expectException(TranslatableException::class);
        $this->expectExceptionMessage('The "jp" locale is not in the list of supported locales.');

        $result = (new ArticleModel())->find(1);

        $this->assertSame('error', $result->translate('jp')->title);
    }
}
