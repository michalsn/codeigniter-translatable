# Model

When the `HasTransactions` trait is used, the model receives new methods.

## Query methods

### withAllTranslations()

Will load all translations for model.

```php
model(ArticleModel::class)->withAllTranslations()->find(1);
```

### withTranslations()

Will load only given translations from the parameter.

```php
model(ArticleModel::class)->withTranslations(['en', 'pl'])->find(1);
```

### useFallbackLocale()

While this option can be set globally via the config file, here you can change the setting only for a given query.

When given translation is not found it will fall back to the `$fallbackLocale`.

```php
// if no "pl" translation is found, it will fall back
// to the locale recognized in the request
model(ArticleModel::class)
    ->useFallbackLocale()
    ->withTranslations(['pl'])
    ->find(1);
```

### setFallbackLocale()

While this option can be set globally via the config file, here you can change the setting only for a given query.

We can set manually what locale will be used when the given translation is not found.

```php
// if no "pl" translation is found, it will fall back to "en"
model(ArticleModel::class)
    ->setFallbackLocale('en')
    ->withTranslations(['pl'])
    ->find(1);
```

### useFillOnEmpty()

While this option can be set globally via the config file, here you can change the setting only for a given query.

When the translation is not found, we may want to return an empty object with properties.

```php
// if no "pl" translation is found, it will fall back to an empty object
model(ArticleModel::class)
    ->useFillOnEmpty()
    ->withTranslations(['pl'])
    ->find(1);
```

## Search methods

### whereTranslation()

This works like normal `where()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->whereTranslation('title', 'Sample 1')
    ->find(1);
```

### orWhereTranslation()

This works like normal `orWhere()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->whereTranslation('title', 'Sample 1')
    ->orWhereTranslation('title', 'Sample 2')
    ->find(1);
```

### whereInTranslation()

This works like normal `whereIn()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->whereInTranslation('title', ['Sample 1', 'Sample 2'])
    ->find(1);
```

### whereNotInTranslation()

This works like normal `whereNotIn()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->whereNotInTranslation('title', ['Sample 1', 'Sample 2'])
    ->find(1);
```

### likeTranslation()

This works like normal `like()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->likeTranslation('title', 'Sample', 'after')
    ->find(1);
```

### orLikeTranslation()

This works like normal `orLike()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->likeTranslation('title', 'Sample 1')
    ->orLikeTranslation('title', 'Sample 2')
    ->find(1);
```

### notLikeTranslation()

This works like normal `orLike()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->notLikeTranslation('title', 'Sample 1')
    ->find(1);
```

### orNotLikeTranslation()

This works like normal `orNotLike()` method, but relates to the translated content only.

```php
model(ArticleModel::class)
    ->notLikeTranslation('title', 'Sample 1')
    ->orNotLikeTranslation('title', 'Sample 2')
    ->find(1);
```

## Working with result data

The translated content is always available via the property or array key with name `translations`.

```php
$article = model(ArticleModel::class)
    ->asArray()
    ->withAllTranslations()
    ->find(1);

// will return:
[
    'id'           => '1',
    'author'       => 'Sample user 1'
    'created_at'   => '...',
    'updated_at'   => '...',
    'translations' => [
        'en' => (object) [
            'title'   => 'Sample 1',
            'content' => 'Content 1',
        ],
        'pl' => (object) [
            'title'   => 'Przykład 1',
            'content' => 'Treść 1',
        ],
    ]
];
```

The only difference is when we use the entity with `TranslatableEntity` trait. Then we have some helpful methods that make working with translations more comfortable. You can read about it [here](entity.md).

## Inserting / Updating the data

As long as you will stick to the returned structure, you can also update or insert the whole model at once. Translations will be updated automatically - as long as locale keys are listed in the `Config\App::supportedLocales` array.

```php
$articleModel = model(ArticleModel::class);
$article      = $articleModel
    ->asArray()
    ->withAllTranslations()
    ->find(1);

$article['translations']['en']->title = 'Updated sample';

$articleModel->save($article);

```
