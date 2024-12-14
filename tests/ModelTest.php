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
final class ModelTest extends CIUnitTestCase
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

    public function testModelInstance(): void
    {
        $model = new ArticleModel();
        $this->assertInstanceOf(ArticleModel::class, $model);
    }

    public function testFindAsArray()
    {
        $result = (new ArticleModel())
            ->asArray()
            ->find(1);

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(1, $result['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result['translations']['en']);
    }

    public function testFindAllAsArray()
    {
        $result = (new ArticleModel())
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testFindAsArrayWithAllTranslations()
    {
        $result = (new ArticleModel())
            ->asArray()
            ->withAllTranslations()
            ->find(1);

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
                'pl' => [
                    'id'      => '2',
                    'title'   => 'Przykładowy tytuł 1',
                    'content' => 'Przykładowa treść 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(2, $result['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result['translations']['en']);
        $this->assertSame($expected['translations']['pl'], (array) $result['translations']['pl']);
    }

    public function testFindAsObject()
    {
        $result = (new ArticleModel())
            ->asObject()
            ->find(1);

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result->author);
        $this->assertCount(1, $result->translations);
        $this->assertSame($expected['translations']['en'], (array) $result->translations['en']);
    }

    public function testFindWithAllTranslations()
    {
        $result = (new ArticleModel())
            ->asObject()
            ->withAllTranslations()
            ->find(1);

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
                'pl' => [
                    'id'      => '2',
                    'title'   => 'Przykładowy tytuł 1',
                    'content' => 'Przykładowa treść 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result->author);
        $this->assertCount(2, $result->translations);
        $this->assertSame($expected['translations']['en'], (array) $result->translations['en']);
        $this->assertSame($expected['translations']['pl'], (array) $result->translations['pl']);
    }

    public function testFindWithNoFallback()
    {
        $result = (new ArticleModel())
            ->withTranslations(['pl'])
            ->asArray()
            ->find(3);

        $expected = [
            'author'       => 'Test User 3',
            'translations' => [
                'pl' => [
                    'id'      => '1',
                    'title'   => 'Sample title 3',
                    'content' => 'Sample content 3',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertSame([], $result['translations']);
    }

    public function testFindWithFillOnEmpty()
    {
        $config                = config('Translatable');
        $config->fillWithEmpty = true;
        Factories::injectMock('config', 'Translatable', $config);

        $result = (new ArticleModel())
            ->withTranslations(['pl'])
            ->asArray()
            ->find(3);

        $expected = [
            'author'       => 'Test User 3',
            'translations' => [
                'pl' => [
                    'id'      => null,
                    'title'   => '',
                    'content' => '',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(1, $result['translations']);
        $this->assertSame('', $result['translations']['pl']['title']);
    }

    public function testFindWithUseFallbackLocale()
    {
        $config                    = config('Translatable');
        $config->useFallbackLocale = true;
        Factories::injectMock('config', 'Translatable', $config);

        $result = (new ArticleModel())
            ->withTranslations(['pl'])
            ->asArray()
            ->find(3);

        $expected = [
            'author'       => 'Test User 3',
            'translations' => [
                'pl' => [
                    'id'      => null,
                    'title'   => 'Sample title 3',
                    'content' => 'Sample content 3',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(1, $result['translations']);
        $this->assertSame($expected['translations']['pl'], (array) $result['translations']['pl']);
    }

    public function testSetCallbackLocale()
    {
        $config                = config('App');
        $config->defaultLocale = 'pl';
        Factories::injectMock('config', 'App', $config);

        $result = (new ArticleModel())
            ->setFallbackLocale('en')
            ->asArray()
            ->find(3);

        $expected = [
            'author'       => 'Test User 3',
            'translations' => [
                'pl' => [
                    'id'      => null,
                    'title'   => 'Sample title 3',
                    'content' => 'Sample content 3',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(1, $result['translations']);
        $this->assertSame($expected['translations']['pl'], (array) $result['translations']['pl']);
    }

    public function testUseWithEmpty()
    {
        $config                = config('App');
        $config->defaultLocale = 'pl';
        Factories::injectMock('config', 'App', $config);

        $result = (new ArticleModel())
            ->useFillOnEmpty()
            ->asArray()
            ->find(3);

        $expected = [
            'author'       => 'Test User 3',
            'translations' => [
                'pl' => [
                    'id'      => null,
                    'title'   => 'Sample title 3',
                    'content' => 'Sample content 3',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result['author']);
        $this->assertCount(1, $result['translations']);
        $this->assertSame('', $result['translations']['pl']['title']);
    }

    public function testInsert()
    {
        $data = [
            'author'       => 'Test insert',
            'translations' => [
                'en' => [
                    'title'   => 'Insert title 1',
                    'content' => 'Insert content 1',
                ],
                'pl' => [
                    'title'   => 'Dodaj tytuł 1',
                    'content' => 'Dodaj treść 1',
                ],
                'de' => [
                    'title'   => 'Missing',
                    'content' => 'Missing',
                ],
            ],
        ];

        (new ArticleModel())->insert($data);

        $this->seeInDatabase('articles', [
            'author' => 'Test insert',
        ]);

        $this->seeInDatabase('article_translations', [
            'locale' => 'en',
            'title'  => 'Insert title 1',
        ]);

        $this->seeInDatabase('article_translations', [
            'locale' => 'pl',
            'title'  => 'Dodaj tytuł 1',
        ]);

        $this->dontSeeInDatabase('article_translations', [
            'locale' => 'de',
            'title'  => 'Missing',
        ]);
    }

    public function testUpdate()
    {
        $data = [
            'author'       => 'Test update',
            'translations' => [
                'en' => [
                    'title'   => 'Update title 1',
                    'content' => 'Update content 1',
                ],
                'pl' => [
                    'title'   => 'Aktualizuj tytuł 1',
                    'content' => 'Aktualizuj treść 1',
                ],
                'de' => [
                    'title'   => 'Missing',
                    'content' => 'Missing',
                ],
            ],
        ];

        (new ArticleModel())->update(1, $data);

        $this->seeInDatabase('articles', [
            'id'     => 1,
            'author' => 'Test update',
        ]);

        $this->seeInDatabase('article_translations', [
            'article_id' => 1,
            'locale'     => 'en',
            'title'      => 'Update title 1',
        ]);

        $this->seeInDatabase('article_translations', [
            'article_id' => 1,
            'locale'     => 'pl',
            'title'      => 'Aktualizuj tytuł 1',
        ]);

        $this->dontSeeInDatabase('article_translations', [
            'article_id' => 1,
            'locale'     => 'de',
            'title'      => 'Missing',
        ]);
    }

    public function testRelationNotDefined(): void
    {
        $this->expectException(TranslatableException::class);
        $this->expectExceptionMessage('The "jp" locale is not in the list of supported locales.');

        (new ArticleModel())->setFallbackLocale('jp')->find(1);
    }

    public function testWhereTransaction(): void
    {
        $result = (new ArticleModel())
            ->whereTranslation('title', 'Sample title 2')
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 2',
            'translations' => [
                'en' => [
                    'id'      => '3',
                    'title'   => 'Sample title 2',
                    'content' => 'Sample content 2',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testOrWhereTransaction(): void
    {
        $result = (new ArticleModel())
            ->whereTranslation('title', 'Sample title 1')
            ->orWhereTranslation('content', 'Przykładowa treść 2')
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testWhereInTransaction(): void
    {
        $result = (new ArticleModel())
            ->whereInTranslation('title', ['Sample title 2'])
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 2',
            'translations' => [
                'en' => [
                    'id'      => '3',
                    'title'   => 'Sample title 2',
                    'content' => 'Sample content 2',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testWhereNotInTransaction(): void
    {
        $result = (new ArticleModel())
            ->whereNotInTranslation('title', ['Sample title 2'])
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testLikeTransaction(): void
    {
        $result = (new ArticleModel())
            ->likeTranslation('title', '2')
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 2',
            'translations' => [
                'en' => [
                    'id'      => '3',
                    'title'   => 'Sample title 2',
                    'content' => 'Sample content 2',
                ],
            ],
        ];

        $this->assertCount(1, $result);

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testOrLikeTransaction(): void
    {
        $result = (new ArticleModel())
            ->likeTranslation('title', '2')
            ->orLikeTranslation('title', '3')
            ->asArray()
            ->findAll();

        $expected1 = [
            'author'       => 'Test User 2',
            'translations' => [
                'en' => [
                    'id'      => '3',
                    'title'   => 'Sample title 2',
                    'content' => 'Sample content 2',
                ],
            ],
        ];

        $expected2 = [
            'author'       => 'Test User 3',
            'translations' => [
                'en' => [
                    'id'      => '5',
                    'title'   => 'Sample title 3',
                    'content' => 'Sample content 3',
                ],
            ],
        ];

        $this->assertCount(2, $result);

        $this->assertSame($expected1['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected1['translations']['en'], (array) $result[0]['translations']['en']);

        $this->assertSame($expected2['author'], $result[1]['author']);
        $this->assertCount(1, $result[1]['translations']);
        $this->assertSame($expected2['translations']['en'], (array) $result[1]['translations']['en']);
    }

    public function testNotLikeTransaction(): void
    {
        $result = (new ArticleModel())
            ->notLikeTranslation('title', '2')
            ->notLikeTranslation('title', '3')
            ->asArray()
            ->findAll();

        $expected = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $this->assertCount(1, $result);

        $this->assertSame($expected['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected['translations']['en'], (array) $result[0]['translations']['en']);
    }

    public function testOrNotLikeTransaction(): void
    {
        $result = (new ArticleModel())
            ->likeTranslation('title', '2')
            ->orNotLikeTranslation('title', '3')
            ->asArray()
            ->findAll();

        $expected1 = [
            'author'       => 'Test User 1',
            'translations' => [
                'en' => [
                    'id'      => '1',
                    'title'   => 'Sample title 1',
                    'content' => 'Sample content 1',
                ],
            ],
        ];

        $expected2 = [
            'author'       => 'Test User 2',
            'translations' => [
                'en' => [
                    'id'      => '3',
                    'title'   => 'Sample title 2',
                    'content' => 'Sample content 2',
                ],
            ],
        ];

        $this->assertCount(2, $result);

        $this->assertSame($expected1['author'], $result[0]['author']);
        $this->assertCount(1, $result[0]['translations']);
        $this->assertSame($expected1['translations']['en'], (array) $result[0]['translations']['en']);

        $this->assertSame($expected2['author'], $result[1]['author']);
        $this->assertCount(1, $result[1]['translations']);
        $this->assertSame($expected2['translations']['en'], (array) $result[1]['translations']['en']);
    }
}
