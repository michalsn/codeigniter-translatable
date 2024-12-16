# Entity

When the `TranslatableEntity` trait is used, the entity receives new methods.

## Methods

### translate()

By default, will return the translation for the current request locale.

```php
$article = model(ArticleModel::class)->withAllTranslations()->find(1);
// will return title for "en" locale
$article->translate()->title;
// will return title for "pl" locale
$article->translate('pl')->title;
```

### hasTranslation()

Checks if given locale is available.

```php
$article = model(ArticleModel::class)->withAllTranslations()->find(1);
// will return: true
$article->hasTranslation('en');
```

### getTranslationKeys()

Returns available translations keys.

```php
$article = model(ArticleModel::class)->withAllTranslations()->find(1);
// will return: ['en', 'pl']
$article->getTranslationKeys();
```

## Working with translations

```php
$articleModel = model(ArticleModel::class);
/** @var Article $article */
$article = $articleModel
    ->withAllTranslations()
    ->find(1);

$article->translate()->title = 'Updated sample title';
$article->translate('pl')->title = 'Zaktualizowany tytuł';

$articleModel->save($article);
```
