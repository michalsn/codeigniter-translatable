<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Tests\Support\Models\ArticleModel;

class SeedTests extends Seeder
{
    public function run()
    {
        config('App')->supportedLocales = ['en', 'pl'];

        $data = [
            [
                'author'       => 'Test User 1',
                'translations' => [
                    'en' => [
                        'title'   => 'Sample title 1',
                        'content' => 'Sample content 1',
                    ],
                    'pl' => [
                        'title'   => 'Przykładowy tytuł 1',
                        'content' => 'Przykładowa treść 1',
                    ],
                ],
            ],
            [
                'author'       => 'Test User 2',
                'translations' => [
                    'en' => [
                        'title'   => 'Sample title 2',
                        'content' => 'Sample content 2',
                    ],
                    'pl' => [
                        'title'   => 'Przykładowy tytuł 2',
                        'content' => 'Przykładowa treść 2',
                    ],
                ],
            ],
            [
                'author'       => 'Test User 3',
                'translations' => [
                    'en' => [
                        'title'   => 'Sample title 3',
                        'content' => 'Sample content 3',
                    ],
                ],
            ],
        ];

        $model = model(ArticleModel::class);

        foreach ($data as $item) {
            $model->insert($item);
        }
    }
}
