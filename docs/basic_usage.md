# Basic usage

## Defining languages

This library relies on settings from `Config\App::defaultLocale` and `Config\App::supportedLocales`.

The current language is based on actual HTTP request.

## Tables design

To easily describe how this library is working, we will use an example where we have articles with `title` and `content` fields that should be translated.

We will need two tables:

- Table `articles`
    - `id`
    - `author`
    - `created_at`
    - `updated_at`

- Table `article_translations`
    - `id`
    - `article_id`
    - `locale`
    - `title`
    - `content`

Each table will have its own model.

!!! note

    You can generate the skeleton for the translations based on the main table name. To do so use `php spark translatable:generate` command.

    For the details, please see: [Generating the skeleton](configuration.md#generating-the-skeleton) section in the configuration page.

## Defining models

The main model should use `HasTranslations` trait and then the `initialize()` method should be used to initialize a library, like in the below example:

```php
<?php

namespace App\Models;

// ...

class ArticleModel extends Model
{
    use HasTranslations;

    protected $table         = 'articles';
    protected $primaryKey    = 'id';
    protected $returnType    = Article::class;
    protected $allowedFields = ['author'];
    protected $useTimestamps = true;

    // ...

    protected function initialize(): void
    {
        $this->initTranslations(ArticleTranslationModel::class);
    }

    // ...
}
```

Now the model with translations:

```php
<?php

namespace App\Models;

// ...

class ArticleTranslationModel extends Model
{
    protected $table         = 'article_translations';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = ['article_id', 'locale', 'title', 'content'];

    // ...
}
```

## Defining the entity

Using the entity is not required but it might be handy. We use it only for the main model and it should use `TranslatableEntity` trait:

```php
<?php

namespace App\Entities;

// ...

class Article extends Entity
{
    use TranslatableEntity;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [];


}

```

## Selecting the data

We will assume that our request locale is recognized as `en` and our `supportedLocalse` are set to `['en', 'pl', 'de']`.

The usage will be very basic. The model will load only translations for the current locale (`en`):

```php
$article = model(ArticleModel::class)->find(1);
// will print author
echo $article->author;
// will print "en" title
echo $article->translate()->title;
```

But we can also load all translations:
```php
$article = model(ArticleModel::class)->withAllTranslations()->find(1);
// will print author
echo $article->author;
// will print "en" title
echo $article->translate()->title;
// will print "en" title
echo $article->translate('en')->title;
// will print "pl" title
echo $article->translate('pl')->title;
// will print "de" title
echo $article->translate('de')->title;
```
